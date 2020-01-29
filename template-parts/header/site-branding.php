<?php
/**
 * Displays header site branding
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

?>
<div class="site-branding">
	
	<?php the_custom_logo(); ?>
	
	<div class="site-branding-text">
		<?php 
			$tag = is_front_page() ? 'h1' : 'div';
			$description = get_bloginfo( 'description', 'display' ); 
		?>
		<<?php echo $tag ?> class="site-title">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="site-name"><?php bloginfo( 'name' ); ?></span>
				<?php if ( $description || is_customize_preview() ) : ?>
					<span class="site-description"><?php echo $description; ?></span>
				<?php endif; ?>
			</a>
		</<?php echo $tag ?>>
	</div><!-- .site-branding-text -->

	<?php if ( ( is_home() && is_front_page() ) && ! has_nav_menu( 'top' ) ) : ?>
		<a href="#content" class="screen-reader-text"><?php _e( 'Scroll down to content', 'campus' ); ?></a>
	<?php endif; ?>

</div><!-- .site-branding -->
