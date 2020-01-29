<?php

/**
 * Admin Notices
 *
 */

class Campus_Update_Add_Admin_Notice {
    private $message = array();

    function __construct( $type, $message ) {
        $this->type = $type;
        $this->message = $message;

        add_action( 'admin_notices', array( $this, 'render' ) );
    }

    function render() {
        printf( '<div class="notice notice-%1$s"><p>%2$s</p></div>', esc_attr( $this->type ), $this->message );
    }
}

/**
 * Copy and paste data of old termmeta table to the new one
 *
 */
function campus_update_term_meta_table() {
	global $wpdb;

	$old_table = $wpdb->prefix . 'campus_termmeta';
	$new_table = $wpdb->prefix . 'termmeta';
	$old_table_exists = $wpdb->query( "SHOW TABLES LIKE '$old_table'" );

	if( $old_table_exists ) {
		$wpdb->query( "INSERT $new_table SELECT * FROM $old_table" );
		$wpdb->query( "DROP TABLE IF EXISTS $old_table" );

		new Campus_Update_Add_Admin_Notice( 'warning', sprintf( '<strong>Term Meta :</strong> La table %s à été supprimée, la table %s est maintenant utilisée.', $old_table, $new_table ) );
	}
}
add_action( 'admin_init', 'campus_update_term_meta_table', 10 );

/**
 * Update termmeta data structure
 *
 */
function campus_update_term_meta_structure() {

	// For categories
	$terms = get_terms( array(
	    'hide_empty' => false,
	) );

	if( $terms ) {

		$total = count( $terms );
		$updated_rows = 0;

		foreach( $terms as $term ) {

			$old_social_link_values = get_term_meta( $term->term_id, 'social_links', true );

			if( ! empty( $old_social_link_values ) ) {

				foreach( $old_social_link_values as $name => $value ) {
					if( $name == 'external' && ! empty( $value['url'] ) ) {
						update_term_meta( $term->term_id, $name, array( 'url' => $value['url'], 'title' => $value['title'] ) );

					} else if( ! empty( $value['url'] ) ) {
						update_term_meta( $term->term_id, $name, $value['url'] );
					}
				}

				delete_term_meta( $term->term_id, 'social_links' );

				$updated_rows++;
			}
		}

		if( $updated_rows > 0 )
			new Campus_Update_Add_Admin_Notice( 'warning', sprintf( '<strong>Social Links :</strong> %d categories sur %d ont été mises à jour', $updated_rows, $total ) );
	}
}
add_action( 'admin_init', 'campus_update_term_meta_structure', 11 );

/**
 * Update campus_daily_playlist table
 *
 */
function campus_update_campus_daily_playlist_table() {
	global $wpdb;

    if( ! current_user_can( 'manage_categories' ) )
        return;

    new Campus_Update_Add_Admin_Notice( 'warning', sprintf( '<strong>Programmation Automatique :</strong> Mise à jour des données' ) );

	$old_table = $wpdb->prefix . 'campus_playlist';
	$new_table = Campus_Daily_Playlist_Options::create_table();

	// If new table exists
	if( $new_table == $wpdb->prefix . CAMPUS_DAILY_PLAYLIST_TABLE ) {

		$is_data = $wpdb->get_results( "SELECT * FROM $new_table LIMIT 1");

		// If table contains no data, copy from old table
		if( ! $is_data ) {

			$excluded_terms = Campus_Daily_Playlist::get_excluded_terms();
			$sql_array_excluded_terms = array();
			$sql_excluded_terms = '';

			if( $excluded_terms ) {
				foreach( $excluded_terms as $excluded_term ) {
					$sql_array_excluded_terms[] = "`category` NOT LIKE '%$excluded_term%'";
				}
				$sql_excluded_terms = "WHERE " . join( ' AND ', $sql_array_excluded_terms );
			}

			$inserts = $wpdb->query( "
				INSERT INTO $new_table
					(`time`, `duration`, `intro`, `category`, `artist_term_id`, `title_term_id`, `genre_term_id`)
				SELECT
					`time`, `duration`, `intro`, `category`, `artist`, `title`, `type`
				FROM $old_table
				$sql_excluded_terms"
			);

			new Campus_Update_Add_Admin_Notice( 'warning', sprintf( '<strong>Programmation Automatique :</strong> La table à été mise à jour.' ) );
		}

		$results = $wpdb->get_results( "
			SELECT *
			FROM $new_table
			WHERE
				`artist_term_id` REGEXP '[A-Z|a-z|\s]+'
			AND
				`title_term_id` REGEXP '[A-Z|a-z|\s]+'
			AND
				`genre_term_id` REGEXP '[A-Z|a-z|\s]+'
            ORDER BY `time` DESC
            LIMIT 50;",
			ARRAY_A
		);

		$total = count( $results );
		$updated_rows = 0;

		if( $results ) {

			foreach( $results as $result ) {

				// Function prepare_song need those fields
				$result['artist'] = $result['artist_term_id'];
				$result['title'] = $result['title_term_id'];
				$result['genre'] = $result['genre_term_id'];

				$prepared_result = Campus_Daily_Playlist_Options::prepare_song( $result );

				if( $prepared_result ) {

					$updated = $wpdb->update(
						$new_table,
						array(
							'artist_term_id' => $prepared_result['artist_term_id'],
							'title_term_id' => $prepared_result['title_term_id'],
							'genre_term_id' => $prepared_result['genre_term_id']
						),
						array( 'artist_term_id' => $result['artist_term_id'] ),
						array(
							'%d',
							'%d',
							'%s'
						),
						array( '%s' )
					);

					if( $updated )
						$updated_rows += $updated;
				}
			}

			new Campus_Update_Add_Admin_Notice( 'warning', sprintf( '<strong>Programmation Automatique :</strong> %d lignes sur %d ont été mises à jour', $updated_rows, $total ) );
		}
	}
}
//add_action( 'admin_init', 'campus_update_campus_daily_playlist_table', 11 );
