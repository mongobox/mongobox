var loadRSS = function()
{
    $('#flux_rss').load(basepath + 'flux_rss');
    $('#tumblr').load(basepath + 'tumblr/tumblr');
};

var loadProposeVotes = function()
{
	$.ajax({
		url: basepath + 'tumblr/propose_votes',
		type: 'POST',
		success: function(data)
		{
			$('#propose-votes').html(data);
			tumblr.loadPopover();
		}
	});
};

function loadStatistiques()
{
	$.ajax({
			type: "GET",
			dataType: "json",
			url: basepath + 'statistiques?json'
		}).done(
		function( json )
		{
			refreshStatistiquesFail = 0;
			$('#statistiques').html(json.render);
		}).fail(
		function()
		{
			refreshStatistiquesFail++;
			if(refreshStatistiquesFail >= 3) clearInterval(refreshStatistiques);
		});
}

$(document).ready(function()
{
	setInterval( loadRSS, 300000 );
	loadRSS();
	loadProposeVotes();
});

var refreshStatistiques = setInterval('loadStatistiques()', 500000);
var refreshStatistiquesFail = 0;