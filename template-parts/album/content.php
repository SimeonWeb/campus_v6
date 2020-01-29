<?php
/**
 * Template part for displaying albums
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'list-item' ); ?>>

	<div class="post-thumbnail">
		<?php the_post_thumbnail( 'sticky-thumbnail' ); ?>
	</div><!-- .post-thumbnail -->

	<div class="post-content">

		<header class="entry-header">
			<div class="entry-title" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></div>
			<?php echo get_the_term_list( get_the_ID(), 'album_artist', '', ' / ', '' ); ?>
		</header><!-- .entry-header -->

		<div class="entry-content">
  			<div class="archive-infos-type"><?php echo get_the_term_list( get_the_ID(), 'album_genre', '', ' / ', '' ); ?></div>
  			<div class="archive-secondary-infos-type"><?php echo get_the_term_list( get_the_ID(), 'album_label', '', ' / ', '' ); ?></div>
  			<div class="playlist-type"><?php echo get_the_term_list( get_the_ID(), 'album_playlist', campus_get_svg( array( 'icon' => 'playlist', 'class' => 'icon-small' ) ) . '<span class="icon-title">', ' / ', '</span>' ); ?></div>
		</div><!-- .entry-content -->
	</div>

	<?php campus_the_album_player(); ?>

</article><!-- #post-## -->
