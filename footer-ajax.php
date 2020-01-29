<?php
/**
 * The footer ajax call
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since Radio Campus Angers V5.0
 */

//get the page content
$content = ob_get_clean();

//get the head
ob_start();
wp_head();
$head = ob_get_contents();
ob_get_clean();

preg_match_all( '/<script.+type=["|\']text\/javascript["|\'][\s\S]+?<\/script>/i', $head, $head_scripts );
preg_match_all( '/<link.+type=["|\']text\/css["|\'][^>]+>/i', $head, $head_styles_link );
preg_match_all( '/<style.+type=["|\']text\/css["|\'][\s\S]+?<\/style>/i', $head, $head_styles_line );

//get the footer
ob_start();
wp_footer();
$footer = ob_get_contents();
ob_get_clean();

preg_match_all( '/<script.+type=["|\']text\/javascript["|\'][\s\S]+?<\/script>/i', $footer, $footer_scripts );
preg_match_all( '/<link.+type=["|\']text\/css["|\'][^>]+>/i', $footer, $footer_styles_link );
preg_match_all( '/<style.+type=["|\']text\/css["|\'][\s\S]+?<\/style>/i', $footer, $footer_styles_line );


$head_scripts = isset( $head_scripts[0] ) ? $head_scripts[0] : array();
$footer_scripts = isset( $footer_scripts[0] ) ? $footer_scripts[0] : array();

$head_styles_link = isset( $head_styles_link[0] ) ? $head_styles_link[0] : array();
$head_styles_line = isset( $head_styles_line[0] ) ? $head_styles_line[0] : array();
$footer_styles_link = isset( $footer_styles_link[0] ) ? $footer_styles_link[0] : array();
$footer_styles_line = isset( $footer_styles_line[0] ) ? $footer_styles_line[0] : array();

$scripts = array_merge( $head_scripts, $footer_scripts );
// Remove global scripts
foreach( $scripts as $key => $script ) {
	// Do not reload these scripts
	preg_match( '/src=[\"|\'].+(jquery|html5|skip-link-focus-fix|history|ajaxify|cookie|imagesloaded|masonry|mediaelement|infinity|navigation|global|program-grid)/', $script, $matches );

	if( ! empty( $matches ) ) {
		unset( $scripts[$key] );
	}
}

//Init data
$data = array();

//$data['wp_query'] = $wp_query;
$data['wpTitle'] = html_entity_decode( wp_title( '- ' . get_bloginfo( 'name' ), false, 'right' ) );
$data['scripts'] = join( "\n", array_merge( $scripts ) );
$data['styles'] = join( "\n", array_merge( $head_styles_link, $footer_styles_link, $head_styles_line, $footer_styles_line ) );
$data['bodyClass'] = join( ' ', get_body_class() );
$data['menu'] = wp_nav_menu( array( 'theme_location' => 'primary', 'echo' => false ) );
$data['content'] = $content;
$data['campus'] = campus_script_vars();

// Json
$json = json_encode( $data );
$error = json_last_error_msg();

if( $error == 'No error' )
	die( $json );
else
	die( $error );
