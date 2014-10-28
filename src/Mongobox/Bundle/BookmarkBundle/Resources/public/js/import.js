var importBookmark = importBookmark || {};

(function($)
{
	importBookmark.init = function()
	{
		this.linkList = $('.span-link');
		this.parentCheckbox = $('.parent-checkbox');
		this.childrenCheckbox = $('.children-checkbox');
		this.btnStartImport = $('#btn-start-import');
		this.btnCreateGroup = $('#create-groupe-import');
		this.btnSubmitGroupCreate = $('#btn-submit-group-create');
		this.selectGroup = $('#select-group-import');
		this.linkOptions = $('.nav-link-import');
		this.typeOption = $('#type-import-choice');
		this.btnChecking = $('.videos-check');
		this.searchFieldVideos = $('#input-video-search-import');
		this.searchFieldVideosGroup = $('#searchVideosInList');
		this.typeOption.val('list');
		this.btnClearSearch = $('#clear-video-search');
		this.divLoaderGroupVideos = $('#loader-group-ajax');
		this.divGroupVideos = $('#videos-in-list');

		this.observeShowBookmarks();
		this.observeParentCheckboxClick();
		this.observeChildrenCheckboxClick();
		this.observeBtnStartImport();
		this.observeCreateGroup();
		this.observeBtnSubmitGroupCreate();
		this.initOptionsObserver();
		this.observeCheckUncheckVideo();
		this.observeResearchVideosOption();
		this.observeResearchVideosGroup();
		this.observeRemoveSearchText();
		this.loadGroupVideo();
		this.observeGroupSelect();
	};

	importBookmark.initOptionsObserver = function()
	{
		this.linkOptions.bind('click', function(e)
		{
			importBookmark.linkOptions.parent().removeClass('active');
			$(this).parent().addClass('active');
			e.preventDefault();
			var id_target = $(this).attr('href');
			if( id_target === "#videos-choices" )
			{
				$(id_target).show();
				$('#list-choices').hide();
				importBookmark.typeOption.val('video');
			} else if (id_target === "#list-choices")
			{
				$(id_target).show();
				$('#videos-choices').hide();
				importBookmark.typeOption.val('list');
			}
		});
	};

	importBookmark.observeShowBookmarks = function()
	{
		this.linkList.bind('click', function(e)
		{
			e.preventDefault();
			var id_list = $(this).attr('data-id-list');
			var div_bookmarks = $('#list-bookmarks-'+id_list);
			if( div_bookmarks.is(':visible') )
			{
				div_bookmarks.hide();
				$(this).find('i').removeClass('glyphicon glyphicon-chevron-down').addClass('glyphicon glyphicon-chevron-right');
			}
			else
			{
				div_bookmarks.show();
				$(this).find('i').addClass('glyphicon glyphicon-chevron-down').removeClass('glyphicon glyphicon-chevron-right');
			}
			return false;
		});
	};

	importBookmark.observeParentCheckboxClick = function()
	{
		this.parentCheckbox.bind('click', function()
		{
			var id_list = $(this).val();
			$('#list-bookmarks-'+id_list).find('.children-checkbox').prop("checked", this.checked);
		});
	};

	importBookmark.observeChildrenCheckboxClick = function()
	{
		this.childrenCheckbox.bind('click', function()
		{
			$(this).closest('.list-import').find('.parent-checkbox').prop("checked", false);
		});
	};

	importBookmark.observeCreateGroup = function()
	{
		this.btnCreateGroup.bind('click', function(e)
		{
			e.preventDefault();

			$('#modal-import-group-create').modal({
				show: true,
				backdrop: 'static',
				keyboard: false
			});

			$.ajax({
				url: $(this).attr('href'),
				type: 'GET',
				success: function(data)
				{
					$('#loader-group-ajax').hide();
					$("#form-modal-body").html(data);
				}
			});
		});
	};

	importBookmark.observeBtnStartImport = function()
	{
		this.btnStartImport.bind('click', function(e)
		{
			e.preventDefault();
			var checkboxes = ( importBookmark.typeOption.val() == "video" ) ? $('.checkbox-import-video:checked') : $('.children-checkbox:checked');
			if( checkboxes.length == 0 )
			{
				alertify.error('Veuillez sélectionner au moins une vidéo à importer');
				return false;
			}

			alertify.confirm('Êtes vous sûr de vouloir importer les vidéos dans le groupe "'+$('#select-group-import option:selected').text()+'" ?', function (response) {
				if (response)
				{
					$('#form-import').submit();
				} else
				{
					return false;
				}
			});
		});
	};

	importBookmark.observeBtnSubmitGroupCreate = function()
	{
		this.btnSubmitGroupCreate.bind('click', function(e)
		{
			e.preventDefault();
			var form = $('#form-ajax-group-create');

			$('.modal-error-import').remove();
			var error = false;
			$.each( form.find('input'), function()
			{
				if( $(this).val() == '' )
				{
					$(this).parent().before("<div class='modal-error-import alert alert-danger'>Veuillez renseigner ce champs</div>");
					$(this).focus();
					error = true;
					return false;
				}

				if( $(this).attr('type') == 'number' )
				{
					if( !$.isNumeric($(this).val()) || !($(this).val() > 0) )
					{
						$(this).parent().before("<div class='modal-error-import alert alert-danger'>La valeur de ce champs doit être numérique et supérieure à 0</div>");
						$(this).focus().val('');
						error = true;
						return false;
					}
				}
			});

			if(error)
				return false;

			$.ajax({
				url: $(this).attr('href'),
				type: 'POST',
				data: form.serialize(),
				dataType: 'json',
				success: function(data)
				{
					if( data.success )
					{
						alertify.log(data.message);
						importBookmark.selectGroup.append('<option value="'+data.group_id+'" selected="selected">'+data.group_text+'</option>');
						$('#divider-group-after').before(data.html_navbar);
						$('#modal-import-group-create').modal('hide');
					}
				}
			});
		});
	};

	importBookmark.observeCheckUncheckVideo = function()
	{
		this.btnChecking.bind('click', function(e)
		{
			e.preventDefault();
			var checkboxState = ( $(this).attr('data-check') == "true" ) ? true: false;
			$(".checkbox-import-video").prop("checked", checkboxState);
		});
	};

	importBookmark.observeRemoveSearchText = function()
	{
		this.btnClearSearch.bind('click', function(e)
		{
			e.preventDefault();
			importBookmark.searchFieldVideos.val('');
			$('#videos-choices').find('.fav-import-dual').show();
		});
	};

	importBookmark.observeResearchVideosOption = function()
	{
		this.searchFieldVideos.bind('keyup', function(e)
		{
			importBookmark.toggleVideoSearch(e, "videos-choices", $(this).val());
		});
	};

	importBookmark.toggleVideoSearch = function(event, div_id, searchText)
	{
		if(event.which == 13 ) return false;
		else if (searchText == '') $('#'+div_id).find('.fav-import-dual').show();
		else
		{
			$('#'+div_id).find('.fav-import-dual').filter(function()
			{
				var fav_text = $(this).find('.video-text-import').text();
				var checkContains = fav_text.toLowerCase().search(searchText.toLowerCase()) == -1;
				if( !checkContains ) $(this).show();
				return checkContains;
			}).hide();
		}
	};

	importBookmark.observeGroupSelect = function()
	{
		this.selectGroup.bind('change', function()
		{
			importBookmark.loadGroupVideo();
		});
	};

	importBookmark.observeResearchVideosGroup = function()
	{
		this.searchFieldVideosGroup.bind('keyup', function(e)
		{
			importBookmark.toggleVideoSearch(e, "videos-in-list", $(this).val());
		});
	};

	importBookmark.loadGroupVideo = function()
	{
		importBookmark.divLoaderGroupVideos.show();
		importBookmark.divGroupVideos.hide();
		var group_id = $('#select-group-import option:selected').val();
		$.ajax({
			url: basepath+"import/group/"+group_id+"/load/videos",
			type: "POST",
			success: function(html)
			{
				importBookmark.divLoaderGroupVideos.hide();
				importBookmark.divGroupVideos.html(html).show();
			}
		});
	};

})(jQuery);

$(function()
{
	importBookmark.init();
});
