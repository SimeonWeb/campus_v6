<?php
/**
 * SMN Social
 *
 * @Version 1.1
 */

define( 'SMN_SOCIAL_PLUGIN_CACHE_DISABLED' , false );
define( 'SMN_SOCIAL_PLUGIN_DEBUG' , false );

/**
 * Load Social functions.
 */
require dirname( __FILE__ ) . '/smn-social-functions.php';

/**
 * Load global Class.
 */
require dirname( __FILE__ ) . '/class-smn-social.php';


/*--------------------------------------------------------------
Optional Class
--------------------------------------------------------------*/

/**
 * Load Facebook Class.
 */
require dirname( __FILE__ ) . '/class-facebook.php';

/**
 * Load twitter Class.
 */
require dirname( __FILE__ ) . '/class-twitter.php';

/**
 * Load instagram Class.
 */
require dirname( __FILE__ ) . '/class-instagram.php';


/*--------------------------------------------------------------
For Admin only
--------------------------------------------------------------*/

/**
 * Load Social options.
 */
if ( is_admin() )
	require dirname( __FILE__ ) . '/smn-social-options.php';
