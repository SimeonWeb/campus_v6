<?php
/**
 * Daily Playlist class
 *
 * Playlist quotidienne des titres passés en programmation automatique.
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 4.0
 */

class Campus_Daily_Playlist {

	static $instance = false;

	static $tax_prefix = 'album';

	static $page_id;

	static $page_url;

	static $table_name;

	static $default_live_fields;

	/**
	 * Singleton
	 * @static
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new Campus_Daily_Playlist;
		}

		return self::$instance;
	}

	function __construct() {
		global $wpdb;

		self::$default_live_fields = array(
			'start' 	  	=> 0,
			'duration'		=> 0,
			'name'			=> get_bloginfo( 'name' ),
			'title'   		=> 'Programmation musicale',
			'link'			=> '',
			'display_start' => '',
			'display_end'   => '',
			'display_time'  => '',
			'image'  		=> campus_get_svg( array( 'icon' => 'song', 'class' => 'wp-category-image' ) ),
			'class'			=> ''
		);

		self::$page_id = get_option( 'playlist_page' );

		if( self::$page_id )
			self::$page_url = get_page_link( self::$page_id );

		self::$table_name = $wpdb->prefix . CAMPUS_DAILY_PLAYLIST_TABLE;

		add_action( 'wp_ajax_get_hourly_results', array( $this, 'get_hourly_results' ) );
		add_action( 'wp_ajax_nopriv_get_hourly_results', array( $this, 'get_hourly_results' ) );

		add_action( 'wp_ajax_get_live_results', array( $this, 'get_live_results' ) );
		add_action( 'wp_ajax_nopriv_get_live_results', array( $this, 'get_live_results' ) );

		add_shortcode('daily_playlists', array( $this, 'display_daily_playlist_form' ) );
	}

	static function playlist_options() {
		return get_option( 'playlist_option', array() );
	}

	static function playlist_scope_option() {
		return get_option( 'playlist_scope_option', array('begin' => '', 'end' => '') );
	}

	/**
	 * Search form
	 * Scope: Admin & Front
	 */
	static function daily_playlist_form( $args = '' ) {
		$scope = self::playlist_scope_option();

		// sfs = search for songs
		$date = $time = '';

		date_default_timezone_set( get_option( 'timezone_string' ) );

		if( isset( $_GET['sfs'] ) ) {

			$date = $_GET['date'];
			$time = $_GET['time'];

		} else if( ! isset( $args['empty_values'] ) || ! $args['empty_values'] ) {

			$date = date( 'Y-m-d' );
			$time = date( 'H:i' );
		}

		// Set action form
		$action = ! empty( $args['action'] ) ? $args['action'] : self::$page_url;

		// Set class form
		$classes = array( 'search-form', 'search-song-form' );
		if( ! empty( $args['class'] ) )
			$classes[] = esc_attr( $args['class'] );

		// Parse url and add query args to form
		$hidden_inputs = '';
		$url = parse_url( $action );
		if( ! empty( $url['query'] ) ) {
			parse_str( $url['query'], $get_args );

			if( $get_args ) {
				foreach( $get_args as $name => $value ) {
					$hidden_inputs .= sprintf( '<input type="hidden" name="%s" value="%s" />', $name, $value );
				}
			}
		}

		?>
		<form id="search-playlist-form" class="<?php echo join( ' ', $classes ); ?>" action="<?php echo $action; ?>" method="get">
			<?php echo $hidden_inputs; ?>
			<input type="hidden" name="sfs">
			<label for="date">Le</label>
			<input type="date" id="date" name="date" placeholder="yyyy-mm-dd" min="<?php echo $scope['end']; ?>" max="<?php echo date( 'Y-m-d' ); ?>" value="<?php echo $date ?>" />
			<label for="date">à</label>
			<input type="time" id="time" name="time" placeholder="--:--" value="<?php echo $time ?>" />
			<button id="send" class="search-submit" type="submit" title="Rechercher">
				<?php echo campus_get_svg( array( 'icon' => 'search-song' ) ); ?><span class="screen-reader-text"><?php echo _x( 'Search', 'submit button', 'campus' ); ?></span>
			</button>
		</form>
		<?php
	}


	/**
	 * Search results
	 * Scope: Admin & Front
	 */
	static function get_hourly_results() {
		global $wpdb;

		$table_name = self::$table_name;

		date_default_timezone_set( get_option( 'timezone_string' ) );

		$time = date('Y-m-d H:i:s');
		$date = date('Y-m-d');

		$_timestamp = time();

		$excluded_terms = self::get_excluded_terms();
		$sql_excluded_terms = '';
		foreach( $excluded_terms as $excluded_term ) {
			$sql_excluded_terms .= " AND `category` NOT LIKE '%$excluded_term%'";
		}

		if( isset( $_GET['sfs'] ) || isset( $_POST['get_hourly_results'] ) ) {

			if( isset( $_POST['get_hourly_results'] ) )
				$result = $_POST;
			else if( isset( $_GET['sfs'] ) )
				$result = $_GET;

			$_date = $result['date'];
			$_time = $result['time'];

			$_timestamp = strtotime( $_date . ' ' . $_time );

			if( ! preg_match( '/[0-9]{4}-[0-9]{2}-[0-9]{2}/' , $_date ) ) {

				$union = $union_pg = '';
			} else {
				$time = sprintf( '%s %s:59', $_date, $_time );
				$date = $_date;

				$union = "UNION ( SELECT `time` , `title_term_id` , `artist_term_id`, `duration`, '1' as `tag`
								  FROM `$table_name`
								  WHERE `time` >= TIMESTAMP( '$time' )
								  AND `time` <= NOW()
								  AND DATE_ADD( '$time' , INTERVAL 3 HOUR ) > `time`
								  $sql_excluded_terms
								  LIMIT 0,20
								)";
			}
		} else {
			$union = '';
		}

		$sql = "( SELECT `time` , `title_term_id` , `artist_term_id`, `duration`, '0' as `tag`
						FROM `$table_name`
						WHERE `time` <= TIMESTAMP( '$time' )
						AND `time` <= NOW()
						AND DATE_ADD( '$time' , INTERVAL '-3' HOUR ) < `time`
						$sql_excluded_terms
						ORDER BY `time` DESC
						LIMIT 0,20
				)
				$union
				ORDER BY `time` DESC;";

		// Playlist
		$playlist_results = $wpdb->get_results( $sql );

		$optimum = 0;
		$total = 20;
		$before_lenght = 20;

		// Get programs
		RCA_CAL()->set_calendar( 'program' );
		$event_results = RCA_CAL()->get_events( array(
			'year'	  	  => date_i18n( 'o', $_timestamp ),
			'week_number' => date_i18n( 'W', $_timestamp )
		) );

		// Final results
		$final_results = self::parse_results( $event_results, $playlist_results, $_timestamp );

		// Most recent one before
		krsort( $final_results );

		if( $final_results ) {

			foreach ( $final_results as $key => $result ) {

				if ( $key < strtotime( $time ) ) {
					$optimum = $key;
					break;
				}
			}

			$optimum_index = array_search( $optimum, array_keys( $final_results ) );

			// Display only 2 results before optimum
			$start_index = ( $optimum_index > 2 ) ? $optimum_index - 2 : 0;
			$final = array_slice( $final_results, $start_index, null, true );
			if( $_timestamp > time() )
				self::display_hourly_result( false, true );

			foreach( $final as $key => $result ) {
				self::display_hourly_result( $result, $optimum, $key );
			}

		} else {
			self::display_hourly_result( false );
		}

		// Ajax call
		if( isset( $_POST['action'] ) && $_POST['action'] == 'get_hourly_results' )
			die;
	}

	static function parse_results( $events, $playlist, $time_search = false ) {

		date_default_timezone_set( get_option( 'timezone_string' ) );

		$results = array();

		$time_search = $time_search ? $time_search : time();

		$last_index = count( $playlist ) - 1;
		$first = array_slice( $playlist, 0, 1 );
		$first = array_shift( $first );
		$last = array_slice( $playlist, $last_index, 1 );
		$last = array_shift( $last );

		$events = array_reverse( $events );

		// Programs
		foreach( $events as $key => $event ) {

			if( $first && $last ) {

				$event = RCA_CAL()->populate_event( $event );

				if( $event && $event->start_timestamp <= strtotime( $first->time ) &&
					$event->end_timestamp >= ( strtotime( $last->time ) + $last->duration ) ) {

					$results[$event->start_timestamp] = $event;
				}
			} else {

				$event = RCA_CAL()->populate_event( $event );

				if( $event && $event->start_timestamp <= $time_search &&
					$event->end_timestamp >= $time_search ) {
					$results[$event->start_timestamp] = $event;
				}
			}
		}

		// Song
		foreach( $playlist as $song ) {

			$key = strtotime( $song->time );

			if( isset( $results[$key] ) )
				$key++;

			$results[$key] = $song;
		}

		ksort( $results );

		// Remove Songs who are at the same time as programs
		$mask = null;
		foreach( $results as $time => $result ) {

			// Program
			if( isset( $result->end_timestamp ) ) {

				$mask = $time;

			// Song
			} else {

				if( ! is_null( $mask ) && $time < $results[$mask]->end_timestamp ) {

					// For live display, add song to the program
					if( isset( $results[$mask]->live_display ) && ! $results[$mask]->live_display ) {

						if( ! isset( $results[$mask]->playlist ) )
							$results[$mask]->playlist = array();

						// if result duration is 80 percent smaller than the mask result
						// For 1 hours, limit is 48 minutes
						if( $result->duration / $results[$mask]->duration < .8 ) {
							// Add result to mask playlist
							$results[$mask]->playlist[strtotime($result->time)] = $result;
						}
					}

					// Remove song from the results
					unset( $results[$time] );
				}
			}
		}

		return $results;
	}

	static function display_hourly_result( $result, $optimum = null, $index = null ) {

		$classes = array(
			'list-item',
			'hentry',
			'list-entry',
			'daily-playlist-result',
			'program-list-entry'
		);

		if( $result ) {

			if( $index == $optimum )
				$classes[] = 'optimum';

			$content_meta = '';

			// Daily playlist
			if( isset( $result->time ) ) {

				if( ! empty( $result->category ) ) {
					$thumbnail = sprintf( '<a href="%s">%s</a>',
						get_category_link( $result->category->term_id ),
						campus_get_category_thumbnail( array( 'term_id' => $result->category->term_id, 'taxonomy' => 'category', 'size' => array(180,180) ) )
					);
				} else {
					$thumbnail = campus_get_svg( array( 'icon' => 'song', 'class' => 'wp-category-image' ) );
				}

				$id = str_replace( array(' ', '-', ':'), array('_', '', ''), $result->time );

				$title = self::get_song_term_name( $result, 'title' );
				$artist = self::get_song_term_name( $result, 'artist' );

				$albums = Campus_Album::get_albums( array( 'artist' => $result->artist_term_id, 'title' => $result->title_term_id ) );

				if( ! empty( $albums ) ) {
					$thumbnail = get_the_post_thumbnail( $albums[0], array(180,180) );

					if( $playlist_list = get_the_term_list( $albums[0]->ID, 'album_playlist', campus_get_svg( array( 'icon' => 'playlist', 'class' => 'icon-small' ) ) . ' ', ' / ', '' ) ) {
						$content_meta .= '<div class="playlist-type">' . $playlist_list . '</div>';
					}
				}

				if( $content_meta ) {
					$content_meta = '<div class="entry-content">' . $content_meta . '</div>';
				}

				$content = sprintf( '<div class="entry-header"><div class="entry-title">%s</div><div class="entry-description">%s</div></div>%s',
					$title,
					$artist,
					$content_meta
				);

				$meta = sprintf( '<time datetime="%s">%s</time>',
					date( DATE_W3C, strtotime( $result->time ) ),
					date_i18n( get_option( 'time_format' ), strtotime( $result->time ) )
				);

			// Program grid
			} else {

				// A category is associated to the event
				if( isset( $result->post_category ) ) {

					$term_link = get_term_link( $result->post_category );

					$title = sprintf( '<a href="%s">%s</a>',
						$term_link,
						apply_filters( 'the_title', $result->post_category->name )
					);
					$description = sprintf( '<a href="%s">%s</a>',
						$term_link,
						get_term_meta( $result->post_category->term_id, 'secondary_description', true )
					);
					$thumbnail = sprintf( '<a href="%s">%s</a>',
						$term_link,
						campus_get_category_thumbnail( array( 'term_id' => $result->post_category->term_id, 'taxonomy' => $result->post_category->taxonomy, 'size' => array(180,180) ) )
					);

				// It's just a google event
				} else {

					$title = apply_filters( 'the_title', $result->getSummary() );
					$description = '';
					$thumbnail = campus_get_svg( array( 'icon' => 'program', 'class' => 'wp-category-image' ) );
				}

				$id = 'program-' . $result->start_timestamp;

				if( ! empty( $result->playlist ) ) {

					$songs = '<ul class="entry-playlist">';
					foreach( $result->playlist as $song ) {

						$song_title = self::get_song_term_name( $song, 'title' );
						$song_artist = self::get_song_term_name( $song, 'artist' );

						$sep = ( $song_title && $song_artist ) ? ' > ' : '';
						$songs .= sprintf( '<li class="playlist-item"><span class="field-title">%s</span>%s<span class="field-artist">%s</span></li>', $song_title, $sep, $song_artist );
					}
					$songs .= '</ul>';

					$content_meta .= $songs;
				}

				if( $content_meta ) {
					$content_meta = '<div class="entry-content">' . $content_meta . '</div>';
				}

				$content = sprintf( '<div class="entry-header"><div class="entry-title">%s</div><div class="entry-description">%s</div></div>%s',
					$title,
					$description,
					$content_meta
				);

				$meta = sprintf( '<span class="taxonomy-schedules-hours">%s > %s</span>',
					$result->display_start,
					$result->display_end
				);
			}

		} else {

			if( $optimum ) {

				$id = 'cheat';
				$thumbnail = campus_get_svg( array( 'icon' => 'arrow-down', 'class' => 'wp-category-image' ) );
				$content = sprintf( '<div class="entry-header"><div class="entry-title">Un peu de patience...</div><div class="entry-description">Il n\'est que %s, ne vous gachez pas la surprise ;)</div></div>', date( get_option( 'time_format' ) ) );
				$meta = '';
			} else {

				$id = 'no-result';
				$thumbnail = campus_get_svg( array( 'icon' => 'today', 'class' => 'wp-category-image' ) );
				$content = '<div class="entry-header"><div class="entry-title">On a fouillé...</div><div class="entry-description">...mais on a rien trouvé :(</div></div>';
				$meta = '';
			}
		}

		// Display result
		printf( '<article id="%s" class="%s"><figure class="post-thumbnail list-thumbnail">%s</figure><header class="post-content list-content">%s</header><footer class="post-meta list-meta">%s</footer></article>',
			$id,
			join( ' ', $classes ),
			$thumbnail,
			$content,
			$meta
		);
	}

	/**
	 * Live results
	 * Scope: Admin & Front
	 */
	static function get_live_results() {
		global $wpdb;

		$table_playlist = self::$table_name;

		date_default_timezone_set( get_option( 'timezone_string' ) );

		// Excluded terms
		$excluded_terms = self::get_excluded_terms();
		$sql_excluded_terms = '';
		foreach( $excluded_terms as $excluded_term ) {
			$sql_excluded_terms .= " AND `category` NOT LIKE '%$excluded_term%'";
		}

		// Create query
		$sql_playlist = "(SELECT `time` , `title_term_id` , `artist_term_id`, `duration`
		    			 FROM `$table_playlist`
		    			 WHERE `time` <= NOW()
		    			 AND ( CURDATE() = DATE_FORMAT( `time`, '%Y-%m-%d' )
		    			    OR DATE_ADD( CURDATE() , INTERVAL 1 DAY ) = DATE_FORMAT( `time`, '%Y-%m-%d' )
		    			    OR DATE_ADD( CURDATE() , INTERVAL '-1' DAY ) = DATE_FORMAT( `time`, '%Y-%m-%d' ) )
						 $sql_excluded_terms
		    			 ORDER BY `time` DESC
		    			 LIMIT 1)
		    		UNION (
		    			 SELECT `time` , `title_term_id` , `artist_term_id`, `duration`
		    			 FROM `$table_playlist`
		    			 WHERE `time` > NOW()
		    			 AND ( CURDATE() = DATE_FORMAT( `time`, '%Y-%m-%d' )
		    			    OR DATE_ADD( CURDATE() , INTERVAL 1 DAY ) = DATE_FORMAT( `time`, '%Y-%m-%d' )
		    			    OR DATE_ADD( CURDATE() , INTERVAL '-1' DAY ) = DATE_FORMAT( `time`, '%Y-%m-%d' ) )
						 $sql_excluded_terms
		    			 ORDER BY `time` ASC
		    			 LIMIT 1)
					ORDER BY `time` ASC;";

		// Get playlist
		$playlist_results = $wpdb->get_results( $sql_playlist );

		// Get current program
		RCA_CAL()->set_calendar( 'program' );
		$event = RCA_CAL()->current_show; // @need RCA_CAL()->set_calendar called first
		$event_results = $event ? array( $event ) : array();

		// Set current timestamp
		$_timestamp = time();

		// Final results
		$final_results = self::parse_results( $event_results, $playlist_results, $_timestamp );

		// Set output
		$output = self::$default_live_fields;
		$output['cal_events'] = $event_results;
		$output['pl_events'] = $playlist_results;

		if( $final_results ) {

			$output['results'] = $final_results;
			$output['is_playing'] = false;

			foreach( $final_results as $time => $result ) {

				$output['end'] = $time + $result->duration;

				$output['is_playing'] = array( 'timestamp' => $_timestamp, 'start' => $time, 'end' => ( $time + $result->duration ) );

				// Check if result is realy playing now
				// Add ten seconds float to current time, to be sure
				if( $time <= ( $_timestamp + 10 ) && ( $time + $result->duration ) >= ( $_timestamp - 10 ) ) {

					$output['start'] = $time;
					$output['duration'] = $result->duration;

					// Daily playlist
					if( isset( $result->time ) ) {

						$title = self::get_song_term_name( $result, 'title' );
						$description = self::get_song_term_name( $result, 'artist' );

					// Program
					} else {

						// A category is associated to the event
						if( isset( $result->post_category ) ) {

							$title = $result->post_category->name;
							$description = get_term_meta( $result->post_category->term_id, 'secondary_description', true );

							$output['image'] = campus_get_category_thumbnail( array( 'term_id' => $result->post_category->term_id, 'taxonomy' => $result->post_category->taxonomy, 'size' => array(180,180) ) );

							$output['class'] = campus_get_all_term_classes( $result->post_category->term_id );

							$output['link'] = get_term_link( $result->post_category );

						} else {

							$title = apply_filters( 'the_title', $result->getSummary() );
							$description = '';

							$output['image'] = campus_get_svg( array( 'icon' => 'program', 'class' => 'wp-category-image category-autres' ) );

							$output['class'] = 'category-autres';
						}

						if( ! empty( $result->playlist ) ) {

							foreach( $result->playlist as $song_time => $song ) {

								if( $song_time < $_timestamp && ( $song_time + $song->duration ) > $_timestamp ) {
									$sub_title = self::get_song_term_name( $song, 'title' );
									$description = self::get_song_term_name( $song, 'artist' );

									$title .= $sub_title ? ' / ' . $sub_title : '';

									$output['start'] = $song_time;
									$output['duration'] = $song->duration;

									break;
								}
							}
						}

						$output['display_start'] = $result->display_start;
						$output['display_end'] = $result->display_end;
						$output['display_time'] = $result->display_start . ( $result->display_start && $result->display_end ? ' / ' : '' ) . $result->display_end;
					}

					$output['name'] = sprintf( '%s / %s / %s',
						$title,
						$description,
						esc_attr( get_bloginfo( 'name' ) )
					);

					$output['title'] = sprintf( '<span class="title-hierarchical-prefix">%s</span> <span class="title-hierarchical">%s</span>',
						$title,
						$description
					);

					break;
				}
			}
		}

		// Ajax call
		if( isset( $_POST['action'] ) && $_POST['action'] == 'get_live_results' ) {
			echo json_encode( $output );
			die;
		} else {
			return $output;
		}
	}

	static function get_song_term_name( $song, $part ) {

		$type = $part . '_term_id';

		if( isset( $song->$type ) ) {

			$taxonomy = ($part == 'title') ? CAMPUS_DAILY_PLAYLIST_PREFIX . 'song' : CAMPUS_DAILY_PLAYLIST_PREFIX . $part;
			$term = get_term( $song->$type, $taxonomy );

			if( $term && ! is_wp_error( $term ) )
				return $term->name;
		}

		return false;
	}

	/**
	 * Return shorcode
	 */
	static function display_daily_playlist_form( $action = '' ) {

		?>
		<section class="playlist content-wrapper">
		<header class="content-header fixed">
			<?php self::daily_playlist_form( array( 'action' => $action ) ); ?>
		</header>
		<div class="daily-playlists content-hentry">
			<div id="daily-playlists-results">

				<?php self::get_hourly_results(); ?>

			</div>
		</div>
		</section>
		<?php
	}

	static function get_excluded_terms() {
		$excluded_terms = get_option( 'daily_playlist_excluded_terms', array() );

		if( ! is_array( $excluded_terms ) )
			$excluded_terms = explode( ',', $excluded_terms );

		return $excluded_terms;
	}
}

Campus_Daily_Playlist::init();
