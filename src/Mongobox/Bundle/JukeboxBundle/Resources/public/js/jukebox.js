$(document).on("click", ".btn-vote", function(e)
{
    e.preventDefault();
	e.stopPropagation();
    var element = $(this);
    $.ajax({
        type: "POST",
        url: element.attr('href')
    }).done(function( msg )
    {
        $('#' + element.attr('rel')).html(msg);
    });
});

function loadVideoEnCours()
{
	$('.video-thumbnail').tooltip('hide');
    $('#video_en_cours').load(basepath + 'video_en_cours');
    $('#statistiques').load(basepath + 'statistiques');
}
function loadRSS()
{
    $('#flux_rss').load(basepath + 'flux_rss');
    $('#tumblr').load(basepath + 'mongo-pute/tumblr');
}

$(document).ready(function()
{
    //setInterval( "loadVideoEnCours()", 5000 );
    //setInterval( "loadRSS()", 300000 );
});