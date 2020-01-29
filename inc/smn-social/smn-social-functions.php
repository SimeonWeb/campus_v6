<?php
/**
 * Social formating functions
 *
 */

/**
 * Parse text to add class on username, hashtags...
 *
 */
function smn_social_parse_text( $text ) {

	if( $text == 'Timeline Photos' )
		$text = 'Photos du journal';
	elseif( $text == 'Cover Photos' )
		$text = 'Photos de couverture';
	elseif( $text == 'Profile Pictures' )
		$text = 'Photos de profil';
	elseif( $text == 'Mobile Uploads' )
		$text = 'Téléchargements mobiles';

	// Limit to 50 words and add ellipsis
	$text = wp_trim_words( $text, 50 );

	// wrap hashtags & usernames
	$text = preg_replace( '/(#\S+|@\S+)/', '<span class="hashtag-or-username">$0</span>', $text );

	// add insecable space
	$text = str_replace( array( ' :', ' ;' ), array( '&nbsp;:', '&nbsp;;' ), $text );

	// trim url
	$text = str_replace( array( 'http://', 'https://' ), '', $text );

	return $text;
}

/**
 * Parse date
 *
 */
function smn_social_parse_date( $date ) {

	$today = time();
	$created = is_int( $date ) ? $date : strtotime( $date );

	$since = ( $today - $created ) / 60;

	if( $since == 0 )
		$output = 'À l\'instant ';

	else if( $since == 1 )
		$output = 'Il y a 1 minute ';

	else if( $since < 60 )
		$output = sprintf( 'Il y a %d minutes', $since );

	else if( $since < 120 )
		$output = 'Il y a 1 heure ';

	else if( $since < 1440 )
		$output = sprintf( 'Il y a %d heures', $since / 60 );

	else if( $since < 2880 )
		$output = 'Il y a 1 jour ';

	else if( $since < 20160 )
		$output = sprintf( 'Il y a %d jours', $since / 60 / 24 );

	else
		$output = date_i18n( get_option( 'date_format' ), $created );

	return $output;
}

/**
 * Merge all statuses flux
 *
 */
function smn_social_get_merge_statuses() {

	$args = func_get_args();

	if( func_num_args() == 1 && is_array( $args ) )
		$args = $args[0];

	if( empty( $args ) )
		return 'SMN Social : Veuillez renseigner les flux !';

	$statuses = array();

	foreach( $args as $class ) {

		if( ! empty( $class->data ) ) {

			foreach( $class->data as $status ) {

				$key = strtotime( $status['date'] );
				$statuses[$key][] = $status;
			}
		}
	}

	// Sort by date desc
	krsort( $statuses );

	return $statuses;
}

/**
 * Template for social status
 *
 */
function smn_social_status_template( $status, $tag = 'div' ) {

	// Link
	$url = ! empty( $status['url'] ) ? esc_url( $status['url'] ) : '#';

	// Icon
	$icon = campus_get_svg( array( 'icon' => $status['type'] ) );

	// Content
	$text = smn_social_parse_text( $status['text'] );

	if ( ! empty( $status['media'] ) ) {

		$media = '';

		if( $status['media']['type'] == 'video'  ) {
			$media .= '<video src="' . esc_url( $status['media']['video_url'] ) . '" preload="auto" loop muted autoplay /></video>';
		}

		$media .= '<img src="' . esc_url( $status['media']['url'] ) . '" />';

		if( $status['count'] > 1 ) {
			$more = $status['count'] - 1;
			$media .= sprintf( '<span class="status-counter" title="%s">%s</span>', sprintf( _n( '+ %s photo', '+ %s photos', $more ), $more ), campus_get_svg( array( 'icon' => 'album', 'class' => 'icon-small' ) ) );
		}

		$content = sprintf( '<figure class="status-image">%1$s<figcaption class="status-text">%2$s</figcaption></figure>',
			$media,
			$text
		);
	} else {

		$content = sprintf( '<span class="status-text">%s</span>',
			$text
		);
	}

	// Time
	if ( ! empty( $status['updated'] ) ) {

		$time = sprintf( '<time class="status-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>',
			date( DATE_W3C, strtotime( $status['date'] ) ),
			smn_social_parse_date( $status['date'] ),
			date( DATE_W3C, strtotime( $status['updated'] ) ),
			smn_social_parse_date( $status['updated'] )
		);
	} else {

		$time = sprintf( '<time class="status-date published updated" datetime="%1$s">%2$s</time>',
			date( DATE_W3C, strtotime( $status['date'] ) ),
			smn_social_parse_date( $status['date'] )
		);
	}

	// echo status
	printf( '<%1$s class="social-status"><a href="%2$s" class="status-link" target="_blank"><span class="status-icon">%3$s</span><span class="status-content">%4$s<span class="status-meta">%5$s</span></span></a></%1$s>',
		$tag,
		$url,
		$icon,
		$content,
		$time
	);
}

/**
 * Get all statuses
 *
 */
function smn_get_social_statuses( $args = array() ) {

	$default = array(
		'facebook_id' 	 => false,
		'twitter_id' 	 => false,
		'instagram_id' 	 => false,
		'linkedin_id' 	 => false,
		'posts_per_flux' => 10
	);

	$args = wp_parse_args( $args, $default );

	$all_statuses = array();

	// Facebook
	if ( class_exists( 'SMN_Facebook_App' ) )
		$all_statuses['facebook'] = new SMN_Facebook_App( $args['facebook_id'], 'posts', $args['posts_per_flux'] );

	// Twitter
	if ( class_exists( 'SMN_Twitter_App' ) )
		$all_statuses['twitter'] = new SMN_Twitter_App( $args['twitter_id'], $args['posts_per_flux'] );

	// Instagram
	if ( class_exists( 'SMN_Instagram_App' ) )
		$all_statuses['instagram'] = new SMN_Instagram_App( $args['instagram_id'], $args['posts_per_flux'] );

	if ( ! empty( $all_statuses ) ) {

		$statuses = smn_social_get_merge_statuses( $all_statuses );

		if( $statuses )
			return $statuses;
	}

	return false;
}

/**
 * Set once social_statuses to use global
 *
 */
function smn_get_the_social_statuses() {
	global $smn_social_statuses;

	if ( ! isset( $smn_social_statuses ) )
        $smn_social_statuses = smn_get_social_statuses();

	return $smn_social_statuses;
}

/**
 * Remove social widget from sidebar if there's no status
 *
 */
function smn_social_sidebars_widgets( $sidebars_widgets ) {

	// Use this after wp_query was defined
	if( get_queried_object_id() > 0 ) {

		foreach( $sidebars_widgets as $sidebar => $widgets ) {

			foreach( $widgets as $widget => $widget_id ) {

				if( substr( $widget_id, 0, 13 ) == 'social_widget' && ! smn_get_the_social_statuses() )
					unset($sidebars_widgets[$sidebar][$widget]);
			}
		}
	}

	return $sidebars_widgets;
}
add_filter( 'sidebars_widgets', 'smn_social_sidebars_widgets' );
