<?php
/**
 * The template for displaying category block
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
$meta = get_post_meta( get_the_ID(), 'block_type_category', true );

if( ! $meta['term_id'] )
	return;

$category = get_term( $meta['term_id'], 'category' );

if( is_null( $category ) )
	return;

$the_title  = $category->name;
$invert     = $meta['invert'] === 'on' ? true : false;
$has_parent = $meta['no_parent'] === 'on' ? false : true;

if( $meta['title'] ) {
	$the_title = $meta['title'];
} else if( $category->description ) {
	$the_title = $category->description;
}

if( $the_title == $category->name )
	$classes[] = 'without-title';

$permalink = get_term_link( $category );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>

	<div class="post-thumbnail">
		<a href="<?php echo $permalink; ?>">
			<?php echo campus_get_category_thumbnail( (array) $category ); ?>
		</a>
	</div><!-- .post-thumbnail -->

	<div class="post-content">

		<header class="entry-header">
			<a href="<?php echo $permalink; ?>">
				<div class="entry-title"><?php echo apply_filters( 'the_title', $invert ? $category->name : $the_title ) ?></div>
			</a>
		</header><!-- .entry-header -->

		<?php if( $category->count ) : ?>
			<div class="entry-content">
				<a href="<?php echo $permalink; ?>">
					<?php printf( _n( '%d article', '%d articles', $category->count ), $category->count ); ?>
				</a>
			</div><!-- .entry-content -->
		<?php endif; ?>

		<aside class="entry-meta">
			<a href="<?php echo $permalink; ?>">
				<?php echo $has_parent ? '<span class="category-hierarchical-ancestor">' . campus_get_term_parent_name( $category->term_id ) . '</span> ' : ''; ?><span class="category-hierarchical"><?php echo $invert ? $the_title : $category->name; ?></span>
			</a>
		</aside>

	</div>

</article><!-- #post -->
