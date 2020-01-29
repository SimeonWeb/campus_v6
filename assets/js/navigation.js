/* global campusScreenReaderText */
/**
 * Theme functions file.
 *
 * Contains handlers for navigation and widget area.
 */

(function( $ ) {
	var masthead, menuToggle, siteNavContain, siteNavigation;

	function initMainNavigation( container ) {

		// Add dropdown toggle that displays child menu items.
		var dropdownToggle = $( '<button />', { 'class': 'dropdown-toggle', 'aria-expanded': false })
			.append( campusScreenReaderText.icon )
			.append( $( '<span />', { 'class': 'screen-reader-text', text: campusScreenReaderText.expand }) );

		container.find( '.menu-item-has-children > a, .page_item_has_children > a' ).after( dropdownToggle );

		var currentAncestor = container.find( '.current-menu-ancestor' ).length ? container.find( '.current-menu-ancestor' ) : container.find( '.menu-item-depth-0' );

		// Set the active submenu dropdown toggle button initial state.
		currentAncestor.find( '> button' ).first()
			.addClass( 'toggled-on' )
			.attr( 'aria-expanded', 'true' )
			.find( '.screen-reader-text' )
			.text( campusScreenReaderText.collapse );
		// Set the active submenu initial state.
		currentAncestor.find( '> .sub-menu' ).first().addClass( 'toggled-on' );
		currentAncestor.find( '> .sub-menu' ).first().parents('.menu-item').addClass( 'toggled-on' );

		container.find( '.dropdown-toggle' ).click( function( e ) {
			var _this = $( this ),
				screenReaderSpan = _this.find( '.screen-reader-text' );

			e.preventDefault();
			// Reset all opened .menu-item
			$('.menu-item, .dropdown-toggle, .children, .sub-menu').removeClass( 'toggled-on' );
			$( '.dropdown-toggle' ).attr( 'aria-expanded', 'false' ).find( '.screen-reader-text' ).text( campusScreenReaderText.expand );

			// Set this item
			_this.addClass( 'toggled-on' );
			_this.parents('.menu-item').addClass( 'toggled-on' );
			_this.next( '.children, .sub-menu' ).addClass( 'toggled-on' );
			_this.attr( 'aria-expanded', 'true' );

			screenReaderSpan.text( campusScreenReaderText.collapse );
		});

		// Remove toggled-on on user touch 
		$( 'body' ).on( 'touchstart', function(e) {
			if( ! $(e.target).parents('.sub-menu').length ) {
				removeToggledOn();
			}
		} );
	}

	function removeToggledOn() {
		var _this = $( '.dropdown-toggle.toggled-on' );

		_this.removeClass( 'toggled-on' );
		_this.parents('.menu-item').removeClass( 'toggled-on' );
		_this.next( '.children, .sub-menu' ).removeClass( 'toggled-on' );
	}

	initMainNavigation( $( '.main-navigation' ) );

	masthead       = $( '#masthead' );
	menuToggle     = $( '.menu-toggle' );
	siteNavContain = masthead.find( '.main-navigation' );
	siteNavigation = masthead.find( '.main-navigation > div > ul' );

	// Enable menuToggle.
	(function() {

		// Return early if menuToggle is missing.
		if ( ! menuToggle.length ) {
			return;
		}

		// Add an initial value for the attribute.
		menuToggle.attr( 'aria-expanded', 'false' );

		menuToggle.on( 'click.campus', function() {
			siteNavContain.add(menuToggle).toggleClass( 'toggled-on' );
			$( 'body' ).toggleClass( 'has-overlay' );

			$( this ).attr( 'aria-expanded', siteNavContain.hasClass( 'toggled-on' ) );
		});
	})();

	// Fix sub-menus for touch devices and better focus for hidden submenu items for accessibility.
	(function() {
		if ( ! siteNavigation.length || ! siteNavigation.children().length ) {
			return;
		}

		// Toggle `focus` class to allow submenu access on tablets.
		function toggleFocusClassTouchScreen() {
			if ( 'none' === $( '.menu-toggle' ).css( 'display' ) ) {

				$( document.body ).on( 'touchstart.campus', function( e ) {
					if ( ! $( e.target ).closest( '.main-navigation li' ).length ) {
						$( '.main-navigation li' ).removeClass( 'focus' );
					}
				});

				siteNavigation.find( '.menu-item-has-children > a, .page_item_has_children > a' )
					.on( 'touchstart.campus', function( e ) {
						var el = $( this ).parent( 'li' );

						if ( ! el.hasClass( 'focus' ) ) {
							e.preventDefault();
							el.toggleClass( 'focus' );
							el.siblings( '.focus' ).removeClass( 'focus' );
						}
					});

			} else {
				siteNavigation.find( '.menu-item-has-children > a, .page_item_has_children > a' ).unbind( 'touchstart.campus' );
			}
		}

		if ( 'ontouchstart' in window ) {
			$( window ).on( 'resize.campus', toggleFocusClassTouchScreen );
			toggleFocusClassTouchScreen();
		}

		siteNavigation.find( 'a' ).on( 'focus.campus blur.campus', function() {
			$( this ).parents( '.menu-item, .page_item' ).toggleClass( 'focus' );
		});
	})();

	// Document events
	$( document )

		// Ajaxify loading
		.on('ajaxloading', function() {
			siteNavContain.add(menuToggle).removeClass( 'toggled-on' );
			removeToggledOn();
		});

})( jQuery );
