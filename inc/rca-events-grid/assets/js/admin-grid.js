/* global navGrid, rcaEventGrid, tb_show, Cookies */
( function( $ ) {
	
	/**
	 * View
	 *
	 */
	$( '.view-switch a' )
		.on( 'click', function(e) {
			e.preventDefault();
			
			var type = $(this).parents('.wrap').data('type'),
				view = $(this).data('view');
			
			$('.content-wrap').removeClass('content-grid content-list content-list-detail').addClass('content-'+view);
			$(this).addClass('current').siblings().removeClass('current');
			
			navGrid.setView();
			
			Cookies.set('calendar_' + type + '_view', view, { expires: 365, path: '/wp-admin' });
		} );
		
	$( '.view-switch select' )
		.on( 'change', function(e) {
			e.preventDefault();
			
			var type = $(this).parents('.wrap').data('type'),
				filter = $(this).val();
			
			Cookies.set('calendar_' + type + '_filter', filter, { expires: 365, path: '/wp-admin' });
		} );
	
	$( '.date-picker-button' )
		.on( 'click', function(e) {
			e.preventDefault();
			
			$('#date-picker').toggle();
		} );
	
	/**
	 * Add Event on grid
	 *
	 */
	$( document )
		.on( 'DOMNodeRemoved', '#TB_load', function() {
			$.timepicker.setDefaults( $.timepicker.regional.fr );
			$('[type="time"]').timepicker();
			
			$('input[type="date"]').datepicker( { 
				dateFormat : 'yy-mm-dd',
				beforeShow: function() {					
					var startDate = $('#start_date').datepicker( 'getDate' );
					if( $(this).attr('id') == 'end_date' && startDate ) {					
						var newDate = startDate;
						
						$('#end_date').datepicker( 'option', 'minDate', new Date(newDate) );
					}
				}
			} );	
				
		})
		.on( 'click', '.wrap[data-can-add-event="1"] .programs-content .programs-day', function(e) {
			e.preventDefault();
			
			if( $(e.target).hasClass('programs-day') ) {
				
				var hourHeight = 4.16667,
					elemHeight = $(this).height(),
					cursorPosY = e.offsetY,
					date = new Date( $(this).data('date') ),
					hour = Math.floor( ( 24 / elemHeight ) * cursorPosY ),
					newDate = date.setHours( hour ),
					url = rcaEventGrid.add_event_form_url;
				
				if( url ) {
				
					$('<article class="programs-entry-temp programs-entry duration-3600 hour-'+hour+'-00-00"><div class="program category-autres"></div></div>').css({top: (hourHeight * hour) + '%', height: hourHeight + '%'})
						.appendTo($(this));
					
					url += '&calendar=' + $(this).parents('.wrap').data('type');
					url += '&event_date=' + newDate / 1000;
					
					console.log(url);
					
					tb_show( 'Ajouter un événement', url );
				}
			}
		});
	
	$( 'body' ).bind( 'thickbox:removed', function() {
		$('.programs-entry-temp').remove();
	} );
	
	/**
	 * Form
	 *
	 */
	$( document )
		.on( 'click', '.event-add-attendee', function(e) {
			e.preventDefault();
			
			var _attendeesWrap = $(this).siblings('.event-attendees'),
				_attendees = _attendeesWrap.find('li'),
				_newAttendee = _attendees.first().clone();
			
			_newAttendee.find('[name^="event"]').each(function() {
				$(this).attr('name', $(this).attr('name').replace('[0]', '['+ _attendees.length +']') );
				if( $(this).attr('type') == 'checkbox' ) {
					$(this).attr('id', $(this).attr('id').replace('-0-', '-'+ _attendees.length +'-') );
					$(this).removeAttr('checked');
				} else if( $(this).attr('type') != 'hidden' )
					$(this).val('');
			});
			
			_newAttendee.appendTo(_attendeesWrap);
			
		})
		.on( 'change', '[name^="event"]', function() {
			console.log( $(this).attr('name') + ' change' );
			$('#major-publishing-actions [type="submit"]').removeAttr('disabled');
		})
		.on( 'submit', '.admin-event-form', function() {
			
			var _t = this,
				_inputs = $('.event-attendees [name$="[email]"]'),
				checkEmails = [];
			
			_inputs.removeClass('error');
			
			_inputs.each(function(i) {
				
				if( $.inArray( $(this).val(), checkEmails ) > -1 ) {
					$(this).addClass('error');
				}
				
				if( $(this).val() !== '' )
					checkEmails[i] = $(this).val();
					
			});
			
			if( _inputs.filter('.error').length === 0 ) {
				$('#major-publishing-actions [type="submit"]').attr('disabled', 'disabled');
				$(_t).find('#major-publishing-actions .spinner').css({'visibility': 'visible'});
				return _t;
			}
			
			return false;
			
		});
	
	
	/**
	 * Document ready
	 *
	 */
	$( document ).ready(function() {
		
		$('#date-picker').datepicker({
			showOtherMonths: true,
			dateFormat: 'yy-mm-dd',
			onSelect: function( dateText ) {				
				document.location = document.location.href + '&date=' + dateText;
			}
		});
		
	});
	
} )( jQuery );