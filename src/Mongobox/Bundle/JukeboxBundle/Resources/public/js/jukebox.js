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
$(document).on("click", ".btn-dedicace", function () {
    var videoId = $(this).data('id');
    $.ajax({
        type: "GET",
        dataType: "html",
        url: basepath + 'videos/'  + videoId + '/add_to_playlist_width_dedicace'
    }).done(
        function( html )
        {
            $('#post-dedicace-modal .modal-content').html(html);
            $('.loader').hide();
        });
});

function loadVideoEnCours()
{
	$('.video-thumbnail').tooltip('destroy');
    $.ajax({
			type: "GET",
			dataType: "json",
			url: basepath + 'video_en_cours?json'
		}).done(
		function( json )
		{
			refreshLoadVideoEnCoursFail = 0;
			$('#video_en_cours').html(json.render);
			$('.video-thumbnail').tooltip({'html' : 'true', 'placement' : 'left'});

            if ($("#videoid").val() != $("#videodedicaces").val()) {
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: basepath + 'dedicaces_en_cours?json'
                    }).done(
                    function( json )
                    {
                        $('#dedicaces').html(json.render);
                    });

            }
		}).fail(
		function()
		{
			refreshLoadVideoEnCoursFail++;
			if(refreshLoadVideoEnCoursFail >= 3) clearInterval(refreshLoadVideoEnCours);
		});
}


function btn_submit_video()
{
	$.ajax({
		type: "POST",
		url: basepath + 'post_video',
		data: $('#form_video').serialize()
	}).done(function( msg )
	{
		$('.loader').hide();
		$('#form_video').html('Vidéo ajoutée avec succès.');
	});
}

$(document).ready(function()
{
    $('#post-video-modal').on('show', function () {
        $('.loader').show();
        $('#post-video-modal .modal-content').html('');
        $.ajax({
            type: "GET",
            dataType: "html",
            url: basepath + 'post_video'
        }).done(
            function( html )
            {
                $('#post-video-modal .modal-content').html(html);
                $('.loader').hide();
            });
    });
    $('#post-dedicace-modal').on('show', function () {
        $('.loader').show();
        $('#post-dedicace-modal .modal-content').html('');
    });

	$(document).on('change', '#video_lien', function()
	{
		$('.loader').show();
		$('#search-btn-video').hide();
		//fonction magique qui prend l'url et renvoi un tableau avec l'artist et la songname guess
		$.ajax({
			type: "POST",
			dataType: "json",
			url: basepath + 'get_info_video',
			data: {'lien' : $(this).val()}
		}).done(
		function( infos )
		{
			if(infos.type == 'new')
			{
				$('.loader').hide();
                $('#videolien').append('Artiste : <input type="text" name="artist" value="' + infos.artist + '" /><br />Chanson : <input type="text" name="songName" value="' + infos.songName + '" />');
                $('#form_video').append('<br /><a class="btn" id="btnSubmitVideo">Valider</a>');
				$('#btnSubmitVideo').bind('click', function(e)
				{
					$('.loader').show();
					e.preventDefault();
					btn_submit_video();
				});
			}
			else
			{
				btn_submit_video();
			}
		});
	});

	$('.video-thumbnail').tooltip({'html' : 'true', 'placement' : 'left'});
});

var refreshLoadVideoEnCours = setInterval('loadVideoEnCours()', 5000);
var refreshLoadVideoEnCoursFail = 0;