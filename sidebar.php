<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

$sidebar_id = campus_get_sidebar_id();

if ( ! is_active_sidebar( 'sidebar-' . $sidebar_id ) ) {
	return;
}

$classes = array( 'widget-area' );

// Add color classes on is_single
if( is_single() ) {
	if( $parents = campus_get_post_category_parents() )
		foreach( $parents as $parent )
			$classes[] = 'category-' . $parent->slug;
}

?>

<aside id="secondary" class="<?php echo join( ' ', $classes ); ?>" role="complementary">
	<div class="fixed-scroll-wrap" autofocus>
		<?php dynamic_sidebar( 'sidebar-' . $sidebar_id ); ?>
	</div>
</aside><!-- #secondary -->
