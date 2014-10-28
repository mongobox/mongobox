var listVideoTags = new Array();
var videoTags = videoTags || {};

(function($){

	videoTags.init = function(){
		this.clear();
		this.observeAddTag();
		this.observeRemoveTag();
		this.observeSubmitForm();
	};

	videoTags.clear = function(){
		videoTags.form = $('#form_video_info');
		videoTags.containerSelectedTags = $('#container-selected-tags');

		// Get the div that holds the collection of videoTags
		videoTags.collectionHolder = $('#video_tags_list');


		videoTags.addTagButton = '#video-button-add-tag';
		videoTags.removeTagButton = this.collectionHolder.find('button.close');
		videoTags.buttonSubmit = $('#submit-form-video-tag-add');

		// count the current form inputs we have (e.g. 2), use that as the new
		// index when inserting a new item (e.g. 2)
		videoTags.collectionHolder.data('index', videoTags.collectionHolder.find(':input').length);
		videoTags.videoTags = new Array();
		videoTags.autocompleteField = $('#autocompleter_video_info_tag');
	};

	videoTags.observeAddTag = function(){

		$(document).on('click', this.addTagButton, function(event)
		{
			event.preventDefault();
            videoTags.loadTag( videoTags.autocompleteField.val() );
       	});
	};

	videoTags.observeSubmitForm = function()
	{
		$(document).on('submit', '#form_video_info', function(e)
		{
			e.preventDefault();

			// Check tag choices
			if( $('#video_tags_list div.tag-item'). length === 0 )
			{
				$('#video_tags_list').parents('.span4:first').append('<div class="alert alert-danger error-add-video">A tag must be added.</div>')
				return false;
			}

			videoTags.loadAjax();
		});
	};


	videoTags.loadAjax = function()
	{
		this.submitting = true;
		$.ajax({
			type: 'POST',
			url: this.form.attr('action'),
			data: this.form.serialize(),
			dataType: 'json',
			success: function(data)
			{
				$('#action-video-modal').modal('hide')
			}
		});
	};

	videoTags.addTag = function(tag){

        // si le mot clé n'appartient pas aux mots clés ajoutés précédemment, on génère "l'ajout"
		if(jQuery.inArray(tag.id, this.videoTags) == -1){

            // Save list tag
            videoTags.videoTags.push(tag.id);

            videoTags.prototypeTagsContainer = $('#video_tags_list');
            videoTags.prototypeTags = this.prototypeTagsContainer.attr('data-prototype');

            // Get the data-prototype explained earlier
            var prototype = this.collectionHolder.data('prototype');

            // get the new index
            var index = this.collectionHolder.data('index');

            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newTagItem = prototype.replace(/__name__/g, tag.name);
            newTagItem = newTagItem.replace(/__id__/g, tag.id);

            var $newFormLi = $('<span class="tag-item label label-primary"></span>').append(newTagItem);
            videoTags.collectionHolder.append( $newFormLi );

            // increase the index with one for the next item
            videoTags.collectionHolder.data('index', index + 1);

            videoTags.autocompleteField.val('');
            videoTags.observeRemoveTag();
            console.log( prototype, index );
        }
        else{
            alert('Tag already selected');
        }

    };

    videoTags.observeRemoveTag = function(){

		$(document).on('click', '#form_video_info .tag-item button.close',  function(event){
            event.preventDefault();
            videoTags.removeTag( this );
        });

    };

    videoTags.removeTag = function(element){
        var tagId = $(element).next('input:hidden').val();
        this.videoTags.splice( $.inArray(tagId, this.videoTags), 1 );
        $(element).parent().remove();
        return false;
    };

    videoTags.loadTag = function(tagName){

        $.ajax({
            url: videoTags.urlLoadTag,
            processData: false,
            data: 'tag=' + tagName,
            dataType: 'json',
            success:  function(tag){
                 if( tag ){
                    videoTags.addTag( tag );
                 }
                 else{
                    alert("error during load tag");
                    return false;
                 }
            }
        });
    };
})(jQuery);
