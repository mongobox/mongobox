var tumblr = tumblr || {};
(function($)
{
	tumblr.init = function(class_img, model_class, model_note, model_user )
	{
		this.pathToImg = ''; // Path to get img folder for stars png
		this.pathToRating = basepath+"mongo-pute/tumblr_vote/"; // Path to submit rating
		this.regexClass = model_class; // String to match in regexp for tumblr class
		this.classImg = class_img; // Img html class
		this.noteModel = model_note; // Note's id model
		this.userModel = model_user; // User note's class model
		this.tumblr_id_displayed = 0; // Tumblr's id displayed 
		tumblr.loadPopover();
		tumblr.listenHoverImg();
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
            // Regex matching to get tumblr's id
	        var pattern_regex_id = new RegExp(tumblr.regexClass+'-(\\d+)');
	    	var tumblr_id = $(this).attr('class').match(pattern_regex_id)[1];
	    	
	    	// Doesn't execute the code below if it's the same tumblr clicked
	    	if( tumblr.tumblr_id_displayed == tumblr_id ) return false;
	    	// Save the current id displayed
	    	tumblr.tumblr_id_displayed = tumblr_id;
	    	// Close all popover oppened
	    	tumblr.closeAll(false);
	        
	    	// Get tumblr's score
	    	var tumblr_score = $("."+tumblr.noteModel+"-"+tumblr_id).val();
	    	// Get user's vote
	    	var user_score = $("."+tumblr.userModel+"-"+tumblr_id).val();
	    	// Cloning image
		    var new_image = $(this).clone();
		    // Groups
		    var groups_hidden = $(this).siblings('.tumblr-groups-'+tumblr_id).val();
		    var groups = groups_hidden.split(',');
		    // Remove style attribute of img balise
			// Remove img id to avoid duplicate ids
			// Remove img class to avoid js hover bind
		    new_image.removeAttr('style').removeAttr('id').removeClass(''+tumblr.classImg);
			// Load content into popover content
			$(this).attr('data-content', tumblr.getHTMLContent(tumblr_id, tumblr_score, new_image[0].outerHTML, groups) );
		    // Popover
		    $(this).popover('show');
		    $('.popover-title').append('<button type="button" title="Fermer" class="close close-tumblr-popover" aria-hidden="true">&times;</button>');
		    // Star rating init
		    tumblr.starRating(tumblr_id);
		    // Load actual vote from user
		    tumblr.loadRatingFromUser(tumblr_id, user_score);
		    tumblr.listenClickHide();
        });
	},
	
	tumblr.listenClickHide = function()
	{
		$('body').on('click', '.close-tumblr-popover', function(e)
		{
				tumblr.closeAll(true);
		});
	},
	
	tumblr.closeAll = function(suppr_id)
	{
		// For each tumblr img, if the popover is displayed, we hide it
        $("."+tumblr.classImg).each( function(index, element)
        {
	        if( $(element).data('popover').tip().hasClass('in') )
	        	$(element).popover('hide');
	        
	        if( suppr_id ) tumblr.tumblr_id_displayed = 0;
        });
	},

	// Function to create HTML content displayed in popover
	tumblr.getHTMLContent = function(tumblr_id, score, img_html, groups)
	{
		// Create HTML content
    	var html_content = '';
		html_content += '<div id="vote-mongo">';
		html_content += '   <div id="note-globale-'+tumblr_id+'" class="note-globale">Note globale : '+score+'</div>';
		html_content += '   <div class="star" id="rating-'+tumblr.classImg+'-'+tumblr_id+'"></div>';
		html_content += '</div>';
		html_content += '<div class="img-tumblr-big">'+img_html+'</div>';
		html_content += '<div class="groups-diffusion">';
		for( var index_group in groups)
		{
			html_content += '<span class="badge badge-info">'+groups[index_group]+'</span>';
		}
		html_content += '</div>';
		return html_content;
	},

	// Function to initialize rating
	tumblr.starRating = function(tumblr_id)
	{
		$('#rating-'+tumblr.classImg+'-'+tumblr_id).raty({
		    cancel: false,
		    click : function(score, evt) 
		    {
				//+tumblr.classImg
				// Regex matching to get tumblr's id
		    	var pattern_regex_id = new RegExp('rating-'+tumblr.classImg+'-(\\d+)');
				var tumblr_id = $(this).attr('id').match(pattern_regex_id)[1];
				// Ajax request to submit vote and refresh the displayed one
				$.ajax({
				    type: 'POST',
				    url: tumblr.pathToRating+tumblr_id+"/"+score,
				    success:function(data)
				    {
						// Changing the displayed note
						$('#note-globale-'+tumblr_id).html('Note globale : '+data);
						$("."+tumblr.noteModel+"-"+tumblr_id).val(data);
						$("."+tumblr.userModel+"-"+tumblr_id).val(score);
				    }
				});
		    },
		    half: true,
		    hints: ['Bouhhhh !', 'Peut faire mieux', 'Bof', 'Pas mal !', 'MONGOOOOOO !'],
		    number: 5,
		    path: tumblr.pathToImg,
		    score: 1,
		    size: 24,
		    starHalf: 'star-half-big.png',
		    starOff: 'star-off-big.png',
		    starOn: 'star-on-big.png'
		});
	},

	tumblr.loadRatingFromUser = function(tumblr_id, score)
	{
	    // Adding the note to star rating
	    $('#rating-'+tumblr.classImg+'-'+tumblr_id).raty('score', score);
	};
})(jQuery);