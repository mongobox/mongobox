var app = require('http').createServer(handler);
var io  = require('socket.io').listen(app);
var fs  = require('fs');

app.listen(8080);

function handler (req, res) {
    fs.readFile(__dirname + '/index.html',
        function (err, data) {
            if (err) {
                res.writeHead(500);
                return res.end('Error loading index.html');
            }

            res.writeHead(200);
            res.end(data);
        });
}

io.sockets.on('connection', function (socket) {
    var user;
    var room;

    socket.on('subscribe', function (data) {
        user    = data.user;
        room    = data.room;

        socket.join(room);
    });

    socket.on('player updated', function (params) {
        socket.in(room).broadcast.emit('synchronize player', params);
    });

    socket.on('scores updated', function (scores) {
        socket.in(room).broadcast.emit('synchronize scores', scores);
    });

    socket.on('volume updated', function (volume) {
        socket.in(room).broadcast.emit('synchronize volume', volume);
    });

    socket.on('putsch started', function () {
        socket.in(room).broadcast.emit('putsch attempt', user);
    });

    socket.on('putsch noticed', function (userId) {
        socket.in(room).broadcast.emit('putsch acknowledgment', userId);
    });

    socket.on('putsch accepted', function (userId) {
        socket.in(room).broadcast.emit('putsch done', userId);
    });

    socket.on('putsch refused', function (userId) {
        socket.in(room).broadcast.emit('putsch failed', userId);
    });
});
