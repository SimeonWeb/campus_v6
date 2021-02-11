<?php
/**
 * Add scripts
 */
add_action( 'admin_enqueue_scripts', 'campus_register_admin_scripts' );
add_action( 'admin_enqueue_scripts', 'campus_admin_scripts', 100 );

function campus_load_scripts_on_post() {
	//add_action( 'admin_head', 'campus_admin_head' );
	//add_action( 'admin_footer', 'campus_admin_footer' );
	add_action( 'admin_enqueue_scripts', 'campus_admin_scripts' );
}
add_action( 'load-edit.php', 'campus_load_scripts_on_post' );
add_action( 'load-post.php', 'campus_load_scripts_on_post' );
add_action( 'load-post-new.php', 'campus_load_scripts_on_post' );
add_action( 'load-term.php', 'campus_load_scripts_on_post' );
add_action( 'load-edit-tags.php', 'campus_load_scripts_on_post' );
add_action( 'load-options-general.php', 'campus_load_scripts_on_post' );
add_action( 'load-posts_page_presenter_options', 'campus_load_scripts_on_post' );

function remove_unclassifed_when_multiple_categories( $query ) {
	global $wpdb;

	if( ! is_admin() )
		return;

	if( ! $query->is_main_query() )
		return;

	if( isset( $_GET['clean_categories'] ) ) {

		$post_query = new WP_Query( array( 'cat' => 1, 'post_status' => 'any', 'posts_per_page' => -1 ) );

		if( $post_query->have_posts() ) {
			foreach( $post_query->get_posts() as $p ) {
				$categories = get_the_category( $p->ID );

				if( count( $categories ) > 1 ) {
					$deleted = $wpdb->delete( $wpdb->term_relationships, array( 'object_id' => $p->ID, 'term_taxonomy_id' => 1 ), array( '%d', '%d' ) );
				}
			}
		}
	}
}

add_action( 'pre_get_posts', 'remove_unclassifed_when_multiple_categories' );

/**
 * Min dimention for image upload
 */
$GLOBALS['image_min_dim'] = 768;

function campus_get_podcast_category_image_sizes() {
	return array(
		'final_height' => 1400,
		'final_width' => 1400,
		'image_height' => 1120,
		'image_width' => 1120,
		'image_margin_y' => 140,
		'image_margin_x' => 140
	);
}
/**
 * admin inline scripts
 */
function campus_admin_head() {
	?>
	<style type="text/css">

	</style>
	<?php
}

/**
 * admin inline scripts
 */
function campus_admin_footer() {

}

/**
 * Register scripts
 */
function campus_register_admin_scripts( $hook ) {

	$suffix = WP_DEBUG || SCRIPT_DEBUG ? '' : '.min';

	wp_register_style( 'campus-admin', get_theme_file_uri( '/assets/css/admin.css' ), array(), THEME_VERSION );
	wp_register_script( 'campus-admin', get_theme_file_uri( '/assets/js/admin' . $suffix . '.js' ), array( 'wp-color-picker' ), THEME_VERSION, true );

	// Doc: https://codex.wordpress.org/Javascript_Reference/wp.media
	wp_register_script( 'campus-media-upload', get_theme_file_uri( '/assets/js/admin-media-upload' . $suffix . '.js' ), array( 'jquery' ), THEME_VERSION, true );
}

/**
 * Enqueue scripts
 */
function campus_admin_scripts( $hook ) {
	global $image_min_dim;

	$screen = get_current_screen();

	wp_enqueue_style( 'campus-admin' );
	wp_enqueue_script( 'campus-admin' );

	if( in_array( $screen->id, array( 'edit-category', 'edit-post_tag', 'settings_page_campus_options', 'posts_page_presenter_options' ) ) ) {

		wp_enqueue_media();

		// Doc: https://codex.wordpress.org/Javascript_Reference/wp.media
		wp_enqueue_script( 'campus-media-upload' );

		$campus_admin_text = apply_filters( 'campus_admin_text', array(
			'title'						=> __( 'Select or Upload Media Of Your Chosen Persuasion', 'campus' ),
			'choose_file'				=> __( 'Choose file', 'campus' ),
			'choose'	 				=> __( 'Choose', 'campus' ),
			'download_already_added'	=> __( 'You already added this file!', 'campus' ),
			'insert_into_the_content'	=> __( 'Insert into the content', 'campus' ),
			'remove'					=> __( 'Remove', 'campus' ),
			'icon'						=> array(
				'remove' => '<span class="dashicons-before dashicons-trash"></span>',
			)
		) );
		wp_localize_script( 'campus-media-upload', 'campusAdminText', $campus_admin_text );

		$campus_admin_params = apply_filters( 'campus_admin_params', array(
			'mask' => campus_get_term_thumbnail_mask_urls(),
			'media' => array(
				'width' => $image_min_dim,
				'height' => $image_min_dim,
				'flex_width' => false,
				'flex_height' => false,
			)
		) );
		wp_localize_script( 'campus-media-upload', 'campusAdminParams', $campus_admin_params );
	}
}

/**
 * Filters enabled shortcodes
 */
function campus_smn_enabled_shortcodes( $enabled ) {
	$enabled = array_flip( $enabled );

	if( ! current_user_can( 'manage_categories' ) ) {
		unset( $enabled['the_title'] );
		unset( $enabled['termlist'] );
		unset( $enabled['postcastslist'] );
	}

	$enabled = array_flip( $enabled );

	return $enabled;
}
add_filter( 'smn_enabled_shortcodes', 'campus_smn_enabled_shortcodes' );

/**
 * Define presenter fields
 *
 */
function campus_tag_options_fields() {

	if( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'post_tag' && isset( $_GET['tag_ID'] ) )
		$term_id = (int) $_GET['tag_ID'];
	else if( isset( $_POST['taxonomy'] ) && $_POST['taxonomy'] == 'post_tag' && isset( $_POST['tag_ID'] ) )
		$term_id = (int) $_POST['tag_ID'];
	else
		$term_id = false;

	$fields = array(
		'thumbnail_id' => array(
			'type'  	    => 'image_uploader',
			'title' 	    => 'Image',
			'value'		    => get_term_meta( $term_id, 'thumbnail_id', true ),
		),
		'type'		     => array(
			'type'  	    => 'text',
			'title' 	    => 'Type de partenaire',
			'description' => 'Ex: Partenaire, mécène, subvention...',
			'value'		    => get_term_meta( $term_id, 'type', true )
		)
	);

	$fields = $fields 
		+ [
			'sidebar' => array(
				'type'  	  => 'section',
				'title' 	  => __( 'Sidebar' )
			)
		]
		+ campus_term_sidebar_fields( $term_id, 'post_tag' )
		+ [
			'social_links' => array(
				'type'  	  => 'section',
				'title' 	  => 'Liens'
			)
		]
		+ campus_term_social_links_fields( $term_id, 'post_tag' );

	return $fields;
}

/**
 * Define presenter fields
 *
 */
function campus_presenter_options_fields() {

	if( $user_cat = Author_Category::get_user_cat() )
		$term_id = $user_cat;
	else if( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'category' && isset( $_GET['tag_ID'] ) )
		$term_id = (int) $_GET['tag_ID'];
	else if( isset( $_POST['taxonomy'] ) && $_POST['taxonomy'] == 'category' && isset( $_POST['tag_ID'] ) )
		$term_id = (int) $_POST['tag_ID'];
	else
		$term_id = false;

	$fields = array(
		'informations' => array(
			'type'  	  => 'section',
			'title' 	  => 'Informations'
		),
		'secondary_description' => array(
			'type' 			=> 'text',
			'title' 		=> 'Description courte',
			'description'	=> 'Ex: Magazine d\'info, décorticage musical...',
			'value'			=> get_term_meta( $term_id, 'secondary_description', true )
		),
		'interests' => array(
			'type' 			=> 'textarea',
			'title' 		=> 'Centres d\'intérêt',
			'description'	=> 'Les centres d\'intérêt de l\'émission en 140 caractères.<br>Ex : Culture, Cinéma, Arts, Danse, Théâtre, Interviews...',
			'value'			=> get_term_meta( $term_id, 'interests', true ),
			'attr'			=> array(
				'maxlength' => 140,
			)
		),
		'social' => array(
			'type'  	  => 'section',
			'title' 	  => 'Réseaux sociaux'
		),
		'facebook_username'	=> array(
			'type'  	  => 'text',
			'title' 	  => 'Nom d\'utilisateur Facebook',
			'description' => 'Sans le @',
			'value' 	  => get_term_meta( $term_id, 'facebook_username', true )
		),
		'twitter_username' => array(
			'type'  	  => 'text',
			'title' 	  => 'Nom d\'utilisateur Twitter',
			'description' => 'Sans le @',
			'value' 	  => get_term_meta( $term_id, 'twitter_username', true )
		),
		'instagram_username' => array(
			'type'  	  => 'text',
			'title' 	  => 'Nom d\'utilisateur Instagram',
			'description' => 'Sans le @',
			'value' 	  => get_term_meta( $term_id, 'instagram_username', true )
		),
		'instagram_user_id' => array(
			'type'  	  => 'text',
			'title' 	  => 'User ID Instagram',
			'description' => '<a href="https://codeofaninja.com/tools/find-instagram-user-id" target="_blank">Obtenir l\'identifiant de l\'utilisateur</a>',
			'value' 	  => get_term_meta( $term_id, 'instagram_user_id', true )
		),
		'instagram_user_token' => array(
			'type'  	  => 'text',
			'title' 	  => 'User Token Instagram',
			'description' => '<a href="http://instagram.pixelunion.net/" target="_blank">Obtenir le jeton d\'accès</a>',
			'value' 	  => get_term_meta( $term_id, 'instagram_user_token', true )
		),
		'social_links' => array(
			'type'  	  => 'section',
			'title' 	  => 'Liens'
		),
	);

	if( $term_id && $user_cat ) {

		if( $term = get_term( $term_id, 'category' ) ) {

			$new_fields = array( 'description' => array(
				'type' 			=> 'textarea',
				'title' 		=> 'Description',
				'class'			=> 'widefat',
				'attr'			=> array(
					'rows' => 5
				),
				'value'			=> $term->description
			) );

			$fields = array_slice( $fields, 0, 1 ) + $new_fields + array_slice( $fields, 1 );
		}
	}

	if( current_user_can( 'manage_categories' ) ) {

		$hours = get_term_meta( $term_id, 'hours', true );

		$new_fields = array(
			'singular_name' => array(
				'type' 			=> 'text',
				'title' 		=> 'Nom (au singulier)',
				'description'	=> 'Le nom de la catégorie au singulier',
				'value'			=> get_term_meta( $term_id, 'singular_name', true )
			),
			'schedules' => array(
				'type'  	  	=> 'composed',
				'title' 	  	=> 'Horaires',
				'inputs'	  => array(
					'day' => array(
						'type'  	=> 'text',
						'title'		=> 'Jour(s)',
						'value'		=> get_term_meta( $term_id, 'day', true )
					),
					'hours[begin]' => array(
						'type'  	=> 'text',
						'title'		=> 'Heure',
						'value'		=> isset( $hours['begin'] ) ? $hours['begin'] : ''
					),
					'hours[end]' => array(
						'type'  	=> 'text',
						'title'		=> ' > ',
						'value'		=> isset( $hours['end'] ) ? $hours['end'] : ''
					)
				),
				// 'value'			=> get_term_meta( $term_id, 'hours', true )
			),
			'users' => array(
				'type' 			=> 'user_list',
				'title' 		=> 'Animateurs',
				'description'	=> 'Attention ! Pour limiter un utilisateur à poster dans une catégorie spécifique, vous devez le faire depuis son profil.',
				'value'			=> get_term_meta( $term_id, 'users', true )
			),
			'live_display' => array(
				'type' 			=> 'checkbox',
				'title' 		=> 'Affichage du direct',
				'description'	=> 'Les titres de la programmation automatique sont affichés pendant cette émission.',
				'value'			=> get_term_meta( $term_id, 'live_display', true )
			)
		);

		$fields = [
				'sidebar' => array(
					'type'  	  => 'section',
					'title' 	  => __( 'Sidebar' )
				)
			]
			+ campus_term_sidebar_fields( $term_id, 'post_tag' )
			+ array_slice( $fields, 0, 1 ) 
			+ $new_fields 
			+ array_slice( $fields, 1 );

	}

	if( current_user_can( 'smn_can_crop_image' ) ) {

		$fields = array(
			'category' => array(
				'type'  	  => 'section'
			),
			'thumbnail_id' => array(
				'type'  	  => 'image_cropper',
				'title' 	  => 'Image',
				'value'		  => get_term_meta( $term_id, 'thumbnail_id', true ),
			),
			'podcast_thumbnail_id' => array(
				'type' 			=> 'image_hidden',
				'title' 		=> 'Image pour Apple Podcasts / Google Play...',
				'description'	=> 'Vous ne pouvez pas modifier cette image, elle est créée automatiquement à partir de l\'image principale.',
				'value'			=> get_term_meta( $term_id, 'podcast_thumbnail_id', true ),
				'readonly'	=> true,
			)
		) + $fields;
	}

	// Add social links fields
	$fields = $fields + campus_term_social_links_fields( $term_id );

	return $fields;
}

/**
 * Add options page to posts menu
 *
 */
function campus_add_presenter_options_page() {
	$hook = add_posts_page(
		'Paramètres de l\'emission',
		'Paramètres',
		'edit_show_infos',
		'presenter_options',
		'campus_presenter_options_page'
	);
}

add_action( 'admin_menu', 'campus_add_presenter_options_page' );

/**
 * Add setting fields to admin
 *
 */
function campus_init_admin_presenter_options() {

	campus_init_admin_setting_fields( 'campus_presenter_options', campus_presenter_options_fields(), false );
}

add_action( 'admin_init', 'campus_init_admin_presenter_options', 20 );

/**
 * Create options page
 *
 */
function campus_presenter_options_page() {

	$term_id = is_user_logged_in() ? Author_Category::get_user_cat() : false;

	if( $term_id ) {

		$term = get_term( $term_id, 'category' );
		?>
		<div class="wrap">
			<h1>Paramètres de l'emission <?php echo $term->name; ?></h1>
			<p class="description">Vous êtes perdu ? Consultez la <a href="/wp-admin/admin.php?page=smn_faq" target="_blank">F.A.Q.</a> pour vous aider à mettre à jour vos informations.</p>
			<form method="POST" action="">
			<?php

			echo '<input type="hidden" name="term_id" value="' . $term_id . '" />';
			echo '<input type="hidden" name="tag_ID" value="' . $term_id . '" />';
			echo '<input type="hidden" name="taxonomy" value="category" />';

			if( $term ) {
				echo '<input id="parent" type="hidden" name="parent" value="' . $term->parent . '" />';
			}

			settings_fields( 'campus_presenter_options' );	//pass slug name of page, also referred
			                                	        //to in Settings API as option group name
			do_settings_sections( 'campus_presenter_options' ); 	//pass slug name of page
			submit_button();
			?>
			</form>
		</div>
		<?php
	} else {
		?>
		<div class="wrap">
			<h1>Paramètres de l'emission</h1>
			<p class="description">Aucune catégorie n'est liée à votre profil, veuillez contacter votre coordinateur d'antenne (antenne@radiocampusangers.com).</p>
		</div>
		<?php
	}
}

/**
 * Init setting fields
 *
 */
function campus_init_admin_setting_fields( $page, $fields, $register = true ) {

	$current_section = false;

	foreach( $fields as $option_name => $field ) {

		if( $field['type'] == 'section' ) {

			$current_section = $option_name;

			add_settings_section(
				$option_name,
				! empty( $field['title'] ) ? $field['title'] : false,
				! empty( $field['callback'] ) ? $field['callback'] : false,
				$page
			);

		} else {

			if( $register ) {
				register_setting(
					$page,
					$option_name
				);
			}

			add_settings_field(
				$option_name,
				! empty( $field['title'] ) ? $field['title'] : false,
				'campus_admin_setting_field',
				$page,
				! empty( $field['section'] ) ? $field['section'] : $current_section,
				array(
					'option_name' 	=> $option_name,
					'option_args' 	=> $field
				)
			);
		}
	}
}

/**
 * Create setting field
 *
 * @args $option_name
 * @args $option_args
 */
function campus_admin_setting_field( $args ) {
	extract( $args );

	// Vars
	$default_value = ! empty( $option_args['value'] ) ? $option_args['value'] : false;
	$id = str_replace( array( '][', '[', ']' ), array( '-', '-', '' ), $option_name );
	$title = ! empty( $option_args['title'] ) ? $option_args['title'] : '';
	$description = ! empty( $option_args['description'] ) ? $option_args['description'] : '';
	$attr = ! empty( $option_args['attr'] ) ? (array) $option_args['attr'] : array();

	$attr['class'] = isset( $option_args['class'] ) ? $option_args['class'] : 'regular-text';

	$attr_str = campus_parse_attr( $attr );

	// Prefix
	echo ! empty( $option_args['prefix'] ) ? sprintf( '<span class="prefix-for-%s">%s</span>', $id, $option_args['prefix'] ) : '';

	switch( $option_args['type'] ) {

		case 'composed' :

			if( ! empty( $option_args['inputs'] ) ) {

				foreach( $option_args['inputs'] as $input_name => $input_args ) {

					if( $input_args['type'] == 'composed' )
						continue;

					$new_option_name = ! is_numeric( $input_name ) ? $input_name : $option_name . '[]';
					$new_option_args = $input_args;

					if( ! empty( $input_args['title'] ) ) {
						if( $input_args['type'] == 'checkbox' || $input_args['type'] == 'radio' )
							$new_option_args['suffix'] = $input_args['title'];
						else
							$new_option_args['prefix'] = $input_args['title'];
					}

					// Get value from name
					$is_array = strpos( $input_name, '[' );
					$key_name = $is_array ? str_replace( array( substr( $input_name, 0, $is_array ), '[', ']' ) , '', $input_name ) : $input_name;

					// Merge values, if global values are set and there's not in local
					if( ! empty( $option_args['value'] ) && ! empty( $option_args['value'][$key_name] ) && empty( $input_args['value'] ) ) {
						$new_option_args['value'] = $option_args['value'][$key_name];
					}
					campus_admin_setting_field( array( 'option_name' => $new_option_name, 'option_args' => $new_option_args ) );

				}
			}
			break;

		case 'dropdown_pages' :
			wp_dropdown_pages( array(
				'show_option_none' => 'Choisir une page',
				'name' => $option_name,
				'class' => $attr['class'],
				'selected' => get_option( $option_name, $default_value )
			) );
			break;

		case 'dropdown_category' :
			wp_dropdown_categories( array(
				'show_option_none' => 'Choisir une catégorie',
				'name' => $option_name,
				'hide_empty' => false,
				'hierarchical' => 1,
				'class' => $attr['class'],
				'selected' => get_option( $option_name, $default_value )
			) );
			break;

		case 'image_hidden' :

			$image_id = get_option( $option_name, $default_value );

			$image_src = wp_get_attachment_image_src( $image_id, 'full' );

			$have_img = $image_src != '';

			if( $have_img ) {

				printf( '<div class="custom-img-container"><img src="%3$s" /></div><input type="hidden" name="%1$s" value="%2$s" id="%1$s-image-id" class="custom-img-id" />',
					$option_name,
					$image_id,
					$have_img ? $image_src[0] : ''
				);
			}

			break;

		case 'image_uploader' :
		case 'image_cropper' :

			$container_class = $option_args['type'] == 'image_cropper' ? 'campus-media-cropper' : 'campus-media-uploader';

			// Get WordPress' media upload URL
			$upload_link = esc_url( get_upload_iframe_src( 'image' ) );

			// See if there's a media id already saved as post meta
			$image_id = get_option( $option_name, $default_value );

			// Get the image src
			$image_src = wp_get_attachment_image_src( $image_id, 'full' );

			// For convenience, see if image_id exists
			$have_img = $image_src != '';

			if( ! $have_img ) {
				$image_id = false;
			}

			echo '<div class="campus-media-container ' . $container_class . '">';

				/* Image container, which can be manipulated with js */
				printf( '<div class="custom-img-container no-mask">%3$s</div><input type="hidden" name="%1$s" value="%2$s" id="%1$s-image-id" class="custom-img-id" />',
					$option_name,
					$image_id,
					$have_img ? '<img src="' . $image_src[0] . '" class="upload-custom-img" />' : ''
				);

				/* Add & remove image links */
				printf( '<p class="hide-if-no-js"><a class="button upload-custom-img %s" href="%s">%s</a> <a class="button delete-custom-img %s" href="#">%s</a></p>',
					$have_img ? 'hidden' : '',
					$upload_link,
					__( 'Ajouter une image', 'campus' ),
					! $have_img ? 'hidden' : '',
					__( 'Supprimer l\'image', 'campus' )
				);

				if( current_user_can( 'manage_categories' ) && $option_args['type'] == 'image_cropper' ) {

					echo '<p class="hide-if-no-js" style="margin-top:1em">';

						printf( ' <label class="toggle-input button button-small %2$s" for="%1$s"><input type="checkbox" id="%1$s"> %3$s</label>',
							'campus-toggle-media-cropper',
							$have_img ? 'hidden' : '',
							__( 'Ne pas recadrer l\'image', 'campus' )
						);

						printf( ' <label class="toggle-input button button-small %2$s" for="%1$s"><input type="checkbox" id="%1$s"> %3$s</label>',
							'campus-toggle-media-cropper-mask',
							$have_img ? 'hidden' : '',
							__( 'Ne pas utiliser de masque', 'campus' )
						);

					echo '</p>';
				}

			echo '</div>';

			break;

		case 'user_list' :

			// Selected users
			$checked_users = (array) get_option( $option_name, $default_value );

			// All editable roles
			$roles = get_editable_roles();

			?>
			<div id="term-users" class="userdiv">
				<div id="user-all" class="tabs-panel">
					<input type="hidden" value="" name="<?php echo $option_name ?>[]">
					<ul id="userchecklist" class="userchecklist form-no-clear">

					<?php
					foreach( $roles as $slug => $role ) {

						$users = get_users( array( 'role' => $slug, 'orderby' => 'display_name' ) );

						if( ! $users )
							continue;

						$role_users = '';

						foreach( $users as $user ) {

							$role_users .= sprintf( '<li class="term-user-%1$s"><label class="selectit"><input id="term-user-%1$s" type="checkbox"%4$s name="%3$s[]" value="%1$s"> %2$s</label></li>',
								$user->ID,
								sprintf( '%s <small>(%s)</small> <a href="%s" title="Éditer le profil de l\'utilisateur"><i class="dashicons-before dashicons-edit"></i></a>',
									$user->data->display_name,
									$user->data->user_email,
									add_query_arg(
									    array(
									        'user_id' 	 => $user->ID,
									        'wp_http_referer' => campus_current_page_url()
									    ),
									    admin_url( 'user-edit.php' )
									)
								),
								$option_name,
								checked( true, in_array( $user->ID, $checked_users ), false )
							);
						}

						printf( '<li class="term-role-%1$s"><label class="selectit">%2$s</label><ul class="children">%3$s</ul></li>',
							$slug,
							_x( $role['name'], 'User role' ),
							$role_users
						);

					}
					?>
					</ul>
				</div>
			</div>
			<?php
			break;

		case 'checkbox' :
		case 'radio' :


			printf( '<input type="%s" id="%s" name="%s"%s />',
				$option_args['type'],
				$id,
				$option_name,
				checked( get_option( $option_name, $default_value ), 'on', false )
			);

			if( $description )
				printf( '<label for="%s">%s</label>', $id, $description );

			break;

		case 'select' :

			if( ! empty( $option_args['options'] ) ) {

				$selected = get_option( $option_name, $default_value );
				$options = $option_args['options'];
				$options_str = '';

				foreach( $options as $value => $option )
					$options_str .= sprintf( '<option value="%1$s"%3$s>%2$s</option>', $value, $option, selected( $value, $selected, false ) );

				printf( '<select id="%s" name="%s"%s>%s</select>',
					$id,
					$option_name,
					$attr_str,
					$options_str
				);
			}
			break;

		case 'textarea' :
			printf( '<textarea id="%s" name="%s"%s>%s</textarea>',
				$id,
				$option_name,
				$attr_str,
				get_option( $option_name, $default_value )
			);

			break;

		default :
			printf( '<input type="%s" id="%s" name="%s" value="%s"%s />',
				$option_args['type'],
				$id,
				$option_name,
				get_option( $option_name, $default_value ),
				$attr_str
			);

			break;
	}

	// Suffix
	echo ! empty( $option_args['suffix'] ) ? sprintf( '<span class="suffix-for-%s">%s</span>', $id, $option_args['suffix'] ) : '';

	if( $description && $option_args['type'] != 'checkbox' && $option_args['type'] != 'radio' )
		echo '<p class="description">' . $description . '</p>';
}


/**
 * Add Featured Image column
 */
function campus_edit_admin_columns( $columns ) {
	if ( current_theme_supports( 'post-thumbnails' ) ) {
		// add featured image before 'Formation'
		$columns = array_slice( $columns, 0, 1, true ) + array( 'thumbnail' => '<span class="dashicons-before dashicons-format-image"></span>' ) + array_slice( $columns, 1, NULL, true );
	}

	return $columns;
}
add_filter( 'manage_post_posts_columns', 'campus_edit_admin_columns' );

/**
 * Add featured image to column
 */
function campus_admin_columns( $column, $post_id ) {
	global $post;
	switch ( $column ) {
		case 'thumbnail':
			echo '<div class="thumbnail-wrap" style="position:relative;">';
				echo get_the_post_thumbnail( $post_id, 'player-thumbnail' );
			echo '</div>';
			break;
	}
}
add_filter( 'manage_post_posts_custom_column', 'campus_admin_columns', 10, 2 );

/**
 * Thumbnail column added to term admin.
 *
 * @access public
 * @param mixed $columns
 * @return void
 */
function campus_term_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb'] = $columns['cb'];
	$new_columns['thumbnail'] = '<span class="dashicons-before dashicons-format-image"></span>';
	$new_columns['name'] = __( 'Name' );
	$new_columns['short_description'] = __( 'Description' );

	unset( $columns['cb'] );
	unset( $columns['name'] );
	unset( $columns['description'] );

	return array_merge( $new_columns, $columns );
}

add_filter( 'manage_edit-category_columns', 'campus_term_columns' );
add_filter( 'manage_edit-post_tag_columns', 'campus_term_columns' );

/**
 * Thumbnail column value added to category admin.
 *
 * @access public
 * @param mixed $columns
 * @param mixed $column
 * @param mixed $id
 * @return void
 */
function campus_term_column( $columns, $column, $term_id ) {

	$screen = get_current_screen();
	$taxonomy = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : $screen->taxonomy;

	if ( $column == 'thumbnail' ) {

		$image = campus_get_category_thumbnail( array( 'term_id' => $term_id, 'taxonomy' => $taxonomy, 'size' => array(180,180) ) );
		if( $image ) {
			$classes = campus_get_all_term_classes( $term_id, $taxonomy );
			$columns .= '<div class="thumbnail-wrap ' . $classes . '"><div class="taxonomy-thumbnail">' . $image . '</div></div>';
		}
	} else if ( $column == 'short_description' ) {

		$columns .= campus_excerpt( term_description( $term_id, $taxonomy ), 100, false );

	}

	return $columns;
}

add_filter( 'manage_category_custom_column', 'campus_term_column', 10, 3 );
add_filter( 'manage_post_tag_custom_column', 'campus_term_column', 10, 3 );



/**
 * Add term meta fields.
 *
 * @access public
 * @return void
 */
function campus_add_term_fields() {

    $term = get_queried_object();
    $screen = get_current_screen();
    $taxonomy = $screen->taxonomy;
    $post_type = $screen->post_type;
	$fields = array();

	if( $taxonomy == 'category' )
		$fields = campus_presenter_options_fields();
	elseif( $taxonomy == 'post_tag' )
		$fields = campus_tag_options_fields();

	$fields = apply_filters( 'campus_custom_term_fields', $fields, $taxonomy );

	foreach( $fields as $name => $field ) {

		$title 		 = ! empty( $field['title'] ) ? $field['title'] : false;
		$description = ! empty( $field['description'] ) ? $field['description'] : false;
		$value 		 = ! empty( $field['value'] ) ? $field['value'] : false;

		if( $screen->base == 'edit-tags' ) {
			if( $field['type'] == 'section' ) {
				if( $title ) { ?>
					<div class="form-field">
		    			<h3><?php printf( '<label for="term_%s">%s</label>', $name, $title ); ?></h3>
		    		</div>
    			<?php }
			} else { ?>
				<div class="form-field">
	    			<?php if( $title ) printf( '<label for="term_%s">%s</label>', $name, $title ); ?>
	    			<?php echo campus_admin_setting_field( array( 'option_name' => $name, 'option_args' => $field ) ); ?>
	    		</div>
    		<?php }
    	} else {
			if( $field['type'] == 'section' ) {
				if( $title ) { ?>
					<tr class="form-field">
						<th colspan="2">
		    				<h3><?php printf( '<label for="term_%s">%s</label>', $name, $title ); ?></h3>
						</th>
		    		</tr>
    			<?php }
			} else { ?>
	    		<tr class="form-field">
	    			<th scope="row" valign="top"><?php if( $title ) printf( '<label for="term_%s">%s</label>', $name, $title ); ?></th>
					<td>
						<?php echo campus_admin_setting_field( array( 'option_name' => $name, 'option_args' => $field ) ); ?>
	    			</td>
	    		</tr>
    		<?php }
    	}
	}
}

add_action( 'category_add_form_fields', 'campus_add_term_fields' );
add_action( 'category_edit_form_fields', 'campus_add_term_fields' );

add_action( 'post_tag_add_form_fields', 'campus_add_term_fields' );
add_action( 'post_tag_edit_form_fields', 'campus_add_term_fields' );


/**
 * term_fields_save function.
 *
 * @access public
 * @param mixed $term_id Term ID being saved
 * @param mixed $tt_id
 * @param mixed $taxonomy Taxonomy of the term being saved
 * @return void
 */
function campus_term_fields_save( $term_id = null, $tt_id = null, $taxonomy = null ) {
	$term_id = ! is_null( $term_id ) ? $term_id : (int) $_POST['tag_ID'];
	$taxonomy = ! is_null( $taxonomy ) ? $taxonomy : $_POST['taxonomy'];

	if( ! $term_id || ! $taxonomy )
		return;

	$fields = array();

	if( $taxonomy == 'category' ) {
		$fields = campus_presenter_options_fields();
	} elseif( $taxonomy == 'post_tag' ) {
		$fields = campus_tag_options_fields();
	}

	$fields = apply_filters( 'campus_custom_term_fields', $fields, $taxonomy );

	if( $fields ) {

		// Decompose fields
		foreach( $fields as $name => $field ) {
			if( $field['type'] === 'composed' ) {

				// unset composed field
				unset( $fields[$name] );

				// Recreate it with good values
				foreach( $field['inputs'] as $input_name => $input_field ) {
					if( strpos( $input_name, '[' ) ) {

						preg_match( '/([^\[]+)\[([^\]]+)/', $input_name, $name_parts );

						if( count( $name_parts ) ) {
							$fields[$name_parts[1]][$name_parts[2]] = $input_field;
						}
					} else {
						$fields[$input_name] = $input_field;
					}
				}
			}
		}

		// Save data
		foreach( $fields as $name => $field ) {

			if( isset( $field['readonly'] ) && $field['readonly'] || $name == 'description' )
				continue;

			$new_value = ! empty( $_POST[$name] ) ? $_POST[$name] : false;
			$old_value = ! empty( $field['value'] ) ? $field['value'] : false;

			if( is_array( $new_value ) ) {
				$new_value = array_filter( $new_value );
				if( empty( $new_value ) )
					$new_value = false;
			}

			if( $new_value ) {
				if( $new_value != $old_value ) {
					update_term_meta( $term_id, $name, $new_value, $old_value );
				}
			} else {
				delete_term_meta( $term_id, $name );
			}

			// Create / Save podcast image...
			if( $name == 'thumbnail_id' ) {

				// ...only if thumbnail_id has changed or if there's no podcast_thumbnail_id yet
				if( $new_value && $new_value != $old_value || empty( $_POST['podcast_thumbnail_id'] ) ) {
					campus_save_podcast_category_image();
				}
			}
		}

		// Log it
		$current_user = wp_get_current_user();
		$term = get_term( $term_id, $taxonomy );
		SMN_Admin_Notices::save_message( sprintf( '<a href="%s">%s (%s)</a> modifié(e) par <a href="%s">%s</a>',
			admin_url( sprintf( 'term.php?taxonomy=%s&tag_ID=%s',
				$taxonomy,
				$term->term_id
			) ),
			$term->name,
			$taxonomy,
			admin_url( sprintf( 'user-edit.php?user_id=%s',
				$current_user->ID
			) ),
			$current_user->display_name
		) );
	}
}

add_action( 'created_term', 'campus_term_fields_save', 10, 3 );
add_action( 'edit_term', 'campus_term_fields_save', 10, 3 );

/**
 * Presenter term_fields_save function.
 *
 * Used on campus_presenter_page to save term fields
 *
 * @access public
 * @return void
 */
function campus_presenter_term_fields_save() {

	if( empty( $_POST['option_page'] ) || $_POST['option_page'] != 'campus_presenter_options' ||
		empty( $_POST['action'] ) || $_POST['action'] != 'update' ||
		empty( $_POST['tag_ID'] ) )
		return;

	if( $_POST['tag_ID'] != Author_Category::get_user_cat() ) {
		add_action( 'admin_notices', function() { echo '<div class="notice notice-error"><p>' . __( 'Cheatin&#8217; uh?' ) . '</p></div>'; } );
		return;
	} else {
		add_action( 'admin_notices', function() { echo '<div class="notice notice-success"><p>Paramètres sauvegardés</p></div>'; } );
	}

	// Update Description and all meta
	wp_update_term( $_POST['tag_ID'], $_POST['taxonomy'], array(
	    'description' => $_POST['description']
	) );

	//campus_term_fields_save();

}
add_action( 'admin_init', 'campus_presenter_term_fields_save' );


/**
 * Save Apple podcast image
 */
function campus_save_podcast_category_image() {
	$term_id = (int) $_POST['tag_ID'];
	$taxonomy = $_POST['taxonomy'];
	$thumbnail_id = $_POST['thumbnail_id'];

	if( ! $term_id || ! $taxonomy || ! $thumbnail_id )
		return;

	$option = get_option( 'powerpress_cat_feed_' . $term_id );

	$podcast_thumbnail_id = campus_set_podcast_category_image( $term_id, $thumbnail_id );

	if( ! $podcast_thumbnail_id )
		return false;

	// Update term meta
	update_term_meta( $term_id, 'podcast_thumbnail_id', $podcast_thumbnail_id );

	// Update PowerPress feed option
	if( ! is_array( $option ) )
		$option = array( 'itunes_image' => '' );
	$option['itunes_image'] = wp_get_attachment_url( $podcast_thumbnail_id );
	update_option( 'powerpress_cat_feed_' . $term_id, $option );

}

/**
 * Create Apple podcast image
 */
function campus_set_podcast_category_image( $term_id, $attachment_id = null ) {

	// Check for $term_id
	if( ! $term_id ) {
		return array( 'message' => __( 'Podcast Image : Aucune catégorie n\'est définie' ) );
	}

	// Check for $term_id
	if( ! $attachment_id ) {
		$attachment_id = get_term_meta( $term_id, 'thumbnail_id', true );

		if( ! $attachment_id )
			return array( 'message' => __( 'Podcast Image : Aucune image n\'est définie' ) );
	}

	$ancestors = get_ancestors( $term_id, 'category', 'taxonomy' );

	// If there's no ancestor, there's no color
	if( $ancestors ) {

		$color = false;

		// Check for color
		foreach( $ancestors as $ancestor ) {

			$colors = campus_get_term_colors();
			if( array_key_exists( $ancestor, $colors ) ) {
				$color = $colors[$ancestor];

				// Check image type
				if( ! preg_match( '/^(?:#[0-9a-fA-F]{6})$/', $color ) ) {
					return array( 'message' => 'Podcast Image : La couleur n\'est pas correctement définie' );
				}

				// Stop loop
				break;
			}
		}

		// Combine images
		if( $color ) {

			// Get term
			$term = get_term( $term_id, 'category' );

			// Get upload dir
			$wp_upload_dir = wp_upload_dir();

			$image = wp_get_attachment_url( $attachment_id );

			if( ! $image ) {
				return array( 'message' => 'Podcast Image : L\'image n\'existe pas' );
			}

			/*------------------------------------------------------
			term image
			------------------------------------------------------*/

			// Get sizes
			$sizes = campus_get_podcast_category_image_sizes();

			// Get path of images
			$image_filename = str_replace( get_bloginfo( 'url' ) . '/', ABSPATH, $image );

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$image_filetype = wp_check_filetype( basename( $image_filename ), null );

			// Set images
			if( $image_filetype['ext'] == 'png' ) {
				$_image = imagecreatefrompng( $image_filename );
			} else if( $image_filetype['ext'] == 'gif' ) {
				$_image = imagecreatefromgif( $image_filename );
			} else if( $image_filetype['ext'] == 'jpg' ) {
				$_image = imagecreatefromjpeg( $image_filename );
			} else {
				return array( 'message' => 'Podcast Image : L\'image n\'a pas pu être enregistrée car vous ne pouvez utiliser que des images jpg, png ou gif.' );
			}

			// Set image new dimensions
			list( $_image_width, $_image_height ) = getimagesize( $image_filename );
			// $category_image = imagecreatetruecolor( $sizes['image_width'], $sizes['image_height'] );
			// imagecopyresampled( $category_image, $_image, 0, 0, 0, 0, $sizes['image_width'], $sizes['image_height'], $sizes['image_width'], $sizes['image_height'] );

			// Create image
			$new_image = imagecreatetruecolor( $sizes['final_width'], $sizes['final_width'] );

			// Add background
			$rgb_color = smn_hex2rgb( $color, true );
			$bg_color = imagecolorallocate( $new_image, $rgb_color[0], $rgb_color[1], $rgb_color[2] );
			imagefill( $new_image, 0, 0, $bg_color );

			// Add image
			imagecopyresampled( $new_image, $_image, $sizes['image_margin_x'], $sizes['image_margin_y'], 0, 0, $sizes['image_width'], $sizes['image_height'], $_image_width, $_image_height );

			/*------------------------------------------------------
			Logo
			------------------------------------------------------*/

			// Get logo filename
			$logo_filename = get_parent_theme_file_path( '/assets/images/logo-cache-podcast.png' );

			// Set logo
			$_logo = imagecreatefrompng( $logo_filename );

			// Add logo
			imagecopyresampled( $new_image, $_logo, 0, 0, 0, 0, $sizes['final_width'], $sizes['final_width'], $sizes['final_width'], $sizes['final_width'] );


			/*------------------------------------------------------
			Text
			------------------------------------------------------*/

			// Get fonts
			$font_black = get_parent_theme_file_path( '/assets/fonts/28CFF3_5_0.ttf' );
			$font_bold = get_parent_theme_file_path( '/assets/fonts/28CFF3_3_0.ttf' );
			$white = imagecolorallocate( $new_image, 255, 255, 255 );

			// Add title
			$title_fz = 56;
			$title_xy = imagettfbbox ( $title_fz , 0 , $font_black , $term->name );
			$title_x = $title_xy[0] + ( $sizes['final_width'] / 2 ) - ( $title_xy[4] / 2 );
			$title_y = ( $sizes['image_margin_y'] / 2 ) + ( $title_fz / 2 );
			imagettftext( $new_image, $title_fz, 0, $title_x, $title_y, $white, $font_black, $term->name );

			// Add url
			$url_text = strtoupper( str_replace( array( 'http://', 'https://' ), '', get_bloginfo( 'url' ) ) );
			$url_text = trim( implode( '&#8202;', str_split( $url_text ) ) );
			$url_text_fz = 26;
			$url_text_xy = imagettfbbox( $url_text_fz, 270 , $font_black , $url_text );
			$url_text_x = ( $sizes['image_margin_x'] / 2 ) - ( $url_text_fz / 2 );
			$url_text_y = ( $sizes['final_height'] / 2 ) - ( $url_text_xy[5] / 2 ) + 15; // 15 because there is some shift in imagettfbbox calculation...
			imagettftext( $new_image, $url_text_fz, 270, $url_text_x, $url_text_y, $white, $font_bold, $url_text );

			// Add schedules
			$schedules = strtoupper( campus_get_broadcast_schedules_inline( $term_id ) );
			// $schedules = trim( implode( '&#8202;', str_split( $schedules ) ) );
			$schedules_fz = 34;
			$schedules_xy = imagettfbbox( $schedules_fz , 90 , $font_black , $schedules );
			$schedules_x = $sizes['image_margin_x'] + $sizes['image_width'] + ( $sizes['image_margin_x'] / 2 ) + ( $schedules_fz / 2 );
			$schedules_y = $schedules_xy[1] + ( $sizes['final_height'] / 2 ) - ( $schedules_xy[5] / 2 );
			imagettftext( $new_image, $schedules_fz, 90, $schedules_x, $schedules_y, $white, $font_bold, $schedules );

			/*------------------------------------------------------
			Save image
			------------------------------------------------------*/

			// New image path
			$filename = sprintf( '%s/podcast-category-%s-%s.png', $wp_upload_dir['path'], $term->term_id, time() );

			// For test
			// header('Content-Type: image/png');
			// imagepng( $new_image );
			// imagedestroy( $new_image );
			// die;

			// Save image
			imagepng( $new_image, $filename );

			// Reset memory
			imagedestroy( $new_image );

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$filetype = wp_check_filetype( basename( $filename ), null );

			// Prepare an array of post data for the attachment.
			$object = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => 'Podcast - ' . $term->name,
				'post_content'   => '',
				'context'        => 'podcast-category-thumbnail',
			);

			// Insert the attachment.
			$attachment_id = wp_insert_attachment( $object, $filename );

			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
			wp_update_attachment_metadata( $attachment_id, $attach_data );

			return $attachment_id;
		}
	}

	return false;
}

/**
 * Ajax handler for cropping an image.
 *
 * @need capability smn_can_crop_image
 *
 * @since 6.0
 */
function smn_ajax_crop_image() {
	global $image_min_dim;

	$attachment_id = absint( $_POST['id'] );
	$term_id = absint( $_POST['term_id'] );

	check_ajax_referer( 'image_editor-' . $attachment_id, 'nonce' );
	if ( ! current_user_can( 'smn_can_crop_image' ) ) {
		wp_send_json_error();
	}

	$context = str_replace( '_', '-', $_POST['context'] );
	$data    = array_map( 'absint', $_POST['cropDetails'] );
	$cropped = wp_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );
	$mask    = filter_var( $_POST['mask'], FILTER_VALIDATE_URL ) ? $_POST['mask'] : false;

	if ( ! $cropped || is_wp_error( $cropped ) ) {
		wp_send_json_error( array( 'message' => __( 'Image could not be processed.' ) ) );
	}

	switch ( $context ) {

		case 'category-thumbnail':

			// Check for $term_id
			if( ! $term_id ) {
				wp_send_json_error( array( 'message' => __( 'Cheatin&#8217; uh?' ) ) );
			}

			// Get term
			$term = get_term( $term_id, 'category' );

			// Get upload dir
			$wp_upload_dir = wp_upload_dir();

			// Get path of images
			$image_filename = str_replace( get_bloginfo( 'url' ) . '/', ABSPATH, $cropped );

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$image_filetype = wp_check_filetype( basename( $image_filename ), null );

			// Set images
			if( $image_filetype['ext'] == 'png' ) {
				$_image = imagecreatefrompng( $image_filename );
			} else if( $image_filetype['ext'] == 'gif' ) {
				$_image = imagecreatefromgif( $image_filename );
			} else if( $image_filetype['ext'] == 'jpg' ) {
				$_image = imagecreatefromjpeg( $image_filename );
			} else {
				wp_send_json_error( array( 'message' => 'L\'image n\'a pas pu être enregistrée car vous ne pouvez utiliser que des images jpg, png ou gif.' ) );
			}

			// Get dimentions
			$min_width = $min_height = $image_min_dim;
			list( $image_width, $image_height ) = getimagesize( $image_filename );

			// Check dimentions
			$new_width = ( $image_width < $min_width ) ? $min_width : $image_width;
			$new_height = ( $image_height < $min_height ) ? $min_height : $image_height;

			// Create image
			$new_image = imagecreatetruecolor( $new_width, $new_height );

			// Add image
			imagecopyresampled( $new_image, $_image, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height );

			// Combine images
			if( $mask ) {

				// Get path of images
				$mask_filename = str_replace( get_bloginfo( 'url' ) . '/', ABSPATH, $mask );

				// Set images
				$_mask = imagecreatefrompng( $mask_filename );

				// Get dimentions
				list( $mask_width, $mask_height ) = getimagesize( $mask_filename );

				// Add mask
				imagecopyresampled( $new_image, $_mask, 0, 0, 0, 0, $new_width, $new_height, $mask_width, $mask_height );

			}

			// New image path
			$filename = sprintf( '%s/category-%s-%s.png', $wp_upload_dir['path'], $term->term_id, time() );

			// Save image
			imagepng( $new_image, $filename );

			// Reset memory
			imagedestroy( $new_image );

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$filetype = wp_check_filetype( basename( $filename ), null );

			// Prepare an array of post data for the attachment.
			$object = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => $term->name,
				'post_content'   => '',
				'context'        => $context,
			);

			// Insert the attachment.
			$attachment_id = wp_insert_attachment( $object, $filename );

			if( $attachment_id ) {
				unlink( $cropped );
			}

			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
			wp_update_attachment_metadata( $attachment_id, $attach_data );

			break;

		default:

			/**
			 * Fires before a cropped image is saved.
			 *
			 * Allows to add filters to modify the way a cropped image is saved.
			 *
			 * @since 4.3.0
			 *
			 * @param string $context       The Customizer control requesting the cropped image.
			 * @param int    $attachment_id The attachment ID of the original image.
			 * @param string $cropped       Path to the cropped image file.
			 */
			do_action( 'wp_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

			/** This filter is documented in wp-admin/custom-header.php */
			$cropped = apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

			$parent_url = wp_get_attachment_url( $attachment_id );
			$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );

			$size       = @getimagesize( $cropped );
			$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';

			$object = array(
				'post_title'     => basename( $cropped ),
				'post_content'   => $url,
				'post_mime_type' => $image_type,
				'guid'           => $url,
				'context'        => $context,
			);

			$attachment_id = wp_insert_attachment( $object, $cropped );
			$metadata = wp_generate_attachment_metadata( $attachment_id, $cropped );

			/**
			 * Filters the cropped image attachment metadata.
			 *
			 * @since 4.3.0
			 *
			 * @see wp_generate_attachment_metadata()
			 *
			 * @param array $metadata Attachment metadata.
			 */
			$metadata = apply_filters( 'wp_ajax_cropped_attachment_metadata', $metadata );
			wp_update_attachment_metadata( $attachment_id, $metadata );

			/**
			 * Filters the attachment ID for a cropped image.
			 *
			 * @since 4.3.0
			 *
			 * @param int    $attachment_id The attachment ID of the cropped image.
			 * @param string $context       The Customizer control requesting the cropped image.
			 */
			$attachment_id = apply_filters( 'wp_ajax_cropped_attachment_id', $attachment_id, $context );
	}

	wp_send_json_success( wp_prepare_attachment_for_js( $attachment_id ) );
}

add_action( 'wp_ajax_smn_crop_image', 'smn_ajax_crop_image' );
