/* global campus, mejs, Cookies, MediaElementPlayer */
(function( window, $ ) {

	window.campusPlayer = $( '#campus-player' );

	var campusPlayer = window.campusPlayer;
	var playerContainer;
	var player;
	var settings = {};

	// add mime-type aliases to MediaElement plugin support
	mejs.plugins.silverlight[0].types.push('video/x-ms-wmv');
	mejs.plugins.silverlight[0].types.push('audio/x-ms-wma');

	/*--------------------------------------------------------------
	Set builders
	--------------------------------------------------------------*/

	// Image
    MediaElementPlayer.prototype.buildimage = function( player, controls ) {

        $('<div class="mejs-image"></div>').appendTo( controls );
    };

	// More
    MediaElementPlayer.prototype.buildmore = function( player, controls, layers/* , media */ ) {
        var
            // create the more button
            more =
            $('<div class="mejs-button mejs-more">' +
				'<div class="meta-link more-link"><a class="meta-button" title="Options" href="#">' +
				    getIcon( 'live' ) +
				'</a></div>' +
        	'</div>')
            // append it to the toolbar
            .appendTo( controls );

        var
            // create the layers
			moreLayer =
            $('<div class="mejs-more-layer mejs-layer">' +
            	'<div class="mejs-layer-content">' +

					'<div class="layer-section columns-elem">' +
            			'<div class="live-link dynamic-link">' +
							'<a href="#" title="Écouter le direct">' + getIcon( 'live' ) + '<span class="icon-title">Écouter le direct</span></a>' +
						'</div>' +
						'<div class="podcast-link dynamic-link">' +
							'<a href="#" title="Reprendre la lecture du podcast">' + getIcon( 'podcast' ) + '<span class="icon-title">Reprendre la lecture du podcast</span></a>' +
						'</div>' +
            			'<div class="download-link dynamic-link">' +
            				'<a href="' + campus.player.podcast_infos.url + '" download target="_blank" title="Télécharger le podcast">' + getIcon( 'download' ) + '<span class="icon-title">Télécharger le podcast</span></a>' +
						'</div>' +
						'<div class="podcast-post-link dynamic-link">' +
							'<a href="#" title="Lire l\'article du podcast">' + getIcon( 'podcast-post' ) + '<span class="icon-title">Lire l\'article du podcast</span></a>' +
						'</div>' +
            			'<div class="search-song-link dynamic-link">' +
            				'<a title="Retrouve ton titre" href="#search-box" class="box-link">' + getIcon( 'search-song' ) + '<span class="icon-title">Rechercher un titre</span></a>' +
						'</div>' +
            			'<div class="programs-link dynamic-link">' +
            				'<a title="Tous les programmes" href="' + campus.programsUrl + '" class="box-link">' + getIcon( 'program' ) + '<span class="icon-title">Tous les programmes</span></a>' +
						'</div>' +
            			'<div class="player-popup-link dynamic-link min-1024">' +
            				'<a class="no-ajaxy" title="Écouter dans une nouvelle fenêtre (popup)" href="' + campus.playerUrl + '">' + getIcon( 'player-popup' ) + '<span class="icon-title">Écouter dans une nouvelle fenêtre (popup)</span></a>' +
						'</div>' +
					'</div>' +

					'<div class="layer-section podcasts-elem">' +
            			'<div class="meta-link big backward-10-link">' +
            				'<a class="meta-button" title="Revenir de 10 secondes" href="#">' + getIcon( 'backward-10' ) + '</a>' +
						'</div>' +
            			'<div class="meta-link big backward-30-link">' +
            				'<a class="meta-button" title="Revenir de 30 secondes" href="#">' + getIcon( 'backward-30' ) + '</a>' +
						'</div>' +
						'<div class="sep mejs-time-clone"></div>' +
            			'<div class="meta-link big forward-30-link">' +
            				'<a class="meta-button" title="Avancer de 30 secondes" href="#">' + getIcon( 'forward-30' ) + '</a>' +
						'</div>' +
            			'<div class="meta-link big forward-10-link">' +
            				'<a class="meta-button" title="Avancer de 10 secondes" href="#">' + getIcon( 'forward-10' ) + '</a>' +
						'</div>' +
					'</div>' +
            	'</div>' +
            '</div>')
            // append it to the toolbar
            .appendTo( layers );

		// More link event
		more.find('.more-link')
			.on( 'click', function(e) {
				e.preventDefault();

				moreLayer.toggleClass( 'toggled-on' );
			} )
			.on( 'mouseenter', function(e) {
				e.preventDefault();

				moreLayer.addClass( 'toggled-on' );
			} );

		$( document )
			.on( 'click', function(e) {
				if( ! $(e.target).parents('.mejs-container').length && moreLayer.is(':visible') )
					moreLayer.removeClass( 'toggled-on' );
			} )

			.on('ajaxloading', function() {
				moreLayer.removeClass( 'toggled-on' );
			});

		var timer;

		// More layer events
		moreLayer

			// Close layer only after 300 milliseconds
			.on( 'mouseenter', function(e) {
				e.preventDefault();

				clearTimeout( timer );
			} )

			// Close layer when mouse leave
			.on( 'mouseleave', function(e) {
				e.preventDefault();

				timer = setTimeout(function() {
					moreLayer.removeClass( 'toggled-on' );
				}, 300);
			} )

			// Time actions
			.find('.backward-10-link').click(function(e) {
				e.preventDefault();
				player.setCurrentTime( player.getCurrentTime() - 10 );
			}).end()
			.find('.forward-10-link').click(function(e) {
				e.preventDefault();
				player.setCurrentTime( player.getCurrentTime() + 10 );
			}).end()
			.find('.backward-30-link').click(function(e) {
				e.preventDefault();
				player.setCurrentTime( player.getCurrentTime() - 30 );
			}).end()
			.find('.forward-30-link').click(function(e) {
				e.preventDefault();
				player.setCurrentTime( player.getCurrentTime() + 30 );
			});
    };

	// Time
	MediaElementPlayer.prototype.buildtime = function( player, controls, layers, media ) {
		var t = this;

		$('<div class="mejs-time" role="timer" aria-live="off">' +
			'<div class="mejs-podcast-time" style="display: none;">' +
				'<span class="mejs-currenttime">' +
					( player.options.alwaysShowHours ? '00:' : '' ) +
					( player.options.showTimecodeFrameCount ? '00:00:00':'00:00' ) +
				'</span>'+
				'<span class="mejs-duration" style="display: none;">' +
					( t.options.duration > 0 ?
						mejs.Utility.secondsToTimeCode( t.options.duration, t.options.alwaysShowHours || t.media.duration > 3600, t.options.showTimecodeFrameCount, t.options.framesPerSecond || 25 ) :
						( ( player.options.alwaysShowHours ? '00:' : '' ) + ( player.options.showTimecodeFrameCount ? '00:00:00':'00:00' ) )
					) +
				'</span>' +
			'</div>' +
			'<div class="mejs-live-time"></div>' +
		'</div>')
		// append it to the toolbar
        .appendTo( controls );

		t.currenttime = t.controls.find('.mejs-currenttime');
		t.durationD = t.controls.find('.mejs-duration');

    	// Add current time cookie
		media.addEventListener( 'timeupdate', function() {

			if( isPodcast() ) {
				Cookies.set( 'campus-player-current-time', media.getCurrentTime(), { expires: 30, path: '/' } );
				$('.mejs-time-clone').html( $('.mejs-podcast-time').html() );

				player.updateDuration();
				player.updateCurrent();
			}

		}, false );

		// live time
	    setInterval( function() {
		    if( ! isPodcast() )
				t.setCurrentLiveRail( player, controls, layers, media );
		}, 150);

    };

	/*--------------------------------------------------------------
	Player functions
	--------------------------------------------------------------*/

	// Ajax load for live infos
	MediaElementPlayer.prototype.loadLiveInfos = function() {

		if( this.loadingLiveInfos )
			return false;

		var t = this;
			t.loadingLiveInfos = true;

		$.ajax({
		    url: campus.ajaxUrl,
		    type: 'POST',
		    dataType: 'json',
		    data: {
		    	action: 'get_live_results'
		    },
		    success : function( result ) {
		    	campus.player.live_infos = result;

				// DEBUG
				if( $('pre.ajax').length )
					$('pre.ajax').html(JSON.stringify(result.results));

				t.setPlayerInfos( false );

				setTimeout(function() {
					t.loadingLiveInfos = false;
				}, 5000);
		    }
		});
	};

	// Set podcast time rail
	MediaElementPlayer.prototype.setCurrentLiveRail = function( player, controls/*, layers, media*/ ) {
		var t = this,
		liveRail = controls.find('.mejs-time-live');

		//liveRail = ( typeof this.liveRail == 'undefined' ) ? t.controls.find('.mejs-time-live')[0] : this.liveRail;

		if( campus.player.live_infos.start !== undefined && campus.player.live_infos.duration && campus.player.live ) {

			var date = new Date(),
				currentTime = Math.floor( date.getTime() / 1000 ) - campus.player.live_infos.start; // - (date.getTimezoneOffset()*60)

			// Load player infos when the song is ended
			if( currentTime > campus.player.live_infos.duration && ! t.loadingLiveInfos ) {
				t.loadLiveInfos();

			// update bar and handle
			} else {
				var newWidth = currentTime / campus.player.live_infos.duration;
				newWidth = newWidth > 1 ? 1 : newWidth;
				liveRail.css({ transform: 'scaleX(' + newWidth + ')' });
			}

		} else {

			liveRail.css({ transform: 'scaleX(0)' });
		}

	};

	// Set player infos
	// this = mejs mediaelementwrapper node
	MediaElementPlayer.prototype.setPlayerInfos = function( reset ) {

		var data,
			currentTime;

		// Set reset
		reset = typeof( reset ) !== 'undefined' ? reset : true;

		// Set forceLive option
		//this.options.forceLive = campus.player.live;

		/*--------------------------
		Prepare data
		--------------------------*/

		// Podcast
		if( isPodcast() ) {

			// Get data from campus object
			data = Object.assign( {}, campus.player.podcast_infos );

			if( data.link && ! /href/.test(data.image) ) {
				data.image = '<a href="' + data.link + '">' + data.image + '</a>';
			}

			// Set Time
			var cookieTime = Cookies.get( 'campus-player-current-time' );
			currentTime = ! reset && cookieTime ? cookieTime : 0;

			// Toggle time
			this.controls.find('.mejs-podcast-time').show();
			this.controls.find('.mejs-live-time').hide();

			// Set cookie
			Cookies.set('campus-player-podcast-infos', data, { expires: 30, path: '/' });

			// Add class to time rail
			this.controls.find('.mejs-time-rail').removeClass('mejs-live-time-rail').addClass('mejs-podcast-time-rail');

		// Live
		} else {

			// Get data from campus object
			data = Object.assign( {}, campus.player.live_infos );

			// Set setSrc
			data.url = campus.player.settings.url;

			if( reset )
				data.url += '?' + new Date().getTime(); // Add timestamp to force reload of live src

			// Display time
			this.controls.find('.mejs-live-time').html( data.display_time );

			// Toggle time
			this.controls.find('.mejs-podcast-time').hide();
			this.controls.find('.mejs-live-time').show();

			// Add type to class
			data.timeRailClass = 'mejs-live-time-rail';

			// Add class to time rail
			this.controls.find('.mejs-time-rail').removeClass('mejs-podcast-time-rail').addClass('mejs-live-time-rail');
		}

		/*--------------------------
		Push data
		--------------------------*/

		// Set source of media
		if( isPodcast() || ! isPodcast() && reset )
			this.setSrc( data.url );
		//this.$media.attr( 'src', data.url );

		// Add title to media
		this.$media.attr( 'title', data.name );

		// Set currentTime to media
		if( typeof currentTime != 'undefined' )
			this.media.setCurrentTime( currentTime );

		// Add image
		this.controls.find('.mejs-image').html( '<div class="post-thumbnail">' + data.image + '</div>' );

		// Add title
		if( this.controls.find('.mejs-time-title').length )
			this.controls.find('.mejs-time-title').html( data.title );
		else
			this.controls.find('.mejs-time-rail').prepend( '<div class="mejs-time-title">' + data.title + '</div>' );

		// Add class to mejs-inner
		this.controls.parent().removeClass(function(index, className) {
			return className.replace( 'mejs-inner', '' );
		}).addClass(data.class);

		// Set layer items
		this.setLayer();

		// Reset hentry class
		$('.hentry').removeClass('current paused');

		// Trigger events
		$( document ).trigger( 'setplayerinfos', [this, data] );

		// Set cookie
		Cookies.set('campus-player-live', campus.player.live ? 1 : 0, { expires: 30, path: '/' });
	};

	// Reset live source and settings
	MediaElementPlayer.prototype.setLive = function() {

		//if there's nothing in queue
		campus.player.live = 1;

		this.setPlayerInfos();

		return true;
	};

	// Set podcast source and settings
	MediaElementPlayer.prototype.setPodcast = function( data ) {

		if( typeof data == 'undefined' || ! data.url ) {
			alert( 'Le podcast ne peut pas être ajouté car les données ne sont pas conformes.' );
			return false;
		}

		var reset = data.url != campus.player.podcast_infos.url;

		campus.player.live = 0;
		campus.player.podcast_infos = data;

		// Reset if url is different
		this.setPlayerInfos( reset );

		return true;
	};

	// Share
    MediaElementPlayer.prototype.setLayer = function() {

        if( isPodcast() ) {

	        $('.dynamic-link.podcast-link').hide().find('a').data( 'podcast', campus.player.podcast_infos );
	        $('.dynamic-link.download-link').show().find('a').attr( 'href', campus.player.podcast_infos.url );
			$('.dynamic-link.podcast-post-link').show().find('a').attr( 'href', campus.player.podcast_infos.link ); //.ajaxify()

	        $('.layer-section.podcasts-elem').slideDown();
        } else {

			$('.dynamic-link.podcast-link, .dynamic-link.download-link, .dynamic-link.podcast-post-link').hide();

	        if( campus.player.podcast_infos.url )
	        	$('.dynamic-link.podcast-link').show().find('a').data( 'podcast', campus.player.podcast_infos ).attr( 'title', $('.dynamic-link.podcast-link').text() + ' : ' + campus.player.podcast_infos.name );

	        $('.layer-section.podcasts-elem').slideUp();
        }
    };

	// Get icon
	var getIcon = function( icon ) {
		return '<svg class="icon icon-' + icon + '" aria-hidden="true" role="img"><use href="#icon-' + icon + '" xlink:href="#icon-' + icon + '"></use></svg>';
	};

	var isPodcast = function() {
		return ( ! campus.player.live && typeof campus.player.podcast_infos != 'undefined' && campus.player.podcast_infos.url !== '' );
	};

	/**
	 * Player popup
	 */
	var getPopupSpecs = function() {

		if( typeof campus == 'undefined' )
			return false;

		if( isPodcast() ) {
			campus.popup.height = campus.popup.podcastHeight;
		} else {
			campus.popup.height = campus.popup.liveHeight;
		}
		delete campus.popup.podcastHeight;
		delete campus.popup.liveHeight;

		var specs = '';

		$.each(campus.popup, function(k, v) {
			specs += k+'='+v+',';
		});

		return specs;
	};

	/*--------------------------------------------------------------
	Player controls from the site
	--------------------------------------------------------------*/

	$(document)

		// DEBUG
		.ready(function() {

			$('.add-ajax-results').click(function() {
				$('pre.ajax').html(JSON.stringify(campus.player.live_infos.results));
			});

			$('.load-ajax-results').click(function() {
				player.loadLiveInfos();
			});
		})

		// Ajax content loaded
		.on( 'ajaxready', function() {
			if( ! isPodcast() )
				player.setPlayerInfos( false );
		} )

		// Time toggle
		.on( 'click', '.mejs-time-wrap', function() {
			if( $(this).children('.mejs-currenttime').is(':visible') ) {
				$('.mejs-currenttime').hide();
				$('.mejs-duration').show();
			} else {
				$('.mejs-currenttime').show();
				$('.mejs-duration').hide();
			}
    	} )

		// Add to player
		.on( 'click', '.podcast-link a', function(e) {
		    e.preventDefault();

			var button = $(this),
				post = button.parents('.hentry');

			// Podcast is already the current one
			if( post.hasClass('current') ) {

				if( post.hasClass('paused') ) {

					player.play();

				} else {

					player.pause();

				}

			// Add the new podcast
			} else if( $(this).data('podcast') !== '' ) {

				player.pause();

				if( player.setPodcast( $(this).data('podcast') ) )
					player.play();
			}
		} )

		// Live selector
		.on( 'click', '.live-link a', function(e) {
		    e.preventDefault();

		    player.pause();

		    if( player.setLive() )
			    player.play();
		} )

		// Play/Pause external
		.on( 'click', '.playpause-link', function(e) {
		    e.preventDefault();

		    if( player.paused ) {
				player.play();
			} else {
				player.pause();
			}
		} )

		// Popup
		.on( 'click', '.player-popup-link a', function(e) {
		    e.preventDefault();

			if( campus.playerUrl ) {

				player.pause();
				window.open( campus.playerUrl + '?autoplay=1', '_blank', getPopupSpecs() );
			}
		} );


	/*--------------------------------------------------------------
	Initialize media elements.

	Ensures media elements that have already been initialized won't be
	processed again.
	--------------------------------------------------------------*/

	// Set settings
	// Doc: https://github.com/mediaelement/mediaelement/blob/master/src/js/player.js
	settings = {
// 		classPrefix: 'campusmejs-', Wordpress override this setting
		setDimensions: false,
		alwaysShowHours: false,
		enableAutosize: false,

		//forceLive: campus.player.live,

		// doc: https://github.com/mediaelement/mediaelement/blob/master/src/js/features/
		features: ['image', 'playpause', 'progress', 'time', 'more' ],

		// Init player actions
		// mejs: player media element
		success: function( mejs ) {

			// Set autoplay
			var autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
			if ( 'flash' === mejs.pluginType && autoplay ) {
				mejs.addEventListener( 'canplay', function () {
					mejs.play();
				}, false );
			}

			// Get html player
			var playerElem = $(mejs).parents('.mejs-container');

			// Add svg to play button
			playerElem.find('.mejs-playpause-button button').html( campus.player.settings.button_playpause );

			// Add live time rail
			if( ! playerElem.find('.mejs-time-live').length ) {
				playerElem.find('.mejs-time-total').append('<span class="mejs-time-live"></span>');
				mejs.liveRail = playerElem.find('.mejs-time-live')[0];
			}

    		// Add some stuff on play event
			mejs.addEventListener( 'play', function() {

				$( document ).find( '.playpause-link' ).parents( '.list-item' ).addClass('current').removeClass('paused');

				//Podcasts
				if( isPodcast() )
					$( document ).find( '#post-' + campus.player.podcast_infos.id ).addClass('current').removeClass('paused');
			} );

    		// Add some stuff on pause event
			mejs.addEventListener( 'pause', function() {

				$( document ).find( '.playpause-link' ).parents( '.list-item' ).addClass('paused');

				//Podcasts
				if( isPodcast() )
					$( document ).find( '#post-' + campus.player.podcast_infos.id ).addClass('paused');
			} );

    		// Add some stuff on ended event
			mejs.addEventListener( 'ended', function() {

				$( document ).find( '.playpause-link' ).parents( '.list-item' ).removeClass('current paused');

				//Podcasts
				if( isPodcast() ) {
					$( document ).find( '#post-' + campus.player.podcast_infos.id ).removeClass('current paused');
					if( ( campus.player.live_infos.end * 1000 ) < Date.now() ) {
						player.loadLiveInfos();
					}
				}

				player.setLive();
			} );
		}
	};

	// Initialize new media element.
	campusPlayer.mediaelementplayer( settings );

	// For safari
	playerContainer = campusPlayer.parents('.mejs-container').length ? campusPlayer.parents('.mejs-container') : $('.site-player .mejs-container');

	player = mejs.players[playerContainer.attr('id')];
	player.setPlayerInfos( false );

})( window, jQuery );
