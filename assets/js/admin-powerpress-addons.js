/* globals jQuery, ajaxurl */
(function($) {

	function getUrlSearch() {
		let params = location.search.slice(1);

		if( params ) {
			params = params.split( '&' );

			if( params.length ) {
				let urlParams = {};
				for( let key in params ) {
					let param = params[key].split( '=' );
					urlParams[param[0]] = param[1];
				}
				return urlParams;
			}
		}

		return [];
	}

	function addQueryArg( args, url ) {
		args = args || {};
		url  = url || '/';

		let params = [];

		for( let key in args ) {
			params.push( `${key}=${args[key]}` );
		}

		return `${url}?${params.join('&')}`;
	}

	function addTaxonomyPodcasting(e) {
		e.preventDefault();

		let $button = $(this);

		let termId         = parseInt( $(this).data('term_id') );
		let hasDescription = parseInt( $(this).data('has_description') );

		if( termId && ! $(this).hasClass('updating-message') ) {

			if( ! hasDescription ) {
				if( window.confirm( 'Vous devez d\'abord ajouter une description. Souhaitez-vous le faire maintenant ?' ) ) {
					window.location = $button.parents('tr#tag-' + termId).find('.row-title').attr('href');
				}

			} else if( window.confirm( 'Créer le flux de podcast ?' ) ) {

				$button.addClass('updating-message');

				$.ajax({
					url: ajaxurl,
					data: {
						action: 'add_category_podcasting',
						term_id: termId
					},
					type: 'post',
					success: function( data ) {
						if( data.success ) {
							const url = addQueryArg( {
								'page': 'powerpress%2Fpowerpressadmin_categoryfeeds.php', 
								'action': 'powerpress-editcategoryfeed', 
								'cat': termId
							}, '/wp-admin/admin.php' )

							if ( confirm( "Modifier le podcast ?\n\nFlux RSS : " + data.data ) ) {
								window.location = url
							} else {	
								$button
									.removeClass('updating-message button-smn_powerpress_category_podcasting dashicons-plus').addClass('dashicons-edit')
									.attr( 'href', url );
							}

						} else {
							let message = '';
							for( let i in data.data ) {
								message += data.data[i]['code'] + "\n" + data.data[i]['message'];
							}
							$button.removeClass('updating-message');

							alert( message );
						}
					}
				});
			}
		}

	}

	function addArtworkButton() {

		let urlParams = getUrlSearch();

		// If term exists
		if( typeof urlParams['term'] !== 'undefined' ) {

			const $artworkInput = $('#itunes_image');
			const $artworkButton = $('<button class="button">Réinitialiser l\'image</button>');
			const $artworkP = $('<p>Si vous avez changé le nom de la taxonomy, vous pouvez recréer l\'image : <br></p>');

			$artworkP.append($artworkButton);
			$artworkInput.parent().append( $artworkP );

			$artworkButton.on( 'click', function(e) {
				e.preventDefault();

				if( ! $(this).hasClass('updated-message') && ! $(this).hasClass('updating-message') ) {

					if( window.confirm( 'Réinitialiser l\'image iTunes ?' ) ) {

						$artworkButton.addClass('updating-message');

						$.ajax({
							url: ajaxurl,
							data: {
								action: 'reset_podcast_artwork',
								term_id: urlParams['term']
							},
							type: 'post',
							success: function( data ) {
								if( data.success ) {
									$artworkInput.val( data.data );

									$artworkButton.removeClass('updating-message').addClass('updated-message');
								} else {
									let message = '';
									for( let i in data.data ) {
										message += data.data[i]['code'] + "\n" + data.data[i]['message'];
									}
									$artworkButton.removeClass('updating-message');

									alert( message );
								}
							}
						});
					}
				}
			} );

		}
	}

	// $(document).ready( addArtworkButton );

	$(document).on( 'click', '.button-smn_powerpress_category_podcasting', addTaxonomyPodcasting );

})(jQuery);
