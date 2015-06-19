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

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

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
		dataType: "json",
		url: basepath + 'videos/post_video',
		data: $('#form_video').serialize(),
		success: function(data)
		{
			$('.loader').hide();
			$('#action-video-modal .modal-header h3').html(data.title);
			$('#action-video-modal .modal-body').html(data.content);
		}
	});
}

$(document).ready(function()
{
	$(document).on('click', '#add-video-button', function(e)
	{
		e.preventDefault();
		button = $(this);

		$('.loader').show();
		$('#action-video-modal').modal('show');
		//$('#action-video-modal .modal-content').html('');
		$.ajax({
			type: "GET",
			dataType: "json",
			url: basepath + 'videos/post_video',
			success: function(data)
			{
				$('#action-video-modal .modal-header h3').html(data.title);
				$('#action-video-modal .modal-body').html(data.content);
				$('.loader').hide();
			}
		});
	});

	$(document).on('click', '.edit-video', function(e)
	{
		e.preventDefault();
		button = $(this);

		$('#action-video-modal').modal('show');
		// Loading content from twig template
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: button.attr('href'),
			success: function(data)
			{
				$('#action-video-modal .modal-header h3').html(data.title);
				$('#action-video-modal .modal-body').html(data.content);
			}
		});
	});

	$(document).on('change', '#video_lien', function()
	{
		$('.loader').show();
		$('#search-btn-video').hide();
		//fonction magique qui prend l'url et renvoi un tableau avec l'artist et la songname guess
		$.ajax({
			type: "POST",
			dataType: "json",
			url: basepath + 'videos/get_info_video',
			data: {'lien' : $(this).val()}
		}).done(
		function( infos )
		{
			if(infos.type == 'new')
			{
				$('.loader').hide();
				$('#youtube_add').append('Artiste : <input type="text" class="form-control" name="artist" value="' + infos.artist + '" /><br />Vidéo : <input type="text" class="form-control" name="songName" value="' + infos.songName + '" /><br /><a class="btn btn-primary" id="btnSubmitVideo">Valider</a>');
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

	$(document).on('click', '#valide-tags-btn-video', function()
	{
		$.ajax({
			type: "POST",
			dataType: "html",
			url: basepath + 'post_tag_video',
			data: $('#form_tags').serialize()
		}).done(
		function( tag )
		{
			$('#tag-list').append(tag);
		});
	});

	$(document).on('click', '.delete-tag-video', function(e)
	{
		e.preventDefault();
		e.stopPropagation();
		tag = $(this).parent();
		$.ajax({
			type: "POST",
			dataType: "json",
			url: $(this).attr('href')
		}).done(
		function()
		{
			tag.remove();
		});
	});

	$('.video-thumbnail').tooltip({'html' : 'true', 'placement' : 'left'});

	$(document).on('mouseover', '.show-edit-video', function(e)
	{
		$(this).find('.edit-video').addClass('edit-video-visible');
		$(this).find('.btn-favoris-add').addClass('jukebox-visible');
	});

	$(document).on('mouseout', '.show-edit-video', function(e)
	{
		$(this).find('.edit-video').removeClass('edit-video-visible');
		$(this).find('.btn-favoris-add').removeClass('jukebox-visible');
	});

	$(document).on('submit', '#form_video_info', function(e)
	{
		e.preventDefault();

		// Loading content from twig template
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: $('#form_video_info').attr('action'),
			data : $('#form_video_info').serialize(),
			success: function(data)
			{
				$('#action-video-modal .modal-body').html(data.content);
			}
		});
	})
	
	$(document).on('keyup', '#video_search_search', function(e)
	{
		//On met un delai pour éviter de chercher pour chaque lettre tappée
		delay(function()
		{
			// Loading content from twig template
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: basepath + 'videos/ajax/search/keyword',
				data : $('#video_search_search').serialize(),
				success: function(data)
				{
					$('#mongobox_search').html(data.mongobox);
					$('#youtube_search').html(data.youtube);
				}
			});
	    }, 500 );
	});

	$(document).on('click', '.video_search_send', function(e)
	{
		e.preventDefault();
		$('#video_lien').val($(this).attr('rel'));
		btn_submit_video();
	});
});

var refreshLoadVideoEnCours = setInterval('loadVideoEnCours()', 15000);
var refreshLoadVideoEnCoursFail = 0;