<?php
/**
 * Radio Campus Angers functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */


/*
 * Set up the theme version number.
 *
 */
define( 'THEME_VERSION' , wp_get_theme()->get( 'Version' ) . '.' . filemtime( get_parent_theme_file_path( '/style.css' ) ) );


/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function campus_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/campus
	 * If you're building a theme based on Radio Campus Angers, use a find and replace
	 * to change 'campus' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'campus' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/**
	 * Enable support for custom logo
	 *
	 * @link https://developer.wordpress.org/themes/functionality/custom-logo/
	 */
	add_theme_support( 'custom-logo' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 300, 300, true );

	add_image_size( 'player-thumbnail', 180, 180, true );

	add_image_size( 'sticky-thumbnail', 600, 600, true );

	// Set the default content width.
	$GLOBALS['content_width'] = 600;

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary'    => __( 'Navigation Menu', 'campus' ),
		'social'	 => __( 'Social Menu', 'campus' )
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside',
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add Jetpack Infinite Scroll Support
	 *
	 * Doc: https://jetpack.com/support/infinite-scroll/#theme
	 */
	add_theme_support( 'infinite-scroll', array(
	    'container'		 => 'main',
	    'wrapper'  		 => true,
	    'footer'		 => false,
	    'render'		 => 'campus_infinite_scroll_render',
	    'posts_per_page' => get_option( 'posts_per_page' )
	) );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
 	 */
	add_editor_style( array( 'assets/css/editor-style.css', campus_fonts_url() ) );
}
add_action( 'after_setup_theme', 'campus_setup' );

/**
 * Register custom fonts.
 */
function campus_fonts_url() {
	$fonts_url = get_theme_file_uri( '/assets/fonts/bender.css' );

	return esc_url_raw( $fonts_url );
}

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function campus_widgets_init() {

	register_sidebar( array(
		'name'          => __( 'Barre latérale de la page d\'accueil', 'campus' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Barre latérale émission', 'campus' ),
		'id'            => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Barre latérale actus', 'campus' ),
		'id'            => 'sidebar-3',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Barre latérale pages', 'campus' ),
		'id'            => 'sidebar-4',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Barre latérale playlists', 'campus' ),
		'id'            => 'sidebar-5',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'campus_widgets_init' );

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 *
 * @since Radio Campus Angers 6.0
 *
 * @param string $link Link to single post/page.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function campus_excerpt_more( $link ) {
	if ( is_admin() ) {
		return $link;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'campus_excerpt_more' );

/**
 * Excerpt length
 *
 * @since Radio Campus Angers 6.0
 *
 * @return int.
 */
function campus_excerpt_length() {
	return 20;
}
add_filter( 'excerpt_length', 'campus_excerpt_length' );

/**
 * Do shortcode in Excerpt
 *
 * @since Radio Campus Angers 6.0
 *
 * @return string.
 */
function campus_do_shortcode_in_excerpt( $excerpt ) {
	return wp_trim_words( do_shortcode( get_the_content(), campus_excerpt_length() ) );
}
//add_filter( 'get_the_excerpt', 'campus_do_shortcode_in_excerpt' );

/**
 * Remove Sticky posts from main query
 *
 * @since Radio Campus Angers 6.0
 */
function campus_pre_get_posts( $query ) {

	if( $query->is_main_query() && ! is_admin() ) {
		$query->set( 'ignore_sticky_posts', true );
	}
}
add_action( 'pre_get_posts', 'campus_pre_get_posts' );


/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Radio Campus Angers 6.0
 */
function campus_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'campus_javascript_detection', 0 );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function campus_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
	}
}
add_action( 'wp_head', 'campus_pingback_header' );

/**
 * Register scripts and styles.
 *
 * @since Radio Campus Angers 5.2
 */
function campus_register_scripts() {

	$suffix = WP_DEBUG ? '' : '.min';

	// Infinite scroll
	wp_deregister_script( 'the-neverending-homepage' );
	wp_register_script( 'the-neverending-homepage', get_theme_file_uri( '/assets/js/infinity' . $suffix . '.js' ), array( 'jquery' ), '4.0.0', true );


	// Add date/time picker Doc: https://jqueryui.com/datepicker/ - https://fgelinas.com/code/timepicker/
	//wp_register_script( 'jquery-ui-timepicker', get_theme_file_uri( '/assets/js/jquery.ui.timepicker.js' ), array( 'jquery-ui-datepicker' ), '0.3.3', true  );
	//wp_register_script( 'jquery-ui-timepicker-fr', get_theme_file_uri( '/assets/js/i18n/jquery.ui.timepicker-fr.js' ), array( 'jquery-ui-timepicker' ), '0.3.3', true  );
	//
	wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', array(), '1.8' );
	//wp_register_style( 'jquery-ui-timepicker', get_theme_file_uri( '/assets/css/jquery.ui.timepicker.css' ), array( 'jquery-ui' ), '0.3.3' );

	// Register Javascript Cookie
	wp_register_script( 'js-cookie', 'https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js', array(), '2.0', true );

	// Register program grid
	wp_register_script( 'campus-program-grid', get_theme_file_uri( '/assets/js/program-grid' . $suffix . '.js' ), array(), THEME_VERSION, true );
	wp_register_style( 'campus-program-grid', get_theme_file_uri( '/assets/css/program-grid.css' ), array(), THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'campus_register_scripts' );
add_action( 'admin_enqueue_scripts', 'campus_register_scripts', 9 );

/**
 * Jetpack styles.
 *
 * @since Radio Campus Angers 5.3
 */
function campus_jetpack_style() {

	wp_enqueue_script( 'the-neverending-homepage' );

	// Share
	wp_deregister_style( 'sharedaddy' );
	wp_register_style( 'sharedaddy', get_theme_file_uri( '/assets/css/sharing.css' ), false, THEME_VERSION );

	// Jetpack Share
	wp_enqueue_style( 'sharedaddy' );
	wp_enqueue_style( 'social-logos' );
}
add_action( 'wp_print_styles', 'campus_jetpack_style' );


/**
 * Enqueue scripts and styles.
 */
function campus_scripts() {

	$suffix = WP_DEBUG ? '' : '.min';

	// Add date/time picker
	//wp_enqueue_script( 'jquery-ui-timepicker-fr' );
	//wp_enqueue_style( 'jquery-ui-timepicker' );

	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'campus-fonts', campus_fonts_url(), array(), null );

	// Theme stylesheet.
	wp_enqueue_style( 'campus-style', get_stylesheet_uri(), [], THEME_VERSION );

	// Program grid
	wp_enqueue_style( 'campus-program-grid' );
	wp_enqueue_script( 'campus-program-grid' );

	// Load the Internet Explorer 9 specific stylesheet, to fix display issues in the Customizer.
	if ( is_customize_preview() ) {
		wp_enqueue_style( 'campus-ie9', get_theme_file_uri( '/assets/css/ie9.css' ), array( 'campus-style' ), THEME_VERSION );
		wp_style_add_data( 'campus-ie9', 'conditional', 'IE 9' );
	}

	// Load the Internet Explorer 8 specific stylesheet.
	wp_enqueue_style( 'campus-ie8', get_theme_file_uri( '/assets/css/ie8.css' ), array( 'campus-style' ), THEME_VERSION );
	wp_style_add_data( 'campus-ie8', 'conditional', 'lt IE 9' );

	// Load the html5 shiv.
	wp_enqueue_script( 'html5', get_theme_file_uri( '/assets/js/html5.js' ), array(), '3.7.3' );
	wp_script_add_data( 'html5', 'conditional', 'lt IE 9' );

	wp_enqueue_script( 'campus-skip-link-focus-fix', get_theme_file_uri( '/assets/js/skip-link-focus-fix.js' ), array(), '1.0', true );

	$campus_l10n = array(
		'quote'          => campus_get_svg( array( 'icon' => 'quote-right' ) ),
	);

	if ( has_nav_menu( 'primary' ) ) {
		wp_enqueue_script( 'campus-navigation', get_theme_file_uri( '/assets/js/navigation' . $suffix . '.js' ), array( 'jquery' ), THEME_VERSION, true );
		$campus_l10n['expand']         = __( 'Expand child menu', 'campus' );
		$campus_l10n['collapse']       = __( 'Collapse child menu', 'campus' );
		$campus_l10n['icon']           = campus_get_svg( array( 'icon' => 'arrow-down', 'fallback' => true ) );
	}
	wp_enqueue_script( 'jquery-easing', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js', array(), '1.4.1', true );
	wp_enqueue_script( 'jquery-masonry' );

	// https://github.com/browserstate/history.js/
	wp_enqueue_script( 'history.js', get_theme_file_uri( '/assets/js/history.js' ), array(), '1.8b2', true );

	// https://github.com/browserstate/ajaxify
	wp_enqueue_script( 'campus-ajaxify', get_theme_file_uri( '/assets/js/ajaxify' . $suffix . '.js' ), array( 'jquery' ), THEME_VERSION, true );

	wp_enqueue_script( 'campus-global', get_theme_file_uri( '/assets/js/global' . $suffix . '.js' ), array( 'jquery' ), THEME_VERSION, true );

	// Add JavaScript variables to functionalities of Campus V6.
	wp_localize_script( 'campus-global', 'campus', campus_script_vars() );

	wp_enqueue_script( 'js-cookie' );

	wp_enqueue_script( 'jquery-scrollto', get_theme_file_uri( '/assets/js/jquery.scrollTo.js' ), array( 'jquery' ), '2.1.2', true );

	wp_localize_script( 'campus-skip-link-focus-fix', 'campusScreenReaderText', $campus_l10n );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Player
	wp_deregister_style( 'mediaelement' );
	wp_deregister_style( 'wp-mediaelement' );
	wp_enqueue_style( 'campus-mediaelement', get_theme_file_uri( '/assets/css/mediaelementplayer.css' ), array(), THEME_VERSION );
	//wp_register_style( 'mediaelement', get_theme_file_uri( '/assets/css/mediaelementplayer.css' ), array(), THEME_VERSION );
	//wp_register_style( 'wp-mediaelement', get_theme_file_uri( '/assets/css/wp-mediaelement.css' ), array( 'mediaelement' ), THEME_VERSION );

	//wp_deregister_script( 'wp-mediaelement' );
	wp_enqueue_script( 'campus-mediaelement', get_theme_file_uri( '/assets/js/mediaelement' . $suffix . '.js' ), array( 'mediaelement', 'campus-global' ), THEME_VERSION, true );
	//wp_dequeue_script( 'powerpress-mejs' );
	//wp_enqueue_script( 'powerpress-player' );
}
add_action( 'wp_enqueue_scripts', 'campus_scripts' );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images.
 *
 * @since Radio Campus Angers 6.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function campus_content_image_sizes_attr( $sizes, $size ) {
	$width = $size[0];

	if ( 740 <= $width ) {
		$sizes = '(max-width: 706px) 89vw, (max-width: 767px) 82vw, 740px';
	}

	if ( is_active_sidebar( 'sidebar-1' ) || is_archive() || is_search() || is_home() || is_page() ) {
		if ( ! ( is_page() && 'one-column' === get_theme_mod( 'page_options' ) ) && 767 <= $width ) {
			 $sizes = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
		}
	}

	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'campus_content_image_sizes_attr', 10, 2 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails.
 *
 * @since Radio Campus Angers 6.0
 *
 * @param array $attr       Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size       Registered image size or flat array of height and width dimensions.
 * @return string A source size value for use in a post thumbnail 'sizes' attribute.
 */
function campus_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
	if ( is_archive() || is_search() || is_home() ) {
		$attr['sizes'] = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
	} else {
		$attr['sizes'] = '100vw';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'campus_post_thumbnail_sizes_attr', 10, 3 );


/**
 * Rendering fallback used when themes don't specify their own handler.
 *
 * @uses have_posts, the_post, get_template_part, get_post_format
 * @action infinite_scroll_render
 * @return string
 */
function campus_infinite_scroll_render() {
	while ( have_posts() ) {
		the_post();
		/*
		 * Include the Post-Format-specific template for the content.
		 * If you want to override this in a child theme, then include a file
		 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
		 */
		get_template_part( 'template-parts/' . get_post_type() . '/content', get_post_type() === 'block' ? campus_get_block_type() : get_post_format() );

		/**
		 * Add ads
		 */
		get_template_part( 'template-parts/block/content', 'ads' );
	}
}

/**
 * Remove sticky posts from post__not_in infinite scroll query_args.
 *
 * @since Kost 2015 1.0
 *
 * @return array $query_args.
 */
function campus_infinite_scroll_query_args( $query_args ) {
	$query_args['post__not_in'] = array();

	return $query_args;

}
add_filter( 'infinite_scroll_query_args', 'campus_infinite_scroll_query_args' );

/**
 * Tests if any of a post's assigned categories are descendants of target categories
 *
 * @param int|array $cats The target categories. Integer ID or array of integer IDs
 * @param int|object $_post The post. Omit to test the current post in the Loop or main query
 * @return bool True if at least 1 of the post's categories is a descendant of any of the target categories
 * @see get_term_by() You can get a category by name or slug, then pass ID to this function
 * @uses get_term_children() Passes $cats
 * @uses in_category() Passes $_post (can be empty)
 * @version 2.7
 * @link http://codex.wordpress.org/Function_Reference/in_category#Testing_if_a_post_is_in_a_descendant_category
 */
if ( ! function_exists( 'post_is_in_descendant_category' ) ) {
    function post_is_in_descendant_category( $cats, $_post = null ) {
        foreach ( (array) $cats as $cat ) {
            // get_term_children() accepts integer ID only
            $descendants = get_term_children( (int) $cat, 'category' );
            if ( $descendants && in_category( $descendants, $_post ) )
                return true;
        }
        return false;
    }
}

/**
 * Display website on new window when readers click Commenter's name
 */
function campus_get_comment_author_link( $author_link ) {
    return str_replace( '<a', '<a target="_blank"', $author_link );
}
add_filter( 'get_comment_author_link', 'campus_get_comment_author_link' );

/**
 * Parse html attr
 */
function campus_parse_attr( $attr ) {

	return ' ' . implode( ' ', array_map(
	    function( $value, $key ) {
	        if( is_array( $value ) ) {
	            return $key . '[]=' . implode( '&' . $key . '[]=', $value );
	        } else {
	            return $key . '=' . $value;
	        }
	    },
	    $attr,
	    array_keys($attr)
	) );
}

/**
 * Override default social username on category archive.
 *
 */
function campus_smn_social_default_user_params( $params, $class ) {

	$new_params = false;

	if( is_home() ) {

		if( is_array( $params ) )
			$new_params = $class->params;
		else
			$new_params = $class->params['username'];

	} else if( is_category() ) {

		if( is_array( $params ) ) {
			$new_params = $class->params;
			$new_params['username']   = get_term_meta( get_queried_object_id(), $class->type . '_username', true );
			$new_params['user_id'] 	  = get_term_meta( get_queried_object_id(), $class->type . '_user_id', true );
			$new_params['user_token'] = get_term_meta( get_queried_object_id(), $class->type . '_user_token', true );
		} else
			$new_params = get_term_meta( get_queried_object_id(), $class->type . '_username', true );
	}

	return $new_params;
}
add_filter( 'smn_facebook_default_username', 'campus_smn_social_default_user_params', 10, 2 );
add_filter( 'smn_twitter_default_username', 'campus_smn_social_default_user_params', 10, 2 );
add_filter( 'smn_instagram_default_user_params', 'campus_smn_social_default_user_params', 10, 2 );


function campus_wpcf7_validation_error( $error, $name, $class ) {
	return sprintf( '<span role="alert" class="wpcf7-not-valid-tip">! <span>%s</span></span>', esc_html( $error ) );
}

add_filter( 'wpcf7_validation_error', 'campus_wpcf7_validation_error', 10, 3 );


function smn_hex2rgb( $hex, $array = false ) {
	$hex = str_replace( '#', '', $hex );

	if( strlen($hex) == 3 ) {
		$r = hexdec( substr( $hex, 0, 1 ).substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ).substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ).substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}

	$rgb = array( $r, $g, $b );

	if( $array )
		return $rgb;
	else
		return implode( ',', $rgb );
}

/*--------------------------------------------------------------
Require files
--------------------------------------------------------------*/

/**
 * Load external libraries.
 */
require get_parent_theme_file_path( '/vendor/autoload.php' );

/**
 * Update theme, then remove this file.
 */
require get_parent_theme_file_path( '/inc/theme-updater.php' );

/**
 * Additional features to allow styling of the templates.
 */
require get_parent_theme_file_path( '/inc/template-functions.php' );

/**
 * Additional features to allow styling of the player.
 */
require get_parent_theme_file_path( '/inc/player-functions.php' );

/**
 * Additional features to allow styling of the menus.
 */
require get_parent_theme_file_path( '/inc/menus-functions.php' );

/**
 * Additional features to allow styling of the terms.
 */
require get_parent_theme_file_path( '/inc/terms-functions.php' );

/**
 * SVG icons functions and filters.
 */
require get_parent_theme_file_path( '/inc/icon-functions.php' );

/**
 * Load powerpress addons class.
 */
require get_parent_theme_file_path( '/inc/class-powerpress-addons.php' );

/**
 * Load admin functions.
 */
if( is_admin() ) {

	/**
	 * Add notice libraries.
	 */
	require get_parent_theme_file_path( '/inc/smn-admin-notices.php' );

	/**
	 * Load author category managment.
	 */
	require get_parent_theme_file_path( '/inc/author-category/author-category.php' );

	/**
	 * Load admin addons.
	 */
	require get_parent_theme_file_path( '/inc/admin-addons.php' );

	/**
	 * Load admin options.
	 */
	require get_parent_theme_file_path( '/inc/admin-options.php' );

	/**
	 * Add faq libraries.
	 */
	require get_parent_theme_file_path( '/inc/class-faq-post-type.php' );

}

/**
 * Load social libraries.
 */
require get_parent_theme_file_path( '/inc/smn-social/smn-social.php' );

/**
 * Load shortcode libraries.
 */
require get_parent_theme_file_path( '/inc/smn-shortcodes/smn-shortcodes.php' );

/**
 * Load playlist libraries.
 */
require get_parent_theme_file_path( '/inc/campus-playlist/campus-playlist.php' );

/**
 * Add programs class.
 */
require get_parent_theme_file_path( '/inc/rca-events-grid/rca-events-grid.php' );

/**
 * Load block libraries.
 */
require get_parent_theme_file_path( '/inc/class-block-post-type.php' );

/**
 * Custom template tags for this theme.
 */
require get_parent_theme_file_path( '/inc/template-tags.php' );

/**
 * Custom template tags for this theme.
 */
require get_parent_theme_file_path( '/inc/post-meta-boxes.php' );

/**
 * Additional widgets.
 */
require get_parent_theme_file_path( '/inc/widgets.php' );

/**
 * Customizer additions.
 */
require get_parent_theme_file_path( '/inc/customizer.php' );
