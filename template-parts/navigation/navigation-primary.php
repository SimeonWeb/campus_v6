<?php
/**
 * Displays top navigation
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.2
 */

?>
<div class="menu-toggle-link meta-links">
	<div class="meta-link">
		<button class="meta-button menu-toggle" aria-controls="top-menu" aria-expanded="false">
			<span class="icon-wrap"><?php echo campus_get_svg( array( 'icon' => 'menu', 'title' => __( 'Menu', 'campus' ) ) ); ?></span>
		</button>
	</div>
</div>

<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'campus' ); ?>">

	<?php wp_nav_menu( array(
		'theme_location'  => 'primary',
		'menu_class'	  => 'nav-menu',
		'container_class' => 'primary-menu',
		'items_wrap'	  => '<div id="%1$s" class="%2$s">%3$s</div>',
		'walker'		  => new SMN_Walker_Nav_Menu
	) ); ?>

	<?php if( is_home() && is_front_page() ) : ?>
		<a href="#content" class="screen-reader-text"><?php _e( 'Scroll down to content', 'campus' ); ?></a>
	<?php endif; ?>
</nav><!-- #site-navigation -->
