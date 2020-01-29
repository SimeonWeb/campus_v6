<?php
/**
 * Template part for displaying posts on single
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="post-thumbnail">
		<?php the_post_thumbnail( 'post-thumbnails' ); ?>
		<?php //campus_the_entry_player(); ?>
	</div><!-- .post-thumbnail -->
	
	<header class="entry-header">
		<hgroup class="entry-header-group">
			<?php campus_posted_on(); ?>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</hgroup>
	</header><!-- .entry-header -->
	
	<div class="entry-content">
		<?php
		/* translators: %s: Name of current post */
		the_content( sprintf(
			__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'campus' ),
			get_the_title()
		) );
		
		wp_link_pages( array(
			'before'      => '<div class="page-links">' . __( 'Pages:', 'campus' ),
			'after'       => '</div>',
			'link_before' => '<span class="page-number">',
			'link_after'  => '</span>',
		) );
		?>
	</div><!-- .entry-content -->
	
	<aside class="entry-aside entry-meta">
		<?php campus_entry_meta(); ?>
	</aside>
	
	<footer class="entry-footer entry-meta entry-section">
		<?php campus_entry_footer(); ?>
	</footer>
	
</article><!-- #post-## -->
