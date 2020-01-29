<?php
/**
 * Additional widgets
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 */

/**
 * Widget social
 *
 * @use smn-social
 */
class Campus_Widget_Social extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'social_widget',
			'description' => 'Affiche les derniers posts Facebook/Twitter/Instagram...',
		);
		parent::__construct( 'social_widget', 'Social Widget', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$statuses = smn_get_the_social_statuses( /*
array(
			'facebook_id' 	 => $instance['facebook_id'],
			'twitter_id' 	 => $instance['twitter_id'],
			'posts_per_flux' => $instance['posts_per_flux']
		)
*/ );

		if( $statuses ) {

			echo '<ul class="social-statuses">';

			foreach( $statuses as $time => $time_statuses ) {

				foreach( $time_statuses as $status ) {

					smn_social_status_template( $status, 'li' );
				}
			}

			echo '</ul>';
		}

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title  		= ! empty( $instance['title'] ) ? $instance['title'] : '';
		$posts_per_flux = ! empty( $instance['posts_per_flux'] ) && is_numeric( $instance['posts_per_flux'] ) ? (int) $instance['posts_per_flux'] : 10;
		$facebook_id 	= ! empty( $instance['facebook_id'] ) ? $instance['facebook_id'] : '';
		$twitter_id 	= ! empty( $instance['twitter_id'] ) ? $instance['twitter_id'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title', 'campus' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'posts_per_flux' ) ); ?>"><?php esc_attr_e( 'Nombre de statuts pour chaque flux', 'campus' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'posts_per_flux' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts_per_flux' ) ); ?>" type="number" max="10" value="<?php echo esc_attr( $posts_per_flux ); ?>">
		</p>
		<?php
		if( class_exists( 'SMN_Facebook_App' ) ) {
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'facebook_id' ) ); ?>"><?php esc_attr_e( 'Facebook ID', 'campus' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'facebook_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'facebook_id' ) ); ?>" type="text" value="<?php echo esc_attr( $facebook_id ); ?>">
			<span class="description">Votre nom d'utilisateur ou de page, sans le @</span>
		</p>
		<?php
		}
		if( class_exists( 'SMN_Twitter_App' ) ) {
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'twitter_id' ) ); ?>"><?php esc_attr_e( 'Twitter ID', 'campus' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'twitter_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'twitter_id' ) ); ?>" type="text" value="<?php echo esc_attr( $twitter_id ); ?>">
			<span class="description">Votre nom d'utilisateur, sans le @</span>
		</p>
		<?php
		}

	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['posts_per_flux'] = is_numeric( $new_instance['posts_per_flux'] ) ? (int) $new_instance['posts_per_flux'] : 10;
		$instance['facebook_id'] = ( ! empty( $new_instance['facebook_id'] ) ) ? str_replace('@', '', $new_instance['facebook_id'] ) : '';
		$instance['twitter_id'] = ( ! empty( $new_instance['twitter_id'] ) ) ? str_replace('@', '', $new_instance['twitter_id'] ) : '';

		return $instance;
	}
}

/**
 * Widget playlists
 *
 * @use campus-playlist
 */
class Campus_Widget_Playlists extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'playlists_widget',
			'description' => 'Affiche toutes les playlists',
		);
		parent::__construct( 'playlists_widget', 'Playlists', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$query_args = array(
			'taxonomy'     => 'album_playlist',
			'orderby'      => 'slug',
			'order'	       => 'DESC'
		);

		if( ! is_user_logged_in() || ! current_user_can( 'manage_album_terms' ) ) {
			$query_args['meta_key'] 	  = 'album_playlist_visibility';
			$query_args['meta_compare'] = 'EXIST';
		}

		$playlists = get_terms( $query_args );

		if( $playlists ) {

			echo '<ul class="playlists">';

			foreach( $playlists as $playlist ) {

				printf( '<li><a href="%s">%s</a></li>',
					get_term_link( $playlist ),
					$playlist->name
				);
			}

			echo '</ul>';
		}

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$labels 		= get_taxonomy_labels( get_taxonomy( 'album_playlist' ) );
		$title  		= ! empty( $instance['title'] ) ? $instance['title'] : $labels->name;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title', 'campus' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php

	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

/**
 * Register widgets
 *
 */
function campus_register_widgets() {

    register_widget( 'Campus_Widget_Social' );
    register_widget( 'Campus_Widget_Playlists' );

}
add_action( 'widgets_init', 'campus_register_widgets' );
