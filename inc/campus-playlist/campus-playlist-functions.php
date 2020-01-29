<?php
/**
 * Playlist functions
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

/**
 * Album functions
 *
 */
function is_playlist_active( $term_id = null ) {
	global $wp_query;
	
	if( is_null($term_id) )
		$term_id = $wp_query->queried_object_id;
	
	$active = (bool) get_term_meta( $term_id, 'album_playlist_visibility', true );
	
	return $active;
	
}


function is_album_visible( $post = 0 ) {
	$post = get_post( $post );

	if ( empty($post->ID) )
		return true;
	
	$terms = wp_get_post_terms( $post->ID, 'album_playlist' );
	
	if( !$terms )
		return true;
		
	foreach( $terms as $term ) {
		if( is_playlist_active( $term->term_id ) )
			return true;
	}
	
	return false;
}

function campus_is_playlist_archive() {
	
	$queried_object = get_queried_object();
	
	// Set archive block because we rewrite query in Campus_Album class
	
	return is_post_type_archive( 'album' ) || is_post_type_archive( 'block' ) || isset( $queried_object->taxonomy ) && substr( $queried_object->taxonomy, 0, 5 ) == 'album';
}