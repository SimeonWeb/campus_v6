<?php
/**
* RCA_Events_Grid
*/

class RCA_Events_Grid {


	/**
	 * Define post type slug
	 * @since 1.0
	 */
	public $slug = 'rca_events_grid';


	/**
	 * Holds the singleton instance of this class
	 * @since 1.0
	 * @var SMN_Partners
	 */
	static $instance = false;
	
    /**
     * Form messages
     */
    public $messages = array( 
    	'error' => array(), 
    	'success' => array() 
    );
	
    /**
     * Form messages
     */
	public $capability = 'manage_options';
	
    /**
     * Options
     */
    public $options = array();
	
    /**
     * Default options
     */
    public $default_options = array(
	    'program_calendar_id' => null,
	    'booking_calendar_id' => null,
    );
	
	/**
	 * Singleton
	 * @static
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new RCA_Events_Grid;
		}

		return self::$instance;
	}

	/**
	 * Constructor.  Initializes WordPress hooks
	 */
	private function __construct() {
		
		// Set current options
		$this->options = array_replace_recursive( $this->default_options, get_option( $this->slug . '_options', array() ) );
	
	}
	
	public function get_post_meta( $post_id, $key ) {
		
		return get_post_meta( $post_id, '_' . $this->slug . '_' . $key, true );
		
	}
	
	public function get_option( $section, $name ) {
		
		$options = $this->options;
		
		if( ! array_key_exists( $section, $options ) )
			return false;
		
		if( ! array_key_exists( $name, $options[$section] ) )
			return false;
		
		return trim( $options[$section][$name] );
		
	}
	
	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts_styles( $hook_suffix ) {
		$screen = get_current_screen();
		
		if( ! is_object( $screen ) )
			return;
		
		if( $screen->post_type != $this->slug || $screen->base != 'post' )
			return;
		
		// Enqueue Scripts
	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation( $network_wide ) {
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
	}
}