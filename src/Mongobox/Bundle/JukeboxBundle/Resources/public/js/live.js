
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
    },

    this.synchronize = function (object)
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
                this.synchronize('volume');
            break;
        }
    }
};
