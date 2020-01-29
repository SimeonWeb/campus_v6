<?php
/**
 * The template for displaying testimonials
 *
 * Template Name: Témoignages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0.2
 * @version 1.0
 */

$name = campus_is_ajax() ? 'ajax' : null;

get_header( $name ); ?>

<div class="wrap">

	<?php get_template_part( 'template-parts/page/title', 'page' ); ?>

	<div id="primary" <?php campus_content_classes(); ?>>

		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) : the_post(); ?>

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

						if ( comments_open() || get_comments_number() ) :
							comments_template( '/testimonials.php' );
						endif;

						wp_link_pages( array(
							'before' => '<div class="page-links">' . __( 'Pages:', 'campus' ),
							'after'  => '</div>',
						) );
					?>

					<?php campus_edit_link( get_the_ID() ); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-## -->

			<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :

					echo '<div id="comments" class="comments-area">';

						comment_form( array(
							'comment_field' => '<p class="comment-form-comment"><label for="comment">' . __( 'Testimonial', 'campus' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" aria-required="true" required="required"></textarea></p>',
							'title_reply' => __( 'Leave a testimonial', 'campus' ),
							'title_reply_to' => __( 'Leave a testimonial', 'campus' ),
							'label_submit' => __( 'Post a testimonial', 'campus' )
						) );

					echo '</div>';

				endif;

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->

		<?php campus_this_is_the_end(); ?>

	</div><!-- #primary -->
	<?php get_sidebar(); ?>
</div><!-- .wrap -->

<?php
campus_the_meta_links();

get_footer( $name );
