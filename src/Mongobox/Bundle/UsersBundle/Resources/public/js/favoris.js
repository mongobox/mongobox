var favorisManager = favorisManager || {};

(function($)
{
	favorisManager.init = function()
	{
		this.boutonAddToFavoris = $('.btn-favoris-add');
		this.boutonShowMoreFav = $('#bouton-afficher-plus-favoris');
		this.pageNumberHidden = $('#pageHidden');
		this.listeFavoris = $('#liste-favoris');
		this.addingToFavorite = false;

		this.observeFavorisAdd();
		this.observeShowListsDetails();
		this.observeRemoveVideoFromList();
		this.observeRemoveBookmark();
		this.observeShowListsAdd();
		this.listenAutocompleteAddList();
		this.observeAddBookmarkToList();
		this.observeShowMoreFav();
	};

	// Fonction pour gérer l'ajout de la vidéo en favoris
	favorisManager.observeFavorisAdd = function()
	{
		this.boutonAddToFavoris.bind('click', function(e)
		{
			e.preventDefault();

			if( favorisManager.addingToFavorite === true )
				return false;

			favorisManager.addingToFavorite = true;

			$.ajax({
				type: 'POST',
				url: $(this).attr('href'),
				dataType: 'json',
				success: function(data)
				{
					var span_bouton = favorisManager.boutonAddToFavoris.find('span');
					span_bouton.fadeOut(300, function()
					{
						if(data.add === true)
						{
							span_bouton.text(' Ajoutée aux favoris');
						} else if( data.already === true )
						{
							span_bouton.text(' Déjà dans vos favoris');
						}
						span_bouton.fadeIn('fast');
						favorisManager.boutonAddToFavoris.delay(1000).fadeOut(300, function()
						{
							favorisManager.boutonAddToFavoris.remove();
						});
						favorisManager.addingToFavorite = false;
					});
				}
			});
		});
	};

	// Fonction pour afficher les listes de favoris affectées à une vidéo
	favorisManager.observeShowListsDetails = function()
	{
		$("body").delegate(".btn-lists-bookmark", "click", function(e)
		{
			e.preventDefault();
			$(this).toggleClass('active');
			var div_content = $(this).siblings('.content-lists-bookmark');
			div_content
				.css("left", $(this).offset().left + $(this).width() + 14 - div_content.width())
				.css("top", $(this).offset().top + $(this).height())
				.toggle()
			;
			if( $(this).siblings('.btn-lists-add').addClass('active') )
			{
				$(this).siblings('.content-add-bookmark-liste').css('display', 'none');
				$(this).siblings('.btn-lists-add').removeClass('active');
			}
		});
	};

	// Fonction pour repositionner la div qui apparait en dessous du bouton
	favorisManager.repositionnerDiv = function(div_content, class_bouton, class_div_to_move)
	{
		var bouton_div = div_content.find('.'+class_bouton);
		var div_to_move = div_content.find('.'+class_div_to_move);
		div_to_move
			.css("left", bouton_div.offset().left + bouton_div.width() + 14 - div_to_move.width())
			.css("top", bouton_div.offset().top + bouton_div.height())
		;
	};

	// Fonction pour supprimer une vidéo d'une liste de favoris
	favorisManager.observeRemoveVideoFromList = function()
	{
		$("body").delegate(".btn-actions-list-remove", "click", function(e)
		{
			e.preventDefault();
			var bouton_cliquer = $(this);
			alertify.confirm("Etes vous sûr de vouloir supprimer la vidéo de cette liste ?\n", function (e)
			{
				if (e)
				{
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: bouton_cliquer.attr('href'),
						success: function(data)
						{
							if(data.success)
							{
								alertify.success(data.message);
								bouton_cliquer.parents('tr:first').fadeOut(300, function()
								{
									var div_content = $(this).parents(".content-block-bookmark:first");
									bouton_cliquer.parents('tr:first').remove();
									if( $('#table-list-'+div_content.attr('id')).find('tr').length == 0 )
										$('#table-list-'+div_content.attr('id')).append('<tr class="no-bookmark"><td colspan=2>Vous n\'avez attribué aucune liste à cette vidéo.</td></tr>');
									favorisManager.repositionnerDiv(div_content , "btn-lists-bookmark", "content-lists-bookmark");
								});
							} else
							{
								alertify.error(data.message);
							}
						}
					});
				}
			});
		});
	};

	// Fonction pour supprimer une vidéo des favoris
	favorisManager.observeRemoveBookmark = function()
	{
		$("body").delegate(".btn-remove-bookmark", "click", function(e)
		{
			event.preventDefault();
			var bouton_cliquer = $(this);
			alertify.confirm("Etes vous sûr de vouloir supprimer la vidéo de vos favoris ?\nElle sera ainsi supprimée de toutes vos listes.", function (e)
			{
				if (e)
				{
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: bouton_cliquer.attr('href'),
						success: function(data)
						{
							if(data.success)
							{
								alertify.success(data.message);
								$("#bookmark-"+data.fav).fadeOut(300, function()
								{
									$("#bookmark-"+data.fav).remove();
								});

							} else
							{
								alertify.error(data.message);
							}
						}
					});
				}
			});
		});
	};

	// Fonction pour afficher la div d'ajout des favoris
	favorisManager.observeShowListsAdd = function()
	{
		$("body").delegate(".btn-lists-add", "click", function(e)
		{
			event.preventDefault();
			$(this).toggleClass('active');
			var div_content = $(this).siblings('.content-add-bookmark-liste');
			div_content
				.css("left", $(this).offset().left + $(this).width() + 14 - div_content.width())
				.css("top", $(this).offset().top + $(this).height())
				.toggle()
			;
			if( $(this).siblings('.btn-lists-bookmark').hasClass('active') )
			{
				$(this).siblings('.content-lists-bookmark').css('display', 'none');
				$(this).siblings('.btn-lists-bookmark').removeClass('active');
			}
		});
	};

	// Fonction pour gérer l'autocomplete sur les listes
	favorisManager.listenAutocompleteAddList = function()
	{
		$( ".list-autocomplete" ).autocomplete({
			source: basepath+"ajax_list_search",
			minLength: 2,
			select: function( event, ui )
			{
				$(this).siblings('.hid-value-autocomplete').val(ui.item.value);
				$(event.target).val(ui.item.label);
				return false;
			}
		});
	};

	// Fonction pour ajouter un favoris à la liste
	favorisManager.observeAddBookmarkToList = function()
	{
		$("body").delegate(".btn-add-bookmark-to-list", "click", function(e)
		{
			event.preventDefault();
			var bouton = $(this);
			var div_parent = $(this).parents('.content-add-bookmark-liste:first');
			var id_list = div_parent.find('.hid-value-autocomplete').val();
			if( id_list === '' || div_parent.find('.list-autocomplete').val() === '' || div_parent.find('.list-autocomplete').val() === div_parent.find('.list-autocomplete').attr('placeholder') )
			{
				div_parent.find('.list-autocomplete').focus();
				return false;
			}
			bouton.button('loading');
			$.ajax({
				url: $(this).attr('href'),
				data: { id_liste: id_list },
				type: 'POST',
				dataType: 'json',
				success: function(data)
				{
					div_parent.find('.list-autocomplete').val('');
					if(data.result)
					{
						alertify.success(data.message);
						var table = div_parent.siblings('.content-lists-bookmark').find('table');
						if( table.find('.no-bookmark').length > 0 )
							table.find('.no-bookmark').remove();
						table.append(data.html);
					} else
					{
						alertify.error(data.message);
					}
					bouton.button('reset');
				}
			});
		});
	};

	// Fonction pour afficher plus de favoris
	favorisManager.observeShowMoreFav = function()
	{
		this.boutonShowMoreFav.bind('click', function(event)
		{
			event.preventDefault();
			var bouton = $(this);
			bouton.button('loading');
			$.ajax({
				url: bouton.attr('href'),
				type: 'POST',
				data: {
					page: favorisManager.pageNumberHidden.val()
				},
				dataType: 'json',
				success: function(data)
				{
					bouton.button('reset');
					if( data.nextPage )
					{
						favorisManager.pageNumberHidden.val(data.page);
						favorisManager.listeFavoris.append(data.html);
					} else
					{
						favorisManager.listeFavoris.append(data.html);
						favorisManager.pageNumberHidden.remove();
						bouton.remove();
					}
					favorisManager.listenAutocompleteAddList();
				}
			});
		});
	};
})(jQuery);