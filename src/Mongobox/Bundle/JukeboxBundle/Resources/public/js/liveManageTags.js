$('#liste_tags').dataTable();

$('.admin-tag-video-action').on('click' ,function(e) {
	e.preventDefault();
	$.ajax({
		type: 'POST',
		url: $(this).attr('href'),
		dataType: 'json',
		success: function(data)
		{
			if(data.selected === 1) $('#playlist_tag_actif ul').append(data.html);
			else $('#playlist_tag_inactif ul').append(data.html);
		}
	});
});
//$('#playlist_tag_actif ul')