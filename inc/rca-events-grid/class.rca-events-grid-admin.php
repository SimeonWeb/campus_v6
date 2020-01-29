<?php
/**
* RCA Events Grid Admin
*/

class RCA_Events_Grid_Admin {

	/**
	 * @var RCA_Events_Grid_Admin
	 **/
	private static $instance = null;
	
	private static $view = array( 
		'grid' => 'Grille', 
		'list-detail' => 'Liste' 
	);
	
	private static $capabilities = array(
		'program' => array(
			'edit'
		),
		'booking' => array(
			'add',
			'edit'
		)
	);
	
	private static $calendars = array(
		'program' => 'Grille des programmes',
		'booking' => 'Réservation'
	);
	
	public static $booking_filters = array( 
		'all' => 'Tous', 
		'STUDIO' => 'Studio', 
		'MONTAGE' => 'Salle de montage', 
		'H2' => 'Enregistreur'
	);
	
	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new RCA_Events_Grid_Admin;
		}
		return self::$instance;
	}

	private function __construct() {
		
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		add_action( 'init', array( $this, 'admin_add_event' ) );
		add_action( 'init', array( $this, 'admin_edit_event' ) );
		add_action( 'wp_ajax_admin_add_event_form', array( $this, 'admin_event_form' ) );
		add_action( 'wp_ajax_admin_edit_event_form', array( $this, 'admin_event_form' ) );
	}
	
	public function admin_menu() {
		$options = get_option( 'RCA_Events_Grid' );
		
		if( ! empty( $options['program_calendar_id'] ) ) {
			add_menu_page(
				__( 'Grille' ),
				__( 'Grille' ),
				'edit_posts', 
				'program_grid', 
				array( $this, 'admin_program_grid_page' ),
				'dashicons-calendar-alt',
				45
			);
		}
		
		if( ! empty( $options['booking_calendar_id'] ) ) {
			add_menu_page(
				__( 'Réservation' ),
				__( 'Réservation' ),
				'edit_posts', 
				'booking_grid', 
				array( $this, 'admin_booking_grid_page' ),
				'dashicons-calendar',
				45
			);
		}
	}
	
	private function get_view( $type ) {
		if( ! empty( $_COOKIE['calendar_' . $type . '_view'] ) && array_key_exists( $_COOKIE['calendar_' . $type . '_view'], self::$view ) )
			return $_COOKIE['calendar_' . $type . '_view'];
		
		return 'grid';
	}
	
	private function get_filter( $type ) {
		if( ! empty( $_COOKIE['calendar_' . $type . '_filter'] ) && array_key_exists( $_COOKIE['calendar_' . $type . '_filter'], self::$booking_filters ) )
			return $_COOKIE['calendar_' . $type . '_filter'];
		
		return 'all';
	}
	
	private function get_data( $type ) {
		$caps = self::$capabilities[ $type ];
		
		$data = ' data-type="' . $type . '"';
		
		foreach( $caps as $cap )
			$data .= ' data-can-' . $cap . '-event="1"';
		
		return $data;
	}
	
	public function admin_program_grid_page() {
		$view = $this->get_view( 'program' );
		$data = $this->get_data( 'program' );
		$date = ! empty( $_GET['date'] ) ? $_GET['date'] : false;
		$date_format = get_option( 'date_format' );
		
		// Set calendar range
		RCA_CAL()->set_date( $date );
		?>
		<div class="wrap"<?php echo $data; ?>>
			<h1><?php _e( 'Grille des programmes' ) ?></h1>
			<div class="wp-filter">
				<div class="view-switch media-grid-view-switch">
					<a href="#" data-view="grid" class="view-grid<?php echo $view == 'grid' ? ' current' : ''; ?>">
						<span class="screen-reader-text">Vue en grille</span>
					</a>
					<a href="#" data-view="list-detail" class="view-list<?php echo $view == 'list-detail' ? ' current' : ''; ?>">
						<span class="screen-reader-text">Vue en liste</span>
					</a>
				</div>
				<div class="date-form search-form">
					<div class="date-display">
						<?php printf( '<span class="date-title">Semaine %s :</span> %s / %s',
							RCA_CAL()->week_number,
							date_i18n( $date_format, RCA_CAL()->week_start_timestamp ),
							date_i18n( $date_format, RCA_CAL()->week_end_timestamp )
						); ?>
					</div>
					<a href="#" class="date-picker-button" title="Choisir une date"><i class="dashicons-before dashicons-calendar-alt"></i></a>
					<div id="date-picker"></div>
				</div>
			</div>
			<div class="content-wrap content-<?php echo $view; ?>">
			<?php 
				RCA_CAL()->set_calendar( 'program' );
				RCA_CAL()->set_view( 'admin_grid' );
				RCA_CAL()->get_calendar();
			?>
			</div>
		</div>
		<?php
	}
	
	public function admin_booking_grid_page() {
		$view = $this->get_view( 'booking' );
		$filter = $this->get_filter( 'booking' );
		$data = $this->get_data( 'booking' );
		$date = ! empty( $_GET['date'] ) ? $_GET['date'] : false;
		$date_format = get_option( 'date_format' );
		
		// Set calendar range
		RCA_CAL()->set_date( $date );
		?>
		<div class="wrap"<?php echo $data; ?>>
			<h1><?php _e( 'Réservations' ) ?></h1>
			<div class="wp-filter">
				<div class="view-switch media-grid-view-switch">
					<a href="#" data-view="grid" class="view-grid<?php echo $view == 'grid' ? ' current' : ''; ?>">
						<span class="screen-reader-text">Vue en grille</span>
					</a>
					<a href="#" data-view="list-detail" class="view-list<?php echo $view == 'list-detail' ? ' current' : ''; ?>">
						<span class="screen-reader-text">Vue en liste</span>
					</a>
					<select name="booking_filters">
						<?php foreach( self::$booking_filters as $value => $name ) {
							printf( '<option value="%s"%s>%s</option>', $value, selected( $value, $filter, false ), $name );
						} ?>
					</select>
				</div>
				<div class="date-form search-form">
					<div class="date-display">
						<?php printf( '<span class="date-title">Semaine %s :</span> %s / %s',
							RCA_CAL()->week_number,
							date_i18n( $date_format, RCA_CAL()->week_start_timestamp ),
							date_i18n( $date_format, RCA_CAL()->week_end_timestamp )
						); ?>
					</div>
					<a href="#" class="date-picker-button" title="Choisir une date"><i class="dashicons-before dashicons-calendar-alt"></i></a>
					<div id="date-picker"></div>
				</div>
			</div>
			<div class="content-wrap content-<?php echo $view; ?>">
			<?php 
				RCA_CAL()->set_calendar( 'booking' );
				RCA_CAL()->set_view( 'admin_grid' );
				RCA_CAL()->get_calendar();
			?>
			</div>
		</div>
		<?php
	}
	
	public function admin_edit_event() {
		
		if( ! isset( $_POST['action'] ) )
			return;
		
		// Is Calendar exist?
		if( ! isset( $_POST['calendar'] ) || ! array_key_exists( $_POST['calendar'], self::$calendars ) )
			return;
		
		// Get action and check for calendar capabilities
		if( $_POST['action'] != 'admin_edit_event' || ! in_array( str_replace( array( 'admin_', '_event' ), '', $_POST['action'] ), self::$capabilities[$_POST['calendar']] ) )
			return;
		
		if( empty( $_POST['event'] ) )
			return;
		
		$data = $_POST['event'];
		$sendNotifications = isset( $_POST['sendNotifications'] ) ? $_POST['sendNotifications'] : false;
		
		// Add current user
/*
		$current_user = wp_get_current_user();
		$data['source'] = array(
			'title' => $current_user->user_email,
			'url' => isset( $_POST['wp_referer'] ) ? $_POST['wp_referer'] : get_bloginfo( 'url' )
		);
*/
		
		$updated = RCA_GC()->update_event( $_POST['calendar_id'], $_POST['event_id'], $data, array( 'sendNotifications' => $sendNotifications ) );
		
		if( $updated )
			add_action( 'admin_notices', function() { echo '<div class="notice notice-success is-dismissible"><p>Événement mis à jour</p></div>'; } );
		else
			add_action( 'admin_notices', function() { echo '<div class="notice notice-error is-dismissible"><p>L\'événement n\'a pas pu être mis à jour</p></div>'; } );
		
	}
	
	public function admin_add_event() {
		
		if( ! isset( $_POST['action'] ) )
			return;
		
		// Is Calendar exist?
		if( ! isset( $_POST['calendar'] ) || ! array_key_exists( $_POST['calendar'], self::$calendars ) )
			return;
		
		// Get action and check for calendar capabilities
		if( $_POST['action'] != 'admin_add_event' || ! in_array( str_replace( array( 'admin_', '_event' ), '', $_POST['action'] ), self::$capabilities[$_POST['calendar']] ) )
			return;
		
		if( empty( $_POST['event'] ) )
			return;
		
		$data = $_POST['event'];
		$sendNotifications = isset( $_POST['sendNotifications'] ) ? $_POST['sendNotifications'] : false;
		
		$added = RCA_GC()->add_event( $_POST['calendar_id'], $data, array( 'sendNotifications' => $sendNotifications ) );
		
		if( $added )
			add_action( 'admin_notices', function() { echo '<div class="notice notice-success is-dismissible"><p>Événement ajouté</p></div>'; } );
		else
			add_action( 'admin_notices', function() { echo '<div class="notice notice-error is-dismissible"><p>L\'événement n\'a pas pu être ajouté</p></div>'; } );
		
	}
	
	public function admin_event_form() {
		
		// Get action
		if( ! isset( $_GET['action'] ) )
			die;
		
		// Is Calendar exist?
		if( ! isset( $_GET['calendar'] ) || ! array_key_exists( $_GET['calendar'], self::$calendars ) )
			die;
		
		// Set action
		if( $_GET['action'] == 'admin_edit_event_form' ) {
			$action = 'edit';
			if( ! empty( $_GET['event_id'] ) )
				$event_id = $_GET['event_id'];
			else
				die;
		} else if( $_GET['action'] == 'admin_add_event_form' ) {
			$action = 'add';
		}
		
		// Check for calendar capabilities
		if( ! in_array( $action, self::$capabilities[$_GET['calendar']] ) )
			die;
		
		// Prepare args
		$args = $_GET;
		unset( $args['action'] );
		
		// Get class 
		$class = 'RCA_' . ucfirst( $_GET['calendar'] ) . '_Calendar';
		
		return $class::admin_event_form( $action, $args );
		die;
	}
	
	public function admin_enqueue_scripts() {
		global $current_screen;
		
		if( $current_screen->id != 'toplevel_page_program_grid' && 
			$current_screen->id != 'toplevel_page_booking_grid' )
			return;
		
		add_thickbox();		
		wp_enqueue_script( 'js-cookie' );
		wp_enqueue_script( 'page_program_grid', RCA_EVENTS_GRID_PLUGIN_URL . 'assets/js/admin-grid.js', array( 'jquery', 'jquery-ui-datepicker', 'campus-program-grid' ), RCA_EVENTS_GRID_VERSION, true );
		
		$add_event_form_url = add_query_arg( 
		    array( 
		        'action' 	 => 'admin_add_event_form',  
		        'wp_referer' => ! empty( $current_screen->parent_base ) ? admin_url( 'admin.php?page=' . $current_screen->parent_base ) : admin_url(),
		        'width'		 => 320,
		        'height'	 => 465
		    ), 
		    admin_url( 'admin-ajax.php' ) 
		);
		
		wp_localize_script( 'page_program_grid', 'rcaEventGrid', array( 'add_event_form_url' => $add_event_form_url ) );
		
		wp_enqueue_style( 'page_program_grid', RCA_EVENTS_GRID_PLUGIN_URL . 'assets/css/admin-grid.css', array( 'jquery-ui', 'campus-program-grid' ), RCA_EVENTS_GRID_VERSION );
	}
	
}

RCA_Events_Grid_Admin::init();