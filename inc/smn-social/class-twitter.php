<?php
/**
 * Add TwitterOAuth class
 *
 * See: https://twitteroauth.com/
 */

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Twitter.app Class
 *
 * See: https://developer.twitter.com/en/docs/tweets/timelines/api-reference/get-statuses-user_timeline
 * See: https://developer.twitter.com/en/docs/tweets/tweet-updates
 * @version 1.1
 */
class SMN_Twitter_App extends SMN_Social {

	/**
     * Custom parameters
     * @$string
     */
	var $type = 'twitter';

	/**
     * Custom parameters
     * @$array
     */
	var $params = array(
		'consumer_key' 			=> '',
		'consumer_secret' 		=> '',
		'access_token' 			=> '',
		'access_token_secret' 	=> '',
		'image_min_width'		=> '600',
		'image_min_height'		=> '600',
		'expire'				=> 60 * 60 // 1 hour
	);

	function __construct( $id = null, $count = 1 ) {

		if( SMN_SOCIAL_PLUGIN_DEBUG && SMN_SOCIAL_PLUGIN_CACHE_DISABLED )
			 $this->debug['cache'] = 'SMN_SOCIAL_PLUGIN_CACHE_DISABLED: Cache disabled';

		// Get app infos
		$options = get_option( '_social_twitter', array() );
		$this->params = wp_parse_args( $options, $this->params );

		$id = $id ? $id : apply_filters( 'smn_twitter_default_username', $options['username'], $this );

		if( empty( $options['consumer_key'] ) ||
			empty( $options['consumer_secret'] ) ||
			empty( $options['access_token'] ) ||
			empty( $options['access_token_secret'] ) ||
			! $id )
			return;

		// Status
		if( ctype_digit($id) ) {
			$this->api = 'statuses/show';
			$this->api_params = array(
				'id' => $id
			);
		// User
		} else {
			$this->api = 'statuses/user_timeline';
			$this->api_params = array(
				'screen_name' => $id,
				'count' => $count,
				'include_rts' => false,
				'tweet_mode' => 'extended'
			);
		}

		// Filters params
		$this->api_params = apply_filters( 'smn_twitter_api_params', $this->api_params, $this );

		// Define cache params
		$this->cache_key = get_parent_class() . '_' . get_class() . '_' . join( '_', $this->api_params );

		$this->process();

		if( SMN_SOCIAL_PLUGIN_DEBUG )
			echo '<pre>',print_r($this),'</pre>';
	}

	private function process() {

		// Get cached data
		$this->data = get_transient( $this->cache_key );

		if ( SMN_SOCIAL_PLUGIN_CACHE_DISABLED || false === $this->data ) {

			// Connect and get results
			$connection = new TwitterOAuth( $this->params['consumer_key'], $this->params['consumer_secret'], $this->params['access_token'], $this->params['access_token_secret'] );

			// cURL offers really easy proxy support.
			$proxy = new WP_HTTP_Proxy();

			if ( $proxy->is_enabled() ) {

				$auth = '';
				if ( $proxy->use_authentication() ) {
					$auth = $proxy->authentication();
				}

				$connection->setProxy( array(
				    'CURLOPT_PROXY' => $proxy->host(),
				    'CURLOPT_PROXYUSERPWD' => $auth,
				    'CURLOPT_PROXYPORT' => $proxy->port()
				) );
			}

			$statuses = $connection->get( $this->api, $this->api_params );

			// Store results
			$this->statuses = ! is_array( $statuses ) ? array( $statuses ) : $statuses;

			// Formate statuses content
			$this->overload_statuses();

			// Populate data to store
			$this->populate_data();

			// Put data in cache
			set_transient( $this->cache_key, $this->data, $this->params['expire'] );
		}
	}

	private function overload_statuses() {

		foreach( $this->statuses as $index => $status ) {

			// Check if there is media
			$this->statuses[$index]->has_media = isset( $status->extended_entities->media ) ? true : false;
			$this->statuses[$index]->media = isset( $status->extended_entities->media[0] ) ? $status->extended_entities->media[0] : false;

			if( isset( $this->statuses[$index]->media->type ) && ( $this->statuses[$index]->media->type == 'video' || $this->statuses[$index]->media->type == 'animated_gif' ) ) {

				$this->statuses[$index]->media->type = 'video';

				if( ! empty( $this->statuses[$index]->media->video_info->variants ) ) {
					foreach( $this->statuses[$index]->media->video_info->variants as $variant ) {
						if( $variant->content_type == 'video/mp4' ) {
							$this->statuses[$index]->media->video_url = $variant->url;
							break;
						}
					}
				}

				if( empty( $this->statuses[$index]->media->video_url ) )
					$this->statuses[$index]->has_media = false;

			}
		}
	}

	private function populate_data() {

		$this->data = array();

		foreach( $this->statuses as $index => $status ) {

			$data = $this->default_data;

			$data['type'] 		= $this->type;
			$data['date'] 		= date( DATE_W3C, strtotime( $status->created_at ) );
			$data['text'] 		= isset( $status->full_text ) ? $status->full_text : $status->text;
			$data['username'] 	= $status->user->screen_name;
			$data['author'] 	= $status->user->screen_name;
			//$data['url'] 		= isset( $status->entities->urls[0]->url ) ? $status->entities->urls[0]->url : sprintf( 'https://twitter.com/%s/status/%s', $status->user->screen_name, $status->id );
			$data['url'] 		= sprintf( 'https://twitter.com/%s/status/%s', $status->user->screen_name, $status->id );

			if( $status->has_media ) {

				$data['media'] = array(
					'type' => $status->media->type == 'photo' ? 'image' : $status->media->type,
					'url'  => $status->media->media_url
				);

				if( $status->media->type == 'video' )
					$data['media']['video_url'] = $status->media->video_url;

			}

			$this->data[] = $data;
		}
	}
}
