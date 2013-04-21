var favorisManager = favorisManager || {};

(function($)
{
	favorisManager.init = function()
	{
		this.boutonAddToFavoris = $('.btn-favoris-add');
		this.boutonShowLists = $('.btn-lists-bookmark');
		this.addingToFavorite = false;

		this.observeFavorisAdd();
		this.observeShowListsDetails();
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
		this.boutonShowLists.bind('click', function(e)
		{
			e.preventDefault();
			$(this).toggleClass('active');
			var div_content = $(this).siblings('.content-lists-bookmark');
			div_content
				.css("left", $(this).offset().left + $(this).width() + 14 - div_content.width())
				.css("top", $(this).offset().top + $(this).height())
				.toggle()
			;
		});
	};

	favorisManager.observeRemoveVideoFromList = function()
	{

	};
})(jQuery);