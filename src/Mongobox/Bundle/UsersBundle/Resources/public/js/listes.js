var listesManager = listesManager || {};

(function($)
{
	listesManager.init = function()
	{
		this.btnSubmitCreating = $('#btn-submit-list'); // bouton de soumission du formulaire de création
		this.textNewListTitle = $('#add-list-name'); // input type text, champs titre de la nouvelle liste
		this.btnRemoveList = '.btn-remove-list';
		this.contentList = $('#details-liste');

		this.initTooltip();
		this.observeSubmit();
		this.observeRemoveList();
		this.observeListDetailsDisplay();
	};

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
						alertify.error(data.message);
				}
			});
		});
	};

})(jQuery);

$(function()
{
	listesManager.init();
});