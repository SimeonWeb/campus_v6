<?php
/**
 * Additional features to allow styling of the templates
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

function prepare_meta( $meta, $name, $is_array = false ) {
	if( $is_array ) {
		return isset( $meta[$name] ) && $meta[$name][0] ? unserialize( $meta[$name][0] ) : [];
	}

	return isset( $meta[$name] ) ? $meta[$name][0] : '';
}

/**
 * Define sidebar fields and value
 *
 */
function campus_term_sidebar_fields( $term_id, $taxonomy = 'category' ) {

	if ( $term_id instanceof WP_Term ) {
		$term = $term_id;
		$term_id = $term->term_id;
		$taxonomy = $term->taxonomy;

	} else {
	
		$term = get_term( $term_id, $taxonomy );
	}

	$meta = get_term_meta( $term_id );

	$menus = wp_get_nav_menus();
	$menu_options = [
		'' => 'Choisir un menu'
	];

	foreach( $menus as $menu ) {
		$menu_options[$menu->term_id] = $menu->name;
	}

	$fields = [
		'sidebar_menu' => [
			'type'  	    => 'select',
			'title' 	    => 'Menu',
			'description' => 'Choisissez un menu à afficher dans la barre latérale de la catégorie',
			'class'		    => 'widefat',
			'value' 	    => prepare_meta( $meta, 'sidebar_menu' ),
			'options'     => $menu_options
		]
	];

	return $fields;
}

/**
 * Define default social links fields and value
 *
 */
function campus_term_social_links_fields( $term_id, $taxonomy = 'category' ) {

	if( $term_id instanceof WP_Term ) {
		$term = $term_id;
		$term_id = $term->term_id;
		$taxonomy = $term->taxonomy;

	} else {
	
		$term = get_term( $term_id, $taxonomy );
	}

	$meta = get_term_meta( $term_id );

	$external = prepare_meta( $meta, 'external', true );

	$fields = array(
		'facebook'	 => array(
			'type'  	  => 'url',
			'title' 	  => 'Facebook',
			'description' => 'L\'url de votre page Facebook',
			'class'		  => 'widefat',
			'value' 	  => prepare_meta( $meta, 'facebook' )
		),
		'twitter'	 => array(
			'type'  	  => 'url',
			'title' 	  => 'Twitter',
			'description' => 'L\'url de votre compte Twitter',
			'class'		  => 'widefat',
			'value' 	  => prepare_meta( $meta, 'twitter' )
		),
		// 'google'	 => array(
		// 	'type'  	  => 'url',
		// 	'title' 	  => 'Google+',
		// 	'description' => 'L\'url de votre page Google+',
		// 	'class'		  => 'widefat',
		// 	'value' 	  => prepare_meta( $meta, 'google' )
		// ),
		'youtube'	 => array(
			'type'  	  => 'url',
			'title' 	  => 'YouTube',
			'description' => 'L\'url de votre chaine YouTube',
			'class'		  => 'widefat',
			'value' 	  => prepare_meta( $meta, 'youtube' )
		),
		'instagram'	 => array(
			'type'  	  => 'url',
			'title' 	  => 'Instagram',
			'description' => 'L\'url de votre compte Instagram',
			'class'		  => 'widefat',
			'value' 	  => prepare_meta( $meta, 'instagram' )
		),
		'soundcloud' => array(
			'type'  	  => 'url',
			'title' 	  => 'SoundCloud',
			'description' => 'L\'url de votre profil SoundCloud',
			'class'		  => 'widefat',
			'value' 	  => prepare_meta( $meta, 'soundcloud' )
		),
		'itunes' 	 => array(
			'type'  	  => 'url',
			'title' 	  => 'Apple Podcasts',
			'description' => 'L\'url de vos podcasts iTunes, <a href="https://linkmaker.itunes.apple.com/fr-fr?country=fr&mediaType=podcasts' . ( $term && ! is_wp_error( $term ) ? '&term=' . str_replace( ' ', '+', $term->name ) : '' ) . '" target="_blank">Obtenir le lien</a>',
			'class'		  => 'widefat',
			'icon'		  => 'apple-podcast',
			'value' 	  => prepare_meta( $meta, 'itunes' )
		),
		'linkedin' => array(
			'type'  	  => 'url',
			'title' 	  => 'Linked In',
			'description' => 'L\'url de votre profil Linked In',
			'class'		  => 'widefat',
			'value' 	  => prepare_meta( $meta, 'linkedin' )
		),
		// 'rss' => array(
		// 	'type'  	  => 'url',
		// 	'title' 	  => 'RSS',
		// 	'description' => 'L\'url de flux rss de la catégorie (non modifiable)',
		// 	'class'		  => 'widefat',
		// 	'readonly'	=> true,
		// 	'attr'			=> array(
		// 		'readonly' => true
		// 	),
		// 	'value' 	  => get_term_feed_link( $term_id, $taxonomy ),
		// ),
		'external'	 => array(
			'type'  	  => 'composed',
			'title' 	  => 'Lien externe',
			'description' => 'Un autre site en lien avec la catégorie',
			'inputs'	  => array(
				'external[url]' => array(
					'type'  	=> 'url',
					'title'		=> 'URL',
					'class'		=> 'widefat',
					'value'		=> isset( $external['url'] ) ? $external['url'] : ''
				),
				'external[title]' => array(
					'type'  	=> 'text',
					'title'		=> 'Titre',
					'class'		=> 'widefat',
					'value'		=> isset( $external['title'] ) ? $external['title'] : ''
				)
			),
			'value' 		=> $external
		)
	);

	return $fields;
}

/**
 * Get taxonomy terms instead of taxonomy posts
 */
function campus_term_pre_get_posts( $query ) {

	if( in_array( get_queried_object_id(), campus_get_shows_category_ids() ) && $query->is_main_query() ) {

		$query->set( 'taxonomy', 'category' );

	}
	print_r( $query );
}
//add_action( 'pre_get_posts', 'campus_term_pre_get_posts' );

/**
 * Get array of current category ancestors
 *
 * @return array of term object or str with term_id at key
 */
function campus_get_term_parents( $term_id = null, $taxonomy = 'category', $key = 'slug' ) {

	if( ! is_null( $term_id ) )
		$term_id = $term_id;

	if( is_null( $term_id ) || is_null( $taxonomy ) ) {
		$queried_object = get_queried_object();
		if( isset( $queried_object->taxonomy ) ) {
			$term_id  = $queried_object->term_id;
			$taxonomy = $queried_object->taxonomy;
		}
	}

	$all_parent_categories = array();

	$parent_categories = get_ancestors( $term_id, $taxonomy, 'taxonomy' );

	if( $parent_categories ) {

		foreach( $parent_categories as $parent_category_id ) {

			$parent_category = get_term( $parent_category_id, $taxonomy );

			if( ! array_key_exists( $parent_category_id, $all_parent_categories ) )
				$all_parent_categories[$parent_category_id] = $key && isset( $parent_category->$key ) ? $parent_category->$key : $parent_category;
		}
	}

	return $all_parent_categories;
}

/**
 * Sort post categories by priority
 *
 * @return array of term objects
 */
function campus_get_the_category_by_priority( $post_id = null, $parent_id = null ) {

	if( is_null( $post_id ) )
		$id = get_the_ID();

	$categories = get_the_terms( get_the_ID(), 'category' );
	$default_parent_id = get_option( 'category_by_priority' );

	if( count( $categories ) == 0 )
		return false;

	if( is_null( $parent_id ) && $default_parent_id ) {
		$parent_id = (int) $default_parent_id;
	}

	if( is_null( $parent_id ) || count( $categories ) == 1 )
		return $categories[0];

	if( ! is_int( $parent_id ) ) {
		$parent = get_category_by_slug( sanitize_title( $parent_id ) );
		$parent_id = $parent->term_id;
	}

	foreach( $categories as $key => $category ) {

		if( array_key_exists( $parent_id, (array) campus_get_post_category_parents() ) && $key != 0 ) {

			$first = array_splice( $categories, $key + 1, 1 );
			array_splice( $categories, 1, 0, $first );

		} else
			continue;
	}

	return $categories[0];
}

/**
 * Return category slug and all ancestors slug classes
 *
 */
function campus_get_all_term_classes( $term_id = null, $taxonomy = 'category', $array = false ) {

	if( is_null( $term_id ) || is_null( $taxonomy ) ) {
		$queried_object = get_queried_object();
		if( isset( $queried_object->taxonomy ) ) {
			$term_id  = $queried_object->term_id;
			$taxonomy = $queried_object->taxonomy;
		}
	}

	$classes = array();

	$terms = campus_get_term_parents( $term_id, $taxonomy );
	$current_term = get_term( $term_id, $taxonomy );

	if( $current_term && ! is_wp_error( $current_term ) ) {
		$classes[] = $taxonomy . '-' . $current_term->slug;
	}

	if( $terms ) {
		foreach( $terms as $term_slug ) {
			$classes[] = $taxonomy . '-' . $term_slug;
		}
	}

	if( $array )
		return $classes;
	else
		return join( ' ', $classes );
}

/**
 * Get all post category ancestors
 *
 * @return array of term objects with term_id as key
 */
function campus_get_post_category_parents() {

	$all_parent_categories = array();

	$post_categories = get_the_terms( get_the_ID(), 'category' );

	if( $post_categories ) {

		foreach( $post_categories as $post_category ) {
			$parent_categories = get_ancestors( $post_category->term_id, 'category', 'taxonomy' );

			if( $parent_categories ) {

				foreach( $parent_categories as $parent_category_id ) {

					$parent_category = get_term( $parent_category_id, 'category' );

					if( ! array_key_exists( $parent_category_id, $all_parent_categories ) )
						$all_parent_categories[$parent_category_id] = $parent_category;
				}
			}
		}
	}

	if( ! empty( $all_parent_categories ) )
		return $all_parent_categories;

	return false;
}


/**
 * Get category parent name
 *
 * For show categories, return show parent
 * else return the last parent
 *
 * @access public
 * @return string
 */
function campus_get_term_parent_name( $term_id, $taxonomy = 'category', $singular_name = true ) {

	if( ! is_taxonomy_hierarchical( $taxonomy ) )
		return false;

	$parents = campus_get_shows_category_ids();

	$term = get_term( $term_id, $taxonomy );
	$parent_id = $term->parent;

	if( in_array( $term_id, $parents ) || $parent_id == 0 )  {

		$singular_name_option = get_term_meta( $term_id, 'singular_name', true );
		if( $singular_name && $singular_name_option )
		    return $singular_name_option;
	    else
		    return $term->name;
	} else {
		return campus_get_term_parent_name( $parent_id, $taxonomy, $singular_name );
	}
}


/**
 * Get the post category with ancestor at prefix
 *
 * @access public
 * @return string
 */
function campus_get_post_category( $id = null, $link = false, $depth = 3 ) {

	if( is_null( $id ) )
		$post_id = get_the_ID();

	$post_category = campus_get_the_category_by_priority();

	if( ! $post_category )
		return false;

	$output = '';

	if( $post_category->parent > 0 ) {
		$output = sprintf( '<span class="category-hierarchical-ancestor">%s </span>',
			campus_get_term_parent_name( $post_category->term_id )
		);
	}

	if( $link ) {

		$output .= sprintf( '<a href="%s" class="category-hierarchical">%s<span class="text">%s</span></a>',
			get_category_link( $category->term_id ),
			campus_get_svg( array( 'icon' => $post_category->slug ) ),
			$post_category->name
		);
	} else {

		$output .= sprintf( '<span class="category-hierarchical">%s<span class="text">%s</span></span>',
			campus_get_svg( array( 'icon' => $post_category->slug ) ),
			$post_category->name
		);
	}

	return $output;
}

function campus_the_post_category( $id = null, $link = false, $parent = 3, $first = true, $sep = ', ' ){
	echo campus_get_post_category( $id, $link, $parent, $first, $sep );
}

/**
 * Get the category image element
 *
 * @access public
 * @return string
 */
function campus_get_category_thumbnail( $args = array() ) {
	extract( $args );

	// Inside the loop
	if( isset( $post_id ) ) {
		if( $post_term = campus_get_the_category_by_priority( $post_id ) )
			$term_id = $post_term->term_id;
			$taxonomy = $post_term->taxonomy;
	}

	if( ! isset( $term_id ) )
		$term_id = null;

	if( ! isset( $taxonomy ) )
		$taxonomy = null;

	if( is_null( $term_id ) || is_null( $taxonomy ) ) {
		$queried_object = get_queried_object();
		if( isset( $queried_object->taxonomy ) ) {
			$term_id  = $queried_object->term_id;
			$taxonomy = $queried_object->taxonomy;
		}
	}

	// Get the term thumbnail
	$image = $type = '';
	$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );

	$classes = array( 'wp-category-image' );

	if( isset( $class ) )
		$classes[] = $class;

	// Default is a square
	$size = isset( $size ) && is_array( $size ) ? $size : array( 600, 600 );
	$unit = isset( $unit ) ? $unit : 'px';

	// Set classes
	if( $term_id && $taxonomy ) {
		$classes = array_merge( $classes, campus_get_all_term_classes( $term_id, $taxonomy, true ) );
	}

	// Set thumbnail
	// If term has a thumbnail
	if( $thumbnail_id ) {
	    $image = wp_get_attachment_image_src( $thumbnail_id, $size );
	    $image = $image[0];
	    $type = 'image';

	// If term is a program
	} else if( in_array( 'category-emission', $classes ) ) {
	    $image = 'program';
	    $type = 'icon';

	// Default image
	} else {
	    $image = 'default';
	    $type = 'icon';
	    $classes[] = 'category-default';
	}

	if( isset( $array ) && $array )
		return array( 'image' => $image, 'type' => $type, 'class' => join( ' ', $classes ), 'classes' => $classes );
	else if( $type == 'icon' )
		return campus_get_svg( array( 'icon' => $image, 'class' => join( ' ', $classes ) ) );
	else
		return sprintf( '<img src="%s" class="%s" />', $image, join( ' ', $classes ) );


}

/**
 * Display the category image element
 *
 * @access public
 * @echo string
 */
function campus_the_category_thumbnail( $args = array() ) {
	echo campus_get_category_thumbnail( $args );
}


function campus_get_shows_category_ids() {

	return array(
		'talk'  => get_option( 'category_talk_id' ),
		'music' => get_option( 'category_music_id' ),
		'other' => get_option( 'category_other_id' )
	);
}


function campus_get_term_colors() {

	$category_talk_id = get_option( 'category_talk_id' );
	$category_music_id = get_option( 'category_music_id' );
	$category_other_id = get_option( 'category_other_id' );

	$category_talk_color = get_option( 'category_talk_color', '#333333' );
	$category_music_color = get_option( 'category_music_color', '#333333' );
	$category_other_color = get_option( 'category_other_color', '#333333' );

	$colors = array();

	if( $category_talk_id && $category_talk_color )
		$colors[$category_talk_id] = $category_talk_color;

	if( $category_music_id && $category_music_color )
		$colors[$category_music_id] = $category_music_color;

	if( $category_other_id && $category_other_color )
		$colors[$category_other_id] = $category_other_color;

	return $colors;
}

function campus_get_term_thumbnail_mask_urls() {

	$category_talk_id = get_option( 'category_talk_id' );
	$category_music_id = get_option( 'category_music_id' );
	$category_other_id = get_option( 'category_other_id' );

	$category_talk_thumbnail_mask = get_option( 'category_talk_thumbnail_mask' );
	$category_music_thumbnail_mask = get_option( 'category_music_thumbnail_mask' );
	$category_other_thumbnail_mask = get_option( 'category_other_thumbnail_mask' );

	$masks = array();

	if( $category_talk_id && $category_talk_thumbnail_mask )
		$masks[$category_talk_id] = wp_get_attachment_image_url( $category_talk_thumbnail_mask, 'full' );

	if( $category_music_id && $category_music_thumbnail_mask )
		$masks[$category_music_id] = wp_get_attachment_image_url( $category_music_thumbnail_mask, 'full' );

	if( $category_other_id && $category_other_thumbnail_mask )
		$masks[$category_other_id] = wp_get_attachment_image_url( $category_other_thumbnail_mask, 'full' );

	return $masks;
}

/**
 * Get the category users
 *
 * @access public
 * @return string
 */
function campus_get_term_users( $term_id = null, $sep = ', ' ) {

	if( ( is_null( $term_id ) ) && is_archive() )
		$term_id = get_queried_object_id();

	// Get the users array
	$users = get_term_meta( $term_id, 'users', true );

	if( ! $users )
		return false;

	$users_o = get_users( array( 'include' => $users, 'orderby' => 'display_name' ) );

	$output = array();

	foreach( $users_o as $user ) {
		//$output[] = '<a href="'.get_author_posts_url($user->ID).'">'.$user->display_name.'</a>';
		$output[] = $user->display_name;
	}

	return join( $sep, $output );
}

/**
 * Display the category users
 *
 */
function campus_the_term_users( $before = '', $after = '', $sep = ' / ' ) {
	$users = campus_get_term_users( null, $sep );

	if( $users )
		echo $before . $users . $after;
}

/**
 * Get the term meta
 *
 */
function campus_get_the_term_meta( $key, $before = '', $after = '', $sep = ' / ', $term_id = null ) {

	if( is_null( $term_id ) )
		$term_id = get_queried_object_id();

	$meta = get_term_meta( $term_id, $key, true );

	if( $key == 'singular_name' && ! $meta )
		return get_queried_object()->name;

	if( ! $meta )
		return false;

	if( is_array( $meta ) ) {
		if( $sep )
			$meta = join( $sep, $meta );
		else
			return $meta;
	}
	return $before . $meta . $after;
}

/**
 * Display the term meta
 *
 */
function campus_the_term_meta( $key, $before = '', $after = '', $sep = ' / ' ) {
	echo campus_get_the_term_meta( $key, $before, $after, $sep );
}

/**
 * Return the broadcast schedules
 *
 */
function campus_get_broadcast_schedules_inline( $term_id ) {
	return campus_get_the_term_meta( 'day', '', '', '', $term_id ) . ' / ' . campus_get_the_term_meta( 'hours', '', '', ' > ', $term_id );
}

/**
 * Get term social links
 *
 */
function campus_get_term_social_links( $term_id = null, $taxonomy = null ) {

	if( $term_id instanceof WP_Term ) {
		$term = $term_id;
		$term_id = $term->term_id;
		$taxonomy = $term->taxonomy;

	} else {
	
		if( is_null( $term_id ) || is_null( $taxonomy ) ) {
			$queried_object = get_queried_object();
			if( isset( $queried_object->taxonomy ) ) {
				$term_id  = $queried_object->term_id;
				$taxonomy = $queried_object->taxonomy;
			}
		}
		
		$term = get_term( $term_id, $taxonomy );
	}

	if( ! $term )
		return false;

	$links = campus_term_social_links_fields( $term );

	if( ! is_archive() && $term->count ) {
		$links['archive'] = array(
			'title' => 'Tous les articles',
			'value' => get_term_link( $term )
		);
	}

	$links_list = '';

	foreach( $links as $name => $link ) {

		if( ! $link['value'] || $name === 'rss' && $taxonomy !== 'category' || $name === 'rss' && ! have_posts() )
			continue;

		if( is_array( $link['value'] ) ) {
			$title = ! empty( $link['value']['title'] ) ? $link['value']['title'] : 'les internets';
			$url = $link['value']['url'];
		} else {
			$title = $link['title'];
			$url = $link['value'];
		}

		$target = '_self';

		if( $name === 'rss' ) {
			$title = sprintf( 'Flux RSS de %s', $term->name );
			$target = '_blank';
		} else if( $name != 'archive' ) {
			$title = sprintf( '%s sur %s', $term->name, $title );
			$target = '_blank';
		}

		$icon = isset( $link['icon'] ) ? $link['icon'] : $name;

		$links_list .= sprintf( '<li class="%s-link social-link"><a href="%s" class="meta-button" title="%s" target="%s">%s</a></li>',
			$name,
			$url,
			$title,
			$target,
			campus_get_svg( array( 'icon' => $icon ) )
		);
	}

	if( $links_list )
		return '<div class="meta-link"><ul class="meta-list">' . $links_list . '</ul></div>';
}

/**
 * Display term social links
 *
 */
function campus_the_term_social_links( $term_id = null ) {
	echo campus_get_term_social_links( $term_id );
}
