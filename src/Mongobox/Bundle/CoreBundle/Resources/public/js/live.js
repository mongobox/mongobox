function onPlayerStateChange(event)
{
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

		$('#up-vote a').unbind('click');
		$('#up-vote a').click(function(event) {
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

		$('#down-vote a').unbind('click');
		$('#down-vote a').click(function(event) {
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

	this.synchronizePlayerState = function(params)
	{
		if (params.action == 'update_scores') {
			var scores = JSON.parse(params.scores);
			this.updatePlaylistScores(scores);
		}

		if (playerMode == 'admin') {
            return this;
        }

        switch(params.status) {
            case 1:
                //this.checkCurrentVideoId(params);

                player.seekTo(params.currentTime);
                player.playVideo();

            break;

            case 2:
                //this.checkCurrentVideoId(params);

                player.seekTo(params.currentTime);
                player.pauseVideo();

            break;

            case 0:
                player.loadVideoById({
                    videoId: params.videoId
                });

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

	this.checkCurrentVideoId = function(params)
	{
		var videoId = this.getCurrentVideoId();
		if (videoId != params.currentVideo) {
			player.loadVideoById({
				videoId: params.currentVideo
			});
		}
	},

	this.seekNextVideo = function(params)
	{
		$.get(nextVideoUrl, function(response) {
			data = JSON.parse(response);

			player.loadVideoById({
				videoId: data.videoId
			});

			params.playlistId = data.playlistId;
			params.videoId = data.videoId;

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
	}
};
