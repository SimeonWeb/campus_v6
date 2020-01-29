<?php
/*
 * Plugin Name: Gestion des événements - Radio Campus Angers
 * Plugin URI: http://simeon.web-createur.com
 * Description: Gestion de la grille des programmes et autres événements depuis google agenda.
 * Author: Siméon - Web Créateur
 * Version: 1.1
 * Author URI: http://simeon.web-createur.com
 * License: GPL2+
 * Text Domain: rca-events-grid
 * Domain Path: /languages/
 */

$version = defined( 'THEME_VERSION' ) ? THEME_VERSION : '1.1';
define( 'RCA_EVENTS_GRID_VERSION', 																							$version );

define( 'RCA_EVENTS_GRID_PLUGIN_DIR', 																					trailingslashit( dirname( __FILE__ ) ) );
define( 'RCA_EVENTS_GRID_PLUGIN_URL', 																					trailingslashit( str_replace( ABSPATH, trailingslashit( get_bloginfo( 'url' ) ), dirname( __FILE__ ) ) ) );
define( 'RCA_EVENTS_GRID_PLUGIN_FILE', 																					__FILE__ );

require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-events-grid.php' );
require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.google-calendar.php' );
require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-calendar.php' );
//require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'rca-events-grid-functions.php'           );

if ( is_admin() ) {

	// Add calendar render class
	require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-calendar-program.php' );
	require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-calendar-booking.php' );

	require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-events-grid-options.php' );
	require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-events-grid-admin.php' );
}

/*
if ( is_admin() ) {
	require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-events-grid-admin.php'     );
	require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-events-grid-options.php'   );
} else {
	require_once( RCA_EVENTS_GRID_PLUGIN_DIR . 'class.rca-events-grid-front.php'     );
}
*/

/*
register_activation_hook( __FILE__, array( 'RCA_Events_Grid', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'RCA_Events_Grid', 'plugin_deactivation' ) );

add_action( 'init', array( 'RCA_Events_Grid', 'init' ) );

add_action( 'plugins_loaded', array( 'RCA_EVENTS_GRID', 'plugin_textdomain' ), 99 );
add_action( 'plugins_loaded', array( 'RCA_EVENTS_GRID', 'load_modules' ), 100 );

RCA_Events_Grid::init();
*/
