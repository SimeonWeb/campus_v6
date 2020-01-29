<?php
/**
 * The template for displaying album playlist block
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
$meta = get_post_meta( get_the_ID(), 'block_type_album_playlist', true );

if( ! $meta['term_id'] )
	return;

$playlist = get_term( $meta['term_id'], 'album_playlist' );

if( is_null( $playlist ) )
	return;

$albums = Campus_Album::get_albums( array( 'playlist' => $playlist->term_id ) );

if( ! $albums )
	return;

$the_title = $playlist->name;

if( $meta['title'] ) {
	$the_title = $meta['title'];
} else if( $playlist->description ) {
	$the_title = $playlist->description;
}

if( $the_title == $playlist->name )
	$classes[] = 'without-title';

$permalink = get_term_link( $playlist );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>

	<div class="post-thumbnail">
		<a href="<?php echo $permalink; ?>">
			<?php
			shuffle( $albums );
			$thumbnails = array_slice( $albums, 0, 4 );
			foreach( $thumbnails as $i => $thumbnail )
				echo get_the_post_thumbnail( $thumbnail->ID, array(180,180), array( 'class'	=> "album-$i" ) );
			?>
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
				<?php
					$artists = $artist_names = array();
					foreach( $albums as $album ) {
						$artists = array_merge( $artists, (array) get_the_terms( $album->ID, 'album_artist' ) );
					}
					foreach( array_filter( $artists ) as $artist ) {
						$artist_names[$artist->term_id] = $artist->name;
					}

					//shuffle( $artist_names );
					$artist_names = array_slice( $artist_names, 0, 8 );

					echo 'Featuring ' . join( ', ', $artist_names ) . '…';
				?>
			</a>
		</div><!-- .entry-content -->

		<aside class="entry-meta">
			<a href="<?php echo $permalink; ?>">
				<span class="category-hierarchical-ancestor">Playlist</span> <span class="category-hierarchical"><?php echo $playlist->name; ?></span>
			</a>
		</aside>

	</div>

</article><!-- #post -->
