<?php
/**
 * RCA_Calendar
 *
 * Version 1.0
 */

class RCA_Program_Calendar {
	
	/**
	 * Get admin event html
	 *
	 */	
	public static function admin_grid( $program ) {	
		global $current_screen;
		
		$name = $program->getSummary();
		$rebroadcasting = $program->getColorId() != '' ? true : false;
		
		$img = campus_get_category_thumbnail( array( 'width' => 320, 'height' => 320 ) );
		$secondary_description = $edit_term_url = false;
		$user_can_edit_program_infos = current_user_can( 'manage_categories' ) && is_admin();
		
		// If google event is associated to a wordpress category
		if( isset( $program->post_category ) ) {	
			$term = $program->post_category;
			$img = campus_get_category_thumbnail( array('term_id' => $term->term_id, 'taxonomy' => $term->taxonomy, 'width' => 320, 'height' => 320 ) );
			$secondary_description = get_term_meta( $term->term_id, 'secondary_description', true );
			$user_can_edit_program_infos = apply_filters( 'user_can_edit_program_infos', false, $term->term_id );
		
			$edit_term_url = add_query_arg( 
			    array( 
			        'taxonomy' 	 => 'category', 
			        'tag_ID' 	 => $term->term_id, 
			        'post_type'	 => 'post',
			        'wp_http_referer' => ! empty( $current_screen->parent_base ) ? admin_url( 'admin.php?page=' . $current_screen->parent_base ) : admin_url()
			    ), 
			    admin_url( 'term.php' ) 
			);
		}
		
		$edit_url = add_query_arg( 
		    array( 
		        'action' 	 => 'admin_edit_event_form', 
		        'calendar' 	 => 'program', 
		        'event_id' 	 => $program->getId(), 
		        'wp_referer' => ! empty( $current_screen->parent_base ) ? admin_url( 'admin.php?page=' . $current_screen->parent_base ) : admin_url(),
		        'width'		 => 320,
		        'height'	 => 465
		    ), 
		    admin_url( 'admin-ajax.php' ) 
		);
		
		$title = '<hgroup>';
			$title .= '<div class="category-title" title="' . $name . ' > ' . strip_tags( $program->display_start ) . ' / ' . strip_tags( $program->display_end ) . ( $rebroadcasting ? ' (Rediffusion)' : '' ) . '">';
			$title .= $name . ( $program->onAir ? ' <span class="pictos picto-live" title="En direct"></span>' : '' );
			$title .= '</div>';
			$title .= '<div class="actions">';
			$title .= '<i class="dashicons-before dashicons-info"></i>';
			$title .= '</div>';
		$title .= '</hgroup>';
		
		$title .= '<div class="event-infos">';
			$title .= '<div class="event-infos-header">';
				$title .= '<div class="taxonomy-thumbnail">' . $img . '</div>';
				$title .= '<div class="event-infos-section">';
					$title .= '<div class="category-title">';
					$title .= $name;
					$title .= $rebroadcasting ? '<br><small>(Rediffusion)</small>' : '';
					$title .= '</div>';
					$title .= $secondary_description ? '<span class="archive-secondary-description">' . $secondary_description  . '</span>' : '';
					$title .= '<div class="event-time">';
					$title .= '<span class="begin">' . $program->display_start_dateTime . '</span> <span class="end">' . $program->display_end_dateTime . '</span>';
					$title .= '</div>';
					$title .= '<div class="actions">';
					$title .= $program->onAir ? ' <i class="dashicons-before dashicons-controls-volumeon" title="En direct"></i>' : '';
					$title .= current_user_can( 'manage_categories' ) && $edit_term_url ? '<a href="' . $edit_term_url . '" class="edit-category" title="Modifier la catégorie"><i class="dashicons-before dashicons-category"></i></a>' : '';
					$title .= $user_can_edit_program_infos ? '<a href="' . $edit_url . '" class="edit-event thickbox" title="Modifier les informations de l\'événement"><i class="dashicons-before dashicons-edit"></i></a>' : '';
					$title .= '</div>';
				$title .= '</div>';
			$title .= '</div>';
			$title .= '<div class="event-infos-content">';				
				if( ! empty( $program->getAttendees() ) ) {
					$title .= '<div class="event-infos-section"><h3>Invité(s)</h3>';
					foreach( $program->getAttendees() as $attendee ) {
						$title .= '<p class="dashicons-before status-' . $attendee->getResponseStatus() . '" title="' . RCA_Calendar::attendee_responseStatus_title( $attendee->getResponseStatus() ) . '"><strong>' . $attendee->getDisplayName() . '</strong><br>' . $attendee->getComment() . '</p>';
					}
					$title .= '</div>';
				}
				$title .= $program->getDescription() ? '<div class="event-infos-section"><h3>Thème de l\'émission</h3>' . wpautop( preg_replace( '/--/', '<small>', stripslashes( $program->getDescription() ), 1 ) . '</small></div>' ) : '';
			$title .= '</div>';
		$title .= '</div>';
		
		return $title;
		
	}
	
	public static function admin_event_form( $action, $args = array() ) {
		$event_id = $wp_referer = false;
		extract( $args );
		
		$calendar_id = RCA_CAL()->set_calendar( 'program' );
		$description = false;
		$attendees = array();
		
		if( $action == 'edit' && $event_id ) {
			$event = RCA_CAL()->get_event( $event_id );
			$description = $event->getDescription();
			$attendees = $event->getAttendees();
		}
		?>
		<form class="admin-event-form <?php echo $action; ?>-program-form admin-grid-form" action="" method="post">
			
			<fieldset>
				<h3>Thème de l'émission</h3>
				<textarea name="event[description]"><?php echo $description; ?></textarea>
			</fieldset>
			
			<fieldset>
				<h3>Invité(s)</h3>
				<ul class="event-attendees">
				<?php if( ! empty( $attendees ) ) { ?>
					
					<?php foreach( $attendees as $index => $attendee ) { ?>
						<li class="attendee">
							<p><label>Nom / Prénom</label>
								<input type="text" name="event[attendees][<?php echo $index; ?>][displayName]" value="<?php echo esc_attr( $attendee->getDisplayName() ); ?>"></p>
							<p><label>Organisation</label>
								<input type="text" name="event[attendees][<?php echo $index; ?>][comment]" value="<?php echo esc_attr( $attendee->getComment() ); ?>"></p>
							<p><label>Adresse Email</label>
								<input type="email" name="event[attendees][<?php echo $index; ?>][email]" value="<?php echo esc_attr( $attendee->getEmail() ); ?>"></p>
							<p><input type="hidden" name="event[attendees][<?php echo $index; ?>][responseStatus]" value="needsAction"><input type="checkbox" id="event-attendees-0-responseStatus" name="event[attendees][0][responseStatus]" value="accepted"<?php checked( $attendee->getResponseStatus(), 'accepted' ); ?>><label for="event-attendees-0-responseStatus">Venue confirmée ?</label></p>
						</li>
					<?php } ?>
					
				<?php } else { ?>
					<li class="attendee-<?php echo $index; ?>">
						<p><label>Nom / Prénom</label>
							<input type="text" name="event[attendees][0][displayName]"></p>
						<p><label>Organisation</label>
							<input type="text" name="event[attendees][0][comment]"></p>
						<p><label>Adresse Email</label>
							<input type="email" name="event[attendees][0][email]"></p>
						<p><input type="hidden" name="event[attendees][0][responseStatus]" value="needsAction"><input type="checkbox" id="event-attendees-0-responseStatus" name="event[attendees][0][responseStatus]" value="accepted"><label for="event-attendees-0-responseStatus">Venue confirmée ?</label></p>
					</li>
				<?php } ?>
				</ul>
				<a href="#" class="button event-add-attendee">Ajouter un invité</a>
			</fieldset>
			
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