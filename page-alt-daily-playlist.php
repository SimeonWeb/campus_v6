<?php
/**
 * The template for displaying playlist
 *
 * Template Name: Playlist quotidienne
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

$name = campus_is_ajax() ? 'ajax' : null;

get_header( $name ); ?>

<div class="wrap">

	<header class="fixed-header">
		<hgroup class="fixed-header-group">
			<h1 class="title-hierarchical-prefix"><?php the_title(); ?></h1>
		</hgroup>
	</header><!-- .fixed-header -->

	<div id="primary" <?php campus_content_classes( 'content-list-detail' ); ?>>

		<main id="main" class="site-main" role="main">

			<?php Campus_Daily_Playlist::display_daily_playlist_form(); ?>

		</main><!-- #main -->

		<?php campus_this_is_the_end(); ?>

	</div><!-- #primary -->
</div><!-- .wrap -->

<?php
campus_the_meta_links();

get_footer( $name );
