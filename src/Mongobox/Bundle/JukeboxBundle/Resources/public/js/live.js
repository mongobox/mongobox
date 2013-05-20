var livePlayer;
var connection;

LivePlayer = function()
{
	this.initialize = function(currentPlaylistId, currentUserId)
	{
        this.playlistId = currentPlaylistId;
        this.userId     = currentUserId;

		this.getPlaylistScores(currentPlaylistId);
        this.synchronizePlayerVolume();

		this.initializeVideoRating();
        this.initializeVolumeControl();

        $("#putsch-button").click(function(event) {
            event.preventDefault();
            this.sendPutschAttempt();
        }.bind(this));
	},

    this.initializeVideoRating = function()
    {
        $('#up-vote').unbind('click');
        $('#up-vote').click(function(event) {
            event.preventDefault();

            $.post(voteUrl, {
                playlist: this.playlistId,
                vote: 'up',
                current: 1
            }, function(response) {
                this.playlistScoresUpdate(response);
            }.bind(this));
        }.bind(this));

        $('#down-vote').unbind('click');
        $('#down-vote').click(function(event) {
            event.preventDefault();

            $.post(voteUrl, {
                playlist: this.playlistId,
                vote: 'down',
                current: 1
            }, function(response) {
                this.playlistScoresUpdate(response);
            }.bind(this));
        }.bind(this));
    },

    this.initializeVolumeControl = function()
    {
        $('#up-volume').unbind('click');
        $('#up-volume').click(function(event) {
            event.preventDefault();
            this.updatePlayerVolume('up');
        }.bind(this));

        $('#down-volume').unbind('click');
        $('#down-volume').click(function(event) {
            event.preventDefault();
            this.updatePlayerVolume('down');
        }.bind(this));
    },

	this.synchronizePlayerState = function(params)
	{
		if (params.action === 'update_scores') {
			var scores = JSON.parse(params.scores);
			this.updatePlaylistScores(scores);
		}

        if (params.action === 'update_volume') {
            this.synchronizePlayerVolume();
        }

        switch(params.status) {
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
                    videoId: params.videoId,
                    volume: params.videoVolume
                });

                this.synchronizePlayerVolume();

                this.initialize(params.playlistId);

            break;
        }
	},

	this.sendParameters = function(params)
	{
		var json = JSON.stringify(params);
		connection.send(json);
	},

	this.getPlaylistScores = function(playlistId)
	{
		$.get(scoreUrl, {
			playlist: this.playlistId
        }, function(response) {
			var scores = JSON.parse(response);
			this.updatePlaylistScores(scores);
		}.bind(this));
	},

	this.updatePlaylistScores = function(scores)
	{
		$('#up-score').text('(' + scores.upVotes + ')');
		$('#down-score').text('(' + scores.downVotes + ')');
		$('#video-score').text('Score : ' + scores.votesRatio);
	},

	this.playlistScoresUpdate = function(data)
	{
		var scores = JSON.parse(data);
		this.updatePlaylistScores(scores);

		var params = new Object();
		params.action	= 'update_scores';
		params.scores	= data;

		this.sendParameters(params);
	},

    this.getReplaceForm = function()
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

    this.updatePlayerVolume = function(direction)
    {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: volumeUrl,
            data: {
                'playlist' : this.playlistId,
                'vote': direction
            }
        }).done(function(data) {
            this.updateVolumeControl(data);

            var params = new Object();
            params.action	= 'update_volume';
            params.volume   = data;

            this.sendParameters(params);
        }.bind(this));
    },

    this.synchronizePlayerVolume = function()
    {
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: volumeUrl,
            data: {
                'playlist' : this.playlistId
            }
        }).done(function(data) {
            this.updateVolumeControl(data);
        }.bind(this));
    },

    this.updateVolumeControl = function(data)
    {
        if (typeof player !== 'undefined' && typeof player.setVolume === 'function') {
            player.setVolume(data.currentVolume);
        }

        $('#volume-up-votes').text('(' + data.upVotes + ')');
        $('#volume-down-votes').text('(' + data.downVotes + ')');
        $('#video-volume').text('Volume : ' + data.currentVolume + '%');
    },

    this.sendPutschAttempt = function ()
    {
        var params = new Object();
        params.action   = 'putsch_attempt';
        params.userId   = this.userId;

        this.sendParameters(params);
    }
};
