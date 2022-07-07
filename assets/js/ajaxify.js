/* Ajaxify
 * v1.0.1 - 30 September, 2012
 * https://github.com/browserstate/ajaxify
 */
/* global campus */
(function(window,undefined){

	// Prepare our Variables
	var
		History = window.History,
		$ = window.jQuery,
		document = window.document;

	// Check to see if History.js is enabled for our Browser
	if ( ! History.enabled ) {
		return false;
	}

	// Wait for Document
	$(function(){
		// Prepare Variables
		var
			/* Application Specific Variables */
			contentSelector = '#content',
			$content = $(contentSelector),
			$menu = $('#menu-general'),
			activeClass = 'current-menu-item',
			ancestorClass = 'current-menu-ancestor',
			menuChildrenSelector = '.menu-item',
			/* Application Generic Variables */
			$window = $(window),
			$document = $(document),
			$body = $(document.body),
			rootUrl = campus.url;

		// Ensure Content
		if ( $content.length === 0 ) {
			$content = $body;
		}

		// Internal Helper
		$.expr[':'].internal = function(obj/* , index, meta, stack */){
			// Prepare
			var
				$this = $(obj),
				url = $this.attr('href')||'',
				isInternalLink;

			// Check link
			isInternalLink = url.substring(0,rootUrl.length) === rootUrl || url.indexOf(':') === -1;

			// Ignore or Keep
			return isInternalLink;
		};

		// Get all POSTable data from form
		var getFormData = function(selector) {
			const formData = new FormData(selector);

			const data = {};
			formData.forEach((value, key) => (data[key] = value));

			return data;
		};

		var getFormUrlParams = function(data) {
			const formData = Object.entries(data)

			if (formData.length === 0) {
				return ""
			}

			return '?' + formData.map(([key, value]) => {
				return key + "=" + value
			}).join('&');
		}

		var parseData = function( data, referer ) {

			// Prepare
			var $dataContent = $(data.content),
				relativeUrl = referer.replace(rootUrl,''),
				$menuChildren, $activeMenuItem, $scripts;

			// Fetch campus var
			//campus = data.campus;

			// Fetch the scripts
			$scripts = $(data.scripts);
			$body.find('.ajaxify-script').remove();

			// Fetch the content
			if ( !$dataContent ) {
				document.location.href = referer;
				return false;
			}

			// Update body classes
			$body.attr( 'class', data.bodyClass );

			// Update the menu
			$activeMenuItem = $(data.menu).find(menuChildrenSelector).filter( '.' + activeClass );
			$menuChildren = $menu.find(menuChildrenSelector);
			$menuChildren.removeClass( activeClass + ' ' + ancestorClass )
				.find( '#' + $activeMenuItem.attr('id') ).addClass( activeClass )
				.parents( menuChildrenSelector ).addClass( ancestorClass );

			// Update the content
			$content.stop(true,true);
			$content.html('').append($dataContent).ajaxify();

			// Update the title
			if( data.wpTitle === '' )
				document.title = campus.name + ' - ' + campus.description;
			else
				document.title = data.wpTitle;
			try {
				document.getElementsByTagName('title')[0].innerHTML = document.title.replace('<','&lt;').replace('>','&gt;').replace(' & ',' &amp; ');
			}
			catch ( Exception ) { }

			// Add the scripts
			$scripts.each(function(){
				var $script = $(this), scriptText = $script.text(), scriptNode = document.createElement('script');

				scriptNode.className = 'ajaxify-script';

				if ( $script.attr('src') ) {
					if ( !$script[0].async ) { scriptNode.async = false; }
					scriptNode.src = $script.attr('src');
				}
    				scriptNode.appendChild(document.createTextNode(scriptText));
				document.body.appendChild(scriptNode);
			});

			// Complete the change
			$window.trigger('statechangecomplete');
			$document.trigger('ajaxready');
			$('html, body, .sub-menu').scrollTop(0);
			setTimeout(function() {
				$body.removeClass('loading').addClass('loaded');
			}, 300);

			// Inform Google Analytics of the change
			if ( typeof window._gaq !== 'undefined' ) {
				window._gaq.push(['_trackPageview', relativeUrl]);
			}

/*
			// Inform ReInvigorate of a state change
			if ( typeof window.reinvigorate !== 'undefined' && typeof window.reinvigorate.ajax_track !== 'undefined' ) {
				reinvigorate.ajax_track(url);
				// ^ we use the full url here as that is what reinvigorate supports
			}
*/
		};

		// Ajaxify Helper
		$.fn.ajaxify = function(){
			// Prepare
			var $this = $(this);

			// Ajaxify
			$this.find('*:not(.no-ajaxy) > a:internal:not(.no-ajaxy,[href^="#"],[href*="wp-login"],[href*="wp-admin"],[href*="adrotate"],[href$=".jpg"],[href$=".jpeg"],[href$=".gif"],[href$=".png"],[target="_blank"])').click(function(event){
				// Prepare
				var
					$this = $(this),
					url = $this.attr('href'),
					title = $this.attr('title')||campus.name + ' - ' + campus.description;

				// Continue as normal for cmd clicks etc
				if ( event.which == 2 || event.metaKey ) { return true; }

				// Ajaxify this link
				History.pushState(null,title,url);
				event.preventDefault();
				return false;
			});

			// Chain
			return $this;
		};

		// Ajaxify our Internal Links
		$body.ajaxify();

		// Hook into State Changes
		$window.bind('statechange',function(){
			// Prepare Variables
			var
				State = History.getState(),
				url = State.url;

			// Set Loading
			$body.addClass('loading').removeClass('loaded');
			$document.trigger('ajaxloading');

			// Ajax Request the Traditional Page
			$.ajax({
				url: url,
				type: 'POST',
				data: {
					ajax: true
				},
				dataType: 'json',
				success: function(data/* , textStatus, jqXHR */){
					//console.log(textStatus, data, jqXHR);

					parseData( data, url );
				},
				error: function(jqXHR, textStatus/* , errorThrown */){
					console.error(textStatus, jqXHR.responseText);
					document.location.href = url;
					return false;
				}
			}); // end ajax

		}); // end onStateChange

		// Form submit
		$document.on( 'submit', 'form:not(.wpcf7-form)', function() {

			if( $body.hasClass('loaded') ) {

				// Set Loading
				$body.addClass('loading').removeClass('loaded');
				$document.trigger('ajaxloading');

				const url = $(this).attr('action').length ? $(this).attr('action') : campus.url
				const method = $(this).attr('method').length ? $(this).attr('method') : 'get'
				const data = getFormData(this)
				const fullUrl = url + getFormUrlParams(data)

				data.ajax = true;

				if( method.toLowerCase() == 'get' ) {

					History.pushState(null,null,fullUrl);

				} else {

					$.ajax({
						url: url,
						type: method,
						data: data,
						dataType: 'json',
						success : function( /*data, textStatus, jqXHR */ ) {

							// Reload
							$window.trigger( 'statechange' );
						},
						error: function(jqXHR, textStatus/* , errorThrown */){
							console.error(textStatus, jqXHR.responseText);
							//document.location.href = referer;
							// Reload
							$window.trigger( 'statechange' );
							return false;
						}
					});
				}
			}
			return false;
		}); // end form submit

	}); // end onDomLoad

})(window); // end closure
