$('#liste_tags').dataTable();

$(document).on('click', '.admin-tag-video-action', function(e) {
	e.preventDefault();
	var button = $(this);
	$.ajax({
		type: 'POST',
		url: button.attr('href'),
		dataType: 'json',
		success: function(data)
		{
			if(data.selected === '1')
			{
				$('#playlist_tag_actif ul li.all').remove();
				$('#playlist_tag_actif ul').append(data.html_tag);
			}
			else $('#playlist_tag_inactif ul').append(data.html_tag);

			button.parent().html(data.html_button);
		}
	});
});

$(document).on('click', '.delete-live-tag-video', function(e) {
	e.preventDefault();
	var button = $(this);
	$.ajax({
		type: 'POST',
		url: button.attr('href'),
		dataType: 'json',
		success: function(data)
		{
			button.parent().remove();
			if($('#playlist_tag_actif ul li').length === 0) $('#playlist_tag_actif ul').append('<li class="all">Tous</li>')
		}
	});
});

$(document).on('click', '.btn-empty-playlist', function(e) {
	e.preventDefault();
	var button = $(this);
	$.ajax({
		type: 'POST',
		url: button.attr('href'),
		dataType: 'json',
		success: function(data)
		{
			alert('Done');
		}
	});
});