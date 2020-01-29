<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
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

	<div id="primary" <?php campus_content_classes(); ?>>

		<?php if( ( is_category() || is_tag() ) && get_the_archive_description() ) : ?>

			<header class="taxonomy-header <?php echo campus_get_all_term_classes(); ?>">

				<figure class="taxonomy-thumbnail">
					<?php campus_the_category_thumbnail(); ?>
				</figure>

				<div class="taxonomy-content">
					<?php the_archive_title( '<h1 class="taxonomy-title">', '</h1>' ); ?>
					<?php campus_the_term_meta( 'secondary_description', '<p class="taxonomy-secondary-description">', '</p>' ); ?>
					<?php campus_the_term_meta( 'type', '<p class="taxonomy-secondary-description">', '</p>' ); ?>

					<div class="taxonomy-schedules">
						<?php campus_the_term_meta( 'day', '<p class="taxonomy-schedules-day">', '</p>' ); ?>
						<?php campus_the_term_meta( 'hours', '<p class="taxonomy-schedules-hours">', '</p>', ' > ' ); ?>
					</div>

					<div class="taxonomy-description">
						<?php if( class_exists( 'WPCom_Markdown' ) ) :
							echo wpautop( stripslashes( WPCom_Markdown::get_instance()->transform( get_the_archive_description() ) ) );
						else:
							the_archive_description();
						endif; ?>
					</div>
				</div>

				<div class="taxonomy-meta">
					<p class="taxonomy-users"><?php campus_the_term_users( campus_get_svg( array( 'icon' => 'user', 'class' => 'icon-small' ) ) . '<span class="icon-title">', '</span>' ); ?></p>
					<?php campus_the_term_meta( 'interests', '<p class="taxonomy-interests">' . campus_get_svg( array( 'icon' => 'heart', 'class' => 'icon-small' ) ) . '<span class="icon-title">', '</span></p>' ); ?>
				</div>

				<aside class="taxonomy-aside content-meta-links meta-links">
					<?php campus_the_term_social_links(); ?>
				</aside>
			</header><!-- .page-header -->

		<?php elseif( is_tax( 'album_playlist' ) ) : ?>

			<header class="taxonomy-header <?php echo campus_get_all_term_classes(); ?> max-450">

				<figure class="taxonomy-thumbnail">
					<?php
						$albums = Campus_Album::get_albums( array( 'playlist' => get_queried_object_id() ) );
						shuffle( $albums );
						$thumbnails = array_slice( $albums, 0, 4 );
						foreach( $thumbnails as $i => $thumbnail )
							echo get_the_post_thumbnail( $thumbnail->ID, array(180,180), array( 'class'	=> "album-$i" ) );; ?>
				</figure>

			</header>

		<?php endif; ?>

		<?php
		if ( have_posts() ) : ?>

			<main id="main" class="site-main" role="main">
				<?php
				/* Start the Loop */
				while ( have_posts() ) : the_post();

					/*
					 * Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'template-parts/' . get_post_type() . '/content', get_post_type() === 'block' ? campus_get_block_type() : get_post_format() );

				endwhile;
				?>
			</main><!-- #main -->

		<?php endif; ?>

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
