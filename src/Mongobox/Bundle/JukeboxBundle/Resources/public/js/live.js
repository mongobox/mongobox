function onPlayerStateChange(event)
{
    livePlayer.updateVolumeControl(Math.round(player.getVolume()));

    if (playerMode != 'admin') {
        return this;
    }

	var params = new Object();
	params.status = event.data;

	if (event.data != 0) {
		params.currentTime = player.getCurrentTime();
		livePlayer.sendParameters(params);
	} else {
		livePlayer.seekNextVideo(params);
	}
}

var livePlayer;
var connection;
var playlistId;
var videoId;

LivePlayer = function()
{
	this.initialize = function(currentPlaylistId)
	{
		playlistId = currentPlaylistId;
		this.getPlaylistScores(currentPlaylistId);

		this.initializeVideoRating();
        this.initializeVolumeControl();
	},

    this.initializeVideoRating = function()
    {
        $('#up-vote').unbind('click');
        $('#up-vote').click(function(event) {
            event.preventDefault();
            var playlistId = this.getCurrentPlaylistId();

            $.post(voteUrl, {
                playlist: playlistId,
                vote: 'up',
                current: 1
            }, function(response) {
                this.playlistScoresUpdate(response);
            }.bind(this));
        }.bind(this));

        $('#down-vote').unbind('click');
        $('#down-vote').click(function(event) {
            event.preventDefault();
            var playlistId = this.getCurrentPlaylistId();

            $.post(voteUrl, {
                playlist: playlistId,
                vote: 'down',
                current: 1
            }, function(response) {
                this.playlistScoresUpdate(response);
            }.bind(this));
        }.bind(this));
    },

    this.initializeVolumeControl = function()
    {
        this.controlUsed = '';

        $('#up-volume').unbind('click');
        $('#up-volume').click(function(event) {
            if (this.controlUsed === 'down') {
                var increase = 10;
            } else {
                var increase = 5;
            }

            var newVolume = Math.round(player.getVolume() + increase);

            if (this.controlUsed != 'up' && newVolume <= 100) {
                player.setVolume(newVolume);
                this.controlUsed = 'up';

                this.synchronizePlayerVolume(newVolume);
            }
        }.bind(this));

        $('#down-volume').unbind('click');
        $('#down-volume').click(function(event) {
            if (this.controlUsed === 'up') {
                var decrease = 10;
            } else {
                var decrease = 5;
            }

            var newVolume = Math.round(player.getVolume() - decrease);

            if (this.controlUsed != 'down' && newVolume >= 5) {
                player.setVolume(newVolume);
                this.controlUsed = 'down';

                this.synchronizePlayerVolume(newVolume);
            }
        }.bind(this));
    }

	this.synchronizePlayerState = function(params)
	{
		if (params.action === 'update_scores') {
			var scores = JSON.parse(params.scores);
			this.updatePlaylistScores(scores);
		}

        if (params.action === 'update_volume') {
            player.setVolume(params.volume);
            this.updateVolumeControl(params.volume);
        }

		if (playerMode == 'admin') {
            return this;
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

                this.updateVolumeControl(params.videoVolume);

                this.initialize(params.playlistId);

            break;
        }
	},

	this.getCurrentPlaylistId = function()
	{
		return playlistId;
	},

	this.getCurrentVideoId = function()
	{
		return videoId;
	},

	this.seekNextVideo = function(params)
	{
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: nextVideoUrl,
            data: { 'volume' : player.getVolume() }
        }).done(function(data) {
            player.loadVideoById({
                videoId: data.videoId
            });

            params.playlistId = data.playlistId;
            params.videoId = data.videoId;

            player.setVolume(data.videoVolume);
            params.videoVolume = data.videoVolume;

            this.updateVolumeControl(data.videoVolume);

            this.sendParameters(params);

            this.initialize(data.playlistId);
        }.bind(this));
	},

	this.sendParameters = function(params)
	{
		json = JSON.stringify(params);
		connection.send(json);
	},

	this.getPlaylistScores = function(playlistId)
	{
		$.get(scoreUrl, {
			playlist: playlistId
        }, function(response) {
			scores = JSON.parse(response);
			this.updatePlaylistScores(scores);
		}.bind(this));
	},

	this.updatePlaylistScores = function(scores)
	{
		$('#up-score').text('(' + scores.upVotes + ')');
		$('#down-score').text('(' + scores.downVotes + ')');
		$('#video-score').text('Score : ' + scores.votesRatio);

		if (scores.votesRatio <= -2 && playerMode == 'admin') {
			var params = new Object();
			params.status = 0;

			livePlayer.seekNextVideo(params);
		}
	},

	this.playlistScoresUpdate = function(data)
	{
		scores = JSON.parse(data);
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

    this.synchronizePlayerVolume = function(volume)
    {
        this.updateVolumeControl(volume);

        var params = new Object();
        params.action	= 'update_volume';
        params.volume   = volume;

        this.sendParameters(params);
    },

    this.updateVolumeControl = function(volume)
    {
        $('#video-volume').text('Volume : ' + volume + '%');
    }
};
