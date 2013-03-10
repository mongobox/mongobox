var loadRSS = function()
{
    $('#flux_rss').load(basepath + 'flux_rss');
    $('#tumblr').load(basepath + 'tumblr/tumblr');
}

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
{    setInterval( loadRSS, 300000 );

	loadRSS();
});

var refreshStatistiques = setInterval('loadStatistiques()', 5000);
var refreshStatistiquesFail = 0;