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

    livePlayer.synchronizePlayerState = function(params)
    {
        if (params.action === 'update_scores') {
            var scores = JSON.parse(params.scores);
            this.updatePlaylistScores(scores);
        }

        if (params.action === 'update_volume') {
            this.synchronizePlayerVolume();
        }
    };

    livePlayer.seekNextVideo = function(params)
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
    };

    livePlayer.updatePlaylistScores = function(scores)
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
});
