<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

global $content_loop, $content_block_ad_index;

$content_loop = $content_block_ad_index = 0;

$name = campus_is_ajax() ? 'ajax' : null;

get_header( $name ); ?>

<div class="wrap">

	<div id="primary" <?php campus_content_classes(); ?>>

		<?php echo adrotate_group( 1 ); ?>

		<?php if ( is_home() && ! is_front_page() ) : ?>
			<header class="page-header">
				<h1 class="page-title"><?php single_post_title(); ?></h1>
			</header>
		<?php endif; ?>

		<main id="main" class="site-main" role="main">

			<?php
			/**
			 * Add Player block
			 */
			get_template_part( 'template-parts/block/content', 'player' );

			if ( have_posts() ) :

				/* Start the Loop */
				while ( have_posts() ) : the_post();

					/*
					 * Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'template-parts/' . get_post_type() . '/content', get_post_type() === 'block' ? campus_get_block_type() : get_post_format() );

					/**
					 * Add ads
					 */
					get_template_part( 'template-parts/block/content', 'ads' );

				endwhile;

			else :

				get_template_part( 'template-parts/post/content', 'none' );

			endif;
			?>

		</main><!-- #main -->

		<?php campus_this_is_the_end(); ?>

		<?php
			the_posts_pagination( array(
				'prev_text' => campus_get_svg( array( 'icon' => 'arrow-left' ) ) . '<span class="screen-reader-text">' . __( 'Previous page', 'campus' ) . '</span>',
				'next_text' => '<span class="screen-reader-text">' . __( 'Next page', 'campus' ) . '</span>' . campus_get_svg( array( 'icon' => 'arrow-right' ) ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'campus' ) . ' </span>',
			) );
		?>
	</div><!-- #primary -->
	<?php get_sidebar(); ?>
</div><!-- .wrap -->

<?php
campus_the_meta_links();

get_footer( $name );
