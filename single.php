<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 5.3
 * @version 1.0
 */

$name = campus_is_ajax() ? 'ajax' : null;

get_header( $name ); ?>

<div class="wrap">

	<?php get_template_part( 'template-parts/page/title', 'page' ); ?>

	<div id="primary" <?php campus_content_classes(); ?>>

		<main id="main" class="site-main" role="main">

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();

				get_template_part( 'template-parts/post/content', 'single' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

				the_post_navigation( array(
					'prev_text' => '<span class="nav-title" title="' . __( 'Previous Post', 'campus' ) . '"><span class="nav-title-icon-wrapper">' . campus_get_svg( array( 'icon' => 'arrow-left' ) ) . '</span><span class="screen-reader-text">%title</span></span>',
					'next_text' => '<span class="nav-title" title="' . __( 'Next Post', 'campus' ) . '"><span class="screen-reader-text">%title</span><span class="nav-title-icon-wrapper">' . campus_get_svg( array( 'icon' => 'arrow-right' ) ) . '</span></span>',
					'in_same_term' => true,
				) );

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
