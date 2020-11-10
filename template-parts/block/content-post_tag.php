<?php
/**
 * The template for displaying post_tag block
 *
 * Used for both single and index/archive.
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since Radio Campus Angers V5 1.0
 */

global $content_loop;

$content_loop++;

$classes = array( 'list-item' );
$meta = get_post_meta( get_the_ID(), 'block_type_post_tag', true );

if( ! $meta['term_id'] )
	return;

$post_tag = get_term( $meta['term_id'], 'post_tag' );

if( is_null( $post_tag ) )
	return;

$the_title = $post_tag->name;

if( $meta['title'] ) {
	$the_title = $meta['title'];
} else if( $post_tag->description ) {
	$the_title = $post_tag->description;
}

if( $the_title == $post_tag->name )
	$classes[] = 'without-title';

$permalink = get_term_link( $post_tag );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>

	<div class="list-item-container">

		<div class="post-thumbnail">
			<a href="<?php echo $permalink; ?>">
				<?php echo campus_get_category_thumbnail( (array) $post_tag ); ?>
			</a>
		</div><!-- .post-thumbnail -->

		<div class="post-content">

			<header class="entry-header">
				<a href="<?php echo $permalink; ?>">
					<div class="entry-title"><?php echo apply_filters( 'the_title', $the_title ) ?></div>
				</a>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<a href="<?php echo $permalink; ?>">
					<?php printf( _n( '%d article', '%d articles', $post_tag->count ), $post_tag->count ); ?>
				</a>
			</div><!-- .entry-content -->

			<aside class="entry-meta">
				<a href="<?php echo $permalink; ?>">
					<!--<span class="category-hierarchical-ancestor">Étiquette</span>--> <span class="category-hierarchical"><?php echo $post_tag->name; ?></span>
				</a>
			</aside>

		</div>

	</div>

</article><!-- #post -->
