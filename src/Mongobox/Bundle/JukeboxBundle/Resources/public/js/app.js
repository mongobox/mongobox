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
    socket.on('set user', function (userId) {
        socket.set('user', userId);
    });

    socket.on('player updated', function (params) {
        socket.broadcast.emit('synchronize player', params);
    });

    socket.on('scores updated', function (scores) {
        socket.broadcast.emit('synchronize scores', scores);
    });

    socket.on('volume updated', function (volume) {
        socket.broadcast.emit('synchronize volume', volume);
    });

    socket.on('putsch started', function () {
        socket.get('user', function (err, userId) {
            socket.broadcast.emit('putsch attempt', userId);
        });
    });

    socket.on('putsch noticed', function (userId) {
        socket.broadcast.emit('putsch acknowledgment', userId);
    });

    socket.on('putsch accepted', function (userId) {
        socket.broadcast.emit('putsch done', userId);
    });

    socket.on('putsch refused', function (userId) {
        socket.broadcast.emit('putsch failed', userId);
    });

    socket.on('disconnect', function() {
        console.log('disconnect');
    });
});
