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

		this.observeShowBookmarks();
		this.observeParentCheckboxClick();
		this.observeChildrenCheckboxClick();
		this.observeBtnStartImport();
		this.observeCreateGroup();
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
				type: 'POST',
				success: function(data)
				{
					$('#loader-group-ajax').hide();
					$("#modal-import-group-create .modal-body").append(data);
				}
			});
		});
	};

	importBookmark.observeBtnStartImport = function()
	{
		this.btnStartImport.bind('click', function(e)
		{
			e.preventDefault();
			alert('start import');
		});
	};

})(jQuery);

$(function()
{
	importBookmark.init();
});
