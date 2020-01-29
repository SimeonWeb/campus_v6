<?php
/**
 * Additional features to allow styling of the templates
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

/**
 * Adds custom classes to the array of post classes.
 *
 * @param array $classes Classes for the post element.
 * @return array
 */
function campus_player_post_classes( $classes ) {

	$enclosure = powerpress_get_enclosure_data( get_the_ID() );
	$current_podcast = campus_is_current_podcast();

	if( $enclosure['url'] )
		$classes[] = 'with-enclosure';

	if( $current_podcast )
		$classes[] = 'current';

	if( $current_podcast && campus_is_paused() )
		$classes[] = 'paused';

	return $classes;
}
add_filter( 'post_class', 'campus_player_post_classes' );


/**
 * Get album external player button.
 *
 * @return html
 */
function campus_get_album_player() {

	$external_link = get_post_meta( get_the_ID(), '_' . get_post_type() . '_external_link', true );

	if( ! $external_link )
		return false;

	// Get supported social icons.
	$social_icons = campus_social_links_icons();
	$icon = 'external';

	foreach ( $social_icons as $attr => $value ) {
		if ( false !== strpos( $external_link, $attr ) ) {
			$icon = $value;
			break;
		}
	}

	return sprintf( '<div class="entry-player external-link"><a href="%1$s" title="%2$s" target="_blank">%3$s</a></div>',
		$external_link,
		'Écouter sur ' . $icon,
		campus_get_svg( array( 'icon' => esc_attr( $icon ), 'class' => 'icon-external-player' ) ) . campus_get_svg( array( 'icon' => 'play' ) )
	);
}

/**
 * Display album external player button.
 *
 * @return html
 */
function campus_the_album_player() {

	echo campus_get_album_player();
}


/**
 * Get entry player button.
 *
 * @return html
 */
function campus_get_entry_player() {

	// Get player data
	$player_data = campus_get_post_player_data();
	$link_class = 'podcast';

	if( ! $player_data )
		return false;

	$html = '<div class="entry-player ' . $link_class . '-link">';

		$html .= '<a href="#" rel="player"' . $player_data . '>';
			$html .= campus_get_svg( array( 'icon' => 'play' ) );
			$html .= campus_get_svg( array( 'icon' => 'pause' ) );
		$html .= '</a>';

	$html .= '</div>';


	return $html;
}

/**
 * Display entry player button.
 *
 * @return html
 */
function campus_the_entry_player() {

	echo campus_get_entry_player();
}

/**
 * Get player data
 *
 */
function campus_get_post_player_data( $html = true ) {
	global $post;
	$post_id = get_the_ID();
	$enclosure = powerpress_get_enclosure_data( $post_id );

	if( ! $enclosure )
		return false;

	$player_data = $enclosure;

	$category = campus_get_the_category_by_priority( $post_id );
	$term_id = isset( $category->term_id ) ? $category->term_id : null;
	$taxonomy = isset( $category->taxonomy ) ? $category->taxonomy : null;

	if( has_post_thumbnail() ) {
		$image = get_the_post_thumbnail( $post_id, array(180,180) );
	} else {
		$image = campus_get_category_thumbnail( array( 'term_id' => $term_id, 'taxonomy' => $taxonomy, 'size' => array(180,180) ) );
	}

	// Set output array data
	$player_data['image'] = $image;

	$player_data['name'] = sprintf( '%s / %s / %s',
		$post->post_title,
		$category->name,
		esc_attr( get_bloginfo( 'name' ) )
	);

	$player_data['title'] = sprintf( '<span class="title-hierarchical-prefix">%s</span> <span class="title-hierarchical">%s</span>',
		the_title( '', '', false ),
		$category->name
	);

	$player_data['link'] = get_permalink( $post_id );

	$player_data['class'] = campus_get_all_term_classes( $term_id );

	if( $html )
		return ' data-podcast="' . htmlentities( json_encode( $player_data ) ) . '"';

	return $player_data;
}

function campus_get_current_podcast_infos() {

	$infos = array(
		'name'	   => false,
		'title'    => false,
		'duration' => false,
		'feed'     => false,
		'id'	   => false,
		'image'    => false,
		'link'     => false,
		'size'     => false,
		'title'    => false,
		'type'     => false,
		'url'      => false,
		'height'   => false,
		'width'    => false,
		'class'	   => false
	);

	// If there is a cookie
	if( ! empty( $_COOKIE['campus-player-podcast-infos'] ) ) {

		$infos_cookie = json_decode( stripslashes( $_COOKIE['campus-player-podcast-infos'] ), true );
		$infos = array_merge( $infos, $infos_cookie );
	}

	return $infos;
}

function campus_is_current_podcast() {

	if( ! empty($_POST) && isset( $_POST['live'] ) && isset( $_POST['post_or_category_id'] ) && ! $_POST['live'] &&
		get_the_ID() == $_POST['post_or_category_id'] )
			return true;

	return false;
}

function campus_is_paused() {

	if( ! empty( $_POST ) && isset( $_POST['paused'] ) )
		return $_POST['paused'];

	return false;
}
