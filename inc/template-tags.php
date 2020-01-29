<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

if ( ! function_exists( 'campus_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function campus_posted_on() {

	// Get the author name; wrap it in a link.
	$byline = sprintf(
		/* translators: %s: post author */
		__( 'by %s', 'campus' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . get_the_author() . '</a></span>'
	);

	// Finally, let's write all of this to the page.
	echo '<span class="posted-on">' . campus_time_link() . '</span><span class="byline"> ' . $byline . '</span>';
}
endif;


if ( ! function_exists( 'campus_time_link' ) ) :
/**
 * Gets a nicely formatted string for the published date.
 */
function campus_time_link() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		get_the_date( DATE_W3C ),
		get_the_date(),
		get_the_modified_date( DATE_W3C ),
		get_the_modified_date()
	);

	// Wrap the time string in a link, and preface it with 'Posted on'.
	return sprintf(
		/* translators: %s: post date */
		__( '<span class="screen-reader-text">Posted on </span>%s', 'campus' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);
}
endif;


/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function campus_entry_footer() {

	/* translators: used between list items, there is a space after the comma */
	$separate_meta = __( ', ', 'campus' );

	// Get Categories for posts.
	$categories_list = get_the_category_list( $separate_meta );

	// Get Tags for posts.
	$tags_list = get_the_tag_list( '', $separate_meta );

	// Get player data
	$player_data_html = campus_get_post_player_data();
	$player_data = campus_get_post_player_data( false );

	if( $player_data ) {

		printf( '<p class="entry-podcast podcast-link with-icon"><a href="#" rel="player"%s><span class="icon">%s</span><span class="icon-title">%s</span></a></p>',
			$player_data_html,
			campus_get_svg( array( 'icon' => 'play', 'class' => 'icon-small' ) ) . campus_get_svg( array( 'icon' => 'pause', 'class' => 'icon-small' ) ),
			'Écouter le podcast'
		);

		printf( '<p class="entry-download with-icon"><a href="%s" download target="_blank">%s<span class="icon-title">%s</span></a></p>',
			$player_data['url'],
			campus_get_svg( array( 'icon' => 'download', 'class' => 'icon-small' ) ),
			'Télécharger le podcast'
		);
	}

	if ( $categories_list ) {

		printf( '<p class="entry-categories with-icon">%s<span class="icon-title">%s</span></p>',
			campus_get_svg( array( 'icon' => 'category', 'class' => 'icon-small' ) ),
			$categories_list
		);

	}

	if ( $tags_list ) {

		printf( '<p class="entry-tags with-icon">%s<span class="icon-title">%s</span></p>',
			campus_get_svg( array( 'icon' => 'tags', 'class' => 'icon-small' ) ),
			$tags_list
		);

	}

	printf( '<p class="entry-author with-icon">%s<span class="icon-title">%s</span></p>',
		campus_get_svg( array( 'icon' => 'user', 'class' => 'icon-small' ) ),
		get_the_author()
	);

	edit_post_link( __( 'Edit', 'campus' ), '<p class="entry-edit with-icon">' . campus_get_svg( array( 'icon' => 'edit', 'class' => 'icon-small' ) ) . '<span class="icon-title">', '</span></p>' );


}

/**
 * Print HTML with meta information for podcast and share
 *
 */
function campus_entry_meta() {
	// Get player data
	$player_data_html = campus_get_post_player_data();
	$player_data = campus_get_post_player_data( false );

	if( $player_data ) {

		echo '<div class="entry-player-links">';

			printf( '<span class="meta-link podcast-link"><a href="#" rel="player" title="%3$s"%1$s>%2$s</a></span>',
				$player_data_html,
				campus_get_svg( array( 'icon' => 'play' ) ) . campus_get_svg( array( 'icon' => 'pause' ) ),
				'Écouter le podcast'
			);

			printf( '<span class="meta-link"><a href="%1$s" download target="_blank" title="%3$s">%2$s</a></span>',
				$player_data['url'],
				campus_get_svg( array( 'icon' => 'download' ) ),
				'Télécharger le podcast'
			);

		echo '</div>';
	}

	campus_social_buttons();
}


if ( ! function_exists( 'campus_edit_link' ) ) :
/**
 * Returns an accessibility-friendly link to edit a post or page.
 *
 * This also gives us a little context about what exactly we're editing
 * (post or page?) so that users understand a bit more where they are in terms
 * of the template hierarchy and their content. Helpful when/if the single-page
 * layout with multiple posts/pages shown gets confusing.
 */
function campus_edit_link() {
	edit_post_link(
		sprintf(
			/* translators: %s: Name of current post */
			__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'campus' ),
			get_the_title()
		),
		'<span class="edit-link">',
		'</span>'
	);
}
endif;


/**
 * Get page title with hierarchical prefix
 *
 */
function campus_get_page_hierachical_title() {
	global $wp_query, $post;

	//print_r($wp_query);

	if( is_page() ) {

		$parent = get_post( $post->post_parent );

		if( $post->post_parent ) {

			$return = sprintf( '<a href="%s" class="title-hierarchical-prefix">%s</a> <span class="title-hierarchical">%s</span>',
				get_permalink( $parent ),
				apply_filters( 'the_title', $parent->post_title ),
				apply_filters( 'the_title', $post->post_title )
			);
		} else {

			$return = '<span class="title-hierarchical-prefix">' . apply_filters( 'the_title', $post->post_title ) . '</span>';
		}

	} else if( is_single() ) {

		$category = campus_get_the_category_by_priority();

		if( isset( $category ) ) {

			$return = sprintf( '<a href="%s" class="title-hierarchical-prefix">%s</a> <span class="title-hierarchical to-top">%s</span>',
				get_term_link( $category ),
				$category->name,
				apply_filters( 'the_title', $post->post_title )
			);
		} else {

			$return = '<span class="title-hierarchical-prefix">' . apply_filters( 'the_title', $post->post_title ) . '</span>';
		}

	} else if( is_post_type_archive() ) {

		if( is_post_type_archive( 'album' ) || is_post_type_archive( 'block' ) ) {

			$labels = get_taxonomy_labels( get_taxonomy( 'album_playlist' ) );
			$return = '<span class="title-hierarchical-prefix">' . $labels->name . '</span>';

		} else {

			$return = '<span class="title-hierarchical-prefix">' . get_the_archive_title() . '</span>';
		}

	} else if( is_archive() ) {

		$queried_object = get_queried_object();

		if( substr( $queried_object->taxonomy, 0, 5 ) == 'album' ) {

			$taxonomy_name = '';

			if( $queried_object->taxonomy != 'album_playlist' ) {
				$labels = get_taxonomy_labels( get_taxonomy( 'album_playlist' ) );
				$taxonomy_name .= $labels->name . ' / ';
			}

			$labels = get_taxonomy_labels( get_taxonomy( $queried_object->taxonomy ) );

			$taxonomy_name .= $labels->name;

			$return = sprintf( '<span class="title-hierarchical-prefix">%s</span> <span class="title-hierarchical">%s</span>',
				$taxonomy_name,
				get_the_archive_title()
			);

		} else if( $parent_name = campus_get_term_parent_name( $queried_object->term_id, $queried_object->taxonomy, false ) ) {

			$return = sprintf( '<span class="title-hierarchical-prefix">%s</span> <span class="title-hierarchical">%s</span>',
				$parent_name,
				get_the_archive_title()
			);
		} else {

			$return = '<span class="title-hierarchical-prefix">' . get_the_archive_title() . '</span>';
		}

	} else if( is_search() ) {

		$return = sprintf( '<a href="#search-box" class="box-link search-link"><span class="title-hierarchical-prefix">%s</span> <span class="title-hierarchical">%s</span></a>',
			'Recherche',
			have_posts() ? get_search_query() : __( 'Nothing Found', 'campus' )
		);

	}

	return $return;
}
