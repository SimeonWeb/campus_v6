<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
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
			<span class="site-name">404</span>
			<span class="site-description"><?php _e( 'Oops! That page can&rsquo;t be found.', 'campus' ); ?></span>
		</hgroup>
	</header><!-- .fixed-header -->

	<div id="primary" class="content-area content-grid">
		<main id="main" class="site-main error-404 not-found" role="main">

			<?php
			/**
			 * Add Player block
			 */
			get_template_part( 'template-parts/block/content', 'player' );

			?>

			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>
			<article class="list-item is-empty"></article>

		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php
campus_the_meta_links();

get_footer( $name );
