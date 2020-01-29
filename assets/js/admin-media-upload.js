/* global _, wp, Backbone, campusAdminText, campusAdminParams, _wpMediaViewsL10n */
(function($){
	/**
	 * wp.media.controller.smnCategoryImageCropper
	 *
	 * A state for cropping a Site Icon.
	 *
	 * @memberOf wp.media.controller
	 *
	 * @class
	 * @augments wp.media.controller.Cropper
	 * @augments wp.media.controller.State
	 * @augments Backbone.Model
	 */
	wp.media.controller.smnCategoryImageCropper = wp.media.controller.Cropper.extend(/** @lends wp.media.controller.smnCategoryImageCropper.prototype */{
		activate: function() {
			this.frame.on( 'content:create:crop', this.createCropContent, this );
			this.frame.on( 'close', this.removeCropper, this );
			this.set('selection', new Backbone.Collection(this.frame._selection.single));
		},

		createCropContent: function() {
			this.cropperView = new wp.media.view.smnCategoryImageCropper({
				controller: this,
				attachment: this.get('selection').first()
			});
			this.cropperView.on('image-loaded', this.createCropToolbar, this);
			this.frame.content.set(this.cropperView);

		},

		doCrop: function( attachment ) {
			var cropDetails = attachment.get( 'cropDetails' ),
				control = this.get( 'control' ),
				mask = false;

			cropDetails.dst_width  = control.params.width;
			cropDetails.dst_height = control.params.height;

			if( campusAdminParams.maskEnabled && campusAdminParams.mask[campusAdminParams.categoryParentInput.val()] != 'undefined' ) {
				mask = campusAdminParams.mask[campusAdminParams.categoryParentInput.val()];
			}

			return wp.ajax.post( 'smn_crop_image', {
				nonce: attachment.get( 'nonces' ).edit,
				id: attachment.get( 'id' ),
				term_id: control.params.term_id,
				mask: mask,
				context: control.id,
				cropDetails: cropDetails
			} );
		}
	});

	/**
	 * wp.media.view.smnCategoryImageCropper
	 *
	 * Uses the imgAreaSelect plugin to allow a user to crop a Site Icon.
	 *
	 * Takes imgAreaSelect options from
	 * wp.customize.SiteIconControl.calculateImageSelectOptions.
	 *
	 * @memberOf wp.media.view
	 *
	 * @class
	 * @augments wp.media.view.Cropper
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	wp.media.view.smnCategoryImageCropper = wp.media.view.Cropper.extend(/** @lends wp.media.view.smnCategoryImageCropper.prototype */{
		className: 'crop-content smn-category-image',

		ready: function () {
			wp.media.view.Cropper.prototype.ready.apply( this, arguments );

			this.$( '.crop-image' ).on( 'load', _.bind( this.onCropAreaLoad, this ) );
		},

		onCropAreaLoad: function() {
			this.controller.imgSelect.setOptions({
				onInit: this.setAspectRatio,
				onSelectChange: this.setAspectRatio
			});

			// Hide slidebar
			var styles = '.smn-category-image .media-sidebar { display: none;}';

			// Add mask to the selection area.
			if( campusAdminParams.maskEnabled && typeof campusAdminParams.mask[campusAdminParams.categoryParentInput.val()] != 'undefined' ) {
				styles += '.smn-category-image .imgareaselect-selection { background-image: url(' + campusAdminParams.mask[campusAdminParams.categoryParentInput.val()] + '); background-size: cover; }';
			}

			// Add some css
			$('#smn-category-image-preview-css').remove();
			$( 'head' ).append( '<style id="smn-category-image-preview-css" type="text/css">' + styles + '</style>' );
		},

		setAspectRatio: function() {
		},
	});



	// Set all variables to be used in scope
	var frame,
		addImgLink,
		delImgLink,
		uploader,
		imgContainer,
		imgIdInput,
		toggleInputs,
		podcastImgIdInput;

	campusAdminParams.maskEnabled = true;

	// add parent
	campusAdminParams.categoryParentInput = $('#parent');

	// ADD IMAGE LINK
	var smnImageUploader = function( item ) {

		uploader = item;
		addImgLink = uploader.find( '.upload-custom-img' );
		delImgLink = uploader.find( '.delete-custom-img' );
		imgContainer = uploader.find( '.custom-img-container' );
		imgIdInput = uploader.find( '.custom-img-id' );
		toggleInputs = uploader.find( '.toggle-input' );

		// If the media frame already exists, reopen it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create a new media frame
		frame = wp.media({
			title: campusAdminText.title,
			button: {
				text: campusAdminText.choose_file
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});


		// When an image is selected in the media frame...
		frame.on( 'select', function() {

			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();

			// Send the attachment URL to our custom image input field.
			imgContainer.html('<img src="' + attachment.url + '" class="upload-custom-img" />' );

			// Send the attachment id to our hidden input
			imgIdInput.val( attachment.id );

			// Hide the add image link
			addImgLink.add( toggleInputs ).addClass( 'hidden' );

			// Unhide the remove image link
			delImgLink.removeClass( 'hidden' );
		});

		// Finally, open the modal on click
		frame.open();
	};

	/**
	 * A control for selecting and cropping an image.
	 *
	 * @class
	 * @augments wp.customize.MediaControl
	 * @augments wp.customize.Control
	 * @augments wp.customize.Class
	 */
	var smnCroppedImageControl = {

		/**
		 * Set params.
		 */
		params: campusAdminParams.media,

		/**
		 * Open the media modal to the library state.
		 */
		openFrame: function( item ) {

			this.id 			= 'category_thumbnail';

			this.cropper 	  	= item;
			this.addImgLink   	= this.cropper.find( '.upload-custom-img' );
			this.delImgLink   	= this.cropper.find( '.delete-custom-img' );
			this.imgContainer 	= this.cropper.find( '.custom-img-container' );
			this.imgIdInput   	= this.cropper.find( '.custom-img-id' );
			this.toggleInputs   = this.cropper.find( '.toggle-input' );

			this.params.term_id = $('input[name="tag_ID"]').val();

			// If the media frame already exists, reopen it.
			if ( this.frame ) {
				this.frame.setState( 'library' ).open();
				return;
			}

			this.initFrame();
			this.frame.setState( 'library' ).open();
		},

		/**
		 * Create a media modal select frame, and store it so the instance can be reused when needed.
		 */
		initFrame: function() {
			var l10n = _wpMediaViewsL10n;

			this.frame = wp.media({
				button: {
					text: l10n.select,
					close: false
				},
				states: [
					new wp.media.controller.Library({
						title: campusAdminText.title,
						library: wp.media.query({ type: 'image' }),
						multiple: false,
						date: false,
						priority: 20,
						suggestedWidth: this.params.width,
						suggestedHeight: this.params.height
					}),
					new wp.media.controller.smnCategoryImageCropper({
						imgSelectOptions: this.calculateImageSelectOptions,
						control: this
					})
				]
			});

			this.frame.on( 'select', this.onSelect, this );
			this.frame.on( 'cropped', this.onCropped, this );
			this.frame.on( 'skippedcrop', this.onSkippedCrop, this );
		},

		/**
		 * After an image is selected in the media modal, switch to the cropper
		 * state if the image isn't the right size.
		 */
		onSelect: function() {
			var attachment = this.frame.state().get( 'selection' ).first().toJSON();

// 			if ( this.params.width === attachment.width && this.params.height === attachment.height && ! this.params.flex_width && ! this.params.flex_height ) {

			if( new RegExp('category-').test( attachment.filename ) ) {

				if( new RegExp('category-' + this.params.term_id).test( attachment.filename ) ) {
					this.setImageFromAttachment( attachment );
					this.frame.close();
				} else {
					alert( 'Cette image appartient à une autre catégorie !' );
				}
			} else {
				this.frame.setState( 'cropper' );
			}
		},

		/**
		 * After the image has been cropped, apply the cropped image data to the setting.
		 *
		 * @param {object} croppedImage Cropped attachment data.
		 */
		onCropped: function( croppedImage ) {
			this.setImageFromAttachment( croppedImage );
		},

		/**
		 * Returns a set of options, computed from the attached image data and
		 * control-specific data, to be fed to the imgAreaSelect plugin in
		 * wp.media.view.Cropper.
		 *
		 * @param {wp.media.model.Attachment} attachment
		 * @param {wp.media.controller.Cropper} controller
		 * @returns {Object} Options
		 */
		calculateImageSelectOptions: function( attachment, controller ) {

			var control    = controller.get( 'control' ),
				flexWidth  = !! parseInt( control.params.flex_width, 10 ),
				flexHeight = !! parseInt( control.params.flex_height, 10 ),
				realWidth  = attachment.get( 'width' ),
				realHeight = attachment.get( 'height' ),
				xInit = parseInt( control.params.width, 10 ),
				yInit = parseInt( control.params.height, 10 ),
				ratio = xInit / yInit,
				xImg  = xInit,
				yImg  = yInit,
				x1, y1, imgSelectOptions;

			//controller.set( 'canSkipCrop', ! control.mustBeCropped( flexWidth, flexHeight, xInit, yInit, realWidth, realHeight ) );

			controller.set( 'canSkipCrop', false );

			if ( realWidth / realHeight > ratio ) {
				yInit = realHeight;
				xInit = yInit * ratio;
			} else {
				xInit = realWidth;
				yInit = xInit / ratio;
			}

			x1 = ( realWidth - xInit ) / 2;
			y1 = ( realHeight - yInit ) / 2;

			imgSelectOptions = {
				handles: true,
				keys: true,
				instance: true,
				persistent: true,
				imageWidth: realWidth,
				imageHeight: realHeight,
				minWidth: xImg > xInit ? xInit : xImg,
				minHeight: yImg > yInit ? yInit : yImg,
				x1: x1,
				y1: y1,
				x2: xInit + x1,
				y2: yInit + y1
			};

			if ( flexHeight === false && flexWidth === false ) {
				imgSelectOptions.aspectRatio = xInit + ':' + yInit;
			}

			if ( true === flexHeight ) {
				delete imgSelectOptions.minHeight;
				imgSelectOptions.maxWidth = realWidth;
			}

			if ( true === flexWidth ) {
				delete imgSelectOptions.minWidth;
				imgSelectOptions.maxHeight = realHeight;
			}

			return imgSelectOptions;
		},

		/**
		 * Return whether the image must be cropped, based on required dimensions.
		 *
		 * @param {bool} flexW
		 * @param {bool} flexH
		 * @param {int}  dstW
		 * @param {int}  dstH
		 * @param {int}  imgW
		 * @param {int}  imgH
		 * @return {bool}
		 */
		mustBeCropped: function( flexW, flexH, dstW, dstH, imgW, imgH ) {
			if ( true === flexW && true === flexH ) {
				return false;
			}

			if ( true === flexW && dstH === imgH ) {
				return false;
			}

			if ( true === flexH && dstW === imgW ) {
				return false;
			}

			if ( dstW === imgW && dstH === imgH ) {
				return false;
			}

			if ( imgW <= dstW ) {
				return false;
			}

			return true;
		},

		/**
		 * If cropping was skipped, apply the image data directly to the setting.
		 */
		onSkippedCrop: function() {
			var attachment = this.frame.state().get( 'selection' ).first().toJSON();
			this.setImageFromAttachment( attachment );
		},

		/**
		 * Updates the setting and re-renders the control UI.
		 *
		 * @param {object} attachment
		 */
		setImageFromAttachment: function( attachment ) {
			this.params.attachment = attachment;

			// Replace the attachment URL.
			this.imgContainer.html('<img src="' + attachment.url + '" class="upload-custom-img" />' );

			// Send the attachment id to our hidden input
			this.imgIdInput.val( attachment.id );

			// Hide the add image link
			this.addImgLink.add( this.toggleInputs ).addClass( 'hidden' );

			// Unhide the remove image link
			this.delImgLink.removeClass( 'hidden' );
		}
	};

	// Toggle between cropper and classic uploader
	$('#campus-toggle-media-cropper').on( 'click', function() {
		//event.preventDefault();

		$('.campus-media-container').toggleClass( 'campus-media-cropper campus-media-uploader' );
	} );

	// Toggle cropper mask
	$('#campus-toggle-media-cropper-mask').on( 'click', function() {
		//event.preventDefault();

		campusAdminParams.maskEnabled = ! campusAdminParams.maskEnabled;
	} );

	// Add image
	$('.campus-media-container .upload-custom-img').on( 'click', function( event ) {
		event.preventDefault();

		var $parent = $(this).parents('.campus-media-container');

		if( $parent.hasClass( 'campus-media-cropper' ) )
			smnCroppedImageControl.openFrame( $parent );
		else
			smnImageUploader( $parent );
	} );


	// DELETE IMAGE LINK
	$('.delete-custom-img').on( 'click', function( event ) {

		uploader = $(this).parents( '.campus-media-container' );
		addImgLink = uploader.find( '.upload-custom-img' );
		delImgLink = uploader.find( '.delete-custom-img' );
		imgContainer = uploader.find( '.custom-img-container' );
		imgIdInput = uploader.find( '.custom-img-id' );
		toggleInputs = uploader.find( '.toggle-input' );
		podcastImgIdInput = $('[name="podcast_thumbnail_id"]');

		event.preventDefault();

		// Clear out the preview image
		imgContainer.find('img.upload-custom-img').remove();

		// Un-hide the add image link
		addImgLink.add( toggleInputs ).removeClass( 'hidden' );

		// Hide the delete image link
		delImgLink.addClass( 'hidden' );

		// Delete the image id from the hidden input
		imgIdInput.val( '' );

		// Remove also podcast image, if it exists
		if( podcastImgIdInput.length ) {

			// Clear out the preview image
			podcastImgIdInput.siblings('.custom-img-container').find('img').remove();

			// Delete the image id from the hidden input
			podcastImgIdInput.val( '' );
		}

	});

})(jQuery);
