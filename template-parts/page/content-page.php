<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if( ! has_shortcode( get_the_content(), 'the_title' ) ) : ?>

		<header class="entry-header">
			<?php if( has_post_thumbnail() ) : ?>
				<h1 class="entry-title">
					<?php the_post_thumbnail( 'full', array( 'title' => get_the_title(), 'alt' => get_the_title(), 'class' => 'fullwidth' ) ); ?>
				</h1>
			<?php else: ?>
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			<?php endif; ?>
		</header><!-- .entry-header -->

	<?php endif; ?>

	<div class="entry-content">
		<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'campus' ),
				'after'  => '</div>',
			) );
		?>

		<?php campus_edit_link( get_the_ID() ); ?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->
