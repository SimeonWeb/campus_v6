<?php
/**
* RCA Events Grid Options
*/

class RCA_Events_Grid_Options {

	/**
	 * @var RCA_Events_Grid_Options
	 **/
	private static $instance = null;

    /**
     * Options
     */
    private $options = array();

    /**
     * Default options
     */
    private $default_options = array(
	    'program_calendar_id' => null,
	    'booking_calendar_id' => null,
    );

    /**
     * Title options
     */
    private $title_options = array(
	    'program_calendar_id' => 'Grille des programmes',
	    'booking_calendar_id' => 'Module de réservation',
    );

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new RCA_Events_Grid_Options;
		}
		return self::$instance;
	}

	private function __construct() {

		$this->options = get_option( 'RCA_Events_Grid', $this->default_options );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_init', array( $this, 'delete_cache' ) );
	}

	public function admin_menu() {

		add_submenu_page(
			'tools.php',
			__( 'Google Agenda' ),
			__( 'Google Agenda' ),
			'edit_pages',
			'RCA_Events_Grid_options',
			array( $this, 'options_page' )
		);
	}

	public function options_page() {
		?>
		<div id="<?php echo 'RCA_Events_Grid'; ?>_options" class="wrap">
			<h1><?php _e( 'Google Agenda' ) ?></h1>

			<?php if( current_user_can( 'manage_options' ) ) : ?>

				<?php //print_r($this->options); ?>

				<form method="post" action="options.php">

					<?php settings_fields( 'RCA_Events_Grid_options' ); ?>
					<?php do_settings_sections( 'RCA_Events_Grid_options' ); ?>
					<?php submit_button(); ?>
				</form>

			<?php endif; ?>

			<h2><?php _e( 'Cache' ) ?></h2>

			<form method="post" action="">

				<?php $this->cached_data_table_html(); ?>

				<p class="description">Si vous avez mis à jour un agenda depuis Google, vous pouvez le supprimer dans la liste ci-dessus afin de recharger les données.</p>

				<?php wp_nonce_field( 'rca_events_grid_delete_cache' ); ?>
				<?php submit_button( 'Supprimer le cache', 'delete', 'rca_events_grid_delete_cache' ); ?>
			</form>
		</div>
		<?php
	}

	public function register_settings() {

		register_setting(
			'RCA_Events_Grid_options', // Option group
			'RCA_Events_Grid' // Option name
		);

		add_settings_section(
			'google_calendars', // ID
			'Ids des agendas', // Title
			array( $this, 'settings_section_callback' ), // Callback
			'RCA_Events_Grid_options' // Page
		);

		foreach( $this->default_options as $id => $option ) {

			add_settings_field(
				$id, // ID
				$this->title_options[$id], // Title
				array( $this, 'settings_field_options_callback' ), // Callback
				'RCA_Events_Grid_options', // Page
				'google_calendars', // Section
				$id // Args
			);
		}

		add_settings_section(
			'google_calendars_params', // ID
			'Paramètres', // Title
			false, // Callback
			'RCA_Events_Grid_options' // Page
		);

		register_setting(
			'RCA_Events_Grid_options', // Option group
			'RCA_Events_Grid_cache_expire' // Option name
		);

		add_settings_field(
			'cache_expire', // ID
			'Expiration du cache (en seconde)', // Title
			array( $this, 'settings_field_params_callback' ), // Callback
			'RCA_Events_Grid_options', // Page
			'google_calendars_params', // Section
			'cache_expire' // Args
		);
	}

	public function settings_section_callback() {

		echo 'Ajouter les IDs des agendas google que vous utilisez.<br>Pour connaitre l\'id de l\'agenda, rendez-vous dans "Paramètre de l\'agenda", puis "Adresse URL de l\'agenda"';
	}

	public function settings_field_options_callback( $name ) {

		$value = $this->options[$name];

		printf( '<input type="text" name="RCA_Events_Grid[%s]" value="%s" class="regular-text"%s />',
			$name,
			$value,
			! current_user_can( 'manage_options' ) ? ' readonly' : ''
		);
	}

	public function settings_field_params_callback( $name ) {

		printf( '<input type="text" name="RCA_Events_Grid_%s" value="%s" class="regular-text"%s />',
			$name,
			get_option( 'RCA_Events_Grid_' . $name ),
			! current_user_can( 'manage_options' ) ? ' readonly' : ''
		);
	}

	/**
	 * Cache
	 *
	 */
	public function get_all_cached_data() {
		global $wpdb;

		$options = array_flip( $this->options );
		$titles = $this->title_options;

		$results = $wpdb->get_results( "SELECT * FROM $wpdb->options WHERE `option_name` LIKE '%_RCA_Google_Calendar_%'" );

		if( $results ) {
			$data = array();

			foreach( $results as $result ) {
				$cache_key = str_replace( array( '_transient_timeout_', '_transient_' ), '', $result->option_name );

				// Get Calendar Infos
				preg_match( '/RCA_Google_Calendar_(.+)_([0-9]{4})_([0-9]{1,2})/', $cache_key, $calendar_infos );

				if( count( $calendar_infos ) == 4 ) {

					$name = array_key_exists( $calendar_infos[1], $options ) ? $titles[$options[$calendar_infos[1]]] : $cache_key;
					$year = $calendar_infos[2];
					$week_number = $calendar_infos[3];

					$name .= sprintf(
						' - du %s au %s',
						date_i18n( get_option( 'date_format' ), strtotime( $year . 'W' . $week_number . '1' ) ),
						date_i18n( get_option( 'date_format' ), strtotime( $year . 'W' . $week_number . '7 + 1day' ) )
					);

				} else {
					$name = $cache_key;
				}

				if( get_transient( $cache_key ) ) {

					if( '_transient_timeout_' == substr( $result->option_name, 0, 19 ) ) {
						$data[$cache_key]['timeout'] = $result->option_value;
						$data[$cache_key]['timeout_option'] = $result;
					} else {
						$data[$cache_key]['name'] = $name;
						$data[$cache_key]['name_option'] = $result;
					}
				}
			}

			if( ! empty( $data ) )
				return $data;
		}

		return false;
	}

	public function delete_cache() {

		if( ! isset( $_POST['rca_events_grid_delete_cache'] ) )
			return;

		if( ! check_admin_referer( 'rca_events_grid_delete_cache' ) )
			return;

		if( ! empty( $_POST['rca_events_grid_cache'] ) ) {
			foreach( $_POST['rca_events_grid_cache'] as $cache_key )
				delete_transient( $cache_key );
		} else {
			$data = $this->get_all_cached_data();
			if( $data )
				foreach( $data as $cache_key => $option )
					delete_transient( $cache_key );
		}

	}

	public function cached_data_table_html() {

		$data = $this->get_all_cached_data();

		if( $data ) {
		?>
			<table class="wp-list-table widefat fixed striped">
			<?php
			date_default_timezone_set( get_option( 'timezone_string' ) );

			foreach( $data as $cache_key => $field ) {
				?>
				<tr>
					<th scope="row" class="check-column"><input id="cb-select-<?php echo $cache_key; ?>" name="rca_events_grid_cache[]" value="<?php echo $cache_key; ?>" type="checkbox"></th>
					<td class="title column-title column-primary page-title" data-colname="Titre" style="width:60%;"><label for="cb-select-<?php echo $cache_key; ?>"><strong><?php echo $field['name']; ?></strong></label></td>
					<td class="timeout column-timeout" data-colname="Timeout"><?php printf( 'Expire le %s à %s', date_i18n( get_option( 'date_format' ), $field['timeout'] ), date_i18n( get_option( 'time_format' ), $field['timeout'] ) ); ?></td>
				</tr>
				<?php
			}
			?>
			</table>
		<?php
		} else {
			echo '<p>Il n\'y a aucune donnée en cache.</p>';
		}
	}

}

RCA_Events_Grid_Options::init();
