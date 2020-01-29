// Set navGrid global
var navGrid;

( function( $ ) {

	/**
	 * Nav day
	 *
	 */
	navGrid = {

		// Vars
		today: 0,
		day: 0,
		cols: 0,
		timeHandleInterval: false,

		// Init
		init: function() {

			// Html elems
			this._scrollElem = $( 'html, body' );
			this._wrap = $('.content-wrap');
			this._nav = $('.nav-day');
			this._target = $('.content-hentry');
			this._elems = $('.programs-content');
			this._ref = $('.programs-day').eq(0);
			this._timeHandler = $('.time-handler');

			if( ! this._elems.length )
				return;

			//console.log(this._nav.data('date'));

			// Set today
			this.today = Number( new Date( this._nav.data('date') ).getDay() ) - 1;
			this.today = this.today < 0 ? 6 : this.today;

			// Set current day
			this.day = this.today;

			// So, display!
			this.setView();

			// Call the rest of this function once
			if( this._target.hasClass('nav-ready') )
				return;

			// Grid nav
			var _t = this;
			this._nav.find('a')
				.on( 'click', function(e) {
					e.preventDefault();

					if( $(this).hasClass('disabled') )
						return false;

					// Prev
					if( $(this).hasClass('nav-prev') ) {
						_t.day--;

					// Next
					} else if( $(this).hasClass('nav-next') ) {
						_t.day++;

					// Today
					} else if( $(this).hasClass('nav-today') ) {
						_t.day = _t.today;
					}

					_t.setDay();
				});

			this._target.addClass('nav-ready');
		},

		setVars: function() {
		},

		isGrid: function() {
			return ( $( '.view-switch a.current' ).data('view') == 'grid' || // Admin
				   $( '.toggle-view.current a.meta-button' ).attr('rel') == 'grid' );
		},

		// Current day position
		setDay: function( day ) {

			if( typeof day == 'undefined' )
				day = this.day;
			//this.setVars();

			this._elems.each(function() {
				$(this).children().eq(day).addClass('current').siblings().removeClass('current');
			});
			this.arrowStatus();
		},

		// Current day position
		currentDay: function() {

			this.setDay(this.day);
		},

		// Reset position to day 0
		resetDay: function() {

			this.setDay(0);
		},

		// Focus on current time
		currentTime: function() {
			var hour = new Date().getHours(),
				offsetTop, scrollTop;

				hour = hour < 10 ? '0' + hour : hour;
				offsetTop = $('.hour-' + hour + '-00').offset().top;

			if( this.isGrid() ) {
				scrollTop = offsetTop - $(window).height() / 2;
			} else {
				scrollTop = offsetTop - $( '.content-header' ).height();
			}

			this._scrollElem.scrollTop( scrollTop );
		},

		setTimeHandle: function() {
			var now = new Date(),
				currentTime = now.getSeconds() + ( 60 * now.getMinutes() ) + ( 60 * 60 * now.getHours() ),
				topPos = ( currentTime ) / ( 24 * 60 * 60 ) * 100;

				this._timeHandler.css({ top: topPos + '%' }).addClass( 'today-' + this.today );
		},

		// Arrow display
		arrowStatus: function() {

			this._nav.find('a').removeClass('disabled');

			// Prev
			if( this.day === 0 ) {
				this._nav.find('.nav-prev').addClass('disabled');

			// Next
			} else if( this.day === 6 ) {
				this._nav.find('.nav-next').addClass('disabled');
			}

			// Today
			if( this.day === this.today ) {
				this._nav.find('.nav-today').addClass('disabled');
			}
		},

		setView: function() {
			var _this = this;

			if(	this.isGrid() ) {

				if( $( window ).width() > 768 )
					this.resetDay();

				_this.setTimeHandle();
				this.timeHandleInterval = setInterval( function() { _this.setTimeHandle(); }, 60000 );

			} else {

				this.currentDay();
				clearInterval( _this.timeHandleInterval );
			}

			this.setDay();
			this.currentTime();
		}
	};

	/**
	 * Document ready
	 *
	 */
	$( document ).on('ready ajaxready', function(e) {
		if( $( 'body' ).hasClass( 'page-template-page-alt-programs' ) ) {
			navGrid.init();
			if( e.type == 'ajaxready' ) {
				setTimeout( function() {
					navGrid.currentTime();
				}, 300 );
			}
		}
	});

} )( jQuery );
