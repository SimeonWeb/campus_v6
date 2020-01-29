<?php
/**
 * Add Facebook Graph API
 *
 * See: https://developers.facebook.com/docs/php/gettingstarted
 * See: https://developers.facebook.com/tools/explorer
 */

/**
 * Facebook.app Class
 *
 * @version 1.2
 */
class SMN_Facebook_App extends SMN_Social {

	/**
     * Custom parameters
     * @$string
     */
	var $type = 'facebook';

	/**
     * Custom parameters
     * @$array
     */
	var $params = array(
		'username'				=> false,
		'api_url'				=> 'https://graph.facebook.com/',
		'api_version'			=> 'v2.8',
		'app_id' 				=> '',
		'app_secret' 			=> '',
		'access_token'			=> '',
		'image_min_width'		=> '600',
		'image_min_height'		=> '600',
		'expire'				=> 60 * 60 // 1 hour
	);

	function __construct( $id, $part = 'posts', $count = 1 ) {

		if( SMN_SOCIAL_PLUGIN_DEBUG && SMN_SOCIAL_PLUGIN_CACHE_DISABLED )
			 $this->debug['cache'] = 'SMN_SOCIAL_PLUGIN_CACHE_DISABLED: Cache disabled';

		// Get app infos
		$options = get_option( '_social_facebook', array() );
		$this->params = wp_parse_args( $options, $this->params );

		$id = is_array( $id ) && isset( $id['username'] ) ? $id['username'] : $id;
		$id = $id ? $id : apply_filters( 'smn_facebook_default_username', $options['username'], $this );

		if( empty( $options['app_id'] ) || empty( $options['app_secret'] ) || ! $id )
			return;

		$this->params['access_token'] = $this->params['app_id'] . '|' . $this->params['app_secret'];

		$this->api_params['id'] = $id;
		$this->api_params['part'] = $part;
		$this->api_params['count'] = $count;

		// Filters params
		$this->api_params = apply_filters( 'smn_facebook_api_params', $this->api_params, $this );

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

			// Set current object and from object
			$this->api_params['object'] = $this->get_facebook_object( $this->api_params['id'], false, true );
			$this->api_params['from'] = $this->get_facebook_from();

			// Get results
			$statuses = $this->api_params['part'] == 'albums' ? $this->get_facebook_albums() : $this->get_facebook_statuses();

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

			if( ! isset( $status->message ) && ! isset( $status->name ) ) {
				unset( $this->statuses[$index] );
				continue;
			}

			$media = false;
			$this->statuses[$index]->has_media = false;

			// Check if there is images
			if( isset( $status->images ) ) {
				$media = $this->parse_images_url( $status->images );
			} else if( $status->type == 'photo' && isset( $status->object_id ) ) {
				$media = $this->get_facebook_image_url( $status->object_id );
			}
			//else if( isset( $status->cover_photo ) ) {
			// 	$media = $this->get_facebook_image_url( $status->cover_photo->id );
			// }

			if( $media ) {
				$this->statuses[$index]->has_media = true;
				$this->statuses[$index]->media = $media;
			}

			// Check if there is video
			if( $status->type == 'video' && isset( $status->object_id ) ) {
				$object = $this->get_facebook_video_object( $status->object_id );

				if( ! empty( $object->source ) ) {
					$this->statuses[$index]->has_media = true;
					$this->statuses[$index]->video_url = $object->source;

					if( ! empty( $object->picture ) ) {
						$this->statuses[$index]->media = $object->picture;
					}
				}
			}

		}
	}

	private function populate_data() {

		$this->data = array();

		foreach( $this->statuses as $index => $status ) {

			$data = $this->default_data;

			$data['type'] 		= $this->type;
			$data['date'] 		= date( DATE_W3C, strtotime( $status->created_time ) );
			$data['update'] 	= isset( $status->updated_time ) ? date( DATE_W3C, $status->updated_time ) : false;
			$data['text'] 		= isset( $status->message ) ? $status->message : $status->name;
			$data['username'] 	= isset( $this->api_params['from']->username ) ? $this->api_params['from']->username : $this->username;
			$data['author'] 	= isset( $this->api_params['from']->name ) ? $this->api_params['from']->name : '';
			$data['url'] 		= $status->permalink_url;
			$data['count'] 		= isset( $status->count ) ? $status->count : false;

			if( ! empty( $status->description ) ) {
				if( ! preg_match( '/\n/', $data['text'] ) ) {
					$data['text'] .= "\n" . $status->description;
				}
			}

			if( $status->has_media ) {

				$data['media'] = array(
					'type' => $status->type,
					'url'  => $status->media
				);

				if( $status->type == 'video' )
					$data['media']['video_url'] = $status->video_url;

			}

			$this->data[] = $data;
		}
	}

	private function get_facebook_object( $object_id, $fields = '', $metadata = false ) {

		// Construct url request
		$url = add_query_arg( array(
		    'fields' 		=> $fields,
		    'metadata' 		=> $metadata,
		    'access_token'  => $this->params['access_token']
		), $this->params['api_url'] . $this->params['api_version'] . '/' . $object_id );

		// Send request
		$request = $this->file_get_contents_curl( $url );
		$response = json_decode( $request );

		if( SMN_SOCIAL_PLUGIN_DEBUG && isset( $response->error ) ) {
			$key = isset( $this->debug['get_facebook_object'] ) ? count( $this->debug['get_facebook_object'] ) : 0;
			$this->debug['get_facebook_object'][$key]['url'] = $url;
			$this->debug['get_facebook_object'][$key]['response'] = $response;
		}

		return $response;
	}

	private function get_facebook_from() {

		$id = $this->api_params['id'];

		if( ! isset( $this->api_params['object'] ) )
			$this->api_params['object'] = $this->get_facebook_object( $id, false, true );

		$object = $this->api_params['object'];

		if( isset( $object->metadata->type ) ) {
			if( $object->metadata->type == 'photo' || $object->metadata->type == 'post' ) {
				$from_object = $this->get_facebook_object( $id, 'from{name,username}', false );
				$from_object = $from_object->from;
			} elseif( $object->metadata->type == 'page' ) {
				$from_object = $this->get_facebook_object( $id, 'name,username', false );
			}

			return $from_object;
		}

		return false;
	}

	private function get_facebook_statuses() {

		$params = $this->api_params;

		if( SMN_SOCIAL_PLUGIN_DEBUG )
			$this->debug['get_facebook_statuses'] = array( 'object->metadata->type' => isset( $params['object']->metadata->type ) ? $params['object']->metadata->type : false );

		// If type is not defined (user can't be called by username, page or post is not public or current user have'nt rights on it)
		if( ! isset( $params['object']->metadata->type ) )
			return false;

		$type = $params['object']->metadata->type;

		if( $type == 'photo' || $type == 'post' ) {
			$endpoint = $params['id'];
			$fields = 'message,name,images,permalink_url,link,created_time';
		} elseif( $type == 'page' ) {
			$endpoint = $params['from']->id . '/posts';
			$fields = 'message,description,name,permalink_url,link,object_id,created_time,type';
		}

		if( SMN_SOCIAL_PLUGIN_DEBUG )
			$this->debug['get_facebook_statuses']['endpoint'] = isset( $endpoint ) ? $endpoint : false;

		if( ! isset( $endpoint ) )
			return false;

		// Construct url request
		$url = add_query_arg( array(
		    'fields' 		=> $fields,
		    'limit'			=> $params['count'],
		    'access_token'  => $this->params['access_token']
		), $this->params['api_url'] . $this->params['api_version'] . '/' . $endpoint );

		// Send request
		$request = $this->file_get_contents_curl( $url );
		$response = json_decode( $request );

		if( $type == 'photo' || $type == 'post' ) {
			if( ! empty( $response ) )
				return $response;
		} elseif( $type == 'page' ) {
			if( ! empty( $response->data ) )
				return $response->data;
		}

		if( SMN_SOCIAL_PLUGIN_DEBUG ) {
			$this->debug['get_facebook_statuses']['url'] = $url;
			$this->debug['get_facebook_statuses']['response'] = $response;
		}

		return false;
	}

	private function get_facebook_albums() {

		$params = $this->api_params;

		if( SMN_SOCIAL_PLUGIN_DEBUG )
			$this->debug['get_facebook_albums'] = array( 'object->metadata->type' => isset( $params['object']->metadata->type ) ? $params['object']->metadata->type : false );

		// If type is not defined (user can't be called by username, page or post is not public or current user have'nt rights on it)
		if( ! isset( $params['object']->metadata->type ) )
			return false;

		$type = $params['object']->metadata->type;

		$endpoint = $params['from']->id . '/albums';
		$fields = 'name,description,cover_photo,count,id,link,from,created_time,updated_time';

		if( SMN_SOCIAL_PLUGIN_DEBUG )
			$this->debug['get_facebook_albums']['endpoint'] = isset( $endpoint ) ? $endpoint : false;

		// Construct url request
		$url = add_query_arg( array(
		    'fields' 		=> $fields,
		    'limit'			=> $params['count'],
		    'access_token'  => $this->params['access_token']
		), $this->params['api_url'] . $this->params['api_version'] . '/' . $endpoint );

		// Send request
		$request = $this->file_get_contents_curl( $url );
		$response = json_decode( $request );

		if( ! empty( $response->data ) )
			return $response->data;

		if( SMN_SOCIAL_PLUGIN_DEBUG ) {
			$this->debug['get_facebook_albums']['url'] = $url;
			$this->debug['get_facebook_albums']['response'] = $response;
		}

		return false;
	}

	private function get_facebook_video_object( $object_id ) {

		$object = $this->get_facebook_object( $object_id, 'picture,source' );

		if( ! empty( $object->source ) )
			return $object;

		return false;
	}

	private function get_facebook_image_url( $object_id ) {

		$object = $this->get_facebook_object( $object_id, 'images' );

		if( ! empty( $object->images ) )
			return $this->parse_images_url( $object->images );

		return false;
	}
}
