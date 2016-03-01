var listVideoTags = new Array();
var videoTags = videoTags || {};

(function ($) {

    videoTags.init = function () {
        this.clear();
        this.observeAddTag();
        this.observeRemoveTag();
        this.observeSubmitForm();
    };

    videoTags.clear = function () {
        videoTags.containerSelectedTags = this.form.find('.container-selected-tags');

        // Get the div that holds the collection of videoTags tag-data-prototype
        videoTags.collectionHolder = this.form.find('div.tag-data-prototype');

        videoTags.addTagButton = '#video-button-add-tag';
        videoTags.removeTagButton = this.collectionHolder.find('button.close');
        videoTags.buttonSubmit = $('#submit-form-video-tag-add');

        // count the current form inputs we have (e.g. 2), use that as the new
        // index when inserting a new item (e.g. 2)
        videoTags.collectionHolder.data('index', videoTags.collectionHolder.find(':input').length);
        videoTags.autocompleteField = this.form.find('input.ui-autocomplete-input');

        videoTags.error = 0;
    };

    videoTags.observeAddTag = function () {
        $(this.addTagButton).click(function (event) {
            event.preventDefault();
            videoTags.loadTag(videoTags.autocompleteField.val());
        });
    };

    videoTags.observeSubmitForm = function () {
        $(this.form).submit(function (e) {
            e.preventDefault();

            // Check tag choices
            if ($(videoTags.containerSelectedTags).find('span.tag-item').length === 0 && videoTags.error == 0) {
                videoTags.error = 1;
                $('#video_info').append('<div class="alert alert-danger error-add-video">A tag must be added.</div>');
                return false;
            }

            videoTags.loadAjax();
        });
    };

    videoTags.loadAjax = function () {
        this.submitting = true;
        $.ajax({
            type: 'POST',
            url: this.form.attr('action'),
            data: this.form.serialize(),
            dataType: 'json',
            success: function (data) {
                $('#action-video-modal').modal('hide')
            }
        });
    };

    videoTags.addTag = function (tag) {
        // si le mot clé n'appartient pas aux mots clés ajoutés précédemment, on génère "l'ajout"
        if (jQuery.inArray(tag.id, this.videoTags) == -1) {

            // Save list tag
            listVideoTags.push(tag.id);

            videoTags.prototypeTagsContainer = this.form.find('.container-selected-tags > ul');
            videoTags.prototypeTags = this.prototypeTagsContainer.attr('data-prototype');

            // Get the data-prototype explained earlier
            var prototype = this.collectionHolder.data('prototype');

            // get the new index
            var index = this.collectionHolder.data('index');

            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newTagItem = prototype.replace(/__name__/g, tag.name);
            newTagItem = newTagItem.replace(/__id__/g, tag.id);

            var newFormLi = $('<span class="tag-item label label-primary"></span>').append(newTagItem);
            videoTags.prototypeTagsContainer.append(newFormLi);

            // increase the index with one for the next item
            videoTags.collectionHolder.data('index', index + 1);

            videoTags.autocompleteField.val('');

            videoTags.observeRemoveTag();

        }
        else {
            alert('Tag already selected');
        }
    };

    videoTags.observeRemoveTag = function () {
        var btnClose = videoTags.containerSelectedTags.find('.tag-item button.close');
        $(btnClose).click(function (event) {
            event.preventDefault();

            videoTags.removeTag(this);
        });
    };

    videoTags.removeTag = function (element) {
        var tagId = $(element).next('input:hidden').val();
        listVideoTags.splice($.inArray(tagId, listVideoTags), 1);
        $(element).parent().remove();
        return false;
    };

    videoTags.loadTag = function (tagName) {
        $.ajax({
            url: videoTags.urlLoadTag,
            processData: false,
            data: 'tag=' + tagName,
            dataType: 'json',
            success: function (tag) {
                if (tag) {
                    videoTags.addTag(tag);
                }
                else {
                    alert("error during load tag");
                    return false;
                }
            }
        });
    };


})(jQuery);
