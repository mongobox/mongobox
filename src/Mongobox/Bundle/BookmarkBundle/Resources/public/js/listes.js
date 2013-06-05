var listesManager = listesManager || {};

(function($)
{
	listesManager.init = function()
	{
		this.btnSubmitCreating = $('#btn-submit-list'); // submit button of list creation
		this.textNewListTitle = $('#add-list-name'); // input type text, title field of new list
		this.btnSubmitBookmarkAdd = $('#btn-submit-bookmark'); // submit button of new bookmark in list
		this.textNewBookmark = $('#add-bookmark-name'); // input of autocomplete on bookmark
		this.hidIdBookmark = $('#hid-bookmark-id'); // input hidden with id of autocomplete
		this.btnRemoveList = '.btn-remove-list';
		this.contentList = $('#details-liste');
		this.editing = false;

		this.initTooltip();
		this.observeSubmit();
		this.observeRemoveList();
		this.observeListDetailsDisplay();
		this.observeRemoveBookmarkFromList();
		this.observeEditingListTitle();

		this.autocompleteBookmarkNameOnList();
		this.observeAddNewBookmarkToList();
	};

	// Function to init tooltip on some btn
	listesManager.initTooltip = function()
	{
		$('.btn-tooltip').tooltip();
	};

	// Function to handle list add
	listesManager.observeSubmit = function()
	{
		this.btnSubmitCreating.on('click', function(e)
		{
			e.preventDefault();

			// Checking list name field filled
			if( listesManager.textNewListTitle.val() == '' || listesManager.textNewListTitle.val() == listesManager.textNewListTitle.attr('placeholder') )
			{
				listesManager.textNewListTitle.focus();
				return false;
			}

			listesManager.btnSubmitCreating.button('loading');
			$.ajax({
				url: listesManager.btnSubmitCreating.attr('href'),
				type: 'POST',
				dataType: 'json',
				data: {
					routeName: listesManager.btnSubmitCreating.attr('data-route'),
					listName: listesManager.textNewListTitle.val()
				},
				success: function(data)
				{
					$('#modal_add_list').modal('hide');
					listesManager.btnSubmitCreating.button('reset');
					if( data.success )
					{
						// Success message & refresh lists number
						alertify.success('La liste "'+ data.listName +'" a bien été créée.');
						$('#lists_number').html(data.listNumber);
						// Adding new list DOM if list shown > limitation
						if( $('.listes-content').find('.no-list').length > 0 )
						{
							$('.listes-content').html(data.html);
						} else
						{
							if( $('.listes-content').find('.une-liste').length < data.limitation )
								$('.listes-content').append(data.html);
						}
						listesManager.initTooltip();
					} else
					{
						alertify.error('Erreur lors de l\'ajout de la liste.');
					}
					listesManager.textNewListTitle.val('');
				}
			});
		});
	};

	// Function to handle a delete of a list
	listesManager.observeRemoveList = function()
	{
		$('.listes-content').on('click', this.btnRemoveList , function(e)
		{
			e.stopPropagation();
			e.preventDefault();
			// Cleaning block of list details and adding loader
			listesManager.contentList.empty().html( $('.ajax-loader-div-hidden').clone().removeClass('hide') );
			var btn_delete = $(this);
			$.ajax({
				url: btn_delete.attr('href'),
				dataType: 'json',
				type: 'POST',
				success: function(data)
				{
					if( data.success )
					{
						// Success message & refresh lists number
						alertify.success('La liste a bien été supprimée.');
						$('#lists_number').html( data.listNumber );
						// Cleaning block of list details and adding message
						listesManager.contentList.empty().html( data.message );
						// Removing DOM element
						btn_delete.closest('li').fadeOut(300, function()
						{
							$(this).remove();
						});
					} else
					{
						alertify.error('Erreur lors de la suppression de la liste.');
					}
				}
			});
		});
	};

	// Function to handle list details show
	listesManager.observeListDetailsDisplay = function()
	{
		$('.listes-content').on('click', 'li.une-liste', function(e)
		{
			e.stopPropagation();
			e.preventDefault();
			// Cleaning block of list details and adding loader
			listesManager.contentList.empty().html( $('.ajax-loader-div-hidden').clone().removeClass('hide') );
			$.ajax({
				url: $(this).attr('href'),
				type: 'POST',
				dataType: 'json',
				success: function(data)
				{
					if( data.success )
					{
						listesManager.contentList.empty().html(data.html);
						listesManager.initTooltip();
					}
					else
						alertify.error(data.error);
				}
			});
		});
	};

	// Function to handle deleting bookmark from list
	listesManager.observeRemoveBookmarkFromList = function()
	{
		$('#details-liste').on('click', '.btn-remove-bookmark-list', function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			var bouton_suppr = $(this);
			var div_bookmark = bouton_suppr.closest('.bookmark-list');
			div_bookmark.fadeOut(300);
			$.ajax({
				url: bouton_suppr.attr('href'),
				type: 'POST',
				dataType: 'json',
				success: function(data)
				{
					if(data.success)
					{
						div_bookmark.remove();
						alertify.success(data.message);
					} else
						alertify.error(data.message);
				}
			});
		});
	};

	// Function to change DOM between click
	listesManager.handleEditingAction = function(bouton, $type)
	{
		if( $type === "editing" )
		{
			// Hide span / Show input
			bouton.siblings('span.list-name').hide();
			$('#list-input-edit-name').show().focus();
			bouton.find('i').hide();
			bouton.attr('data-action', 'submitting');
			// Listen keypress enter
			listesManager.observeEditingSubmitListTitle();
		} else if( $type === "submitting")
		{
			// Show span / Hide input
			bouton.siblings('span.list-name').show();
			$('#list-input-edit-name').hide();
			bouton.find('i').show();
			bouton.attr('data-action', 'editing');
		}
	};

	// Function to handle editing list title
	listesManager.observeEditingListTitle = function()
	{
		$('#details-liste').on('click', '.btn-edit-list-name', function(e)
		{
			e.preventDefault();
			e.stopPropagation();

			var $type = $(this).attr('data-action');
			listesManager.handleEditingAction( $(this), $type);
		});
	};

	// Function to listen on "ENTER", send & refresh list name
	listesManager.observeEditingSubmitListTitle = function()
	{
		$('#list-input-edit-name').unbind('keypress');
		$('#list-input-edit-name').bind('keypress', function(e)
		{
			if(e.which === 13 && !listesManager.editing )
			{
				// Lock submit
				listesManager.editing = true;
				$(this).attr('disabled', 'disabled');
				// Show loader
				$('#img-loader-edit-name').show();
				var input_text = $(this);
				$.ajax({
					url: basepath+"ajax/list/"+$(this).attr('data-id-list')+"/update/title",
					type: "POST",
					data: { 'name': $(this).val() },
					dataType: 'json',
					success: function(data)
					{
						if( data.success )
						{
							alertify.success(data.message);
							// Refresh list name on DOM
							$('.list-name', $('#liste-'+input_text.attr('data-id-list'))).html(data.newName);
							input_text.siblings('.list-name').html(data.newName);
						} else
						{
							alertify.error(data.message);
						}
						input_text.removeAttr('disabled');
						// Unlock submit
						listesManager.editing = false;
						listesManager.handleEditingAction(input_text.siblings('.btn-edit-list-name'), 'submitting');
						$('#img-loader-edit-name').hide();
					}
				});
			}
		});
	};

	// Function to handle autocomplete on bookmark name
	listesManager.autocompleteBookmarkNameOnList = function()
	{
		this.textNewBookmark.autocomplete({
			source: basepath+"ajax_bookmark_search",
			minLength: 2,
			select: function( event, ui )
			{
				listesManager.hidIdBookmark.val(ui.item.value);
				$(event.target).val(ui.item.label);
				return false;
			}
		});
	};

	// Function to handle click on add button
	listesManager.observeAddNewBookmarkToList = function()
	{
		this.btnSubmitBookmarkAdd.bind('click', function(e)
		{
			e.preventDefault();
			var id_list = $('#btn-add-bookmark-to-list').attr('data-id-list');

			if( listesManager.hidIdBookmark.val() == '' || listesManager.textNewBookmark.val() == '' || listesManager.textNewBookmark.val() == listesManager.textNewBookmark.attr('placeholder') )
			{
				listesManager.textNewBookmark.focus();
				return false;
			}

			var bouton = $(this);
			bouton.button('loading');
			$.ajax({
				url: basepath+"ajax/favoris/"+listesManager.hidIdBookmark.val()+"/add/liste",
				dataType: 'json',
				type: 'POST',
				data: {'id_liste': id_list, 'liste': true},
				success: function(data)
				{
					listesManager.textNewBookmark.val('');
					listesManager.hidIdBookmark.val('');
					if(data.result)
					{
						alertify.success(data.message);
						$('.list-details-bookmarks').append(data.html);
					} else
					{
						alertify.error(data.message);
					}
					bouton.button('reset');
				}
			});
		});
	};

})(jQuery);

$(function()
{
	listesManager.init();
});
