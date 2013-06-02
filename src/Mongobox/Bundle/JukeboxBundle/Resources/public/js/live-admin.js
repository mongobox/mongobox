$(document).ready(function() {
    $("#play-video-button").click(function() {
        player.playVideo();
    });

    $("#pause-video-button").click(function() {
        player.pauseVideo();
    });

    $("#skip-video-button").click(function() {
        var params = new Object();
        params.status = 0;

        livePlayer.seekNextVideo(params);
    });

    $("#replace-video-button").click(function() {
        livePlayer.getReplaceForm();
    });

    $('#admin-tab a').click(function(event) {
        event.preventDefault();
        $(this).tab('show');
    });

    $('#list_tags').dataTable({
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bSort": false,
        "bInfo": false,
        "bAutoWidth": true,
        "bStateSave": true
    });

    livePlayer.seekNextVideo = function(params)
    {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: nextVideoUrl,
            data: {
                'volume' : player.getVolume()
            }
        }).done(function(data) {
            player.loadVideoById({
                videoId: data.videoId
            });

            this.playlistId = data.playlistId;

            params.playlistId = data.playlistId;
            params.videoId = data.videoId;

            socket.emit('player updated', params);

            this.synchronize('scores');
            this.synchronize('volume');
        }.bind(this));
    };

    livePlayer.updateScores = function(scores)
    {
        $('#up-score').text('(' + scores.upVotes + ')');
        $('#down-score').text('(' + scores.downVotes + ')');
        $('#video-score').text('Score : ' + scores.votesRatio);

        if (scores.votesRatio <= worstScoreAllowed) {
            var params = new Object();
            params.status = 0;

            livePlayer.seekNextVideo(params);
        }
    };

    livePlayer.getReplaceForm = function()
    {
        $('#replace-video-modal').on('show', function () {
            $('.loader').show();
            $('#replace-video-modal .modal-content').html('');

            $.ajax({
                type: 'GET',
                dataType: 'html',
                url: replaceUrl
            }).done(function(html) {
                $('#replace-video-modal .modal-content').html(html);
                $('.loader').hide();
            });
        });
    }

    socket.on('putsch attempt', function (userId) {
        livePlayer.receivePutschAttempt(userId);
    }.bind(this));

    livePlayer.receivePutschAttempt = function(userId)
    {
        socket.emit('putsch noticed', userId);

        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: putschUrl,
            data: {
                'user': userId
            }
        }).done(function(html) {
            if (html !== '') {
                $('#putsch-modal').modal('show');
                $('#putsch-modal .loader').show();
                $('#putsch-modal').html(html);
                $('#putsch-modal .loader').hide();
            }
        }.bind(this));
    };

    livePlayer.acceptPutsch = function(userId)
    {
        $('#putsch-modal').modal('hide');

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: putschResponseUrl,
            data: {
                'user': userId,
                'response': 1
            }
        }).done(function(data) {
            if (data.status === "done") {
                socket.emit('putsch accepted', userId);
                window.location.reload();
            }
        }.bind(this));
    };

    livePlayer.refusePutsch = function(userId)
    {
        $('#putsch-modal').modal('hide');
        $('#putsch-video-modal .modal-content').html('');

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: putschResponseUrl,
            data: {
                'user': userId,
                'response': 0
            }
        }).done(function(data) {
            if (data.status === "done") {
                socket.emit('putsch refused', userId);
            }
        }.bind(this));
    };
});
