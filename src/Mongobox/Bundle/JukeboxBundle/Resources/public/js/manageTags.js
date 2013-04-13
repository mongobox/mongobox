
var listTags = new Array();
var tags = tags || {};

(function($){
	
	tags.init = function(){
		this.form = $('#video-add-content');
		this.containerSelectedTags = $('#container-selected-tags');

        // Get the div that holds the collection of tags
        this.collectionHolder = $('#video_tags');

        // count the current form inputs we have (e.g. 2), use that as the new
        // index when inserting a new item (e.g. 2)
        this.collectionHolder.data('index', this.collectionHolder.find(':input').length);
        this.tags = new Array();
		
		this.addTagButton = $('#video-button-add-tag');
        this.autocompleteField = $('#autocompleter_video_addtags');
		this.removeTagButton = this.collectionHolder.find('button.close');
		
		this.observeAddTag();
		this.observeRemoveTag();
	};
	
	tags.observeAddTag = function(){
		
		this.addTagButton.bind('click', function(event){
			event.preventDefault();
            tags.loadTag( tags.autocompleteField.val() );

        });
	};


    tags.addTag = function(tag){

        // si le mot clé n'appartient pas aux mots clés ajoutés précédemment, on génère "l'ajout"
		if(jQuery.inArray(tag.id, this.tags) == -1){

            // Save list tag
            this.tags.push(tag.id);

            this.prototypeTagsContainer = $('#video_tags');
            this.prototypeTags = this.prototypeTagsContainer.attr('data-prototype');

            // Get the data-prototype explained earlier
            var prototype = this.collectionHolder.data('prototype');

            // get the new index
            var index = this.collectionHolder.data('index');

            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newTagItem = prototype.replace(/__name__/g, tag.name);
            newTagItem = newTagItem.replace(/__id__/g, tag.id);

            var $newFormLi = $('<div class="tag-item alert alert-info"></li>').append(newTagItem);
            this.collectionHolder.append( $newFormLi );

            // increase the index with one for the next item
            this.collectionHolder.data('index', index + 1);

            this.autocompleteField.val('');
            this.observeRemoveTag();
            console.log( prototype, index );
        }
        else{
            alert('Tag already selected');
        }

    };

    tags.observeRemoveTag = function(){

        this.removeTagButton = $('#video_tags').find('button.close');
        this.removeTagButton.bind('click', function(event){
            event.preventDefault();
            tags.removeTag( this );
        });

    };

    tags.removeTag = function(element){
        var tagId = $(element).next('input:hidden').val();
        this.tags.splice( $.inArray(tagId, this.tags), 1 );
        $(element).parent().remove();
        return false;
    };

    tags.loadTag = function(tagName){

        $.ajax({
            url: tags.urlLoadTag,
            processData: false,
            data: 'tag=' + tagName,
            dataType: 'json',
            success:  function(tag){
                 if( tag ){
                    tags.addTag( tag );
                 }
                 else{
                    alert("error during load tag");
                    return false;
                 }
            }
        });
    };
})(jQuery);