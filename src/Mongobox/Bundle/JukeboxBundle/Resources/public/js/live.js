LivePlayer = function()
{
	this.initialize = function(currentUserId, currentPlaylistId)
	{
        this.userId     = currentUserId;
        this.playlistId = currentPlaylistId;

        this.initializeSocket();

        this.initializeControls();

        this.synchronize('scores');
        this.synchronize('volume');
    },

    this.initializeSocket = function()
    {
        socket.on('synchronize player', function (params) {
            this.synchronizePlayer(params);
        }.bind(this));

        socket.on('synchronize scores', function (scores) {
            this.updateScores(scores);
        }.bind(this));

        socket.on('synchronize volume', function (volume) {
            this.updateVolume(volume);
        }.bind(this));

        socket.on('putsch acknowledgment', function (userId) {
            if (parseInt(userId) === parseInt(this.userId)) {
                clearInterval(this.putschTimer);

                var putschModal = $('#putsch-modal');
                putschModal.find('.modal-content').html($('#putsch-request-callback').html());
                putschModal.modal('show');
            }
        }.bind(this));

        socket.on('putsch done', function (userId) {
            if (parseInt(userId) === parseInt(this.userId)) {
                window.location.reload();
            }
        }.bind(this));

        socket.on('putsch failed', function(userId) {
            if (parseInt(userId) === parseInt(this.userId)) {
                var putschModal = $('#putsch-modal');
                putschModal.find('.modal-content').html($('#putsch-refuse-callback').html());
                putschModal.modal('show');
            }
        }.bind(this));
    },

    this.initializeControls = function()
    {
        $('#up-vote').unbind('click');
        $('#up-vote').click(function(event) {
            event.preventDefault();
            this.sendUserVote('scores', 'up');
        }.bind(this));

        $('#down-vote').unbind('click');
        $('#down-vote').click(function(event) {
            event.preventDefault();
            this.sendUserVote('scores', 'down');
        }.bind(this));

        $('#up-volume').unbind('click');
        $('#up-volume').click(function(event) {
            event.preventDefault();
            this.sendUserVote('volume', 'up');
        }.bind(this));

        $('#down-volume').unbind('click');
        $('#down-volume').click(function(event) {
            event.preventDefault();
            this.sendUserVote('volume', 'down');
        }.bind(this));

        $('#putsch-button').click(function(event) {
            event.preventDefault();
            this.sendPutschAttempt();
        }.bind(this));
    },

    this.synchronize = function(object)
    {
        switch (object) {
            case 'scores':
                $.ajax({
                    type: 'GET',
                    dataType: 'json',
                    url: scoreUrl,
                    data: {
                        'playlist': this.playlistId
                    }
                }).done(function(scores) {
                    this.updateScores(scores);
                    socket.emit('scores updated', scores);
                }.bind(this));
            break;

            case 'volume':
                 $.ajax({
                     type: 'GET',
                     dataType: 'json',
                     url: volumeUrl,
                     data: {
                        'playlist': this.playlistId
                     }
                 }).done(function(volume) {
                     this.updateVolume(volume);
                     socket.emit('volume updated', volume);
                 }.bind(this));
            break;
        }
    },

    this.updateScores = function(scores)
    {
        $('#up-score').text('(' + scores.upVotes + ')');
        $('#down-score').text('(' + scores.downVotes + ')');
        $('#video-score').text('Score : ' + scores.votesRatio);
    },

    this.updateVolume = function(volume)
    {
        if (typeof player !== 'undefined' && typeof player.setVolume === 'function') {
            player.setVolume(volume.currentVolume);
        }

        $('#volume-up-votes').text('(' + volume.upVotes + ')');
        $('#volume-down-votes').text('(' + volume.downVotes + ')');
        $('#video-volume').text('Volume : ' + volume.currentVolume + '%');
    },

    this.sendUserVote = function(object, vote)
    {
        switch (object) {
            case 'scores':
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: voteUrl,
                    data: {
                        'playlist': this.playlistId,
                        'vote': vote
                    }
                }).done(function(scores) {
                    this.updateScores(scores);
                    socket.emit('scores updated', scores);
                }.bind(this));
            break;

            case 'volume':
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: volumeUrl,
                    data: {
                        'playlist': this.playlistId,
                        'vote': vote
                    }
                }).done(function(volume) {
                    this.updateVolume(volume);
                    socket.emit('volume updated', volume);
                }.bind(this));
            break;
        }
    },

    this.synchronizePlayer = function(params)
    {
        switch (params.status) {
            case 1:
                player.seekTo(params.currentTime);
                player.playVideo();
            break;

            case 2:
                player.seekTo(params.currentTime);
                player.pauseVideo();
            break;

            case 0:
                player.loadVideoById({
                    'videoId': params.videoId,
                    'volume': params.videoVolume
                });

                this.playlistId = params.videoId;
            break;
        }
    },

    this.sendPutschAttempt = function ()
    {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: putschEligibilityUrl,
            data: {
                'user' : this.userId
            }
        }).done(function(data) {
            if (data.result === 'allow') {
                socket.emit('putsch started');
                this.waitPutschAcknowledgment();
            } else {
                var putschModal = $('#putsch-modal');
                putschModal.find('.modal-content').html(data.details);
                putschModal.modal('show');
            }
        }.bind(this));
    },

    this.waitPutschAcknowledgment = function()
    {
        var maximumWaiting  = 5;
        var currentWaiting  = 0;

        this.putschTimer = setInterval(function() {
            currentWaiting++;
            if (currentWaiting === maximumWaiting) {
                clearInterval(this.putschTimer);

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: adminSwitchUrl,
                    data: {
                        'user' : this.userId
                    }
                }).done(function(data) {
                    if (data.status === 'done') {
                        window.location.reload();
                    }
                }.bind(this));
            }
        }.bind(this), 1000);
    }
};
