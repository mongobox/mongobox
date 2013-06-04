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
    socket.on('subscribe', function (data) {
        socket.set('user', data.user);
        socket.set('room', data.room);

        socket.join(data.room);
    });

    socket.on('player updated', function (params) {
        socket.get('room', function (err, roomId) {
            socket.in(roomId).broadcast.emit('synchronize player', params);
        });
    });

    socket.on('scores updated', function (scores) {
        socket.get('room', function (err, roomId) {
            socket.in(roomId).broadcast.emit('synchronize scores', scores);
        });
    });

    socket.on('volume updated', function (volume) {
        socket.get('room', function (err, roomId) {
            socket.in(roomId).broadcast.emit('synchronize volume', volume);
        });
    });

    socket.on('putsch started', function () {
        socket.get('room', function (err, roomId) {
            socket.get('user', function (err, userId) {
                socket.in(roomId).broadcast.emit('putsch attempt', userId);
            });
        });
    });

    socket.on('putsch noticed', function (userId) {
        socket.get('room', function (err, roomId) {
            socket.in(roomId).broadcast.emit('putsch acknowledgment', userId);
        });
    });

    socket.on('putsch accepted', function (userId) {
        socket.get('room', function (err, roomId) {
            socket.in(roomId).broadcast.emit('putsch done', userId);
        });
    });

    socket.on('putsch refused', function (userId) {
        socket.get('room', function (err, roomId) {
            socket.in(roomId).broadcast.emit('putsch failed', userId);
        });
    });
});
