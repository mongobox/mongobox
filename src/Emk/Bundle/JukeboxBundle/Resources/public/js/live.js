function onPlayerStateChange(event)
{
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

LivePlayer = function()
{
	this.initialize = function(currentVideoId)
	{
		this.getVideoScores(currentVideoId);
		
		$('#up-vote a').unbind('click');
		$('#up-vote a').click(function(event) {
			event.preventDefault();
			var videoId = this.getCurrentVideoId();
			
			$.post(voteUrl, {
				video: videoId,
				vote: 'up',
				current: 1
			}, function(response) {
				this.videoScoresUpdate(response);
			}.bind(this));
		}.bind(this));
		
		$('#down-vote a').unbind('click');
		$('#down-vote a').click(function(event) {
			event.preventDefault();
			var videoId = this.getCurrentVideoId();
			
			$.post(voteUrl, {
				video: videoId,
				vote: 'down',
				current: 1
			}, function(response) {
				this.videoScoresUpdate(response);
			}.bind(this));
		}.bind(this));
	},
	
	this.synchronizePlayerState = function(params)
	{
		if (params.action == 'update_scores') {
			var scores = JSON.parse(params.scores);
			this.updateVideoScores(scores);
		}
		
		if (params.mode != 'admin') {
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
					videoId: params.nextVideo
				});
				
				this.initialize(params.nextVideo);
				
				break;
			}
		}
	},
	
	this.getCurrentVideoId = function()
	{
		var videoUrl	= player.getVideoUrl();
		var videoId		= videoUrl.split('v=')[1];
		
		var andPosition = videoId.indexOf('&');
		if (andPosition != -1) {
			videoId = videoId.substring(0, andPosition);
		}
		
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
				videoId: data.nextVideo
			});
			
			params.nextVideo = data.nextVideo;
			this.sendParameters(params);
			
			this.initialize(data.nextVideo);
		}.bind(this));
	}, 
	
	this.sendParameters = function(params)
	{
		json = JSON.stringify(params);
		connection.send(json);
	},
	
	this.getVideoScores = function(videoId)
	{
		$.get(scoreUrl, {
			video: videoId,
		}, function(response) {
			scores = JSON.parse(response);
			this.updateVideoScores(scores);
		}.bind(this));
	},
	
	this.updateVideoScores = function(scores)
	{
		$('#up-score').text('(' + scores.upVotes + ')');
		$('#down-score').text('(' + scores.downVotes + ')');
		$('#video-score').text('Score : ' + scores.votesRatio);
		
		if (scores.votesRatio <= -2 && playerMode == 'admin') {
			var params = new Object();
			params.status = 0;
			
			livePlayer.seekNextVideo(params);
		}
	}
	
	this.videoScoresUpdate = function(data)
	{
		scores = JSON.parse(data);
		this.updateVideoScores(scores);
		
		var params = new Object();
		params.action	= 'update_scores';
		params.scores	= data;
		
		this.sendParameters(params);
	}
};
