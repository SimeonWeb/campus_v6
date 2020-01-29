<?php
/**
 * Functions
 *
 */

function RCA_Calendar( $calendar_id ) {
	global $RCA_Events_Google_Calendar;
	
	return $RCA_Events_Google_Calendar->get_calendar( $calendar_id );

}

function RCA_Calendar_update_event( $calendar_id, $event_id, $data ) {
	global $RCA_Events_Google_Calendar;
	
	$RCA_Events_Google_Calendar->update_event( $calendar_id, $event_id, $data );

}