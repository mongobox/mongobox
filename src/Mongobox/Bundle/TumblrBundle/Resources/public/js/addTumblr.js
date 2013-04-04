var addTumblr = addTumblr || {};

(function($)
{
	addTumblr.init = function()
	{
		this.slider = $('#tumblr');
		this.boutonShowForm = $('#btn-add-tumblr-show');
		this.divFormAdd = $('#tumblr-add-wall');
		this.buttonSubmit = $('#submit-form-tumblr-add');
		this.form = $('#ajax_form_tumblr_add');
		this.ajaxLoader = $('#ajax_loader_tumblr_add');
		this.formImage = $('#tumblr_image');
		this.formText = $('#tumblr_text');
		this.submitting = false;
		this.shown = false;

		// Call function
		this.observeShowForm();
		this.observeSubmitForm();
	};

	addTumblr.displayForm = function()
	{
		this.shown = true;
		this.divFormAdd.slideDown('fast');
		this.boutonShowForm.find('i').addClass('icon-chevron-up').removeClass('icon-chevron-down');
	};

	addTumblr.hideForm = function()
	{
		this.shown = false;
		this.divFormAdd.slideUp('fast', function()
		{
			if( addTumblr.submitting )
			{
				addTumblr.submitting = false;
				addTumblr.form[0].reset();
				$('.tag-item').remove();
				addTumblr.form.show();
				addTumblr.ajaxLoader.hide();
			}
		});
		this.boutonShowForm.find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
	};

	addTumblr.observeShowForm = function()
	{
		this.boutonShowForm.bind('click', function(e)
		{
			( addTumblr.shown ) ? addTumblr.hideForm(): addTumblr.displayForm();
			$('.error-add-tumblr').remove();
		});
	};

	addTumblr.observeSubmitForm = function()
	{
		this.form.on('click', '#submit-form-tumblr-add', function(e)
		{
			e.preventDefault();

			if( addTumblr.formImage.val() === '' || addTumblr.formImage.val() === addTumblr.formImage.attr('placeholder') )
			{
				addTumblr.formImage.focus();
				return false;
			}

			if( addTumblr.formText.val() === '' || addTumblr.formText.val() === addTumblr.formText.attr('placeholder') )
			{
				addTumblr.formText.focus();
				return false;
			}

			addTumblr.form.hide();
			addTumblr.ajaxLoader.show();

			if( addTumblr.submitting === true )
				return false;

			addTumblr.loadAjax();
		});
	};

	addTumblr.loadAjax = function()
	{
		this.submitting = true;
		$.ajax({
			type: 'POST',
			url: this.form.attr('action'),
			data: this.form.serialize(),
			dataType: 'json',
			success: function(data)
			{
				addTumblr.hideForm();
				if( data.success )
				{
					if( addTumblr.slider.find('li').length === 5 )
					{
						addTumblr.slider.find('li').last().fadeOut(300, function()
						{
							$(this).remove();
							addTumblr.displayNewTumblr(data.tumblrView);
						});
					} else
					{
						addTumblr.displayNewTumblr(data.tumblrView);
					}
				} else
				{
					addTumblr.slider.before('<div class="alert alert-error error-add-tumblr">Vous avez ajouté une mongo pute sans groupe, veuillez l\'éditer pour qu\'elle apparaisse sur le wall.</div>');
				}
			}
		});
	};

	addTumblr.displayNewTumblr = function(tumblrView)
	{
		addTumblr.slider.find('ul:first').prepend(tumblrView);
		// Reloading tumblr popover
		tumblr.loadPopover();
	};
})(jQuery);