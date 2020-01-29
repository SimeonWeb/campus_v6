<?php
/*--------------------------------------------------------------
Admin functions
--------------------------------------------------------------*/

/**
 * Add meta boxes to the post edit page
 *
 */
function campus_post_add_meta_boxes() {

	// Broadcast Playlist
	add_meta_box( 'post-playlist', 'Playlist', 'campus_post_playlist_meta_box', 'post', 'normal', 'high' );

	// Show sidebar
	add_meta_box( 'hide-sidebar', 'Barre latérale', 'campus_post_hide_sidebar_meta_box', array( 'post', 'page' ), 'side' );
}

add_action( 'add_meta_boxes', 'campus_post_add_meta_boxes' );


/**
 * Add show sidebar meta box to the post and page edit page
 *
 */
function campus_post_hide_sidebar_meta_box( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'campus_post_page', 'campus_post_page_nonce' );

	$meta = get_post_meta( $post->ID, '_hide_sidebar', true );
	?>
	<p>
		<label><input type="hidden" name="hide-sidebar" value="off"><input type="checkbox" name="hide-sidebar" value="on"<?php checked( $meta, 'on' ); ?>> Masquer la barre latérale</label>
	</p>
	<?php
}

/**
 * Add playlist meta box to the post edit page
 *
 */
function campus_post_playlist_meta_box( $post ) {

	$playlist = get_post_meta( $post->ID, '_playlist', true );

	echo '<div class="post-playlist-entries">';

	if( ! $playlist || !is_array( $playlist ) )
		$playlist = campus_default_playlist_fields();

	foreach( $playlist as $k => $fields ) {

	    echo '<div class="post-playlist-entry'.($k % 2 == 0 ? ' alt' : '').' ui-sortable">';
	    	echo '<div class="post-playlist-num">'.($k+1).'</div>';
	    	echo '<div class="post-playlist-fields">';
	    	foreach( $fields as $name => $field ) {
			echo '<div class="post-playlist-field post-playlist-'.$name.'">';
				echo '<label'.(trim($field) != '' ? ' class="screen-reader-text"' : '').'>'.campus_playlist_field_title($name).'</label>';
				echo '<input type="text" name="post-playlist['.$k.']['.$name.']" value="'.trim($field).'" autocomplete="off" size="30" />';
			echo '</div>';
			}
			echo '<br class="clear">';
			echo '</div>';
	    echo '</div>';
	}

	echo '</div>';

	?>
	<div class="post-playlist-entry">
		<div class="post-playlist-num add-field"><a>+</a></div>
		<div class="post-playlist-fields">
			<div class="post-playlist-field post-playlist-title">
				<label>Titre</label>
				<input type="text" name="post-playlist[%n][title]" value="" autocomplete="off" size="30" />
			</div>
			<div class="post-playlist-field post-playlist-artist">
				<label>Artiste</label>
				<input type="text" name="post-playlist[%n][artist]" value="" autocomplete="off" size="30" />
			</div>
			<div class="post-playlist-field post-playlist-other">
				<label>Autre (album, année...)</label>
				<input type="text" name="post-playlist[%n][other]" value="" autocomplete="off" size="30" />
			</div>
			<div class="post-playlist-field post-playlist-link">
				<label>Lien (URL Youtube, Spotify...)</label>
				<input type="text" name="post-playlist[%n][link]" value="" autocomplete="off" size="30" />
			</div>
			<br class="clear">
		</div>
	</div>

	<p class="description">Copiez-collez le shortcode [playlist] pour ajouter la playlist ou vous voulez dans votre article. Par défaut elle apparait à la fin.</p>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$(document).on('focusin', '.post-playlist-entry input', function() {
		$(this).siblings('label').addClass('screen-reader-text');
	}).on('focusout', '.post-playlist-entry input', function() {
		if( $(this).val() == '' || $(this).val() == ' ')
			$(this).siblings('label').removeClass('screen-reader-text');
	});

	$(document).on('click', '.add-field', function() {
		var $parent = $(this).closest('.post-playlist-entry'),
			$clone = $parent.clone(),
			count = $('.post-playlist-entries .post-playlist-entry').length;

		$clone
			.find('.post-playlist-num').removeClass('add-field').html(count + 1).end()
			.find('input').each(function() {
				$(this).attr('name', $(this).attr('name').replace('%n', count+1));
			}).end()
			.appendTo('.post-playlist-entries');

		$parent
			.find('input').val('').end()
			.find('label').removeClass('screen-reader-text');

	});

	$('.post-playlist-entries').sortable({
		update: function( e, ui ) {
			$(e.target).find('.post-playlist-entry').each(function(i) {
				if( i % 2 == 0 )
					$(this).addClass('alt');
				else
					$(this).removeClass('alt');

				var num = i + 1;
				$(this).find('.post-playlist-num').text(num);
			});
		}
	});

});
</script>
	<?php

}

function campus_default_playlist_fields() {

	$playlist = array();

	for( $i = 0; $i < 5; $i++ ) {
		$playlist[] = array( 'title' => '', 'artist' => '', 'other' => '', 'link' => '' );
	}

	return $playlist;
}

function campus_playlist_field_title( $name ) {
	$titles = array( 'title' => 'Titre', 'artist' => 'Artiste', 'other' => 'Autre (album, année...)', 'link' => 'Lien (URL Youtube, Spotify...)' );
	return $titles[$name];
}


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function campus_post_save_postdata( $post_id ) {

	/*
	 * We need to verify this came from the our screen and with proper authorization,
	 * because save_post can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['campus_post_page_nonce'] ) )
	  return $post_id;

	$nonce = $_POST['campus_post_page_nonce'];

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $nonce, 'campus_post_page' ) )
	    return $post_id;

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	    return $post_id;

	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) )
	      return $post_id;

	if( isset( $_POST['hide-sidebar'] ) ) {
		if( $_POST['hide-sidebar'] == 'on' )
			update_post_meta( $post_id, '_hide_sidebar', 'on' );
		else
			delete_post_meta( $post_id, '_hide_sidebar' );
	}

	/* OK, its safe for us to save the data now. */
	if( isset( $_POST['post-playlist'] ) && is_array( $_POST['post-playlist'] ) ) {

		$playlist_data = array();

		//print_r($_POST);

		foreach( $_POST['post-playlist'] as $fields ) {
			if( trim($fields['title']) == '' && trim($fields['artist']) == '' && trim($fields['other']) == '' && trim($fields['link']) == '' )
				continue;

			$playlist_data[] = array(
				'title' => sanitize_text_field( $fields['title'] ),
				'artist' => sanitize_text_field( $fields['artist'] ),
				'other' => sanitize_text_field( $fields['other'] ),
				'link' => filter_var( $fields['link'], FILTER_VALIDATE_URL ) ? $fields['link'] : ''
			);
		}

		// Update the meta field in the database.
		update_post_meta( $post_id, '_playlist', $playlist_data );

	}
}

add_action( 'save_post', 'campus_post_save_postdata' );
