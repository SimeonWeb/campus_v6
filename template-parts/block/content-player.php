<?php
/**
 * The template for displaying player block
 *
 * Used for both single and index/archive.
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since Radio Campus Angers V5 1.0
 */

global $content_loop;

$content_loop++;


$permalink = '#';
?>

<article id="post-00" class="list-item block type-block status-publish hentry type-block-player live-link">

	<div class="list-item-container">

		<div class="post-thumbnail">
			<a href="<?php echo $permalink; ?>">
			</a>
		</div><!-- .post-thumbnail -->

		<div class="post-content">

			<header class="entry-header">
				<a href="<?php echo $permalink; ?>" class="hero-title">
					<div class="entry-title">
						Écouter <br>
						le direct
					</div>
					<span class="icon-wrap">
						<?php echo campus_get_svg( array( 'icon' => 'live' ) ); ?>
					</span>
				</a>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<a href="/radio/podcasts/" class="hero-subtitle">
					<span class="icon-wrap">
						<?php echo campus_get_svg( array( 'icon' => 'podcast' ) ); ?>
					</span>
					<span class="text">
						Ou (re)découvrez <br>
						nos podcasts
					</span>
				</a>
			</div><!-- .entry-content -->

		</div>
	
	</div>

</article><!-- #post -->
