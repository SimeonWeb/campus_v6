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

$menu_id = campus_get_sidebar_menu_id();

if ( ! is_active_sidebar( 'sidebar-' . $sidebar_id ) && ! $menu_id ) {
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

		<?php if ( $menu_id ) : ?>
			<?php $menu = wp_get_nav_menu_object( $menu_id ); ?>
			<aside class="widget widget_nav_menu">
				<h3 class="widget-title"><?php echo apply_filters( 'widget_title', $menu->name ); ?></h3>
				<div class="menu-sidebar-page-container">
					<?php wp_nav_menu( [
						'menu'  => $menu_id
					] ); ?>
				</div>
			</aside>
		<?php endif; ?>

		<?php dynamic_sidebar( 'sidebar-' . $sidebar_id ); ?>
		
	</div>
</aside><!-- #secondary -->
