<?php
/**
 * Campus Playlist
 *
 * @Version 2.0
 */

define( 'CAMPUS_DAILY_PLAYLIST_ALBUM_POST_TYPE', 'album' );
define( 'CAMPUS_DAILY_PLAYLIST_PREFIX', 'album_' );
define( 'CAMPUS_DAILY_PLAYLIST_PAGE', 'daily_playlist' );
define( 'CAMPUS_DAILY_PLAYLIST_TABLE', 'campus_daily_playlist' );

/**
 * Load daily playlist Class.
 */
require dirname( __FILE__ ) . '/class-daily-playlist.php';

/**
 * Load Social functions.
 */
require dirname( __FILE__ ) . '/campus-playlist-functions.php';

/**
 * Load album Class.
 */
require dirname( __FILE__ ) . '/class-album-post-type.php';


/*--------------------------------------------------------------
For Admin only
--------------------------------------------------------------*/

/**
 * Load playlist options.
 */
if ( is_admin() ) {

	/**
	 * Load songs table class.
	 */
	require dirname( __FILE__ ) . '/class-daily-playlist-songs-table.php';

	/**
	 * Load playlist options.
	 */
	require dirname( __FILE__ ) . '/campus-playlist-options.php';
}
