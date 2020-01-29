<?php

/**
* SMN_Social Class
*
*/

class SMN_Social {

	/**
     * Custom parameters
     * @$array
     */
	var $version = '1.0b';

	/**
     * Custom parameters
     * @$array
     */
	var $debug = array();

	/**
     * Custom parameters
     * @$array
     */
	var $params = array(
		'image_min_width'		=> '600',
		'image_min_height'		=> '600',
		'expire'				=> 60 * 60 // 1 hour
	);

	/**
     * Custom parameters
     * @$array
     */
	var $api;

	/**
     * Custom parameters
     * @$array
     */
	var $cache_key;

	/**
     * Custom parameters
     * @$array
     */
	var $api_params;

	/**
     * Custom parameters
     * @$array
     */
	var $statuses = false;

	/**
     * Custom parameters
     * @$array
     */
	var $default_data = array(
		'type'	 	 => false,
		'date'	 	 => false,
		'update'	 => false,
		'text'	 	 => false,
		'username' 	 => false,
		'author' 	 => false,
		'author_url' => false,
		'url'	 	 => false,
		'media'	 	 => false,
		'count'		 => false
	);

	/**
     * Custom parameters
     * @$array
     */
	var $data = false;

	function __construct() {

		if( SMN_SOCIAL_PLUGIN_DEBUG && SMN_SOCIAL_PLUGIN_CACHE_DISABLED )
			 $this->debug['cache'] = 'SMN_SOCIAL_PLUGIN_CACHE_DISABLED: Cache disabled';
	}

	private function process() {
	}

	private function overload_statuses() {
	}

	private function populate_data() {
	}

	public function file_get_contents_curl( $url ) {
		$ch = curl_init();

		// cURL offers really easy proxy support.
		$proxy = new WP_HTTP_Proxy();

		if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {

			curl_setopt( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
			curl_setopt( $ch, CURLOPT_PROXY, $proxy->host() );
			curl_setopt( $ch, CURLOPT_PROXYPORT, $proxy->port() );

			if ( $proxy->use_authentication() ) {
				curl_setopt( $ch, CURLOPT_PROXYAUTH, CURLAUTH_ANY );
				curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $proxy->authentication() );
			}
		}

		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_URL, $url );

		$data = curl_exec( $ch );
		curl_close( $ch );

		return $data;
	}

	public function parse_images_url( $images ) {

		$min_width = $this->params['image_min_width'];
		$min_height = $this->params['image_min_height'];

		foreach( $images as $index => $image ) {
			// facebook: source / instagram: url
			$tag = isset( $image->source ) ? 'source' : 'url';

			if( $image->width < $min_width || $image->height < $min_height ) {
				if( $index == 0 )
					return $image->$tag;
				else
					return $images[$index-1]->$tag;
			}

		}
		return false;
	}
}
