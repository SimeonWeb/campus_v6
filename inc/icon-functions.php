<?php
/**
 * SVG icons related functions and filters
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

/**
 * Add SVG definitions to the footer.
 */
function campus_include_svg_icons() {

	$assets = (array) apply_filters( 'campus_include_svg_icons', array( 'svg-icons-campus', 'svg-icons-social' ) );

	if( ! empty( $assets ) ) {

		foreach( $assets as $asset ) {

			// Define SVG sprite file.
			$svg = get_parent_theme_file_path( '/assets/images/' . $asset . '.svg' );

			// If it exists, include it.
			if ( file_exists( $svg ) ) {
				require_once( $svg );
			}
		}
	}
}
add_action( 'wp_footer', 'campus_include_svg_icons', 9999 );
add_action( 'admin_footer', 'campus_include_svg_icons', 9999 );

/**
 * Return SVG markup.
 *
 * @param array $args {
 *     Parameters needed to display an SVG.
 *
 *     @type string $icon  Required SVG icon filename.
 *     @type string $title Optional SVG title.
 *     @type string $desc  Optional SVG description.
 * }
 * @return string SVG markup.
 */
function campus_get_svg( $args = array() ) {
	// Make sure $args are an array.
	if ( empty( $args ) ) {
		return __( 'Please define default parameters in the form of an array.', 'campus' );
	}

	// Define an icon.
	if ( false === array_key_exists( 'icon', $args ) ) {
		return __( 'Please define an SVG icon filename.', 'campus' );
	}

	// Set defaults.
	$defaults = array(
		'icon'        => '',
		'id'		  => '',
		'title'       => '',
		'desc'        => '',
		'class'		  => '',
		'style'		  => '',
		'fallback'    => false,
	);

	// Parse args.
	$args = wp_parse_args( $args, $defaults );

	// Set aria hidden.
	$aria_hidden = ' aria-hidden="true"';

	// Set ARIA.
	$aria_labelledby = '';

	/*
	 * Radio Campus Angers doesn't use the SVG title or description attributes; non-decorative icons are described with .screen-reader-text.
	 *
	 * However, child themes can use the title and description to add information to non-decorative SVG icons to improve accessibility.
	 *
	 * Example 1 with title: <?php echo campus_get_svg( array( 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ) ) ); ?>
	 *
	 * Example 2 with title and description: <?php echo campus_get_svg( array( 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ), 'desc' => __( 'This is the description', 'textdomain' ) ) ); ?>
	 *
	 * See https://www.paciellogroup.com/blog/2013/12/using-aria-enhance-svg-accessibility/.
	 */
	if ( $args['title'] ) {
		$aria_hidden     = '';
		$unique_id       = uniqid();
		$aria_labelledby = ' aria-labelledby="title-' . $unique_id . '"';

		if ( $args['desc'] ) {
			$aria_labelledby = ' aria-labelledby="title-' . $unique_id . ' desc-' . $unique_id . '"';
		}
	}

	/**
	 * Set css classes
	 *
	 */
	$classes = array( 'icon', 'icon-' . esc_attr( $args['icon'] ) );

	if ( $args['class'] ) {
		$classes[] = $args['class'];
	}

	// Set id.
	$id = '';
	if ( $args['id'] ) {
		$id = ' id="' . $args['id'] . '"';
	}

	// Set css style.
	$style = '';
	if ( $args['style'] ) {
		$style = ' style="' . $args['style'] . '"';
	}

	// Begin SVG markup.
	$svg = '<svg' . $id . $style . ' class="' . join( ' ', $classes ) . '"' . $aria_hidden . $aria_labelledby . ' role="img">';

	// Display the title.
	if ( $args['title'] ) {
		$svg .= '<title id="title-' . $unique_id . '">' . esc_html( $args['title'] ) . '</title>';

		// Display the desc only if the title is already set.
		if ( $args['desc'] ) {
			$svg .= '<desc id="desc-' . $unique_id . '">' . esc_html( $args['desc'] ) . '</desc>';
		}
	}

	/*
	 * Display the icon.
	 *
	 * The whitespace around `<use>` is intentional - it is a work around to a keyboard navigation bug in Safari 10.
	 *
	 * See https://core.trac.wordpress.org/ticket/38387.
	 */
	$svg .= ' <use href="#icon-' . esc_html( $args['icon'] ) . '" xlink:href="#icon-' . esc_html( $args['icon'] ) . '"></use> ';

	// Add some markup to use as a fallback for browsers that do not support SVGs.
	if ( $args['fallback'] ) {
		$svg .= '<span class="svg-fallback icon-' . esc_attr( $args['icon'] ) . '"></span>';
	}

	$svg .= '</svg>';

	return $svg;
}


/**
 * Get icon from url
 *
 * @return html
 */
function campus_get_icon_from_url( $url, $class = '' ) {

	// Get supported social icons.
	$social_icons = campus_social_links_icons();
	$icon = 'external';

	foreach ( $social_icons as $attr => $value ) {
		if ( false !== strpos( $url, $attr ) ) {
			$icon = $value;
			break;
		}
	}

	return campus_get_svg( array( 'icon' => esc_attr( $icon ), 'class' => $class ) );
}


/**
 * Display SVG icons in social links menu.
 *
 * @param  string  $item_output The menu item output.
 * @param  WP_Post $item        Menu item object.
 * @param  int     $depth       Depth of the menu.
 * @param  array   $args        wp_nav_menu() arguments.
 * @return string  $item_output The menu item output with social icon.
 */
function campus_nav_menu_social_icons( $item_output, $item, $depth, $args ) {
	// Get supported social icons.
	$social_icons = campus_social_links_icons();

	// Change SVG icon inside social links menu if there is supported URL.
	if ( 'social' === $args->theme_location ) {
		foreach ( $social_icons as $attr => $value ) {
			if ( false !== strpos( $item_output, $attr ) ) {
				$item_output = str_replace( $args->link_after, '</span>' . campus_get_svg( array( 'icon' => esc_attr( $value ) ) ), $item_output );
			} else if( in_array( 'toggle-submenu', $item->classes ) ) {
				if( in_array( $attr, $item->classes ) )
					$item_output = str_replace( $args->link_after, '</span>' . campus_get_svg( array( 'icon' => esc_attr( $value ) ) ), $item_output );
				else
					$item_output = str_replace( $args->link_after, '</span>' . campus_get_svg( array( 'icon' => 'arrow-down' ) ), $item_output );
			}
		}
	}

	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'campus_nav_menu_social_icons', 10, 4 );


/**
 * Add social classes on menu items in social links menu.
 *
 * @param  array   $classes 	The menu item classes.
 * @param  WP_Post $item        Menu item object.
 * @param  array   $args        wp_nav_menu() arguments.
 * @param  int     $depth       Depth of the menu.
 */
function campus_nav_menu_css_class_social_icons( $classes, $item, $args, $depth = null ) {

	// Get supported social icons.
	$social_icons = campus_social_links_icons();

	if ( 'social' === $args->theme_location ) {
		foreach ( $social_icons as $attr => $value ) {
			if ( false !== strpos( $item->url, $attr ) ) {
				$classes[] = 'social-link ' . $value . '-link ' . $attr . '-link';
			}
		}
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'campus_nav_menu_css_class_social_icons', 10, 4 );

/**
 * Add dropdown icon if menu item has children.
 *
 * @param  string $title The menu item's title.
 * @param  object $item  The current menu item.
 * @param  array  $args  An array of wp_nav_menu() arguments.
 * @param  int    $depth Depth of menu item. Used for padding.
 * @return string $title The menu item's title with dropdown icon.
 */
function campus_dropdown_icon_to_menu_link( $title, $item, $args, $depth ) {
	if ( 'top' === $args->theme_location ) {
		foreach ( $item->classes as $value ) {
			if ( 'menu-item-has-children' === $value || 'page_item_has_children' === $value ) {
				$title = $title . campus_get_svg( array( 'icon' => 'angle-down' ) );
			}
		}
	}

	return $title;
}
add_filter( 'nav_menu_item_title', 'campus_dropdown_icon_to_menu_link', 10, 4 );

/**
 * Returns an array of supported icons (icon and name).
 *
 * @return array $campus_icons
 */
function campus_icons() {
	$campus_icons = array(
		'default' => 'Défaut',
		'live' => 'Direct',
		'podcast' => 'Podcast',
		'play' => 'Play',
		'pause' => 'Pause',
		'loop' => 'Boucle',
		'backward-10' => 'Revenir de 10 secondes',
		'backward-30' => 'Revenir de 30 secondes',
		'forward-10' => 'Avancer de 10 secondes',
		'forward-30' => 'Avancer de 30 secondes',
		'comments' => 'Commentaires',
		'share' => 'Partager',
		'external' => 'Lien externe',
		'rotate-device' => 'Changer l\'orientation',
		'heart' => 'Coeur',
		'bug' => 'Bug',
		'location' => 'Lieu',
		'map' => 'Carte',
		'grid' => 'Grille',
		'list' => 'Liste',
		'list-detail' => 'Liste détaillée',
		'column-size' => 'Largeur des colonnes',
		'search' => 'Rechercher',
		'search-song' => 'Rechercher un titre',
		'arrow-left' => 'Gauche',
		'arrow-right' => 'Droite',
		'arrow-up' => 'Haut',
		'arrow-down' => 'Bas',
		'user' => 'Utilisateur',
		'tags' => 'Étiquette',
		'category' => 'Catégorie',
		'playlist' => 'Playlist',
		'show-sidebar' => 'Afficher la barre latérale',
		'program' => 'Émission',
		'archive' => 'Archive des émission',
		'today' => 'Vide',
		'rebroadcasting' => 'Rediffusion',
		'album' => 'Album',
		'song' => 'Son',
		'player-popup' => 'Player popup',
		'download' => 'Télécharger',
		'podcast-post' => 'Article du podcast',
		'edit' => 'Modifier',
		'menu' => 'Menu',
		'apple-podcast' => 'Apple Podcast',
		'rss' => 'RSS'
	);

	return $campus_icons;
}

/**
 * Returns an array of supported social links (URL and icon name).
 *
 * @return array $social_links_icons
 */
function campus_social_links_icons() {
	// Supported social links icons.
	$social_links_icons = array(
		'icon-share'	    => 'share',
		'bandcamp.com'    => 'bandcamp',
		'behance.net'     => 'behance',
		'codepen.io'      => 'codepen',
		'deviantart.com'  => 'deviantart',
		'digg.com'        => 'digg',
		'dribbble.com'    => 'dribbble',
		'dropbox.com'     => 'dropbox',
		'facebook.com'    => 'facebook',
		'flickr.com'      => 'flickr',
		'foursquare.com'  => 'foursquare',
		'plus.google.com' => 'google-plus',
		'github.com'      => 'github',
		'instagram.com'   => 'instagram',
		'linkedin.com'    => 'linkedin',
		'mailto:'         => 'envelope-o',
		'medium.com'      => 'medium',
		'pinterest.com'   => 'pinterest-p',
		'getpocket.com'   => 'get-pocket',
		'reddit.com'      => 'reddit-alien',
		'skype.com'       => 'skype',
		'skype:'          => 'skype',
		'slideshare.net'  => 'slideshare',
		'snapchat.com'    => 'snapchat-ghost',
		'soundcloud.com'  => 'soundcloud',
		'spotify.com'     => 'spotify',
		'stumbleupon.com' => 'stumbleupon',
		'tumblr.com'      => 'tumblr',
		'twitch.tv'       => 'twitch',
		'twitter.com'     => 'twitter',
		'vimeo.com'       => 'vimeo',
		'vine.co'         => 'vine',
		'vk.com'          => 'vk',
		'wordpress.org'   => 'wordpress',
		'wordpress.com'   => 'wordpress',
		'yelp.com'        => 'yelp',
		'youtube.com'     => 'youtube',
		'murmures.org'    => 'murmures',
		'dons'			      => 'heart'
	);

	/**
	 * Filter Radio Campus Angers social links icons.
	 *
	 * @since Radio Campus Angers 6.0
	 *
	 * @param array $social_links_icons Array of social links icons.
	 */
	return apply_filters( 'campus_social_links_icons', $social_links_icons );
}
