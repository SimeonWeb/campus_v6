<?php
/**
 * Add Instagram Graph API
 *
 * See: https://www.instagram.com/developer/
 */

// https://api.instagram.com/v1/users/3319748?access_token=3319748.1677ed0.d5e88b20d0424bab8243b91e96f3041f
// https://api.instagram.com/v1/users/3319748/media/recent?count=5&access_token=3319748.1677ed0.d5e88b20d0424bab8243b91e96f3041f
// https://api.instagram.com/v1/media/17843484064159091?access_token=3319748.1677ed0.d5e88b20d0424bab8243b91e96f3041f // does not woek yet


/**
* Instagram.app Class
*
*/

class SMN_Instagram_App extends SMN_Social {
	
	/**
     * Custom parameters
     * @$string
     */
	var $type = 'instagram';
	
	/**
     * Custom parameters
     * @$array
     */
	var $params = array( 
		'api_url'				=> 'https://api.instagram.com/',
		'api_version'			=> 'v1',
		'app_id' 				=> '', 
		'app_secret' 			=> '', 
		'app_status' 			=> '', 
		'access_token'			=> '',
		'image_min_width'		=> '600',
		'image_min_height'		=> '600',
		'expire'				=> 60 * 60 // 1 hour
	);
	
	function __construct( $id, $count = 1, $type = null ) {
		
		if( SMN_SOCIAL_PLUGIN_DEBUG && SMN_SOCIAL_PLUGIN_CACHE_DISABLED )
			 $this->debug['cache'] = 'SMN_SOCIAL_PLUGIN_CACHE_DISABLED: Cache disabled';
		
		// Get app infos
		$options = get_option( '_social_instagram', array() );
		$this->params = wp_parse_args( $options, $this->params );
		$this->params = apply_filters( 'smn_instagram_default_user_params', $this->params, $this );
		
		if( $this->params['app_status'] == 'off' && ( empty( $this->params['user_id'] ) || empty( $this->params['user_token'] ) ) )
			return;
		
		if( $this->params['app_status'] == 'on' && ( empty( $$this->params['app_id'] ) || empty( $this->params['app_secret'] ) ) )
			return;
		
		$id = array(
			'id' => $this->params['username'],
			'user_id' => $this->params['user_id'],
			'user_token' => $this->params['user_token'],
		);
		
		// Set Api params
		if( is_array( $id ) )
			$this->api_params = $id;
		else
			$this->api_params['id'] = $id;
		
		$this->api_params['count'] = $count;
		$this->api_params['type'] = 'user';
		
		// Define cache params
		$this->cache_key = get_parent_class() . '_' . get_class() . '_' . join( '_', $this->api_params );
		
		$this->process();
		
		if( SMN_SOCIAL_PLUGIN_DEBUG )
			print_r($this);
	}
	
	private function process() {	
		
		// Get cached data
		$this->data = get_transient( $this->cache_key );
		
		if ( SMN_SOCIAL_PLUGIN_CACHE_DISABLED || false === $this->data ) {
			
			// Get results
			$statuses = $this->get_instagram_statuses();
			
			// Store results
			$this->statuses = array_filter( (array) $statuses );
			
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
			$media = $video = false;
			
			$this->statuses[$index]->has_media = false;
			
			if( isset( $status->images ) ) {
				// To match the facebook order
				$images = array_reverse( (array) $status->images );
				$images = array_values( $images );
				$media = $this->parse_images_url( $images );
			}
				
			if( $media ) {
				$this->statuses[$index]->has_media = true;
				$this->statuses[$index]->media = $media;
			}
			
			if( isset( $status->videos ) ) {
				// To match the facebook order
				$videos = array_reverse( (array) $status->videos );
				$videos = array_values( $videos );
				$video = $this->parse_images_url( $videos );
			}
				
			if( $video ) {
				$this->statuses[$index]->has_media = true;
				$this->statuses[$index]->video_url = $video;
			}
			
			
		}
	}
	
	private function populate_data() {
		
		$this->data = array();
		
		foreach( $this->statuses as $index => $status ) {
			
			$data = $this->default_data;
			
			$data['type'] 		= $this->type;
			$data['date'] 		= date( DATE_W3C, $status->created_time );
			$data['text'] 		= isset( $status->caption->text ) ? $status->caption->text : '';
			$data['username'] 	= $status->user->username;
			$data['author'] 	= $status->user->full_name;
			$data['url'] 		= $status->link;
			$data['count'] 		= $status->type == 'carousel' ? count( $status->carousel_media ) : 1;
			
			if( $status->has_media ) {
				
				$data['media'] = array(
					'type' => $status->type,
					'url' => $status->media
				);
				
				if( $status->type == 'video' )
					$data['media']['video_url'] = $status->video_url;
				
			}
			
			$this->data[] = $data;
		}
	}
	
	private function get_instagram_statuses() {
		
		$params = $this->api_params;
		
		if( $params['type'] == 'photo' || $params['type'] == 'image' ) {
			$endpoint = 'media/' . $params['id'];
		} elseif( $params['type'] == 'user' ) {
			$endpoint = 'users/' . $params['user_id'] . '/media/recent';
		}
		
		if( ! isset( $endpoint ) )
			return false;		
		
		// Construct url request
		$url = add_query_arg( array(
		    'count' 		=> $params['count'],
		    'access_token'  => $params['user_token']
		), $this->params['api_url'] . $this->params['api_version'] . '/' . $endpoint );
		
		$this->query = $url;
		
		// Send request
		$request = $this->file_get_contents_curl( $url );
		$response = json_decode( $request );
		
		if( $params['type'] == 'photo' || $params['type'] == 'image' ) {
			if( ! empty( $response ) )
				return $response;
		} elseif( $params['type'] == 'user' ) {
			if( ! empty( $response->data ) )
				return $response->data;
		}
		
		return false;		
	}
	
	private function get_instagram_image_url( $object_id ) {
		
		$object = $this->get_instagram_object( $object_id, 'images' );
		
		if( ! empty( $object->images ) ) {
			
			// To match the facebook order
			$images = array_reverse( $object->images );
			$images = array_values( (array) $images );
			
			return $this->parse_images_url( $images );
			
		}
		return false;
	}
}