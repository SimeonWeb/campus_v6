<?php
/**
 * RCA_Calendar
 *
 * Version 1.0
 */

class RCA_Booking_Calendar {
	
	/**
	 * Get admin event html
	 *
	 */	
	public static function admin_grid( $program ) {			
		global $current_screen;
		
		$name = $program->getSummary();
		
		$user_can_edit_program_infos = current_user_can( 'manage_categories' ) && is_admin();
		
		$edit_url = add_query_arg( 
		    array( 
		        'action' 	 => 'admin_edit_event_form', 
		        'calendar' 	 => 'booking', 
		        'event_id' 	 => $program->getId(), 
		        'wp_referer' => ! empty( $current_screen->parent_base ) ? admin_url( 'admin.php?page=' . $current_screen->parent_base ) : admin_url(),
		        'width'		 => 320,
		        'height'	 => 465
		    ), 
		    admin_url( 'admin-ajax.php' ) 
		);
		
		$title = '<hgroup>';
			$title .= '<div class="category-title" title="' . $name . ' > ' . strip_tags( $program->display_start ) . ' / ' . strip_tags( $program->display_end ) . '">';
			$title .= $name;
			$title .= '</div>';
			$title .= '<div class="actions">';
			$title .= '<i class="dashicons-before dashicons-info"></i>';
			$title .= '</div>';
		$title .= '</hgroup>';
		
		$title .= '<div class="event-infos">';
			$title .= '<div class="event-infos-header">';
				$title .= '<div class="event-infos-section">';
					$title .= '<div class="category-title">';
					$title .= $name;
					$title .= '</div>';
					$title .= '<div class="event-time">';
					$title .= '<span class="begin">' . $program->display_start_dateTime . '</span> <span class="end">' . $program->display_end_dateTime . '</span>';
					$title .= '</div>';
					$title .= '<div class="actions">';
					$title .= $user_can_edit_program_infos ? '<a href="' . $edit_url . '" class="edit-event thickbox" title="Modifier les informations de l\'événement"><i class="dashicons-before dashicons-edit"></i></a>' : '';
					$title .= '</div>';
				$title .= '</div>';
			$title .= '</div>';
			$title .= '<div class="event-infos-content">';
				if( !empty( $program->getAttendees() ) ) {
					$title .= '<div class="event-infos-section"><h3>Invité(s)</h3>';
					foreach( $program->getAttendees() as $attendee ) {
						$title .= '<p class="dashicons-before status-' . $attendee->getResponseStatus() . '" title="' . RCA_Calendar::attendee_responseStatus_title( $attendee->getResponseStatus() ) . '"><strong>' . $attendee->getDisplayName() . '</strong><br>' . $attendee->getComment() . '</p>';
					}
					$title .= '</div>';
				}
				$title .= $program->getDescription() ? '<div class="event-infos-section"><h3>Infos</h3>' . wpautop( preg_replace( '/--/', '<small>', stripslashes( $program->getDescription() ), 1 ) . '</small></div>' ) : '';
			$title .= '</div>';
		$title .= '</div>';
		
		return $title;
		
	}
	
	public static function admin_event_form( $action, $args = array() ) {
		$event_id = $event_date = $wp_referer = false;
		extract( $args );
		
		$calendar_id = RCA_CAL()->set_calendar( 'booking' );
		$summary = $description = $filter = false;
		$attendees = array();
		
		// EDIT Event
		if( $action == 'edit' && isset( $event_id ) ) {
			$event = RCA_CAL()->get_event( $event_id );
			$summary = $event->getSummary();
			$description = $event->getDescription();
			$attendees = $event->getAttendees();
			
			// Get filter & summary
			$pos = strpos( $summary, ' ' );
			$filter = substr( $summary, 0, $pos );
			$summary = substr( $summary, $pos + 1 );
			
			// Get event start
			$start = ! empty( $event->start->dateTime ) ? $event->start->dateTime : $event->start->date;
			$start = strtotime( $start );
			
			// Get event end
			$end = ! empty( $event->end->dateTime ) ? $event->end->dateTime : $event->end->date;
			$end = strtotime( $end );
			
			$event_start = array(
				'date' => date( 'Y-m-d', $start ),
				'time' => date( 'H:i', $start )
			);
			
			$event_end = array(
				'date' => date( 'Y-m-d', $end ),
				'time' => date( 'H:i', $end )
			);
		
		// ADD Event
		} else {
		
			$event_start = array(
				'date' => date( 'Y-m-d', $event_date ),
				'time' => date( 'H', $event_date ) . ':00'
			);
		
			$event_end = array(
				'date' => date( 'Y-m-d', $event_date ),
				'time' => date( 'H', $event_date + 3600 ) . ':00'
			);
		} 
		
		$current_user = wp_get_current_user();
		?>
		<form class="admin-event-form <?php echo $action; ?>-program-form admin-booking-form" action="" method="post">
			
			<fieldset>
				<h3>Quand ?</h3>
				<span class="date-time-row"><input id="start_date" type="date" name="event[start][date]" value="<?php echo $event_start['date']; ?>"><input type="time" name="event[start][time]" value="<?php echo $event_start['time']; ?>"> à 
				<input id="end_date" type="date" name="event[end][date]" value="<?php echo $event_end['date']; ?>"><input type="time" name="event[end][time]" value="<?php echo $event_end['time']; ?>"></span>
			</fieldset>
			
			<fieldset>
				<h3>Quoi ?</h3>
				<select name="event[summary][what]">
					<?php foreach( RCA_Events_Grid_Admin::$booking_filters as $value => $name ) {
						if( $value == 'all' )
							echo '<option value="-1">Choisir une réservation</option>';
						else
							printf( '<option value="%s"%s>%s</option>', $value, selected( $value, $filter, false ), $name );
					} ?>
				</select>
			</fieldset>
			
			<fieldset>
				<h3>Qui ?</h3>
				<input type="text" name="event[summary][who]" value="<?php echo $summary; ?>">
			</fieldset>
			
			<fieldset>
				<h3>Infos</h3>
				<textarea name="event[description]"><?php echo $description; ?></textarea>
			</fieldset>
			
			<?php if( $current_user ) :
				
				$cat = get_user_meta( $current_user->ID, '_author_cat', true );
				$show = empty( $cat[0] ) ? $cat[0]->name : false;
				?>
				
				<fieldset class="attendee" style="display:none;">
					<input type="hidden" name="event[attendees][0][displayName]" value="<?php echo $current_user->display_name; ?>">
					<input type="hidden" name="event[attendees][0][email]" value="<?php echo $current_user->user_email; ?>">
					<input type="hidden" name="event[attendees][0][comment]" value="<?php echo $show; ?>">
					<input type="hidden" name="event[attendees][0][responseStatus]" value="accepted">
				</fieldset>
			
			<?php endif; ?>
			
			<div id="major-publishing-actions">
				<input type="hidden" name="action" value="<?php printf( 'admin_%s_event', $action ); ?>">
				<input type="hidden" name="calendar" value="<?php echo $calendar; ?>">
				<input type="hidden" name="calendar_id" value="<?php echo $calendar_id; ?>">
				<input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
				<input type="hidden" name="wp_referer" value="<?php echo $wp_referer; ?>">
				<input type="submit" class="button button-primary" disabled="disabled" value="Valider les modifications">
				<span class="spinner"></span>
			</div>
		</form>
		<?php
		die;
	}
	
}