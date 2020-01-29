<?php
/**
 * Template for displaying search forms in Radio Campus Angers
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

?>

<?php $unique_id = esc_attr( uniqid( 'search-form-' ) ); ?>

<div id="search-box" class="box wrap search-box type-radio-content">

	<div class="box-content">

		<?php /*<ul class="tabs">
			<li class="tab current">
				<a href="#search-playlist-form" class="tab-link">
					<div class="post-thumbnail">
						<?php echo campus_get_svg( array( 'icon' => 'search-song', 'class' => 'wp-category-image' ) ); ?>
					</div>
					<div class="post-content">
						<p class="entry-title"><?php echo _x( 'Rechercher un titre', 'campus' ); ?></p>
					</div>
				</a>
			</li>
			<li class="tab">
				<a href="#search-form" class="tab-link">
					<div class="post-thumbnail">
						<?php echo campus_get_svg( array( 'icon' => 'search', 'class' => 'wp-category-image' ) ); ?>
					</div>
					<div class="post-content">
						<p class="entry-title"><?php echo _x( 'Rechercher une emission, une page...', 'campus' ); ?></p>
					</div>
				</a>
			</li>
		</ul>

		<div class="tabs-content">*/ ?>

			<label class="search-form-label">Rechercher un titre</label>
			<?php Campus_Daily_Playlist::daily_playlist_form( array( 'class' => 'tab-content current', 'empty_values' => true ) ); ?>

			<label class="search-form-label">Rechercher une emission, une page...</label>
			<?php get_search_form(); ?>

		<?php /*</div> */ ?>
	</div>
	<div class="box-overlay close-box bg-gradient"></div>
</div>
