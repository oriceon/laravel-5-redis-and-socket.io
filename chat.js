var app     = require('express')(),
    http    = require('http').Server(app),
    io      = require('socket.io')(http),
    Redis   = require('ioredis'),
    redis   = new Redis();

var port = 8080,
    users = nicknames = {};

http.listen(port, function() {
    console.log('Listening on *:' + port);
});


io.on('connection', function (socket) {

    socket.on('join', function (user) {

        console.info('New client connected (id=' + user.id + ' (' + user.name + ') => socket=' + socket.id + ').');

        // save socket to emit later on a specific one
        socket.userId   = user.id;
        socket.nickname = user.name;

        users[user.id] = socket;

        // store connected nicknames
        nicknames[user.id] = {
            'nickname': user.name,
            'socketId': socket.id,
        };


        function updateNicknames() {
            // send connected users to all sockets to display in nickname list
            io.sockets.emit('chat.users', nicknames);
        }

        updateNicknames();


        // subscribe connected user to a specific channel, later he can receive message directly from our ChatController
        redis.subscribe(['chat.message', 'chat.private'], function (err, count) {

        });

        // get messages send by ChatController
        redis.on("message", function (channel, message) {
            console.log('Receive message %s from system in channel %s', message, channel);

            socket.emit(channel, message);
        });


        // get user sent message and broadcast to all connected users
        socket.on('chat.send.message', function (message) {
            console.log('Receive message ' + message.msg + ' from user in channel chat.message');

            io.sockets.emit('chat.message', JSON.stringify(message));
        });


        socket.on('disconnect', function() {
            if( ! socket.nickname) return;

            delete users[user.id];
            delete nicknames[user.id];

            updateNicknames();

            console.info('Client gone (id=' + user.id+ ' => socket=' + socket.id + ').');
        });

    });
});