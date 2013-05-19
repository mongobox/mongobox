LivePlayer.prototype.synchronizePlayerState = function(params)
{
    if (params.action === 'update_scores') {
        var scores = JSON.parse(params.scores);
        this.updatePlaylistScores(scores);
    }

    if (params.action === 'update_volume') {
        this.synchronizePlayerVolume();
    }
}

LivePlayer.prototype.seekNextVideo = function(params)
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

        this.sendParameters(params);

        this.initialize(data.playlistId);
    }.bind(this));
}

LivePlayer.prototype.updatePlaylistScores = function(scores)
{
    $('#up-score').text('(' + scores.upVotes + ')');
    $('#down-score').text('(' + scores.downVotes + ')');
    $('#video-score').text('Score : ' + scores.votesRatio);

    if (scores.votesRatio <= maxDislikes) { //TODO
        var params = new Object();
        params.status = 0;

        livePlayer.seekNextVideo(params);
    }
}

