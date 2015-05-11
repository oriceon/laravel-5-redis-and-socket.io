@extends('app')

@section('css')
    @parent

    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
    <link href="{{ asset('/css/chat.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <span class="glyphicon glyphicon-comment"></span> Chat
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                            <span class="glyphicon glyphicon-chevron-down"></span>
                        </button>

                        <ul class="dropdown-menu slidedown">
                            <li>
                                <span class="glyphicon glyphicon-ok-sign"></span>
                                Available
                            </li>

                            <li>
                                <span class="glyphicon glyphicon-remove"></span>
                                Busy
                            </li>

                            <li>
                                <span class="glyphicon glyphicon-time"></span>
                                Away
                            </li>

                            <li class="divider"></li>

                            <li>
                                <span class="glyphicon glyphicon-off"></span>
                                Sign Out
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="panel-body">
                    <ul class="chat">

                    </ul>
                </div>

                <div class="panel-footer">
                    <form id="send-message">
                        <div class="input-group">
                            <input id="message-input" type="text" class="form-control input-sm" placeholder="Type your message here..." />

                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-warning btn-sm" id="btn-chat">Send</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <ul class="chatUsers">

            </ul>
        </div>
    </div>
</div>
@endsection

@section('js')
    @parent

    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.socket.io/socket.io-1.3.4.js"></script>

    <script>
        jQuery(function($) {
            var $messageForm = $('#send-message');
            var $messageBox = $('#message-input');
            var $chat = $('ul.chat');
            var $chatUsers = $('ul.chatUsers');

            // open a socket connection
            var socket = new io.connect('http://chat.dev:8080', {
                'reconnection': true,
                'reconnectionDelay': 1000,
                'reconnectionDelayMax' : 5000,
                'reconnectionAttempts': 5
            });

            // when user connect, store the user id and name
            socket.on('connect', function (user) {
                socket.emit('join', {id: "<?= Auth::user()->id ?>", name: "<?= Auth::user()->name ?>"});
            });


            $messageForm.on('submit', function (e) {
                e.preventDefault();

                socket.emit('chat.send.message', {msg: $messageBox.val(), nickname: '<?= Auth::user()->name ?>'});
                $messageBox.val('');
            });


            // get connected users and display to all conected
            socket.on('chat.users', function (nicknames) {
                var html = '';

                $.each(nicknames, function (index, value) {
                    html += '<li><a href="' + value.socketId + '">' + value.nickname + '</a></li>';
                });

                $chatUsers.html(html);
            });


            // wait for a new message and append into each connection chat window
            socket.on('chat.message', function (data) {

                data = JSON.parse(data);

                if(data.hasOwnProperty('system')) {
                    toastr["success"](data.msg);
                } else {
                    $chat.append(
                    '<li class="left clearfix">' +
                        '<span class="chat-img pull-left">' +
                           '<img src="http://placehold.it/50/55C1E7/fff&text=U" alt="User Avatar" class="img-circle" width="50" />' +
                        '</span>' +

                        '<div class="chat-body clearfix">' +
                            '<div class="header">' +
                                '<strong class="primary-font">' + data.nickname + '</strong>' +
                                '<small class="pull-right text-muted">' +
                                '<span class="glyphicon glyphicon-time"></span>' +
                                '12 mins ago' +
                                '</small>' +
                            '</div>' +
                            '<p>' + data.msg + '</p>' +
                        '</div>' +
                    '</li>');
                }
            });
        });
    </script>
@endsection