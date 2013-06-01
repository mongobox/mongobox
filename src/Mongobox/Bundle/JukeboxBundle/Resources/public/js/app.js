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

    socket.on('scores updated', function (scores) {
        socket.broadcast.emit('synchronize scores', scores);
    });

    socket.on('volume updated', function (volume) {
        socket.broadcast.emit('synchronize volume', volume);
    });

    socket.on('disconnect', function() {
        console.log('disconnect');
    });
});

/*
    1°) Synchronisation des players
    2°) Votes sur la chanson en cours           => OK
    3°) Augmentation / diminution du volume     => OK
    4°) Système de putsch
*/
