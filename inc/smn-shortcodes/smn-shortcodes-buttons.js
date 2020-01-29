/* global tinymce, ajaxurl */

(function($) {

    tinymce.create('tinymce.plugins.smnShortcodes', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed/*, url*/) {

			if( typeof tinymce.plugins.smnShortcodes.dialog_html == 'undefined' ) {
				// Get dialog html content
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
					    action: 'smn_shortcodes_dialog'
					},
					success: function( json ) {
					    tinymce.plugins.smnShortcodes.dialog_html = json;
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						console.log(jqXHR, textStatus, errorThrown);
					}
				});
			}

			var buttons = {
				the_title: {
					title: 'Affiche le titre de la page',
					icon: 'header',
                    closeTag: false
				},
				button: {
					title: 'Bouton',
					icon: 'square-o',
					html: '<a href="{href}" class="button button-{color} size-{size} {class}">{content}</a>'
				},
				column: {
					title: 'Colonnes',
					icon: 'columns',
                    closeTag: true
				},
				svg: {
					title: 'Icône',
					icon: 'flag',
					// html: '<svg style="{style}" class="icon icon-{icon} {class}" aria-hidden="true" role="img"><use xlink:href="#icon-{icon}" href="#icon-{icon}"></use></svg>',
                    closeTag: false
				},
				accordion: {
					title: 'Accordéon',
					icon: 'th-list',
                    closeTag: true
				},
				termlist: {
					title: 'Liste de termes',
					icon: 'list-alt',
                    closeTag: false
				}
			};
			var _t = this;

			$.each( buttons, function( name, button ) {

				// button
				ed.addButton('smnButton_' + name, {
	                title : button.title,
	                cmd : 'smnCmd_' + name,
	                icon : 'fa fa-' + button.icon
	            });

	            // command
	            ed.addCommand('smnCmd_' + name, function() {
					_t.command( ed, name, button );
	            });
			} );

			// For svg icon
			$(document).on('change', '.smn_sc_input[name="row"]', function() {
                if( $(this).is(':checked') ) {
                    $('.smn_sc_input_wrap-row:nth-of-type(2)').show();
                    $('.smn_sc_input_wrap-col, .smn_sc_input_wrap-offset').hide();
                } else {
                    $('.smn_sc_input_wrap-row:nth-of-type(2)').hide();
                    $('.smn_sc_input_wrap-col, .smn_sc_input_wrap-offset').show();
                }
            });

			// For svg icon
			$(document).on('change', '#smnShortcode_icon_select', function() {
                $('#smnShortcode_icon').find('use').attr('href', '#icon-' + $(this).val() );
            });
        },

        /**
         * Command to display box
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {String} name Id of the control to create.
         * @param {Object} object object of the control to create.
         * @return void
         */
		command : function( ed, name, object ) {
            if( name  == 'column' )
		              console.log(tinymce.plugins.smnShortcodes.dialog_html[name]);

			// just return shortcode
			if( typeof tinymce.plugins.smnShortcodes.dialog_html[name] == 'undefined' ) {

				ed.execCommand( 'mceInsertContent', 0, '[' + name + ']' );

			// If there's options
			} else {

				ed.windowManager.open({
					title: object.title,
					body: [{
						type   : 'container',
						html   : tinymce.plugins.smnShortcodes.dialog_html[name]
					}],
					buttons: [{
						slug: 'smnClose',
						text: 'Valider',
						onclick: function() {

							var fields = $('.smn_sc_input'),
								last = fields.length - 1,
								suffix = '',
								attr = '',
                                groups = {},
								content = ed.selection.getContent(),
								return_text = '',
								return_html = object.html || '';

    						$('.smn_sc_input[data-group]').serializeArray().reduce(function(obj, item) {
                                var gName = item.name.substring( 0, item.name.indexOf('[') );

                                if( typeof groups[gName] == 'undefined' )
                                    groups[gName] = item.value;
                                else
                                    groups[gName] += ',' + item.value;

    						    return groups;
    						}, {});

                            $.each( groups, function( attrName, attrVal ) {
                                if( attrVal != '0,0,0,0,0' )
                                    attr += ' ' + attrName + '="' + attrVal + '"';
                            } );

							fields.each(function( i ) {
								var _t = $(this),
									attrName = _t.attr('name'),
									attrVal = _t.val(),
                                    group = typeof _t.data('group') != 'undefined',
									inputValid = _t.attr('type') != 'checkbox' && ( attrVal !== '' && attrVal != ' ' && attrVal != '-1'  ),
									checkboxValid = _t.attr('type') == 'checkbox' && _t.is(':checked');

								// Check value
								if( ( inputValid || checkboxValid ) && ! group ) {
									attr += ' ' + attrName + '="' + attrVal + '"';

									return_html = return_html.replace( new RegExp( '{' + attrName + '}', "g"), attrVal );

									if( typeof _t.data('suffix') != 'undefined' )
										suffix = _t.data('suffix');
								}

								// Return shorcode
								if( last == i ) {

									if( typeof object.html != 'undefined' ) {

										// add content
										return_html	= return_html.replace( new RegExp( '{content}', "g"), content );
										return_text = return_html;

									} else {

										// shorcode html
										return_text = '[' + name + suffix + attr + ']';

										// add content and close braket
										if( object.closeTag ) {
											return_text	+= content + '[/' + name + suffix + ']';
										}
									}

									// Add to editor and close modal
									ed.execCommand( 'mceInsertContent', 0, return_text );
									ed.windowManager.close();

								}
							});
						}
					}]
				});
			}
		},

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
	        console.log(n, cm);
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'Siméon Shortcodes Buttons',
                author : 'Siméon ||/\\() Web Créateur',
                authorurl : 'http://simeon.web-createur.com',
                infourl : 'http://simeon.web-createur.com',
                version : "0.2"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add( 'smn_shortcodes', tinymce.plugins.smnShortcodes );

})(jQuery);
