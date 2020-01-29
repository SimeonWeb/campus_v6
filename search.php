<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

$name = campus_is_ajax() ? 'ajax' : null;

get_header( $name ); ?>

<div class="wrap">

	<?php get_template_part( 'template-parts/page/title', 'page' ); ?>

	<div id="primary" class="content-area content-list-detail">
		
		<main id="main" class="site-main" role="main">

			<section class="search content-wrapper">
				<header class="content-header fixed">
					<?php get_search_form(); ?>
				</header>
				<div class="content-hentry">

				<?php
				if ( have_posts() ) :
					/* Start the Loop */
					while ( have_posts() ) : the_post();

						/**
						 * Run the loop for the search to output the results.
						 * If you want to overload this in a child theme then include a file
						 * called content-search.php and that will be used instead.
						 */
						get_template_part( 'template-parts/' . get_post_type() . '/content' );

					endwhile; // End of the loop.

					the_posts_pagination( array(
						'prev_text' => campus_get_svg( array( 'icon' => 'arrow-left' ) ) . '<span class="screen-reader-text">' . __( 'Previous page', 'campus' ) . '</span>',
						'next_text' => '<span class="screen-reader-text">' . __( 'Next page', 'campus' ) . '</span>' . campus_get_svg( array( 'icon' => 'arrow-right' ) ),
						'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'campus' ) . ' </span>',
					) );

				else :

					get_template_part( 'template-parts/post/content', 'none' );

				endif;
				?>

				</div>
			</section>

		</main><!-- #main -->

		<?php campus_this_is_the_end(); ?>

	</div><!-- #primary -->
	<?php get_sidebar(); ?>
</div><!-- .wrap -->

<?php
campus_the_meta_links();

get_footer( $name );
