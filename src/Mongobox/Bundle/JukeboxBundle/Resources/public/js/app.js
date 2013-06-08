var app = require('http').createServer(handler);
var io  = require('socket.io').listen(app);
var fs  = require('fs');

app.listen(8080);

function handler(req, res) {
    fs.readFile(__dirname + '/index.html', function (err, data) {
        if (err) {
            res.writeHead(500);
            return res.end('Error loading index.html');
        }

        res.writeHead(200);
        res.end(data);
    });
}

var users = {};

io.sockets.on('connection', function(socket) {
    var currentUser = false;
    var roomId;

    for (var id in users) {
        socket.emit('user subscription', users[id]);
    }

    socket.on('subscribe', function(data) {
        currentUser     = data.user;
        roomId          = data.room;

        socket.join(roomId);
        socket.in(roomId).broadcast.emit('user subscription', currentUser);

        users[currentUser.id] = currentUser;
    });

    socket.on('player updated', function(params) {
        socket.in(roomId).broadcast.emit('synchronize player', params);
    });

    socket.on('scores updated', function(scores) {
        socket.in(roomId).broadcast.emit('synchronize scores', scores);
    });

    socket.on('volume updated', function(volume) {
        socket.in(roomId).broadcast.emit('synchronize volume', volume);
    });

    socket.on('putsch started', function() {
        socket.in(roomId).broadcast.emit('putsch attempt', currentUser.id);
    });

    socket.on('putsch noticed', function(userId) {
        socket.in(roomId).broadcast.emit('putsch acknowledgment', userId);
    });

    socket.on('putsch accepted', function(userId) {
        socket.in(roomId).broadcast.emit('putsch done', userId);
    });

    socket.on('putsch refused', function(userId) {
        socket.in(roomId).broadcast.emit('putsch failed', userId);
    });

    socket.on('disconnect', function() {
        if (currentUser === false) {
            return false;
        }

        delete users[currentUser.id];
        socket.in(roomId).broadcast.emit('user unsubscription', currentUser);
    });
});

