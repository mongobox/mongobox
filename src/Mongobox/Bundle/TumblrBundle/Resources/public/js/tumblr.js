var tumblr = tumblr || {};
(function($)
{
	tumblr.init = function( class_img )
	{
		this.pathToImg = ''; // Path to get img folder for stars png
		this.pathToRating = basepath+"mongo-pute/tumblr_vote/"; // Path to submit rating
		this.regexId; // String to match in regexp for tumblr id
		this.classImg = class_img; // Img html class
		this.noteModel; // Note's id model
		this.userModel; // User note's id model
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
		$('body').on('mouseenter', "."+tumblr.classImg , function(e)
        {
			// For each tumblr img, if the popover is displayed, we hide it
	        $("."+tumblr.classImg).each( function(index, element)
	        {
		        if( $(element).data('popover').tip().hasClass('in') )
		        	$(element).popover('hide');
	        });
	        
            // Regex matching to get tumblr's id
	        var pattern_regex_id = new RegExp(tumblr.regexId+'-(\\d+)');
	    	var tumblr_id = $(this).attr('id').match(pattern_regex_id)[1];
	    	// Get tumblr's score
	    	var tumblr_score = $("#"+tumblr.noteModel+"-"+tumblr_id).val();
	    	// Get user's vote
	    	var user_score = $("#"+tumblr.userModel+"-"+tumblr_id).val();
	    	// Cloning image
		    var new_image = $(this).clone();
		    // Remove style attribute of img balise
			// Remove img id to avoid duplicate ids
			// Remove img class to avoid js hover bind
		    new_image.removeAttr('style').removeAttr('id').removeClass(''+tumblr.classImg);
			// Load content into popover content
			$(this).attr('data-content', tumblr.getHTMLContent(tumblr_id, tumblr_score, new_image[0].outerHTML) );
		    // Popover
		    $(this).popover('show');
		    // Star rating init
		    tumblr.starRating(tumblr_id);
		    // Load actual vote from user
		    tumblr.loadRatingFromUser(tumblr_id, user_score);
		    tumblr.listenLeaveImg();
        });
	},

	// Function to hide popover
	tumblr.listenLeaveImg = function()
	{
		// Listen mouseleave event from popover
        $('.popover-inner').bind('mouseleave', function(e)
        {
        	$('.'+tumblr.classImg).popover('hide');
        });
	},

	// Function to create HTML content displayed in popover
	tumblr.getHTMLContent = function(tumblr_id, score, img_html)
	{
		// Create HTML content
    	var html_content = '';
		html_content += '<div id="vote-mongo">';
		html_content += '   <div id="note-globale-'+tumblr_id+'" class="note-globale">Note globale : '+score+'</div>';
		html_content += '   <div class="star" id="rating-'+tumblr.classImg+'-'+tumblr_id+'"></div>';
		html_content += '</div>';
		html_content += '<div class="img-tumblr-big">'+img_html+'</div>';
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
						$("#"+tumblr.noteModel+"-"+tumblr_id).val(data);
						$("#"+tumblr.userModel+"-"+tumblr_id).val(score);
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