<?php
/**
 * @see: https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Campus_Daily_Playlist_Songs_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Song', 'campus' ), //singular name of the listed records
			'plural'   => __( 'Songs', 'campus' ), //plural name of the listed records
			'ajax'     => false, //should this table support ajax?,
			'screen'	 => CAMPUS_DAILY_PLAYLIST_PAGE
		] );

	}

	/**
	 * Retrieve songs data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_songs( $per_page = 50, $page_number = 1 ) {

	  global $wpdb;

	  $sql = "SELECT * FROM {$wpdb->prefix}" . CAMPUS_DAILY_PLAYLIST_TABLE;

		if( ! empty( $_REQUEST['song_date'] ) ) {
			$sql .= " WHERE time LIKE '%{$_REQUEST['song_date']}%'";
		}

	  if ( ! empty( $_REQUEST['orderby'] ) ) {
	    $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
	    $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
	  }

	  $sql .= " LIMIT $per_page";

	  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

	  $result = $wpdb->get_results( $sql );

	  return $result;
	}

	/**
	 * Delete a song record.
	 *
	 * @param int $id song ID
	 */
	public static function delete_song( $time ) {
	  global $wpdb;

	  return $wpdb->delete(
	    $wpdb->prefix . CAMPUS_DAILY_PLAYLIST_TABLE,
	    array( 'time' => $time ),
	    array( '%s' )
	  );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
	  global $wpdb;

	  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}" . CAMPUS_DAILY_PLAYLIST_TABLE;

		if( ! empty( $_REQUEST['song_date'] ) ) {
			$sql .= " WHERE time LIKE '%{$_REQUEST['song_date']}%'";
		}

	  return $wpdb->get_var( $sql );
	}

	/**
	 * Text displayed when no song data is available
	 */
	public function no_items() {
	  _e( 'No song avaliable.', 'campus' );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_time( $item ) {

	  // create a nonce
	  $delete_nonce = wp_create_nonce( 'campus_delete_song' );

		$title = sprintf( '<span class="more" title="%s">%s</span>',
			sprintf( __( 'Duration: %s', 'campus' ), $item->duration > 3600 ? date_i18n( 'H:i:s', $item->duration ) : date_i18n( 'i:s', $item->duration ) ),
			date_i18n( 'H:i:s', strtotime( $item->time ) )
		);

	  $actions = [
	    'delete' => sprintf( '<a href="%s">%s</a>',
				esc_url(
					add_query_arg( array(
						'page' => esc_attr( $_REQUEST['page'] ),
						'song_date' => esc_attr( $_REQUEST['song_date'] ),
						'action' => 'delete',
						'time' => esc_attr( $item->time ),
						'_wpnonce' => $delete_nonce
					),
					admin_url( 'admin.php' ) )
				),
				__( 'Delete' )
			)
	  ];

	  return $title . $this->row_actions( $actions );
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
	  switch ( $column_name ) {
	    case 'title':
	    case 'artist':
	    case 'genre':

				$referer = esc_url( add_query_arg( array( 'page' => esc_attr( $_REQUEST['page'] ), 'song_date' => esc_attr( $_REQUEST['song_date'] ), 'paged' => esc_attr( isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1 ) ), admin_url( 'admin.php' ) ) );

				$type = $column_name . '_term_id';

				return sprintf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'taxonomy' => CAMPUS_DAILY_PLAYLIST_PREFIX . $column_name, 'tag_ID' => $item->$type, 'post_type' => CAMPUS_DAILY_PLAYLIST_ALBUM_POST_TYPE, 'wp_http_referer' => $referer ), admin_url( 'term.php' ) ) ),
					Campus_Daily_Playlist::get_song_term_name( $item, $column_name )
				);

	    case 'time':
	      $title = sprintf( '<span class="more" title="%s">%s</span>',
					sprintf( __( 'Duration: %s', 'campus' ), $item->duration > 3600 ? date_i18n( 'H:i:s', $item->duration ) : date_i18n( 'i:s', $item->duration ) ),
					date_i18n( 'H:i:s', strtotime( $item->time ) )
				);

	    default:
	      return $item->$column_name; //Show the whole array for troubleshooting purposes
	  }
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
	  return sprintf(
	    '<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item->time
	  );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
	  $columns = [
	    'cb'       => '<input type="checkbox" />',
			'time'     => __( 'Time', 'campus' ),
	    'title'    => __( 'Title', 'campus' ),
	    'artist' 	 => __( 'Artist', 'campus' ),
	    'category' => __( 'Category', 'campus' ),
			'genre'    => __( 'Genre', 'campus' ),
	  ];

	  return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
	  $sortable_columns = array(
	    'time'     => array( 'time', true )
	  );

	  return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
	  $actions = [
	    'bulk-delete' => __( 'Delete' )
	  ];

	  return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = array(
			$this->get_columns(),       // columns
			array(),           // hidden
			$this->get_sortable_columns(),  // sortable
		);

	  /** Process bulk action */
	  $this->process_bulk_action();

	  $per_page     = $this->get_items_per_page( 'songs_per_page', 50 );
	  $current_page = $this->get_pagenum();
	  $total_items  = self::record_count();

	  $this->set_pagination_args( [
	    'total_items' => $total_items, //WE have to calculate the total number of items
	    'per_page'    => $per_page //WE have to determine how many items to show on a page
	  ] );


	  $this->items = self::get_songs( $per_page, $current_page );
	}

	public function process_bulk_action() {

	  //Detect when a bulk action is being triggered...
	  if ( 'delete' === $this->current_action() ) {

	    // In our file that handles the request, verify the nonce.
	    $nonce = esc_attr( $_REQUEST['_wpnonce'] );

	    if ( ! wp_verify_nonce( $nonce, 'campus_delete_song' ) ) {
	      die( 'Go get a life script kiddies' );

			} else {
	      self::delete_song( esc_attr( $_GET['time'] ) );

	      wp_redirect(
					add_query_arg(
						array(
							'page' => esc_attr( $_REQUEST['page'] ),
							'song_date' => esc_attr( $_REQUEST['song_date'] ),
							'paged' => esc_attr( isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1 ),
							'deleted' => 1
						),
						admin_url( 'admin.php' )
					)
				);
	      exit;
	    }

	  }

	  // If the delete bulk action is triggered
	  if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
			|| ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
	  ) {

	    $delete_songs = esc_sql( $_POST['bulk-delete'] );

			$deleted = 0;

	    // loop over the array of record IDs and delete them
	    foreach ( $delete_songs as $time ) {
	      if( self::delete_song( esc_attr( $time ) ) ) {
					$deleted++;
				}
	    }

	    wp_redirect(
				add_query_arg(
					array(
						'page' => esc_attr( $_REQUEST['page'] ),
						'song_date' => esc_attr( $_REQUEST['song_date'] ),
						'paged' => esc_attr( isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1 ),
						'deleted' => $deleted
					),
					admin_url( 'admin.php' )
				)
			);
	    exit;
	  }
	}
}
