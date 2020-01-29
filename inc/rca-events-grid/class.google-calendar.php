<?php
/**
 * RCA_Google_Calendar
 *
 * Version 1.0
 */

class RCA_Google_Calendar {

	/**
	 * Holds the singleton instance of this class
	 * @since 1.0
	 * @var RCA_Google_Calendar
	 */
	static $instance = false;

	private $events = false;

	/**
	 * Google vars
	 * @since 1.0
	 */
	private $application_name = false;

	private $developer_key = false;

	private $scopes = false;

	private $client = false;

	private $service = false;

	/**
	 * Singleton
	 * @static
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new RCA_Google_Calendar;
		}

		return self::$instance;
	}

	public function __construct() {

		$this->application_name = 'Clé API Calendar';
		$this->developer_key = 'AIzaSyDSUPlKGCF_iXPM0gsw9r4chZ4uqdWPsMg';
	}

	private function connect() {

		// Get the API client and construct the service object.
		$this->setScopes();
		$this->setClient();
		$this->setService();
	}

	private function setScopes() {
		if( $this->scopes === false ) {
			// If modifying these scopes, delete your previously saved credentials
			// at ~/.credentials/calendar-php-quickstart.json
			$this->scopes = implode(' ', array(
				Google_Service_Calendar::CALENDAR
			));
		}
	}

	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	private function setClient() {
	 	if( $this->client === false ) {
			//$this->client = new Google_Client();
			//$this->client->setApplicationName( $this->application_name );
			//$this->client->setDeveloperKey( $this->developer_key );
			//$this->client->setScopes( $this->scopes );

			putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . RCA_EVENTS_GRID_PLUGIN_DIR . 'service-account.json' );
			$this->client = new Google_Client();
			$this->client->setScopes( $this->scopes );
			$this->client->useApplicationDefaultCredentials();
		}
	}

	/**
	 * Returns calendar service.
	 * @return Google_Service_Calendar object
	 */
	private function setService() {
		if( $this->service === false ) {
			$this->service = new Google_Service_Calendar( $this->client );
		}
	}

	/**
	 * Returns calendar events.
	 * @return $events array
	 */
	public function get_events( $calendar_id, $params = array() ) {

		extract( $params );

		if( empty( $year ) )
			$year = date_i18n( 'o' );

		if( empty( $week_number ) )
			$week_number = date_i18n( 'W' );

		// Print the events.
		$list_params = array(
			'orderBy' => 'startTime',
			'singleEvents' => true,
			'timeMin' => date( 'c', strtotime( $year . 'W' . $week_number . '1' ) ),
			'timeMax' => date( 'c', strtotime( $year . 'W' . $week_number . '7 + 1day' ) ),
		);

		// Set cache key
		$cache_key = get_class() . '_' . $calendar_id . '_' . $year . '_' . $week_number;

		// Get cached events
		$this->events = get_transient( $cache_key );

		if ( false === $this->events ) {
			$this->connect();

			$results = $this->service->events->listEvents( $calendar_id, $list_params );
			$this->events = $results->getItems();

			// Put events in cache
			set_transient( $cache_key, $this->events, get_option( 'RCA_Events_Grid_cache_expire', 60 * 60 * 24 * 7 ) );
		}

		return $this->events;
	}

	public function get_event( $calendar_id, $eventId ) {
		$this->connect();

		$event = $this->service->events->get( $calendar_id, $eventId );
		return $event;
	}

	public function update_event( $calendar_id, $eventId, $data, $optParams = array() ) {
		$this->connect();

		$event = $this->service->events->get( $calendar_id, $eventId );

		foreach( $data as $name => $input ) {
			$event_function = 'set' . ucfirst($name);

			switch( $name ) {

				case 'start':
				case 'end':

					$date = strtotime( $input['date'] . ' ' . $input['time'] );

					$new_date = new Google_Service_Calendar_EventDateTime();
					$new_date->setDateTime( date_i18n( 'c', $date ) );
					//$new_date->setTimeZone( date( 'e', $date ) );

					$event->$event_function( $new_date );

					break;

				case 'summary':

					if( is_array( $input ) )
						$new_summary = join( ' ', $input );
					else
						$new_summary = $input;

					$event->$event_function( $new_summary );

					break;

				case 'source':

					$new_source = array();

					$new_source = new Google_Service_Calendar_EventSource();
					foreach( $input as $field => $value ) {
						$source_function = 'set' . ucfirst($field);
						$new_source->$source_function( $value );
					}

					$event->$event_function( $new_source );

					break;

				case 'attendees':

					$new_attendees = array();

					foreach( $input as $key => $fields ) {

/*
						if( trim( $input[$key]['displayName'] ) == '' )
							continue;
*/

						if( trim( $input[$key]['email'] ) == '' || ! filter_var( $input[$key]['email'], FILTER_VALIDATE_EMAIL ) )
							continue;

						$new_attendee = new Google_Service_Calendar_EventAttendee();
						foreach( $fields as $field => $value ) {
							$attendee_function = 'set' . ucfirst($field);
							$new_attendee->$attendee_function( $value );
						}

						$new_attendees[] = $new_attendee;
					}

					$event->$event_function( $new_attendees );

					break;

				case 'description':

					$current_user = wp_get_current_user();
					$input .= sprintf(
						"\n--\nModifié par %s (%d)\nle %s à %s",
						$current_user->display_name,
						$current_user->ID,
						date_i18n( get_option( 'date_format' ) ),
						date_i18n( get_option( 'time_format' ) )
					);

					$event->$event_function( $input );

					break;

				default:
					$event->$event_function( $input );
					break;
			}
		}

		$updatedEvent = $this->service->events->update( $calendar_id, $event->getId(), $event, $optParams );

		// Get event start
		$start = $event->start->dateTime;
		if (empty($start)) {
			$start = $event->start->date;
		}

		// Set cache key
		$cache_key = get_class() . '_' . $calendar_id . '_' . date( 'o\_W', strtotime( $start ) );

		// Delete cache
		delete_transient( $cache_key );

		// Print the updated date.
		return $updatedEvent->getUpdated();

	}

	public function add_event( $calendar_id, $data, $optParams = array() ) {
		$this->connect();

		$event = new Google_Service_Calendar_Event();

		foreach( $data as $name => $input ) {
			$event_function = 'set' . ucfirst($name);

			switch( $name ) {

				case 'start':
				case 'end':

					$date = strtotime( $input['date'] . ' ' . $input['time'] );

					$new_date = new Google_Service_Calendar_EventDateTime();
					$new_date->setDateTime( date_i18n( 'c', $date ) );
					//$new_date->setTimeZone( date( 'e', $date ) );

					$event->$event_function( $new_date );

					break;

				case 'summary':

					if( is_array( $input ) )
						$new_summary = join( ' ', $input );
					else
						$new_summary = $input;

					$event->$event_function( $new_summary );

					break;

				case 'attendees':

					$new_attendees = array();

					foreach( $input as $key => $fields ) {

/*
						if( trim( $input[$key]['displayName'] ) == '' )
							continue;
*/

						if( trim( $input[$key]['email'] ) == '' || ! filter_var( $input[$key]['email'], FILTER_VALIDATE_EMAIL ) )
							continue;

						$new_attendee = new Google_Service_Calendar_EventAttendee();
						foreach( $fields as $field => $value ) {
							$attendee_function = 'set' . ucfirst($field);
							$new_attendee->$attendee_function( $value );
						}

						$new_attendees[] = $new_attendee;
					}

					$event->$event_function( $new_attendees );

					break;

				case 'description':

					$current_user = wp_get_current_user();
					$input .= sprintf(
						"\n--\nCréé par %s (%d)\nle %s à %s",
						$current_user->display_name,
						$current_user->ID,
						date_i18n( get_option( 'date_format' ) ),
						date_i18n( get_option( 'time_format' ) )
					);

					$event->$event_function( $input );

					break;

				default:
					$event->$event_function( $input );
					break;
			}
		}

		$insertedEvent = $this->service->events->insert( $calendar_id, $event, $optParams );

		// Get event start
		$start = $event->start->dateTime;
		if (empty($start)) {
			$start = $event->start->date;
		}

		// Set cache key
		$cache_key = get_class() . '_' . $calendar_id . '_' . date( 'o\_W', strtotime( $start ) );

		// Delete cache
		delete_transient( $cache_key );

		// Print the updated date.
		return $insertedEvent->getId();

	}
}

/**
 * Main instance of RCA_Google_Calendar.
 *
 * Returns the main instance of RCA_Google_Calendar to prevent the need to use globals.
 *
 * @since  5.1.1
 * @return RCA_Google_Calendar
 */
function RCA_GC() {
	return RCA_Google_Calendar::init();
}
