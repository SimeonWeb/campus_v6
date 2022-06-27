<?php
/**
 * Add some features to powerpress
 */

class SMN_PowerPress {

	function __construct() {
		add_action( 'admin_enqueue_scripts',           array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'wp_ajax_add_category_podcasting', array( $this, 'add_category_podcasting' ) );
		add_action( 'wp_ajax_reset_podcast_artwork',   array( $this, 'reset_podcast_artwork' ) );

		/**
		 * Add podcast creation to taxonomy
		 * @see manage_{$screen->id}_columns
		 */
		add_filter( 'manage_category_custom_column', array( $this, 'manage_custom_column' ), 10, 3 );
		add_filter( 'manage_edit-category_columns',  array( $this, 'manage_columns' ) );
	}

	function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if( in_array( $screen->id, array( 'powerpress_page_powerpress/powerpressadmin_taxonomyfeeds', 'edit-category', 'edit-category' ) ) ) {
			wp_enqueue_script( 'smn-powerpress', get_theme_file_uri( '/assets/js/admin-powerpress-addons.min.js' ), array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
			wp_enqueue_style( 'smn-admin', get_theme_file_uri( '/assets/css/admin.css' ), array(), wp_get_theme()->get( 'Version' ) );
		}
	}

	function manage_columns( $columns ) {

		$columns['smn_powerpress_category_podcasting'] = sprintf( '<img src="%s"><span class="screen-reader-text">%s</span>', plugins_url( 'powerpress/powerpress_ico.png' ), __( 'Podcasting', 'smn' ) );

		return $columns;
	}

	function manage_custom_column( $columns, $column, $term_id ) {

		switch( $column ) {
			case 'smn_powerpress_category_podcasting':

				$settings = get_option( 'powerpress_cat_feed_' . $term_id, [] );
				$term = get_term( $term_id );

				if( ! empty( $settings ) ) {
					printf( '<a href="%s" style="text-decoration:none" class="button button-link dashicons-before dashicons-edit" title="%s"></a>', add_query_arg( [ 'page' => 'powerpress%2Fpowerpressadmin_categoryfeeds.php', 'action'=> 'powerpress-editcategoryfeed', 'cat' => $term_id ], admin_url( 'admin.php' ) ), __( 'Edit taxonomy podcasting', 'smn' ) );
				} else {
					printf( '<a href="#" style="text-decoration:none" class="button button-link button-smn_powerpress_category_podcasting dashicons-before dashicons-plus" data-term_id="%d" data-has_description="%s" title="%s"></a>', $term_id, $term->description !== '', __( 'Add taxonomy podcasting', 'smn' ) );
				}
				break;
		}

		return $columns;
	}

	function add_category_podcasting() {

		if( empty( $_POST['action'] ) || $_POST['action'] !== 'add_category_podcasting' )
			return;

		if( empty( $_POST['term_id'] ) )
			return;

		$term = get_term( (int) $_POST['term_id'] );

		if( ! $term instanceof WP_Term )
			wp_send_json_error( new WP_Error( 'Error', 'No term found for this id' ) );

		$settings = get_option( 'powerpress_cat_feed_' . $term->term_id, [] );

		if( empty( $settings ) ) {

			// Add the feed to the category podcasting list
			$Settings = get_option('powerpress_general');
			if( empty($Settings['custom_cat_feeds']) ) {
				$Settings['custom_cat_feeds'] = [$term->term_id];
			}
			if( !in_array($term->term_id, $Settings['custom_cat_feeds']) ) {
				$Settings['custom_cat_feeds'][] = $term->term_id;
				powerpress_save_settings($Settings);
			}

			// Get default feed
			$feed = get_option( 'powerpress_feed', [] );

			// Set term vars
			$feed['title'] = $term->name . ' - ' . get_bloginfo( 'name' );
			$feed['description'] = $term->description;
			$feed['url'] = get_term_link( $term );

			$feed['itunes_subtitle'] = $term->name;
			$feed['itunes_summary'] = $term->description;
			$feed['itunes_explicit'] = 2;

			// Add contact email
			$feed['email'] = get_option( 'podcast_email', 'programmation@radiocampusangers.com' );

			// Add default category
			$feed['apple_cat_1'] = $this->get_term_itunes_default_category( $term->term_id );

			// Set artwork
			$artwork_id = get_term_meta( $term->term_id, 'podcast_thumbnail_id', true );
			if( $artwork_id ) {
				$feed['itunes_image'] = wp_get_attachment_url( $artwork_id );
			}

			// Save feed
			powerpress_save_settings( $feed, 'powerpress_cat_feed_' . $term->term_id);

			$url = get_term_feed_link( $term->term_id, $term->taxonomy, 'rss2');
			wp_send_json_success( $url );
		}

		wp_send_json_error( new WP_Error( 'Error', 'Podcast already exists' ) );
	}

	function get_term_itunes_default_category( $term_id ) {

		// $Categories = powerpress_itunes_categories();
		$shows_category_ids = campus_get_shows_category_ids();
		$show_itunes_category_ids = [
			"talk"  => "12-00",
			"music" => "11-00",
			"other" => "04-00"
		];

		$term_parents = get_ancestors( $term_id, "category" );
		$term_parents[] = $term_id;

		$itunes_category = $show_itunes_category_ids["other"];

		if ( count( $term_parents ) > 1 ) {
			foreach( $term_parents as $term_parent_id ) {
				foreach( $shows_category_ids as $key => $shows_category_id ) {
					if ( $term_parent_id != $shows_category_id )
						continue;
					
					$itunes_category = $show_itunes_category_ids[$key];
				}
			}
		}

		return $itunes_category;
	}

	function reset_podcast_artwork() {

		if( empty( $_POST['action'] ) || $_POST['action'] !== 'reset_podcast_artwork' )
			return;

		if( empty( $_POST['term_id'] ) )
			return;

		$term = get_term( (int) $_POST['term_id'] );

		if( ! $term instanceof WP_Term )
			wp_send_json_error( new WP_Error( 'Error', 'No term found for this id' ) );

		$artwork = new SMN_Podcast_Artwork( $term );
		$url = '';

		if( $artwork->attachment_id ) {
			$url = wp_get_attachment_url( $artwork->attachment_id );
			wp_send_json_success( $url );
		}

		wp_send_json_error( new WP_Error( 'Error', 'No artwork created' ) );
	}
}

new SMN_PowerPress();
