var favorisManager = favorisManager || {};

(function($)
{
	favorisManager.init = function()
	{
		this.boutonAddToFavoris = $('#btn-favoris-add');
		this.boutonShowMoreFav = $('#bouton-afficher-plus-favoris');
		this.pageNumberHidden = $('#pageHidden');
		this.listeFavoris = $('#liste-favoris');
		this.addingToFavorite = false;

		this.observeFavorisAdd();
		this.observeHoverFavoris();
		this.observeShowListsDetails();
		this.observeRemoveVideoFromList();
		this.observeRemoveBookmark();
		this.observeShowListsAdd();
		this.observeAddToPlaylist();
		this.listenAutocompleteAddList();
		this.observeAddBookmarkToList();
		this.observeShowMoreFav();
	};

	// Fonction pour gérer l'ajout de la vidéo en favoris
	favorisManager.observeFavorisAdd = function()
	{
		$("body").delegate("#btn-favoris-add", "click", function(e)
		{
			e.preventDefault();

			if( favorisManager.addingToFavorite === true )
				return false;

			favorisManager.addingToFavorite = true;
			var bouton = $(this);

			$.ajax({
				type: 'POST',
				url: $(this).attr('href'),
				dataType: 'json',
				async: true,
				success: function(data)
				{
					favorisManager.addingToFavorite = false;
					var span_bouton = bouton.find('span');
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
						bouton.delay(1000).fadeOut(300, function()
						{
							bouton.remove();
						});
					});
				}
			});
		});
	};

	// Fonction pour initialiser l'event hover sur chaque favoris
	favorisManager.observeHoverFavoris = function()
	{
		$('.un-favoris').hover(
			function()
			{
				$(this).addClass('FavorisTdIsOver');
				$(this).find('.actions-content').show();
			}, function(e)
			{
				if( $('.ui-autocomplete:hover').length > 0 )
					return false;
				$(this).removeClass('FavorisTdIsOver');
				$(this).find('.actions-content').hide();
			}
		);
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
								bouton_cliquer.closest('tr').fadeOut(300, function()
								{
									var div_content = $(this).closest(".content-block-bookmark");
									bouton_cliquer.closest('tr').remove();
									if( $('#table-list-'+div_content.attr('id')).find('tr').length == 0 )
										$('#table-list-'+div_content.attr('id')).append('<tr class="no-bookmark"><td colspan=2>Vous n\'avez attribué aucune liste à cette vidéo.</td></tr>');
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
			e.preventDefault();
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
									favorisManager.observeHoverFavoris();
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
			e.preventDefault();
			$(this).toggleClass('active');
			var div_content = $(this).siblings('.content-add-bookmark-liste');
			div_content
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

	// Fonction pour supprimer une vidéo des favoris
	favorisManager.observeAddToPlaylist = function()
	{
		$("body").delegate(".btn-add-to-playlist", "click", function(e)
		{
			e.preventDefault();
			var bouton_cliquer = $(this);
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
							$(bouton_cliquer).fadeOut(300, function()
							{
								$(bouton_cliquer).remove();
							});
						}
						else
						{
							alertify.error(data.message);
						}
					}
				});
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
					favorisManager.observeHoverFavoris();
					favorisManager.listenAutocompleteAddList();
				}
			});
		});
	};
})(jQuery);