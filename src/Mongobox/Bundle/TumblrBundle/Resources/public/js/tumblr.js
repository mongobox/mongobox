var tumblr = tumblr || {};
(function($)
{
	tumblr.init = function(class_img, model_class, model_note, model_user )
	{
		this.pathToImg = ''; // Path to get img folder for stars png
		this.pathToRating = basepath+"tumblr/vote/"; // Path to submit rating
		this.pathToContent = basepath+"tumblr/load/popover/content/"; // Path to load content
		this.regexClass = model_class; // String to match in regexp for tumblr class
		this.classImg = class_img; // Img html class
		this.noteModel = model_note; // Note's id model
		this.userModel = model_user; // User note's class model
		this.tumblr_id_displayed = 0; // Tumblr's id displayed 
		tumblr.loadPopover();
		tumblr.listenHoverImg();
		tumblr.listenClickHide();
	},

	// Function initializing popover
	tumblr.loadPopover = function()
	{
		$( '.'+tumblr.classImg ).popover({
		    placement: 'bottom',
		    trigger: 'manual',
		    template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><div class="content-link-mongo"></div></div></div></div>',
		    html: true
		});
	},

	// Function to observe the hover event on tumblr img
	tumblr.listenHoverImg = function()
	{
		$('body').on('click', "."+tumblr.classImg , function(e)
        {
			e.preventDefault();
			
            // Regex matching to get tumblr's id
	        var pattern_regex_id = new RegExp(tumblr.regexClass+'-(\\d+)');
	    	var tumblr_id = $(this).attr('class').match(pattern_regex_id)[1];
	    	
	    	// Doesn't execute the code below if it's the same tumblr clicked
	    	if( tumblr.tumblr_id_displayed === tumblr_id )
	    	{ 
	    		$('.'+tumblr.classImg).popover('hide');
	    		tumblr.tumblr_id_displayed = 0;
	    		return false;
	    	}
	    	// Close all popover oppened
	    	tumblr.closeAll();
	    	// Save the current id displayed
	    	tumblr.tumblr_id_displayed = tumblr_id;
	    	
	    	tumblr_image = $(this);
	    	// Loading content from twig template
	    	$.ajax({
				type: 'POST',
				data: { 'class_tumblr': tumblr.classImg },
				dataType: 'json',
				url: tumblr.pathToContent+tumblr_id,
				success: function(data)
				{
					tumblr_image.attr('data-content', data.content);
					tumblr_image.attr('data-original-title', data.title);
					tumblr_image.popover('show');
					$('.popover-title').append('<button type="button" title="Fermer" class="close close-tumblr-popover" aria-hidden="true">&times;</button>');
					var selector = '#rating-'+tumblr.classImg+'-'+tumblr_id;
					var score = $('#rating-'+tumblr.classImg+'-'+tumblr_id).attr('data-score');
					tumblr.starRating(selector, score);
					tumblr.initInfoVote();
				}
			});
        });
	},
	
	tumblr.listenClickHide = function()
	{
		$('body').on('click', '.close-tumblr-popover', function(e)
		{
				tumblr.closeAll();
				tumblr.tumblr_id_displayed = 0;
		});
	},
	
	tumblr.closeAll = function()
	{
		// For each tumblr img, if the popover is displayed, we hide it
        $("."+tumblr.classImg).each( function(index, element)
        {
	        if( $(element).data('popover').tip().hasClass('in') )
	        	$(element).popover('hide');
        });
	},

	// Function to initialize rating
	tumblr.starRating = function(selector, score)
	{
		$(''+selector).raty({
		    cancel: false,
		    click : function(score, evt) 
		    {
				// Regex matching to get tumblr's id
		    	var pattern_regex_id = new RegExp('rating-'+tumblr.classImg+'-(\\d+)');
				var tumblr_id = $(this).attr('id').match(pattern_regex_id)[1];
				// Ajax request to submit vote and refresh the displayed one
				$.ajax({
				    type: 'POST',
				    url: tumblr.pathToRating+tumblr_id+"/"+score,
				    dataType: 'json',
				    success:function(data)
				    {
						// Changing the displayed note
						$('#moyenne-'+tumblr_id).html('Moyenne : '+data.moyenne+' ');
						$('#moyenne-'+tumblr_id).append(data.info_vote);
						$("."+tumblr.noteModel+"-"+tumblr_id).val(data.somme);
						$("."+tumblr.userModel+"-"+tumblr_id).val(score);
						tumblr.initInfoVote();
				    }
				});
		    },
		    half: true,
		    hints: ['Bouhhhh !', 'Peut faire mieux', 'Bof', 'Pas mal !', 'MONGOOOOOO !'],
		    number: 5,
		    path: tumblr.pathToImg,
		    score: score,
		    size: 24,
		    starHalf: 'star-half-big.png',
		    starOff: 'star-off-big.png',
		    starOn: 'star-on-big.png'
		});
	},
	
	tumblr.initScoreRating = function()
	{
		$('.star').each(function()
		{
			$(this).raty('score', $(this).attr('data-score'));
		});
	},
	
	tumblr.initInfoVote = function()
	{
		$('.tumblr-info-votes').tooltip();
	};
})(jQuery);