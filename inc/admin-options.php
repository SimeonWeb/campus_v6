<?php
/**
 * Campus Options
 *
 * @need admin-addons.php
 */


/**
 * Define options fields
 *
 */
function campus_options_fields() {
	$options = array(
		'player' 				  => array(
			'title' => 'Player',
			'type'  => 'section',
		),
		'live_url' 				  => array(
			'title' => 'URL du flux live',
			'type'  => 'url',
			'class'	=> 'widefat',
			'value' => 'http://80.82.229.202/rca',
		),
		'programs_page' 		  => array(
			'title' => 'Page des programmes (grille)',
			'type'  => 'dropdown_pages',
		),
		'playlist_page' 		  => array(
			'title' => 'Page de la playlist',
			'type'  => 'dropdown_pages',
		),
		'player_page' 			  => array(
			'title' => 'Page du player (popup)',
			'type'  => 'dropdown_pages',
		),
		'daily_playlist' => array(
			'title' => 'Programmation automatique',
			'type'  => 'section',
		),
		'daily_playlist_excluded_terms' => array(
			'title' => 'Exclure les entrées dont la catégorie comprend :',
			'type'  => 'textarea',
			'value' => 'Jingle,Jinjle,Auto Promos,Redif,TESTS NE PAS DIFFUSER',
			'class'	=> 'widefat',
			'description' => 'Séparer les termes par des virgules.'
		),
		'ads' => array(
			'title' => 'AdRotate',
			'type'  => 'section',
		),
		'popup_adrotate_group_id' => array(
			'title' => 'Groupe AdRotate du player (popup)',
			'type'  => 'text',
		),
		'content_square_adrotate_group_id' => array(
			'title' => 'Groupe AdRotate du contenu (carré)',
			'type'  => 'text',
		),
		'category' 				  => array(
			'title' => 'Catégories',
			'type'  => 'section',
		),
		'category_by_priority' => array(
			'title' => 'Catégorie prioritaire',
			'type'  => 'dropdown_category',
		),
		'category_talk_id' => array(
			'title' => 'Catégorie "La redac"',
			'type'  => 'dropdown_category',
		),
		'category_talk_color' => array(
			'title' => 'Couleur des émissions "La redac"',
			'type'  => 'text',
			'class' => 'color-picker regular-text'
		),
		'category_talk_thumbnail_mask' => array(
			'title' => 'Masque pour les images des émissions "La redac"',
			'type'  => 'image_uploader',
		),
		'category_music_id' => array(
			'title' => 'Catégorie "Musique"',
			'type'  => 'dropdown_category',
		),
		'category_music_color' => array(
			'title' => 'Couleur des émissions "Musique"',
			'type'  => 'text',
			'class' => 'color-picker regular-text'
		),
		'category_music_thumbnail_mask' => array(
			'title' => 'Masque pour les images des émissions "Musique"',
			'type'  => 'image_uploader',
		),
		'category_other_id' => array(
			'title' => 'Catégories "Autres"',
			'type'  => 'dropdown_category',
		),
		'category_other_color' => array(
			'title' => 'Couleur des émissions "Autres"',
			'type'  => 'text',
			'class' => 'color-picker regular-text'
		),
		'category_other_thumbnail_mask' => array(
			'title' => 'Masque pour les images des émissions "Autres"',
			'type'  => 'image_uploader',
		),
	);

	return $options;
}


function campus_add_options_page() {
	add_options_page(
		'Options',
		'Options',
		'manage_options',
		'campus_options',
		'campus_options_page'
	);
}

add_action( 'admin_menu', 'campus_add_options_page' );

/**
 * Add setting fields to admin
 *
 */
function campus_init_admin_options() {

	campus_init_admin_setting_fields( 'campus_options', campus_options_fields() );
}

add_action( 'admin_init', 'campus_init_admin_options' );

/**
 * Create options page
 *
 */
function campus_options_page() {
	?>
	<div class="wrap">
		<h1>Options</h1>
		<form method="POST" action="options.php">
		<?php settings_fields( 'campus_options' );	//pass slug name of page, also referred
		                                	        //to in Settings API as option group name
		do_settings_sections( 'campus_options' ); 	//pass slug name of page
		submit_button();
		?>
		</form>
	</div>
	<?php
}

function campus_roles_and_capabilities() {

	//remove_role('programmer');
	//remove_role('presenter');
	add_role(
		'presenter',
		'Animateur',
		array(
			'delete_posts' => true,
			'delete_published_posts' => true,
			'edit_posts' => true,
			'edit_published_posts' => true,
			'publish_posts' => true,
			'read' => true,
			'upload_files' => true,
			'book_event' => true,
			'edit_show_infos' => true,
			'smn_can_crop_image' => true
			/* 'manage_categories' => true, */
			/* 'manage_term_interests' => true, */
			/* 'manage_term_social_links' => true */
		)
	);

	add_role(
		'programmer',
		'Programmateur',
		array(
			'delete_others_pages' => true,
			'delete_others_posts' => true,
			'delete_pages' => true,
			'delete_posts' => true,
			'delete_private_pages' => true,
			'delete_private_posts' => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages' => true,
			'edit_others_posts' => true,
			'edit_pages' => true,
			'edit_posts' => true,
			'edit_private_pages' => true,
			'edit_private_posts' => true,
			'edit_published_pages' => true,
			'edit_published_posts' => true,
			'manage_categories' => true,
			'manage_links' => true,
			'moderate_comments' => true,
			'publish_pages' => true,
			'publish_posts' => true,
			'read' => true,
			'read_private_pages' => true,
			'read_private_posts' => true,
			'unfiltered_html' => true,
			'upload_files' => true,
			'create_users' => true,
			'delete_users' => true,
			'edit_users' => true,
			'list_users' => true,
			'promote_users' => true,
			'remove_users' => true,
			'manage_daily_playlists' => true,
			'edit_album' => true,
			'read_album' => true,
			'delete_album' => true,
			'edit_albums' => true,
			'edit_others_albums' => true,
			'publish_albums' => true,
			'read_private_albums' => true,
			'delete_albums' => true,
			'delete_private_albums' => true,
			'delete_published_albums' => true,
			'delete_others_albums' => true,
			'edit_private_albums' => true,
			'edit_published_albums' => true,
			'manage_album_terms' => true,
			'edit_album_terms' => true,
			'delete_album_terms' => true,
			'assign_album_terms' => true,
			'book_unlimited_event' => true,
			'book_event' => true,
			'smn_can_crop_image' => true
		)
	);

	$capabilities = array(
		'administrator' => array(
			'manage_daily_playlists',
			'edit_album',
			'read_album',
			'delete_album',
			'edit_albums',
			'edit_others_albums',
			'publish_albums',
			'read_private_albums',
			'delete_albums',
			'delete_private_albums',
			'delete_published_albums',
			'delete_others_albums',
			'edit_private_albums',
			'edit_published_albums',
			'manage_album_terms',
			'edit_album_terms',
			'delete_album_terms',
			'assign_album_terms',
			'book_unlimited_event',
			'book_event',
			'smn_can_crop_image' ),
		'editor' => array(
			'create_users',
			'delete_users',
			'edit_users',
			'list_users',
			'promote_users',
			'remove_users',
			'manage_daily_playlists',
			'book_unlimited_event',
			'book_event',
			'edit_theme_options',
			'smn_can_crop_image'
			),
		'programmer' => array(
			),
		'author' => array(
			),
		'contributor' => array(
			),
		'presenter' => array(
			),
		'subscriber' => array(
			)
	);

	// gets the author role
	$roles = get_editable_roles();

	foreach( $roles as $slug => $role_array ) {
		$role = get_role( $slug );

		if( ! empty( $capabilities[$slug] ) ) {
			foreach( $capabilities[$slug] as $capability ) {
				// This only works, because it accesses the class instance.
				// would allow the author to edit others' posts for current theme only
				$role->add_cap( $capability );
			}
		}
	}
}

add_action( 'admin_init', 'campus_roles_and_capabilities');
