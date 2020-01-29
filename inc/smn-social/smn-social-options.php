<?php
/**
 * SMN Social options class
 *
 */
class SMN_Social_Options {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = array(
	    'facebook' => false,
	    'twitter' => false,
	    'instagram' => false
    );

	private $default_facebook_options = array(
		'username' => '',
		'app_id' => '',
		'app_secret' => '',
		'expire' => 60 * 60
	);

	private $default_twitter_options = array(
		'username' => '',
		'consumer_key' 			=> '',
		'consumer_secret' 		=> '',
		'access_token' 			=> '',
		'access_token_secret' 	=> '',
		'expire' => 60 * 60
	);

	private $default_instagram_options = array(
		'username' => '',
		'user_id' => '',
		'user_token' => '',
		'app_id' => '',
		'app_secret' => '',
		'app_status' => 'off',
		'expire' => 60 * 60
	);


    /**
     * Holds the values to be used in the fields callbacks
     */
    private $capability = 'manage_options';

	public function __construct() {

		// Enable Facebook
		if( class_exists( 'SMN_Facebook_App' ) )
			$this->options['facebook'] = get_option( '_social_facebook', $this->default_facebook_options );

		// Enable Twitter
		if( class_exists( 'SMN_Twitter_App' ) )
			$this->options['twitter'] = get_option( '_social_twitter', $this->default_twitter_options );

		// Enable Instagram
		if( class_exists( 'SMN_Instagram_App' ) )
			$this->options['instagram'] = get_option( '_social_instagram', $this->default_instagram_options );

		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_init', array( $this, 'delete_cache' ) );
	}

	public function add_options_page() {
		add_options_page(
			__( 'Connections aux réseaux sociaux' ),
			__( 'Social' ),
			$this->capability,
			'social_options',
			array( $this, 'options_page' )
		);
	}

	public function options_page() {
		?>
		<div id="social_options" class="wrap">
			<h1><?php _e( 'Connections aux réseaux sociaux' ) ?></h1>

			<?php //print_r($this->options); ?>

			<form method="post" action="options.php">

				<?php settings_fields( 'social_options_group' ); ?>

				<?php do_settings_sections( 'social_options' ); ?>

				<?php submit_button(); ?>
			</form>

			<h2><?php _e( 'Cache' ) ?></h2>

			<form method="post" action="">

				<?php $this->cached_data_table_html(); ?>

				<?php wp_nonce_field( 'smn_social_delete_cache' ); ?>
				<?php submit_button( 'Supprimer le cache', 'delete', 'smn_social_delete_cache' ); ?>
			</form>
		</div>
		<?php
	}

	public function register_settings() {

		foreach( $this->options as $name => $option ) {

			if( $option )
				call_user_func( array( $this, "add_{$name}_settings" ) );
		}

	}

	public function add_facebook_settings() {

		register_setting(
			'social_options_group', // Option group
			'_social_facebook' // Option name
		);

        add_settings_section(
            'facebook', // ID
            'Facebook', // Title
            array( $this, 'get_form_section' ), // Callback
            'social_options' // Page
        );

        add_settings_field(
            'username', // ID
            'Nom d\'utilisateur', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'facebook', // Section
            array(
	            'name'		  => '_social_facebook[username]',
				'description' => 'Le nom de votre compte/page Facebook (sans le @)',
				'value'		  => esc_attr( $this->options['facebook']['username'] )
            )
        );

        add_settings_field(
            'app_id', // ID
            'ID de l’app', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'facebook', // Section
            array(
	            'name'		  => '_social_facebook[app_id]',
				'description' => 'Vous le trouverez sur le "Tableau de bord" de votre app.',
				'value'		  => esc_attr( $this->options['facebook']['app_id'] )
            )
        );

        add_settings_field(
            'app_secret', // ID
            'Clé secrète', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'facebook', // Section
            array(
	            'name'		  => '_social_facebook[app_secret]',
				'description' => 'Vous la trouverez sur le "Tableau de bord" de votre app.',
				'value'		  => esc_attr( $this->options['facebook']['app_secret'] )
            )
        );

        add_settings_field(
            'expire', // ID
            'Expiration du cache', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'facebook', // Section
            array(
	            'name'		  => '_social_facebook[expire]',
				'description' => 'Les requetes sont stoquées en cache afin d\'optimiser les performances, vous pouvez ajuster la durée pour récupérer les derniers posts plus ou moins souvent (en seconde).<br>Exemples : 1 heure = 3600, 1 jour = 86400',
				'value'		  => esc_attr( $this->options['facebook']['expire'] )
            )
        );
	}

	public function add_twitter_settings() {

		register_setting(
			'social_options_group', // Option group
			'_social_twitter' // Option name
		);

        add_settings_section(
            'twitter', // ID
            'Twitter', // Title
            array( $this, 'get_form_section' ), // Callback
            'social_options' // Page
        );

        add_settings_field(
            'username', // ID
            'Nom d\'utilisateur', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'twitter', // Section
            array(
	            'name'		  => '_social_twitter[username]',
				'description' => 'Le nom de votre compte Twitter (sans le @)',
				'value'		  => esc_attr( $this->options['twitter']['username'] )
            )
        );

        add_settings_field(
            'consumer_key', // ID
            'Consumer Key (API Key)', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'twitter', // Section
            array(
	            'name'		  => '_social_twitter[consumer_key]',
				'description' => 'Vous le trouverez dans la section "Keys and Access Tokens" de votre app.',
				'value'		  => esc_attr( $this->options['twitter']['consumer_key'] )
            )
        );

        add_settings_field(
            'consumer_secret', // ID
            'Consumer Secret (API Secret)', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'twitter', // Section
            array(
	            'name'		  => '_social_twitter[consumer_secret]',
				'description' => 'Vous le trouverez dans la section "Keys and Access Tokens" de votre app.',
				'value'		  => esc_attr( $this->options['twitter']['consumer_secret'] )
            )
        );

        add_settings_field(
            'access_token', // ID
            'Access Token', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'twitter', // Section
            array(
	            'name'		  => '_social_twitter[access_token]',
				'description' => 'Vous le trouverez dans la section "Keys and Access Tokens" de votre app.',
				'value'		  => esc_attr( $this->options['twitter']['access_token'] )
            )
        );

        add_settings_field(
            'access_token_secret', // ID
            'Access Token Secret', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'twitter', // Section
            array(
	            'name'		  => '_social_twitter[access_token_secret]',
				'description' => 'Vous le trouverez dans la section "Keys and Access Tokens" de votre app.',
				'value'		  => esc_attr( $this->options['twitter']['access_token_secret'] )
            )
        );

        add_settings_field(
            'expire', // ID
            'Expiration du cache', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'twitter', // Section
            array(
	            'name'		  => '_social_twitter[expire]',
				'description' => 'Les requetes sont stoquées en cache afin d\'optimiser les performances, vous pouvez ajuster la durée pour récupérer les derniers posts plus ou moins souvent (en seconde).<br>Exemples : 1 heure = 3600, 1 jour = 86400',
				'value'		  => esc_attr( $this->options['twitter']['expire'] )
            )
        );
	}

	public function add_instagram_settings() {

		register_setting(
			'social_options_group', // Option group
			'_social_instagram' // Option name
		);

        add_settings_section(
            'instagram', // ID
            'Instagram', // Title
            array( $this, 'get_form_section' ), // Callback
            'social_options' // Page
        );

        add_settings_field(
            'username', // ID
            'Nom d\'utilisateur', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'instagram', // Section
            array(
	            'name'		  => '_social_instagram[username]',
				'description' => 'Le nom de votre compte Instagram (sans le @)',
				'value'		  => esc_attr( $this->options['instagram']['username'] )
            )
        );

        add_settings_field(
            'user_id', // ID
            'Client ID', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'instagram', // Section
            array(
	            'name'		  => '_social_instagram[user_id]',
				'value'		  => esc_attr( $this->options['instagram']['user_id'] ),
				'description' => '<a href="https://codeofaninja.com/tools/find-instagram-user-id" target="_blank">Obtenir l\'identifiant de l\'utilisateur</a>'
            )
        );

        add_settings_field(
            'user_token', // ID
            'Client Token', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'instagram', // Section
            array(
	            'name'		  => '_social_instagram[user_token]',
				'value'		  => esc_attr( $this->options['instagram']['user_token'] ),
				'description' => '<a href="http://instagram.pixelunion.net/" target="_blank">Obtenir le jeton d\'accès</a>'
            )
        );

        add_settings_field(
            'app_id', // ID
            'App ID', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'instagram', // Section
            array(
	            'name'		  => '_social_instagram[app_id]',
				'value'		  => esc_attr( $this->options['instagram']['app_id'] )
            )
        );

        add_settings_field(
            'app_secret', // ID
            'App Secret', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'instagram', // Section
            array(
	            'name'		  => '_social_instagram[app_secret]',
				'value'		  => esc_attr( $this->options['instagram']['app_secret'] )
            )
        );

        add_settings_field(
            'app_status', // ID
            'Client Status', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'instagram', // Section
            array(
	            'type'		  => 'checkbox',
	            'name'		  => '_social_instagram[app_status]',
	            'label'		  => 'Live (coché) / Sandbox (décoché)',
				'value'		  => esc_attr( $this->options['instagram']['app_status'] ),
	            'description' => 'En mode Sandbox vous n\'êtes pas obligé de remplir les champs <strong>App ID</strong> et <strong>App Secret</strong>. Vous allez devoir ajouter dans la section "Sandbox" de votre app les comptes que vous souhaitez utiliser (10 max par app), puis valider chaque demande pour chaque compte, fastidieux mais obligatoire... :('
            )
        );

        add_settings_field(
            'expire', // ID
            'Expiration du cache', // Title
            array( $this, 'get_form_field' ), // Callback
            'social_options', // Page
            'instagram', // Section
            array(
	            'name'		  => '_social_instagram[expire]',
				'description' => 'Les requetes sont stoquées en cache afin d\'optimiser les performances, vous pouvez ajuster la durée pour récupérer les derniers posts plus ou moins souvent (en seconde).<br>Exemples : 1 heure = 3600, 1 jour = 86400',
				'value'		  => esc_attr( $this->options['instagram']['expire'] )
            )
        );
	}

	public function get_form_section( $args ) {
		extract( $args );

		switch( $id ) {
			case 'facebook':
				?>
				<p>Pour commencer, vous devez créer une <a href="https://developers.facebook.com/" target="_blank">application Facebook</a>, puis renseigner les champs ci-dessous avec les infos de votre app.</p>
				<?php
				break;
			case 'twitter':
				?>
				<p>Pour commencer, vous devez créer une <a href="https://apps.twitter.com/" target="_blank">application Twitter</a>, puis renseigner les champs ci-dessous avec les infos de votre app.</p>
				<?php
				break;
			case 'instagram':
				?>
				<p>Pour commencer, vous devez créer une <a href="https://www.instagram.com/developer/" target="_blank">application Instagram</a>, puis renseigner les champs ci-dessous avec les infos de votre app.</p>
				<?php
				break;
		}
	}

	public function get_form_field( $args ) {
		extract( $args );

		$type = isset( $type ) ? $type : 'text';

		switch( $type ) {

			case 'checkbox':
				$id = str_replace( array( '[', ']' ), array( '-', '' ), $name );
				?>
				<input type="hidden" id="<?php echo $id; ?>-hidden" name="<?php echo $name; ?>" value="off" />
				<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $name; ?>"<?php checked( isset( $value ) ? $value : '' , 'on' ); ?> /> <label for="<?php echo $id; ?>"><?php echo isset( $label ) ? $label : ''; ?></label>
				<?php
				break;

			default:
				?>
				<input type="text" name="<?php echo $name; ?>" value="<?php echo isset( $value ) ? $value : ''; ?>" class="regular-text" />
				<?php
				break;
		}

		if( ! empty( $description )  )
			echo '<p class="description">' . $description . '</p>';
	}

	/**
	 * Cache
	 *
	 */
	public function get_all_cached_data() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT * FROM $wpdb->options WHERE `option_name` LIKE '%_SMN_Social_%'" );

		if( $results ) {
			$data = array();

			foreach( $results as $result ) {
				$cache_key = str_replace( array( '_transient_timeout_', '_transient_' ), '', $result->option_name );

				$name = $cache_key;

				if( get_transient( $cache_key ) ) {

					if( '_transient_timeout_' == substr( $result->option_name, 0, 19 ) ) {
						$data[$cache_key]['timeout'] = $result->option_value;
						$data[$cache_key]['timeout_option'] = $result;
					} else {
						$data[$cache_key]['name'] = $name;
						$data[$cache_key]['name_option'] = $result;
					}
				}
			}

			if( ! empty( $data ) )
				return $data;
		}

		return false;
	}

	public function delete_cache() {

		if( ! isset( $_POST['smn_social_delete_cache'] ) )
			return;

		if( ! check_admin_referer( 'smn_social_delete_cache' ) )
			return;

		if( ! empty( $_POST['smn_social_cache'] ) ) {
			foreach( $_POST['smn_social_cache'] as $cache_key )
				delete_transient( $cache_key );
		} else {
			$data = $this->get_all_cached_data();
			if( $data )
				foreach( $data as $cache_key => $option )
					delete_transient( $cache_key );
		}

	}

	public function cached_data_table_html() {

		$data = $this->get_all_cached_data();

		if( $data ) {
		?>
			<table class="wp-list-table widefat fixed striped">
			<?php
			date_default_timezone_set( get_option( 'timezone_string' ) );

			foreach( $data as $cache_key => $field ) {
				?>
				<tr>
					<th scope="row" class="check-column"><input id="cb-select-<?php echo $cache_key; ?>" name="smn_social_cache[]" value="<?php echo $cache_key; ?>" type="checkbox"></th>
					<td class="title column-title column-primary page-title" data-colname="Titre" style="width:60%;"><label for="cb-select-<?php echo $cache_key; ?>"><strong><?php echo $field['name']; ?></strong></label></td>
					<td class="timeout column-timeout" data-colname="Timeout"><?php printf( 'Expire le %s à %s', date_i18n( get_option( 'date_format' ), $field['timeout'] ), date_i18n( get_option( 'time_format' ), $field['timeout'] ) ); ?></td>
				</tr>
				<?php
			}
			?>
			</table>
		<?php
		} else {
			echo '<p>Il n\'y a aucune donnée en cache.</p>';
		}
	}

}

new SMN_Social_Options();
