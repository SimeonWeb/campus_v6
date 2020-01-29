<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.2
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'list-item' ); ?>>

	<div class="post-thumbnail">
		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
			<?php the_post_thumbnail( 'sticky-thumbnail' ); ?>
		</a>
	</div><!-- .post-thumbnail -->

	<div class="post-content">

		<header class="entry-header">
			<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
				<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
			</a>
		</header><!-- .entry-header -->

		<div class="entry-content">
			<a href="<?php the_permalink(); ?>">
				<?php the_excerpt(); ?>
			</a>
		</div><!-- .entry-content -->
	</div>

</article><!-- #post-## -->
