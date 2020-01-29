<?php
/**
 * RCA_Calendar
 *
 * Version 1.0
 */

class RCA_Calendar {

	/**
	 * Holds the singleton instance of this class
	 * @since 1.0
	 * @var RCA_Google_Calendar
	 */
	static $instance = false;

	/**
	 * The google calendars options
	 *
	 */
	private $calendars = null;

	/**
	 * The google calendar id
	 *
	 */
	private $calendar_id = null;

	/**
	 * The calendar display
	 *
	 */
	private $calendar_view = 'front_grid';

	/**
	 * The date format
	 *
	 */
	private $date_format = 'Y-m-d';

	/**
	 * The time format
	 *
	 */
	private $time_format = 'H:i:s';

	/**
	 * The requested date
	 *
	 */
	public $request_date = false;

	/**
	 * The date week number
	 *
	 */
	public $week_number = false;

	/**
	 * The date year
	 *
	 */
	public $year = false;

	/**
	 * Current term object
	 *
	 */
	public $current_show = null;

	/**
	 * Current term object id
	 *
	 */
	public $current_show_id = null;

	/**
	 * The start end end of the week
	 *
	 */
	public $week_start_timestamp = false;
	public $week_end_timestamp = false;

	/**
	 * Singleton
	 * @static
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new RCA_Calendar;
		}

		return self::$instance;
	}

	public function __construct() {

		$this->calendars = get_option( 'RCA_Events_Grid', array() );

		add_filter( 'campus_content_infos_message', array( $this, 'content_infos' ) );
	}

	/**
	 * Set calendar view
	 * @public
	 */
	public function set_view( $view = false ) {

		$this->calendar_view = $view;
	}

	/**
	 * Set calendar view
	 * @public
	 */
	public function set_date( $date = false ) {

		$date = strtotime($date);

		$this->request_date 		= $date ? $date : strtotime( date_i18n( $this->date_format . ' ' . $this->time_format ) );
		$this->week_number 			= $date ? date( 'W', $date ) : date_i18n( 'W' );
		$this->year 				= $date ? date( 'o', $date ) : date_i18n( 'o' );
		$this->week_start_timestamp = strtotime( $this->year . 'W' . $this->week_number . '1' );
		$this->week_end_timestamp 	= strtotime( $this->year . 'W' . $this->week_number . '7 + 1day' ) - 1;
	}

	/**
	 * Set calendar
	 * @public
	 */
	public function set_calendar( $calendar = false ) {

		$calendar_name = $calendar . '_calendar_id';

		if( array_key_exists( $calendar_name , $this->calendars ) && $this->calendars[$calendar_name] != '' )
			$this->calendar_id = $this->calendars[$calendar_name];

		// After defined calendar, set date
		if( ! $this->week_number || ! $this->year )
			$this->set_date();

		// And set current event
		$this->set_current_event();

		return $this->calendar_id;
	}

	/**
	 * Set google calendar id
	 * @public
	 */
	public function set_calendar_id( $calendar_id = false ) {

		$this->calendar_id = $calendar_id;
	}

	/**
	 * Set google calendar id
	 * @public
	 */
	public function get_calendar_id( $calendar = false ) {

		$calendar_name = $calendar . '_calendar_id';

		if( array_key_exists( $calendar_name , $this->calendars ) && $this->calendars[$calendar_name] != '' )
			return $this->calendars[$calendar_name];
	}


	/**
	 * Set current event
	 *
	 */
	public function set_current_event() {

		if( ! $this->calendar_id || ! function_exists( 'RCA_GC' ) )
			return false;

		date_default_timezone_set( get_option( 'timezone_string' ) );

		// Get google events
		$events = RCA_GC()->get_events(
			$this->calendar_id,
			array(
				'week_number' => $this->week_number,
				'year' => $this->year
			)
		);

		if( $events ) {

			foreach( $events as $event ) {
				$event = $this->populate_event( $event );

				// Check if this programm is broadcasting now
				if( $event->onAir ) {
					$this->current_show = $event;

					if( isset( $event->post_category ) ) {
						$this->current_show_id = $event->post_category->term_id;
					}
					break;
				}
			}
		}
	}

	/**
	 * Get calendar html
	 *
	 */
	public function get_calendar( $request_date = false ) {

		if( $request_date )
			$this->set_date( $request_date );

		$week_table = $this->set_week_events();
		$today = date( $this->date_format, $this->request_date );

		$previous_icon = $today_icon = $next_icon = '';

		//print_r($week_table);

		// Header
		$header_day = '<header class="content-header fixed">';
			$header_day .= '<div class="programs-header programs-content">';

		// Day Content
		$section_day = '<div class="content-hentry">';
			$section_day .= '<div class="programs-entries programs-content">';

		// Day hours
		$footer_day = '<aside class="content-aside">';
			$footer_day .= '<div class="programs-footer">';

		foreach( $week_table as $date => $day_rows ) {

			$day_classes = array( 'programs-day' );
			if( $date == $today ) $day_classes[] = 'today';

			// Day Header
			$header_day .= sprintf( '<div class="programs-title %s"><div class="program-day-title">%s</div></div>',
				join( ' ', $day_classes ),
				sprintf( '<span class="short">%s</span><span class="long">%s</span>',
			    	ucfirst( date_i18n( 'D\. j\/m', strtotime( $date ) ) ),
			    	ucfirst( date_i18n( 'l j F', strtotime( $date ) ) )
			    )
			);

			// Day Begin
			$section_day .= '<section class="' . join( ' ', $day_classes ) . '" data-date="' . date( 'c', strtotime( $date ) ) .'">';

				foreach( $day_rows as $hour => $program ) {

					$program = $this->populate_event( $program );

					// Get google programm title
					$program_name = $program->getSummary();
					$program_slug = sanitize_title( $program_name );
					$program_classes = array( 'program' );

					$is_current = $is_rebroadcasting = false;

					$wrapper_classes = array( 'programs-entry', 'hour-' . str_replace( ':', '-', $hour ), 'full', 'duration-' . $program->duration );
					if( $program->duration < 3600 )
						$wrapper_classes[] = 'smaller';

					// Set style positionning
					$wrapper_style = 'top:' . $program->cssPosition . '%;height:' . $program->cssHeight . '%;';

					// Check if it's a multidays event
					if( $program->multidays ) {
						$wrapper_classes[] = 'multidays';
					}

					// Check if it's a rebroadcasting
					if( $this->calendar_id == $this->get_calendar_id( 'program' ) && $program->getColorId() != '' ) {
						$is_rebroadcasting = true;
						$wrapper_classes[] = 'rebroadcasting';
					}

					// Check if this programm is broadcasting now
					if( $program->onAir ) {
						$is_current = true;
						$wrapper_classes[] = 'current';
					}

					if( isset( $program->post_category ) ) {
						$program_classes += campus_get_all_term_classes( $program->post_category->term_id, $program->post_category->taxonomy, true );
					} else {
						$program_classes[] = 'category-autres';
					}

					$current_icon 		 = campus_get_svg( array( 'icon' => 'live', 'title' => 'En direct', 'class' => 'icon-small' ) );
					$rebroadcasting_icon = campus_get_svg( array( 'icon' => 'rebroadcasting', 'title' => 'Rediffusion' ) );
					$previous_icon 		 = '<i class="dashicons-before dashicons-arrow-left-alt pictos picto-left"></i>';
					$today_icon    		 = '<i class="dashicons-before dashicons-marker pictos picto-place"></i>';
					$next_icon     		 = '<i class="dashicons-before dashicons-arrow-right-alt pictos picto-right"></i>';

					// Front grid view
					if( $this->calendar_view == 'front_grid' ) {

						// If google programm is associated to a wordpress category
						if( isset( $program->post_category ) ) {

							$term = $program->post_category;
							$thumbnail = campus_get_category_thumbnail( array( 'term_id' => $term->term_id, 'taxonomy' => $term->taxonomy, 'width' => 300, 'height' => 300 ) );
							$program_name = $term->name;
							$secondary_description = get_term_meta( $term->term_id, 'secondary_description', true );
							$secondary_description = $secondary_description ? $secondary_description : $program->getDescription();
							$description = wpautop( campus_excerpt( $term->description, 300, false ) );
							$link = get_term_link( $term, $term->taxonomy );
							$day = get_term_meta( $term->term_id, 'day', true );
							$hours = get_term_meta( $term->term_id, 'hours', true );

							// For list display
							$program_schedules = sprintf( '<div class="taxonomy-schedules"><p class="taxonomy-schedules-day">%s</p><p class="taxonomy-schedules-hours">%s > %s</p></div>',
								$day . ( $is_rebroadcasting ? ' (Rediffusion)' : '' ),
								$program->display_start,
								$program->display_end
							);

							// For list display
							$term_social_links = campus_get_term_social_links( $term->term_id );
							$program_aside = $term_social_links ? sprintf( '<aside class="taxonomy-aside content-meta-links meta-links">%s</aside><a href="#" class="taxonomy-open-aside">%s</a>',
								$term_social_links,
								campus_get_svg( array( 'icon' => 'arrow-left', 'class' => 'icon-small' ) )
							) : '';

						} else {

							// For list display
							$program_schedules = sprintf( '<div class="taxonomy-schedules"><p class="taxonomy-schedules-day">%s</p><p class="taxonomy-schedules-hours">%s > %s</p></div>',
								ucfirst( date_i18n( 'l', $program->start_timestamp ) ) . ( $is_rebroadcasting ? ' (Rediffusion)' : '' ),
								$program->display_start,
								$program->display_end
							);
							$secondary_description = $program->getDescription();
							$description = $link = $program_aside = '';
							$thumbnail = campus_get_category_thumbnail( array( 'width' => 300, 'height' => 300, 'class' => 'category-emission' ) );
						}

						// Duplicate color classes and removing program class
						$header_classes = $program_classes;
						unset( $header_classes[0] );
						$header_classes[] = 'taxonomy-header';

						// Attr
						$title_attr = sprintf( '%s / %s > %s', esc_attr( $program_name ), strip_tags( $program->display_start ), strip_tags( $program->display_end ) ) . ( $is_rebroadcasting ? ' (Rediffusion)' : '' );

						// For grid display
						$program_summary = sprintf( '<div class="program-summary" title="%s"><div class="program-title">%s</div><div class="program-description">%s</div></div>',
							$title_attr,
							$program_name,
							$secondary_description
						);

						// For list display
						$program_infos = sprintf( '<figure class="taxonomy-thumbnail">%s</figure><div class="taxonomy-content"><div class="taxonomy-title">%s</div><div class="taxonomy-secondary-description">%s</div>%s<div class="taxonomy-description">%s</div></div>',
							$thumbnail,
							$program_name . ( $is_current ? ' ' . $current_icon : '' ),
							$secondary_description,
							$program_schedules,
							$description
						);

						if( $link ) {
							$program_summary = sprintf( '<a href="%s" class="program-link">%s</a>',
								$link,
								$program_summary
							);

							$program_infos = sprintf( '<div class="%s"><a href="%s" class="program-link">%s</a>%s</div>',
								join( ' ', $header_classes ),
								$link,
								$program_infos,
								$program_aside
							);
						} else {

							$program_infos = sprintf( '<div class="%s">%s%s</div>',
								join( ' ', $header_classes ),
								$program_infos,
								$program_aside
							);
						}

						// Output the content
						$program_content = '<div class="list-item">' . $program_infos . '</div>' . $program_summary;

						$program_content .= $is_rebroadcasting ? $rebroadcasting_icon : '';

						$previous_icon = campus_get_svg( array( 'icon' => 'arrow-left' ) );
						$today_icon    = campus_get_svg( array( 'icon' => 'today' ) );
						$next_icon     = campus_get_svg( array( 'icon' => 'arrow-right' ) );

					// Admin grid view
					} else if( $this->calendar_id == $this->get_calendar_id( 'program' ) && $this->calendar_view == 'admin_grid' ) {

						$program_content = RCA_Program_Calendar::admin_grid( $program );
						$program_content .= $is_rebroadcasting ? $rebroadcasting_icon : '';

					// Admin booking view
					} else if( $this->calendar_id == $this->get_calendar_id( 'booking' ) && $this->calendar_view == 'admin_grid' ) {

						$program_content = RCA_Booking_Calendar::admin_grid( $program );

					}

					// Program output
					$section_day .= sprintf( '<article class="%s" style="%s"><div class="%s">%s</div></article>',
						join( ' ', $wrapper_classes ),
						$wrapper_style,
						join( ' ', $program_classes ),
						$program_content
					);

				}

			$section_day .= '</section>';

		}

		// Hours
		for( $h = 0; $h <= 24; $h++ ) {

			$hour = zeroise( $h, 2 );
			$hour = $hour == 24 ? '00' : $hour;

			$hour_classes = array( 'programs-entry', 'programs-hour', 'hour-' . $hour . '-' . '00' );
			$hour_display = $hour . 'h';

			$footer_day .= '<div class="'.join( ' ', $hour_classes ).'">';
			    $footer_day .= '<div class="hour">'.$hour_display.'</div>';
			$footer_day .= '</div>';
		}

		$nav_day = sprintf( '<nav class="nav-day" data-date="%s"><a href="#" class="nav-prev" title="Jour précédent">%s <span class="screen-reader-text">Jour précédent</span></a><a href="#" class="nav-today" title="Aujourd\'hui">%s <span class="screen-reader-text">Aujourd\'hui</span></a><a href="#" class="nav-next" title="Jour suivant">%s <span class="screen-reader-text">Jour suivant</span></a></nav>',
			date( 'c', $this->request_date ),
			$previous_icon,
			$today_icon,
			$next_icon
		);

		$header_day .= '</div>' . $nav_day . '</header>';
		$section_day .= '<span class="time-handler"></span></div></div>';
		$footer_day .= '</div></aside>';


		echo '<section class="programs content-wrapper">';
			echo $header_day;
			echo $section_day;
			echo $footer_day;
		echo '</section>';

	}

	public function content_infos() {

		$infos = '';

		if( $this->calendar_id == $this->get_calendar_id( 'program' ) && is_page_template('page-alt-programs.php') ) {

			$category_for_colors = campus_get_term_colors();

			foreach( $category_for_colors as $category_id => $color ) {
				$term = get_term( $category_id, 'category' );

				$infos .= sprintf( '<div class="content-info info-color category-%s">%s</div>',
					$term->slug,
					$term->name
				);
			}
		}

		// Add infos to content infos div
		return $infos;
	}


	/**
	 * Set events
	 *
	 */
	private function set_week_events() {

		// Get empty week table
		$current_week = $this->week_array();

		if( ! $this->calendar_id || ! function_exists( 'RCA_GC' ) )
			return $current_week;

		date_default_timezone_set( get_option( 'timezone_string' ) );

		// Get google events
		$events = RCA_GC()->get_events(
			$this->calendar_id,
			array(
				'week_number' => $this->week_number,
				'year' => $this->year
			)
		);

		if( $events ) {

			foreach( $events as $event ) {

				$start = ! empty( $event->start->dateTime ) ? $event->start->dateTime : $event->start->date;
				$end = ! empty( $event->end->dateTime ) ? $event->end->dateTime : $event->end->date;

				$event_start_day = date( $this->date_format, strtotime( $start ) );
				$event_end_day = date( $this->date_format, strtotime( $end ) );

				// Multidays event
				if( $event_end_day != $event_start_day && date( $this->time_format, strtotime( $end ) ) != '00:00:00' ) {

					$end_event = clone $event;

					if( array_key_exists( $event_start_day, $current_week) ) {
						$event->multidays = 'start';
						$current_week[$event_start_day][ date( $this->time_format, strtotime( $start ) ) ] = $event;
					}
					if( array_key_exists( $event_end_day, $current_week) ) {
						$end_event->multidays = 'end';
						$current_week[$event_end_day]['00:00:00'] = $end_event;
					}

				// Singleday event
				} else if( array_key_exists( $event_start_day, $current_week) )
					$current_week[$event_start_day][ date( $this->time_format, strtotime( $start ) ) ] = $event;
			}

		}

		return $current_week;
	}

	/**
	 * Get events
	 *
	 */
	public function get_event( $event_id ) {

		if( ! is_admin() )
			return false;

		if( ! $this->calendar_id )
			return false;

		// Get google event
		$event = RCA_GC()->get_event(
			$this->calendar_id,
			$event_id
		);

		return $event;
	}

	/**
	 * Get events
	 *
	 */
	function get_events( $params = array() ) {

		if( ! $this->calendar_id )
			return false;

		// Get google events
		$events = RCA_GC()->get_events(
			$this->calendar_id,
			$params
		);

		return $events;
	}


	/**
	 * Return the week's days array
	 *
	 */
	private function week_array() {

		if( ! $this->week_number || ! $this->year )
			$this->set_date();

		$days = array();

		for($day = 1; $day <= 7; $day++) {
		    $days[ date( $this->date_format, strtotime( $this->year . 'W' . $this->week_number . $day ) ) ] = array();
		}

		return $days;
	}

	public static function attendee_responseStatus_title( $responseStatus ) {

		$title = array(
			'accepted' => 'Venue confirmée',
			'needsAction' => 'En attente de confirmation'
		);

		if( array_key_exists( $responseStatus, $title ) )
			return $title[$responseStatus];
	}

	public function populate_event( $event ) {

		if( ! is_object( $event ) )
			return false;

		//print_r($event->start);
		//print_r($event->end);

		//print_r($event);

		$start = ! empty( $event->start->dateTime ) ? $event->start->dateTime : $event->start->date . ' 00:00:00';
		$end   = ! empty( $event->end->dateTime ) ? $event->end->dateTime : $event->end->date . ' 00:00:00';

		$start = strtotime( $start );
		$end   = strtotime( $end );

		$date_format = 'l j F Y';
		$start_date = date_i18n( $date_format, $start );
		$end_date = date_i18n( $date_format, $end );

		// time
		$event->display_start = '<span class="smaller">' . date( 'G\h', $start ) . ( date( 'i', $start ) != '00' ? date( 'i', $start ) : '' ) . '</span>';
		$event->display_end = '<span class="smaller">' . ( date( 'G\hi', $end ) != '00h00' ? date( 'G\h', $end ) . ( date( 'i', $end ) != '00' ? date( 'i', $end ) : '' ) : 'Minuit' ) . '</span>';
		$event->display_start_dateTime = '<span class="day">' . $start_date . ' </span>' . $event->display_start;
		$event->display_end_dateTime = '<span class="day' . ( $start_date == $end_date ? ' same-day' : '' ) . '">' . $end_date . ' </span>' . $event->display_end;
		$event->start_timestamp = $start;
		$event->end_timestamp = $end;
		$event->duration = $end - $start;
		$event->onAir = $start < time() && time() < $end ? true : false;

		// css
		if( isset( $event->multidays ) ) {
			if( $event->multidays == 'start' ) {
				$event->cssPosition = ( $start - strtotime( date( $this->date_format, $start ) ) ) / ( 24 * 60 * 60 ) * 100;
				$event->cssHeight = 100 - $event->cssPosition;
			} else if( $event->multidays == 'end' ) {
				$event->cssPosition = 0;
				$event->cssHeight = ( $end - strtotime( date( $this->date_format, $end ) ) ) / ( 24 * 60 * 60 ) * 100;
			}
		} else {
			$event->cssPosition = ( $start - strtotime( date( $this->date_format, $start ) ) ) / ( 24 * 60 * 60 ) * 100;
			if( $event->duration >= 1800 ) {
				$event->cssHeight = $event->duration / ( 24 * 60 * 60 ) * 100;
			} else {
				$event->cssHeight = 1800 / ( 24 * 60 * 60 ) * 100;
			}
		}

		// Get google programm title
		$name = $event->getSummary();
		$slug = sanitize_title( $name );

		if( term_exists( $slug, 'category' ) ) {
			$term = get_term_by( 'slug', $slug, 'category' );
			$event->post_category = $term;
			$event->live_display = get_term_meta( $term->term_id, 'live_display', true) == 'on' ? false : true; // true: current broadcast / false: background music
		}

		return $event;
	}

}

/**
 * Main instance of RCA_Calendar.
 *
 * Returns the main instance of RCA_Calendar to prevent the need to use globals.
 *
 * @since  5.1.1
 * @return RCA_Calendar
 */
function RCA_CAL() {
	return RCA_Calendar::init();
}
