<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Inter_Religio
 * @since 1.0
 * @version 1.0
 */

?>

<section class="hentry list-item no-results not-found">

	<div class="list-item-container">

		<div class="post-thumbnail">
			<?php echo campus_get_svg( array( 'icon' => 'today', 'class' => 'wp-category-image' ) ); ?>
		</div><!-- .post-thumbnail -->

		<div class="post-content">

			<header class="entry-header">
				<div class="entry-title"><?php _e( 'Nothing Found', 'campus' ); ?></div>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<?php
				if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

					<p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'campus' ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>

				<?php else : ?>

					<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'campus' ); ?></p>

				<?php endif; ?>
			</div><!-- .entry-content -->

		</div><!-- .post-content -->
		
	</div>

</section><!-- .no-results -->
