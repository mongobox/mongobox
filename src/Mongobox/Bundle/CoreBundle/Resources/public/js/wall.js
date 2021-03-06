var refreshRss;
var refreshRssInProgress = 0;

function loadRSS() {
    if (refreshStatistiquesInProgress === 0) {
        refreshRssInProgress = 1;
        $('#flux_rss').load(basepath + 'flux_rss', function () {
            refreshRssInProgress = 0;
        });
    }
}

var refreshTumblrVote;
var refreshTumblrVoteInProgress = 0;

function loadProposeVotes() {
    if (refreshTumblrVoteInProgress === 0) {
        refreshTumblrVoteInProgress = 1;
        $.ajax({
            url: basepath + 'tumblr/propose_votes/0',
            type: 'POST',
            success: function (data) {
                $('#propose-votes').html(data);
                tumblr.loadPopover();
            },
            complete: function () {
                refreshTumblrVoteInProgress = 0;
            }
        });
    }
}

var refreshStatistiques;
var refreshStatistiquesInProgress = 0;
var refreshStatistiquesFail = 0;

function loadStatistiques() {
    if (refreshStatistiquesInProgress === 0) {
        refreshStatistiquesInProgress = 1;

        $.ajax({
            type: "GET",
            dataType: "json",
            url: basepath + 'statistiques?json'
        }).done(
            function (json) {
                refreshStatistiquesInProgress = 0;
                refreshStatistiquesFail = 0;
                $('#statistiques').html(json.render);
            }).fail(
            function () {
                refreshStatistiquesFail++;
                if (refreshStatistiquesFail >= 3) clearInterval(refreshStatistiques);
            });
    }
}


// polling stats
refreshStatistiques = setInterval(loadStatistiques, 50000);

// polling RSS
refreshRss = setInterval(loadRSS, 600000);

// polling Tumblr Votes
refreshTumblrVote = setInterval(loadProposeVotes, 300000);


