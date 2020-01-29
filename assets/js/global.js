/* global campus, Cookies, navGrid */
(function( $ ) {

	// Variables and DOM Caching.
	var $body = $( 'body' ),
		//$touchBody = $( '.touch body' ),
		$scrollElem = $( 'html,body' ),
		$content = $('#content'),
		$contentArea = $content.find('.content-area'),
		$grid = $contentArea.find('.site-main'),
		$stamp = $contentArea.find('.sticky').first().add('.content-area .stamp'),
		$termList = $contentArea.find('.term-list'),
		masonryOptions = {
			itemSelector: '.list-item',
			columnWidth: '.list-item:not(.sticky)',
			percentPosition: true,
			stagger: '50ms',
			stamp: $stamp,
			hiddenStyle: {
				transform: 'translateY(1em)',
				opacity: 0
			},
			visibleStyle: {
				transform: 'translateY(0)',
				opacity: 1
			}
		},
		infinite_count = 2,
		resizeTimer;

	if( $termList.length )
		$grid = $termList;

	function setVars() {
		$contentArea = $content.find('.content-area');
		$grid = $contentArea.find('.site-main');
		$stamp = $contentArea.find('.sticky').first().add('.content-area .stamp');
		$termList = $contentArea.find('.term-list');

		if( $termList.length )
			$grid = $termList;

		masonryOptions.stamp = $stamp;

		infinite_count = 2;
	}

	/*
	 * Test if inline SVGs are supported.
	 * @link https://github.com/Modernizr/Modernizr/
	 */
	function supportsInlineSVG() {
		var div = document.createElement( 'div' );
		div.innerHTML = '<svg/>';
		return 'http://www.w3.org/2000/svg' === ( 'undefined' !== typeof SVGRect && div.firstChild && div.firstChild.namespaceURI );
	}

	/**
	 * Mute player from simeon@web-createur.com
	 */
	window.addEventListener('message', function receiveMessage(e) {
		if( e.origin !== 'http://simeon.web-createur.com' )
			return;

		if( typeof e.data == 'undefined' || typeof e.data.method == 'undefined' )
			return;

		if( e.data.method == 'muted' ) {

			if( e.data.delay ) {
				setTimeout( function() {
					$( 'audio, video' ).get(0).muted = e.data.value;
				}, e.data.delay );
			} else {
				$( 'audio, video' ).get(0).muted = e.data.value;
			}
		}

	}, false);

	/**
	 * Test touch screen
	 * @link https://codeburst.io/the-only-way-to-detect-touch-with-javascript-7791a3346685
	 */
	window.addEventListener('touchstart', function onFirstTouch() {
		$( 'html' ).addClass( 'touch' );
		//$touchBody = $( '.touch body' );
		//setTouchNavigation();
		// we only need to know once that a human touched the screen, so we can stop listening now
		window.removeEventListener('touchstart', onFirstTouch, false);
	}, false);

	/**
	 * Test if an iOS device.
	*/
	function checkiOS() {
		return /iPad|iPhone|iPod/.test(navigator.userAgent) && ! window.MSStream;
	}

	/*
	 * Test if background-attachment: fixed is supported.
	 * @link http://stackoverflow.com/questions/14115080/detect-support-for-background-attachment-fixed
	 */
	function supportsFixedBackground() {
		var el = document.createElement('div'),
			isSupported;

		try {
			if ( ! ( 'backgroundAttachment' in el.style ) || checkiOS() ) {
				return false;
			}
			el.style.backgroundAttachment = 'fixed';
			isSupported = ( 'fixed' === el.style.backgroundAttachment );
			return isSupported;
		}
		catch (e) {
			return false;
		}
	}

	function initAccordion() {

		$( '.acc' ).each( function() {
			var active = $(this).data('active');

			if( active ) {
				$(this).find('.acc-title').eq( active ).addClass('active');
				$(this).find('.acc-content').eq( active ).addClass('active');
			}
		});

	}

	// function setTouchNavigation() {
	// 	$touchBody.find('.menu-item-depth-0 > a').on('touchstart', function(e) {
	// 		e.preventDefault();
	// 		console.log('touch menu');
	// 	});
	// }

	// Fire on document ready.
	$( document ).ready( function() {

		if ( true === supportsInlineSVG() ) {
			document.documentElement.className = document.documentElement.className.replace( /(\s*)no-svg(\s*)/, '$1svg$2' );
		}

		if ( true === supportsFixedBackground() ) {
			document.documentElement.className += ' background-fixed';
		}

		// Init masonry
		if( $contentArea.hasClass('content-grid') && ! $body.hasClass( 'page-template-page-alt-programs' ) ) {
			$grid.masonry( masonryOptions );
		}

		// Document events
		$( document )

			// Ajaxify loading
			.on('ajaxloading', function() {
				$('#search-box').removeClass('active');
				$body.removeClass( 'has-overlay' );
			})

			// Ajax content loaded
			.on( 'ajaxready', function() {

				// Init fn
				initAccordion();

				// Reset dom vars
				setVars();

				// Set program grid
				if( $body.hasClass( 'page-template-page-alt-programs' ) ) {
					navGrid.init();

				// Set masonry for grid
				} else {

					$.each( campus.screen, function( key, name ) {
						if( typeof campus.filters.toggle_view[name] != 'undefined' && campus.filters.toggle_view[name] == 'grid' ) {
							$grid.masonry( masonryOptions );
							return;
						}
					} );

					//$grid.masonry( 'destroy' );
				}

			} )

			// Accordion
			.on('click', '.acc-title', function(e) {
				e.preventDefault();

				if( $(this).hasClass( 'active' ) ) {
					$(this).removeClass('active').siblings().removeClass('active');
				} else {
					$(this).addClass('active').siblings().removeClass('active');
					$(this).next('.acc-content').addClass('active');
				}
			})

			// Toggle view
			.on('click', '.toggle-view a', function(e) {
				e.preventDefault();

				// Hide content
				//$body.removeClass('loaded').addClass('loading');

				var value = $(this).attr('rel'),
					cookie = Cookies.getJSON( 'campus-filters' );

				// Set button current state
				$(this).parent().addClass('current')
					.siblings().removeClass('current');

				// Set content class
				$(this).parent().siblings().each(function() {
					$('.content-area').removeClass('content-' + $(this).find('a.meta-button').attr('rel'));
				});
				$('.content-area').addClass('content-' + value);

				// Set program grid
				if( $body.hasClass( 'page-template-page-alt-programs' ) ) {
					navGrid.setView();

				// Set masonry for grid
				} else {
					if( value == 'grid' ) {
						$grid.masonry( masonryOptions );
					} else
						$grid.masonry( 'destroy' );
				}

				// Cookie
				if( typeof cookie == 'undefined' )
					cookie = campus.filters;

				// Set new value
				$.each(campus.screen, function( key, name ) {
					if( typeof cookie.toggle_view[name] != 'undefined' )
						cookie.toggle_view[name] = value;
				});

				// Set cookie
				Cookies.set('campus-filters', cookie, { expires: 365, path: '/' });

				// Show content
/*
				setTimeout(function() {
					$body.addClass('loaded').removeClass('loading');
				}, 100 );
*/
			})

			// Toggle taxonomy aside

			.on('click', '.taxonomy-open-aside', function(e) {
				e.preventDefault();
				$(this).parents('.list-item').toggleClass( 'show-aside' );
			})

			// Toggle sidebar link
			.on('click', '.show-sidebar-link a', function(e) {
				e.preventDefault();
				$body.toggleClass( 'show-aside' );

				if( $body.hasClass( 'show-aside' ) ) {
					$body.addClass( 'has-overlay' );
				} else {
					$body.removeClass( 'has-overlay' );
				}
			})

			// Box links
			.on('click', '.box-link a.meta-button, a.box-link', function(e) {
				e.preventDefault();

				var $box = $($(this).attr('href'));

				$box.toggleClass('active');

				if( $box.hasClass( 'active' ) ) {
					$body.addClass( 'has-overlay' );
				} else {
					$body.removeClass( 'has-overlay' );
				}
			})

			// Close box
			.on('click', '.box', function(e) {
				if( $(e.target).hasClass('close-box') ) {
					e.preventDefault();
					$(this).removeClass('active');
					$body.removeClass( 'has-overlay' );
				}
			})

			// Return to top
			.on('click', '[rel="top"]', function(e) {
				e.preventDefault();

				$scrollElem.animate({
					scrollTop: 0
				}, 600, 'easeInOutExpo' );
			})

			// Tabs
			.on('click', '.tabs a', function(e) {
				e.preventDefault();

				var id = $(this).attr('href');

				$(this).parents('.tab')
					.addClass('current')
					.siblings().removeClass('current');

				$( '.tab-content' + id )
					.addClass('current')
					.siblings().removeClass('current');

				$( '.tab-content' + id )
					.find('input').first().focus();

			}).filter('.current').click();

		// Init fn
		initAccordion();

		// Document is ready
		setTimeout(function() {
			$body.addClass('loaded html-loaded').removeClass('loading html-loading');
		}, 300);
	});

	/**
	 * Body events
	 */
	$( 'body' )
		.on( 'post-load', function( object, response ) {

			if( response.type == 'success' ) {
				var $selector = $('#infinite-view-' + infinite_count);

				if( $selector.length ) {
					var $items = $selector.find('.list-item').ajaxify();

					$grid.append($items);

					if( $contentArea.hasClass('content-grid') && ! $body.hasClass( 'page-template-page-alt-programs' ) )
						$grid.masonry('appended', $items);

					infinite_count++;
				}
			}
	    } )

		.on( 'smn-infinite-scroll-posts-end', function() {
			var message,
				$contentInfos = $('.this-is-the-end');

			console.log(infinite_count);
			if( infinite_count < 5 ) {
				message = '';

			} else if( infinite_count < 10 ) {
				message = 'Félicitation ! vous êtes arrivé en bas de la page :)';

			} else if( infinite_count < 25 ) {
				message = 'Piouuuf ! C\'était long nan ? Quel jour on est ? :)';

			} else if( infinite_count < 50 ) {
				message = 'Vouuuuus ne passereeeezzzz paaaaaaasss !!! <small>En vrai, vous êtes arrivés en bas de la page</small> :)';

			} else {
				message = 'Enfin !!! Vous devriez appeler le Guinness Book des records, <small>je ne sais pas combien de temps vous avez mis pour arriver ici mais chapeau !</small> :)';
			}

			$contentInfos.find('.icon-title').html( message );

			infinite_count = 2;
		} );

	/**
	 * Window events
	 */
	$( window )
		.resize( function() {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( function() {
				if( ( $('.site-branding').height() + $('.navigation-primary').height() + $('.mejs-image').height() + $('.site-player').height() ) > $( window ).height() ) {
					$('.mejs-image').slideUp();
				} else {
					$('.mejs-image').slideDown();
				}
			}, 300 );
		})

		.scroll( function() {
			if( $( window ).scrollTop() > 0 )
				$body.addClass('scrolled');
			else
				$body.removeClass('scrolled');
		});

	// $('.fixed-scroll-wrap').on( 'touchmove', function(e) {
	// 	console.log(e.target);
	// } );

})( jQuery );
