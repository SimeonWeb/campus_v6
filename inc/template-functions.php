<?php
/**
 * Additional features to allow styling of the templates
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

/**
 * Check $_POST var ajax
 */
function campus_is_ajax() {
	return ( ! empty( $_REQUEST ) && isset( $_REQUEST['ajax'] ) && $_REQUEST['ajax'] );
}

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function campus_body_classes( $classes ) {
	// Add class for first load
	if( ! campus_is_ajax() )
		$classes[] = 'html-loading';

	$classes[] = 'front';
	$classes[] = 'loading';

	// Add class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Add class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Add class if we're viewing the Customizer for easier styling of theme options.
	if ( is_customize_preview() ) {
		$classes[] = 'campus-customizer';
	}

	// Add class if sidebar is used.
	if ( is_active_sidebar( 'sidebar-' . campus_get_sidebar_id() ) && ! is_page_template( 'page-alt-daily-playlist.php' ) ) {
		$classes[] = 'has-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'campus_body_classes' );


/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function campus_post_classes( $classes ) {

	if ( is_sticky() ) {
		if ( ! is_home() || is_paged() ) {
			$classes[] = 'sticky';
		}
	}

	if( get_post_type() == 'post' )
		$classes[] = 'gradient-' . campus_get_gradient_direction();

	if( $parents = campus_get_post_category_parents() )
		foreach( $parents as $parent )
			$classes[] = 'category-' . $parent->slug;

	return $classes;
}
add_filter( 'post_class', 'campus_post_classes' );


/**
 * Set custom classes for content.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function campus_content_classes( $class = '' ) {

	$classes = array( 'content-area' );

	// Add filter display class
	$filters = campus_get_current_filters();

	if( $filters ) {

		foreach( $filters as $filter ) {

			foreach( $filter as $screen => $value ) {
				$display_on = campus_display_on( $screen );

				if( $display_on ) {
					$classes[] = 'content-' . $value;
					break;
				}
			}
		}
	}

	if( $class != '' )
		$classes[] = $class;

	// Remove empty values
	$classes = array_filter( $classes );

	printf( 'class="%s"', join( ' ', $classes ) );
}


/**
 * Set custom classes for content.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function campus_taxonomy_classes( $class = '', $term_id = null, $taxonomy = null ) {

	if( is_null( $term_id ) || is_null( $taxonomy ) ) {
		$queried_object = get_queried_object();
		if( isset( $queried_object->taxonomy ) ) {
			$term_id  = $queried_object->term_id;
			$taxonomy = $queried_object->taxonomy;
		}
	}

	$classes = campus_get_all_term_classes( $term_id, $taxonomy, true );
	$classes[] = 'type-' . $taxonomy;

	if( $class != '' )
		$classes[] = $class;

	// Remove empty values
	$classes = array_filter( $classes );

	printf( 'class="%s"', join( ' ', $classes ) );
}

/**
 * Get sidebar id
 */
function campus_get_sidebar_id() {

	$sidebar_id = false;

	if( get_post_meta( get_the_ID(), '_hide_sidebar', true ) )
		return false;

	if( is_search() )
		return false;

	if( campus_is_playlist_archive() ) {
		$sidebar_id = 5;

	} else if( is_page() && ! is_page_template('page-alt-programs.php') && ! is_page_template('page-alt-player-popup.php') ) {
		$sidebar_id = 4;

	} else if( is_single() && post_is_in_descendant_category( get_option( 'category_by_priority' ) ) ) {
		$sidebar_id = 2;

	} else if( is_single() ) {
		$sidebar_id = 3;

	} else {
		$sidebar_id = 1;
	}

	return $sidebar_id;
}

/**
 * Custom archive title
 */
function campus_get_the_archive_title( $title ) {

	$queried_object = get_queried_object();

	if( isset( $queried_object->post_title ) ) {
		$title = $queried_object->post_title;
	}

	// Hide the bullshit part of the title
    if( preg_match( '/:/', $title ) )
	    $title = '<span class="screen-reader-text">' . str_ireplace( ':', ':</span>', $title );

    return $title;
}
add_action( 'get_the_archive_title', 'campus_get_the_archive_title' );


/**
 * Custom Excerpt
 *
 */
function campus_excerpt( $str, $count = 46, $echo = true ) {

	$str = wp_strip_all_tags( $str, true );
	$str = apply_filters( 'before_campus_excerpt', $str );

	if( strlen($str) >= $count ) {
		$str = substr($str, 0, $count);
		$space = strrpos($str, ' ');
		$str = substr($str, 0, $space) . '...';
	}
	// remove part of an entity at the end
	$str = preg_replace( '/&[^;s]{0,6}$/', '', $str );
	$output = apply_filters( 'campus_excerpt', $str );
	if( $echo )
		echo $output;
	else
		return $output;
}

/**
 * Add placeholder when there's not thumbnail
 *
 * @param html 	$html
 * @param int 	$post_id
 * @param int 	$post_thumbnail_id
 * @param mixed $size
 * @param array $attr
 * @return html
 */
function campus_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {

	// Remove Thumbnail posted before 2017-01-01
	if( get_post_type( $post_id ) == 'post' && $post_thumbnail_id ) {

		$post = get_post( $post_id );

		if( $post ) {
			if( strtotime( $post->post_date ) < strtotime( '2017-01-01 00:00:00' ) ) {
				return campus_get_category_thumbnail( array( 'post_id'=> $post_id ) );
			}
		}
	}

	if( $html || is_admin() )
		return $html;

	if( get_post_type( $post_id ) == 'page' ) {

		$html = campus_get_svg( array( 'icon' => 'default', 'class' => 'wp-post-image' ) );

	} else if( get_post_type( $post_id ) == 'post' ) {

		$html = campus_get_category_thumbnail( array( 'post_id'=> $post_id ) );
	}

	return $html;
}
add_filter( 'post_thumbnail_html', 'campus_post_thumbnail_html', 10, 5 );

/**
 * Get gradient direction for post grid
 *
 * @return str
 */
function campus_get_gradient_direction( $sticky = false ) {

	$stp = get_post_meta( get_the_ID(), '_post_sticky_title_position', true );
	$directions = array( 'top-left', 'top-right', 'bottom-right', 'bottom-left' );

	// Sticky
	if( ( is_sticky() || $sticky ) && $stp && in_array( $stp, $directions ) )
		$direction = $stp;

	// Normal
	else {
		$key = array_rand( $directions, 1 );
		$direction = $directions[$key];
	}

	return $direction;
}

/**
 * Return all js variables
 *
 * @since Radio Campus Angers 5.0
 * @return array
 */
function campus_script_vars() {

	RCA_CAL()->set_calendar( 'program' );

	$vars = array( 'name' => get_bloginfo( 'name' ),
				   'description' => get_bloginfo( 'description' ),
				   'url' => get_bloginfo( 'url' ),
		    	   'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		    	   'ajaxReferer' => campus_current_page_url(),
		    	   'loader' => '<span class="campus-loader"><span class="campus-loader-animation"><span class="loader-3d"><svg class="icon icon-loader-1" role="img" viewBox="0 0 60 60"><polygon points="0 23.1 60 30 0 0 0 23.1"/></svg><svg class="icon icon-loader-2" role="img" viewBox="0 0 60 60"><polygon points="0 30 0.3 21.1 60 30 0 30"/></svg><svg class="icon icon-loader-3" role="img" viewBox="0 0 60 60"><polygon points="60 30 0.3 38.9 0 30 60 30"/></svg><svg class="icon icon-loader-4" role="img" viewBox="0 0 60 60"><polygon points="0 36.9 0 60 60 30 0 36.9"/></svg></span></span></span>',
		    	   'programsUrl' => '#',
		    	   'playlistUrl' => '#',
		    	   'playerUrl' => false,
		    	   'player' => array(
				   		'live' => isset( $_COOKIE['campus-player-live'] ) ? (bool) $_COOKIE['campus-player-live'] : true,
				   		'podcast_infos' => campus_get_current_podcast_infos(),
				   		'live_infos' => Campus_Daily_Playlist::get_live_results(),
				   		'settings' => array(
				   			'url' => get_option( 'live_url' ),
				   			'button_playpause' => campus_get_svg( array( 'icon' => 'play' ) ) . campus_get_svg( array( 'icon' => 'pause' ) )
				   		)
		    	   ),
// 		    	   'mobile' => (int) is_mobile(),
		    	   'popup' => array(
				   		'width' => 320,
				   		'height' => 400,
				   		'liveHeight' => 480,
				   		'podcastHeight' => 625,
				   		'toolbar' => 0,
				   		'resizable' => 0,
				   		'left' => 90,
				   		'top' => 90
		    	   ),
		    	   'filters' => campus_get_current_filters(),
		    	   'screen' => campus_get_current_screen()
		    );

	if( $programs_page = get_option('programs_page') )
		$vars['programsUrl'] = get_page_link( $programs_page );

	if( $player_page = get_option('player_page') )
		$vars['playerUrl'] = get_page_link( $player_page );

	if( $playlist_page = get_option('playlist_page') )
		$vars['playlistUrl'] = get_page_link( $playlist_page );

	if( !empty($_POST) && isset( $_POST['live'] ) && $_POST['live'] == false && isset( $_POST['post_or_category_id'] ) )
		$vars['player']['current_podcast_id'] = $_POST['post_or_category_id'];

	if( function_exists( 'adrotate_group' ) ) {

		// Popup
		$adrotate_group = adrotate_group( get_option( 'popup_adrotate_group_id' ) );
		if( $adrotate_group && substr( (string) $adrotate_group, 0, 4 ) !== '<!--' ) { // Merci adRotate de nous simplifier la vie !
			$vars['popup']['liveHeight'] += 224;
			$vars['popup']['podcastHeight'] += 224;
		}
	}

	return apply_filters( 'campus_script_vars', $vars );
}

/**
 * Get theme js variable
 *
 * @since Radio Campus Angers 5.3
 * @return mixed
 */
function campus_get_script_var( $option, $param = null ) {
	$vars = campus_script_vars();

	return ! is_null( $param ) ? $vars[$option][$param] : $vars[$option];
}

/**
 * Return current page URL
 *
 * @since Radio Campus Angers 5.0
 * @return url
 */
function campus_current_page_url( $excluded_args = array( 'wp_http_referer' ) ) {

	$pageURL = 'http';

	// Check for https
	if( isset($_SERVER['HTTPS']) ) {
		if ($_SERVER['HTTPS'] == 'on') {
			$pageURL .= "s";
		}
	}

	// Add host
	$pageURL .= '://' . $_SERVER['SERVER_NAME'];

	// Add port
	if ( $_SERVER['SERVER_PORT'] != '80' ) {
		$pageURL .= ':' . $_SERVER['SERVER_PORT'];
	}

	// Add query args
	if( is_array( $excluded_args ) ) {

		parse_str( $_SERVER['QUERY_STRING'], $args );

		foreach( $excluded_args as $excluded_arg )
			if( array_key_exists( $excluded_arg, $args ) )
				unset( $args[$excluded_arg] );

		$pageURL = add_query_arg( $args, $pageURL . $_SERVER['SCRIPT_NAME'] );

	} else {
		$pageURL .= $_SERVER['REQUEST_URI'];
	}

	return $pageURL;
}

/**
 * Get meta links
 *
 * @access public
 * @return string
 */
function campus_get_meta_links() {

	// Get the users array
	$meta_links = apply_filters( 'campus_meta_links', array(
		'search' => array(
			'title'	  	 => 'Recherche',
			'url'  	  	 => '#search-box',
			'classes' 	 => array(
				'box-link'
			)
		),
		'toggle_view' => array(
			'title' 	 => 'Changer de mode d\'affichage',
			'display_on' => array(
				'campus_is_playlist_archive',
				'is_page_template-page-alt-filters.php',
				'is_page_template-page-alt-programs.php',
				'is_home',
				'is_category',
				'is_tag'
			),
			'value_type' => 'button',
			'values' 	 => array(
				'grid' => array(
					'title' => 'Afficher en grille',
					'classes' => array(
						'toggle-view'
					)
				),
				'list' => array(
					'title' => 'Afficher en liste',
					'classes' => array(
						'toggle-view'
					)
				),
				'list-detail' => array(
					'title' => 'Afficher en liste détaillée',
					'classes' => array(
						'toggle-view'
					)
				)
			),
			// 'classes' 	 => array(
			// 	'min-450'
			// )
		),
		'show-sidebar' => array(
			'title' 	 => 'Afficher la bare latérale',
			'display_on' => array(
				'is_active_sidebar-sidebar-' . campus_get_sidebar_id()
			),
			'classes' 	 => array(
				'max-768',
			)
		),
	) );

	// Remove Show sidebar links if there's no sidebar
	if ( ! is_active_sidebar( 'sidebar-' . campus_get_sidebar_id() ) || is_page_template( 'page-alt-daily-playlist.php' ) ) {
		unset( $meta_links['show-sidebar'] );
	}

	$current = campus_get_current_filters();

	$output = '';

	if( empty( $meta_links ) )
		return false;

	foreach( $meta_links as $name => $link ) {

		// Get display_on or false
		$display_on = isset( $link['display_on'] ) ? $link['display_on'] : false;

		// Get screen name or false
		$screen = campus_display_on( $display_on );

		// Display only on selected screen
		if( $display_on && ! $screen )
			continue;

		// Set attrs
		$url = isset( $link['url'] ) ? $link['url'] : '#';
		$rel = isset( $link['rel'] ) ? $link['rel'] : $name;
		$attr = isset( $link['attr'] ) && is_array( $link['attr'] ) ? campus_parse_attr( $link['attr'] ) : '';
		// Set title
		$title = isset( $link['title'] ) ? $link['title'] : '';

		// Set classes
		$classes = isset( $link['classes'] ) ? $link['classes'] : array();
		$classes[] = 'meta-link';
		$classes[] = $name . '-link';

		$link_classes = isset( $link['link_classes'] ) ? $link['link_classes'] : array();
		$link_classes[] = 'meta-button';

		// Create icon
		$icon = sprintf( '<a href="%s" class="%s" title="%s" rel="%s"%s>%s</a>',
			$url,
			join( ' ', $link_classes ),
			strip_tags( $title ),
			$rel,
			$attr,
			( isset( $link['icon_wrap'] ) && $link['icon_wrap'] ) ? '<span class="icon-wrap">' . campus_get_svg( array( 'icon' => $name ) ) . '</span>' : campus_get_svg( array( 'icon' => $name ) )
		);

		if( isset( $link['value_type'] ) && $link['value_type'] == 'button' ) {
			$icon = '';
		}

		// Create values list
		$values_list = $values = '';
		if( ! empty( $link['values'] ) ) {

			foreach( $link['values'] as $label => $value ) {

				// Set value classes
				$value_classes = isset( $value['classes'] ) ? $value['classes'] : array();

				// Add current class
				if( isset( $current[$name] ) && isset( $current[$name][$screen] ) && $current[$name][$screen] == $label )
					$value_classes[] = 'current';

				if( isset( $link['value_type'] ) && $link['value_type'] == 'button' ) {

					// Create icon
					$values .= sprintf( '<li class="%s"><a href="%s" class="%s" title="%s" rel="%s">%s</a></li>',
						join( ' ', array_filter( $value_classes ) ),
						isset( $value['url'] ) ? $value['url'] : '#',
						join( ' ', $link_classes ),
						isset( $value['title'] ) ? strip_tags( $value['title'] ) : '',
						isset( $value['rel'] ) ? strip_tags( $value['rel'] ) : $label,
						( isset( $link['icon_wrap'] ) && $link['icon_wrap'] ) ? '<span class="icon-wrap">' . campus_get_svg( array( 'icon' => $label ) ) . '</span>' : campus_get_svg( array( 'icon' => $label ) )
					);

				} else {

					// Create value
					$values .= sprintf( '<li class="%s"><a href="%s" title="%s">%s</a></li>',
						join( ' ', array_filter( $value_classes ) ),
						isset( $value['url'] ) ? $value['url'] : $label,
						isset( $value['title'] ) ? strip_tags( $value['title'] ) : '',
						$value['title']
					);
				}
			}

			$values_list = sprintf( '<ul class="meta-list">%s</ul>',
				$values
			);
		}

		$output .= sprintf( '<div class="%1$s">%2$s%3$s</div>',
			// Set classes
			join( ' ', array_filter( $classes ) ),

			// Set icon
			$icon,

			// Set values list
			$values_list
		);
	}

	return sprintf( '<aside id="site-meta-links" class="site-meta-links meta-links">%s</aside>', $output );
}

/**
 * Display meta links
 *
 * @access public
 * @echo string
 */
function campus_the_meta_links() {
	echo campus_get_meta_links();
}

/**
 * Get current screen name
 *
 * @return str - 1st part is a function name / 2nd part is a slug (optional)
 */
function campus_get_current_screen() {
	global $wp_query;

	$slug = '';
	$queried_object = get_queried_object();

	// Get term slug
	if( isset( $queried_object->slug ) )
		$slug = $queried_object->slug;

	// Get post slug (name)
	elseif( isset( $queried_object->post_name ) )
		$slug = $queried_object->post_name;

	$keys = preg_grep( '/^is_/', array_keys( (array) $wp_query ) );

	$screens = array();

	foreach ( $keys as $function ) {
		if( $wp_query->$function ) {
			$screens[] = $function;

			if( $slug )
				$screens[] = $function . '-' . $slug;
		}
	}

	// Page template functions
	if( $template = get_page_template_slug() ) {
		$screens[] = 'is_page_template-' . $template;
	}

	// Custom screen functions
	if( campus_get_sidebar_id() ) {
		$screens[] = 'is_active_sidebar';
		$screens[] = 'is_active_sidebar-sidebar-' . campus_get_sidebar_id();
	}

	// Custom screen functions
	if( campus_is_playlist_archive() )
		$screens[] = 'campus_is_playlist_archive';

	return $screens;
}

/**
 * Get current state of meta filters
 *
 * @return array
 */
function campus_get_current_filters() {

	// Get default current state
	$current = (array) apply_filters( 'campus_default_current_filters', array(
		'toggle_view' => array(
			'campus_is_playlist_archive' => 'list-detail',
			'is_page_template-page-alt-filters.php' => 'list',
			'is_page_template-page-alt-programs.php' => 'grid',
			'is_home' => 'grid',
			'is_category' => 'list',
			'is_tag' => 'list'
		)
	) );

	// If there is a cookie
	if( ! empty( $_COOKIE['campus-filters'] ) ) {

		$selected = json_decode( stripslashes( $_COOKIE['campus-filters'] ), true );

		if( ! empty( $selected ) )
			$current = array_replace_recursive( $current, (array) $selected );
	}

	return $current;
}

/**
 * Filter meta link display
 * Check args and convert it to function
 *
 * @return screen name or false
 */
function campus_display_on( $screens ) {

	// Because $screen args can be empty
	if( ! $screens )
		return false;

	if( ! is_array( $screens ) )
		$screens = array( $screens );

	$display = array();

	foreach( $screens as $screen ) {
		$sep_pos = strpos( $screen, '-' );
		if( $sep_pos ) {
			$function = substr( $screen, 0, $sep_pos );
			$arg = substr( $screen, $sep_pos + 1 );
		} else {
			$function = $screen;
			$arg = null;
		}

		if( call_user_func( $function, $arg ) )
			return $screen;
	}

	return false;
}

/**
 * Add nonbreaking space before : ? !
 *
 */
function campus_add_nonbreaking_space_to_string( $string ) {

	$tags = array(' :', ' ?', ' !', ' ;', ' €', ' %');
	$replacement = array('&nbsp;:', '&nbsp;?', '&nbsp;!', '&nbsp;;', '&nbsp;€', '&nbsp;%');

	return str_ireplace( $tags, $replacement, $string );
}

/**
 * Add searchform at the end of the page
 * Add site-loader wrap
 *
 */
function campus_wp_footer() {
	get_template_part( 'searchbox' );
	get_template_part( 'site-loader' );
}
add_action( 'wp_footer', 'campus_wp_footer' );

/**
 * Add current category to widget post
 */
function campus_widget_posts_args( $args, $instance ) {

	if( is_single() ) {
		$args['post__not_in'] = array( get_the_ID() );

		$category = campus_get_the_category_by_priority();

		if( $category )
			$args['cat'] = $category->term_id;
	}

	return $args;
}
add_filter( 'widget_posts_args', 'campus_widget_posts_args', 10, 2 );

/**
 * Playlist shortcode
 *
 */
function campus_post_playlist_shortcode( $atts ) {
     extract( shortcode_atts( array(
	      'embed' => true,
     ), $atts ) );

	global $post;

	if( ! is_object($post) )
		return false;

	$playlist = get_post_meta( $post->ID, '_playlist', true );

	if( ! $playlist )
		return false;

	$output = '';

	$classes = array(
		'entry-playlist',
		'entry-section'
	);

	if( $embed )
		$classes[] = 'embed';

	$items = array();

	foreach( $playlist as $fields ) {

		$item = '';

		foreach( $fields as $name => $field ) {
			if( $name == 'link' ) continue;

			if( $name == 'title' && $field != '' && $fields['artist'] != '' )
				$sep = ' > ';
			elseif( $name == 'artist' && $fields['other'] != '' )
				$sep = "<br>\n";
			else
				$sep = '';
			$item .= "<span class='field-$name'>$field$sep</span>";
		}

		// Embed with link if there's one
		if( $fields['link'] ) {
			$item = sprintf( "<a href='%s' target='_blank'>%s %s</a>",
				$fields['link'],
				campus_get_icon_from_url( $fields['link'], 'icon-inline icon-small' ),
				$item
			);
		}

		// Add to items
		$items[] = "<p class='playlist-item'>$item<span class='screen-reader-text'> / </span></p>\n";
	}

	if( $items ) {

		$output = sprintf( "<div class='%s'>\n<div class='with-icon'>%s\n<h2 class='screen-reader-text'>%s</h2>\n<div class='icon-title'>%s</div>\n</div>\n</div>\n",
			join( ' ', $classes ),
			campus_get_svg( array( 'icon' => 'playlist', 'class' => 'icon-small' ) ),
			'Playlist : ',
			join( "\n", $items )
		);
	}
	return $output;
}
add_shortcode( 'playlist', 'campus_post_playlist_shortcode' );

/**
 * If user did'nt add playlist to the content, add it to the end
 *
 */
function campus_add_playlist_to_post( $content ) {
	if ( has_shortcode( $content, 'playlist' ) )
		return $content;

	$content .= do_shortcode( '[playlist embed="0"]' );

	return $content;
}

add_filter( 'the_content', 'campus_add_playlist_to_post' );

/**
 * Add share links to post
 *
 */
function campus_social_buttons() {
	if ( function_exists( 'sharing_display' ) ) {
	    sharing_display( '', true );
	}

	if ( class_exists( 'Jetpack_Likes' ) ) {
	    $custom_likes = new Jetpack_Likes;
	    echo $custom_likes->post_likes( '' );
	}
}

/**
 * Remove share links to content
 *
 */
function campus_remove_share() {
    remove_filter( 'the_content', 'sharing_display',19 );
    remove_filter( 'the_excerpt', 'sharing_display',19 );

    if ( class_exists( 'Jetpack_Likes' ) ) {
        remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
    }
}
add_action( 'loop_start', 'campus_remove_share' );

/**
 * Display to top button
 */
function campus_this_is_the_end( $content = '', $this_is_the_end = true ) {
	echo campus_get_content_infos( $content, $this_is_the_end );
}

/**
 * Return to content infos
 */
function campus_get_content_infos( $message = '', $this_is_the_end = true ) {

	$classes = array( 'content-infos' );
	$content = '';
	$message = apply_filters( 'campus_content_infos_message', $message, $this_is_the_end );

	if( $this_is_the_end ) {
		$classes[] = 'this-is-the-end';
		$content .= '<div class="content-up"><a href="#content" rel="top">' . campus_get_svg( array( 'icon' => 'arrow-up' ) ) . '<span class="icon-title"></span></a></div>';
	}

	if( $message !== '' ) {
		$classes[] = 'with-message';
		$content .= '<div class="content-message">' . $message . '</div>';
	}

	return sprintf( '<div class="%s">%s</div>',
	 	join( ' ', $classes ),
		$content
	);
}

/**
 * Define current season date range
 *
 * @since Radio Campus Angers 6.0.1
 * @return void
 */
function campus_get_current_season_range() {

	if( ! defined( 'CAMPUS_SEASON_START' ) && ! defined( 'CAMPUS_SEASON_END' ) ) {

		$current_month = (int) date( 'n' );
		$season_start_month = 9;
		$season_end_month = $season_start_month === 1 ? 12 : $season_start_month - 1;

		$year_begin = date( 'Y' );
		$year_end = $year_begin + 1;

		if( $current_month < $season_start_month ) {
			$year_end = $year_begin;
			$year_begin = $year_begin - 1;
		}

		define( 'CAMPUS_SEASON_START', strtotime( $year_begin . '-' . zeroise( $season_start_month, 2 ) . '-01' ) );
		define( 'CAMPUS_SEASON_END', strtotime( $year_end . '-' . zeroise( $season_end_month, 2 ) . '-' . cal_days_in_month( CAL_GREGORIAN, $season_end_month, $year_end ) ) );
	}
}
add_action( 'init', 'campus_get_current_season_range' );

/**
 * Is current date in current season
 *
 * @since Radio Campus Angers 6.0.1
 * @return bool
 */
function campus_in_current_season( $date ) {

	$timestamp = strtotime( $date );

	if( $timestamp >= CAMPUS_SEASON_START && $timestamp <= CAMPUS_SEASON_END ) {
		return true;
	}

	return false;
}
