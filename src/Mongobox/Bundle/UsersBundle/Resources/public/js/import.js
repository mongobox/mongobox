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

		this.observeShowBookmarks();
		this.observeParentCheckboxClick();
		this.observeChildrenCheckboxClick();
		this.observeBtnStartImport();
		this.observeCreateGroup();
		this.observeBtnSubmitGroupCreate();
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
				$(this).find('i').removeClass('icon-chevron-down').addClass('icon-chevron-right');
			}
			else
			{
				div_bookmarks.show();
				$(this).find('i').addClass('icon-chevron-down').removeClass('icon-chevron-right');
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
			if( $('.children-checkbox:checked').length == 0 )
			{
				alertify.error('Veuillez sélectionner au moins une vidéo à importer');
				return false;
			}

			alertify.confirm("Êtes vous sûr de vouloir importer les vidéos dans le groupe "+$('#select-group-import option:selected').text()+' ?', function (response) {
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
					$(this).parent().before("<div class='modal-error-import alert alert-error'>Veuillez renseigner ce champs</div>");
					$(this).focus();
					error = true;
					return false;
				}

				if( $(this).attr('type') == 'number' )
				{
					if( !$.isNumeric($(this).val()) || !($(this).val() > 0) )
					{
						$(this).parent().before("<div class='modal-error-import alert alert-error'>La valeur de ce champs doit être numérique et supérieure à 0</div>");
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

})(jQuery);

$(function()
{
	importBookmark.init();
});
