<?php
/**
 * @package Campus
 * @version 5.0.79
 */
/*
Plugin Name: Campus Album
Plugin URI: http://www.radiocampusangers.com/
Description: Gestion des playlists mensuelles.
Author: Simon Le Vraux
Version: 1.0
Author URI: http://www.graphonik.fr/
*/

class Campus_Album {

	var $post_type = 'album';

	var $current_term_slug = false;

	var $options;

	function __construct() {

		$this->set_options();

		add_action( 'init', array( $this, 'init_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'add_post_columns' ) );
		add_filter( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'render_post_columns' ), 10, 2);
		add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit_custom_box' ), 10, 2);
		add_action( 'admin_enqueue_scripts', array( $this, 'quick_edit_javascript' ) );

		if( function_exists( 'campus_edit_admin_columns' ) )
			add_filter( 'manage_' . $this->post_type . '_posts_columns', 'campus_edit_admin_columns' );

		if( function_exists( 'campus_admin_columns' ) )
			add_filter( 'manage_' . $this->post_type . '_posts_custom_column', 'campus_admin_columns', 10, 2 );

		add_action( 'save_post', array( $this, 'save_post'), 10, 1 );

		// add_action( 'edit_term', array( &$this, 'edit_term' ), 10, 3 );
		// add_action( 'edited_term', array( &$this, 'edited_term' ), 10, 3 );

		//add_filter( 'get_terms', array(&$this, 'album_get_terms'), 10, 3 );
		//add_filter( 'get_the_terms', array(&$this, 'album_get_terms'), 10, 3 );

		// Query filters
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 20 );
	}

	function pre_get_posts( $query ) {

		if( is_admin() )
			return;

		// Album playlist taxonomy query
		if( $query->get( $this->post_type . '_playlist' ) && $query->is_main_query() ) {
			$query->set( 'posts_per_page', '-1' );
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		}

		// Album archive query
		if( $query->is_post_type_archive( $this->post_type ) && $query->is_main_query() ) {
			$query->set( 'post_type', 'block' );
			$query->set( 'meta_query', array(
				array(
					'key'     => 'block_type',
					'value'   => $this->post_type . '_playlist',
				),
			) );
		}
	}

	function init_post_type() {

		// Add Album post type
		$custom_post_labels = array(
		  'name' => _x('Albums', 'post type general name', 'gkcpt'),
		  'singular_name' => _x('Album', 'post type singular name', 'gkcpt'),
		  'add_new' => _x('Ajouter', 'album', 'gkcpt'),
		  'add_new_item' => __('Ajouter un album', 'gkcpt'),
		  'edit_item' => __('Modifier l\'album', 'gkcpt'),
		  'new_item' => __('Nouvel album', 'gkcpt'),
		  'all_items' => __('Tous les albums', 'gkcpt'),
		  'view_item' => __('Voir l\'album', 'gkcpt'),
		  'search_items' => __('Rechercher un album', 'gkcpt'),
		  'not_found' =>  __('Aucun album trouvé', 'gkcpt'),
		  'not_found_in_trash' => __('Aucun album trouvé dans la corbeille', 'gkcpt'),
		  'parent_item_colon' => '',
		  'menu_name' => _x('Albums', 'post type menu name', 'gkcpt'),

		);

		$custom_post_args = array(
		  'labels' => $custom_post_labels,
		  'public' => true,
		  'publicly_queryable' => true,
		  'show_ui' => true,
		  'show_in_menu' => true,
		  'query_var' => true,
		  'rewrite' => array( 'slug' => 'playlists' ),
		  'capability_type' => 'album', // try with 'album'
		  'has_archive' => true,
		  'hierarchical' => false,
		  'menu_position' => 39,
		  'menu_icon' => 'dashicons-album',
		  'supports' => array( 'title', 'editor', 'thumbnail' )
		);

		register_post_type( $this->post_type, $custom_post_args );

		$tax_capabilities = array(
		    'manage_terms' => 'manage_album_terms',
		    'edit_terms' => 'edit_album_terms',
		    'delete_terms' => 'delete_album_terms',
		    'assign_terms' => 'assign_album_terms'
		);

		// Add artist taxonomy
		$artist_labels = array(
			'name' => __( 'Artistes' ),
			'singular_name' => __( 'Artiste' ),
			'search_items' =>  __( 'Rechercher un artiste' ),
			'popular_items' => __( 'Artistes populaires' ),
			'all_items' => __( 'Tous les artistes' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Modifier l\'artiste' ),
			'update_item' => __( 'Mettre à jour l\'artiste' ),
			'add_new_item' => __( 'Ajouter un nouvel artiste' ),
			'new_item_name' => __( 'New Artiste Name' ),
			'separate_items_with_commas' => __( 'Séparer les artistes par des virgules' ),
			'add_or_remove_items' => __( 'Ajouter ou supprimer des artistes' ),
			'choose_from_most_used' => __( 'Choisir parmis les artistes les plus utilisés' ),
			'menu_name' => __( 'Artistes' ),
		);

		$artist_args = array(
			'hierarchical' => false,
			'labels' => $artist_labels,
			'capabilities' => $tax_capabilities,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'update_count_callback' => '_update_post_term_count',
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'artiste', 'hierarchical' => true )
		);

		register_taxonomy( $this->post_type.'_artist', $this->post_type, $artist_args );


		// Add song title taxonomy
		$song_labels = array(
			'name' => __( 'Titres' ),
			'singular_name' => __( 'Titre' ),
			'search_items' =>  __( 'Rechercher un titre' ),
			'popular_items' => __( 'Titres populaires' ),
			'all_items' => __( 'Tous les titres' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Modifier le titre' ),
			'update_item' => __( 'Mettre à jour le titre' ),
			'add_new_item' => __( 'Ajouter un nouveau titre' ),
			'new_item_name' => __( 'New song Name' ),
			'separate_items_with_commas' => __( 'Séparer les titres par des virgules' ),
			'add_or_remove_items' => __( 'Ajouter ou supprimer des titres' ),
			'choose_from_most_used' => __( 'Choisir parmis les titres les plus utilisés' ),
			'menu_name' => __( 'Titres' ),
		);

		$song_args = array(
			'hierarchical' => false,
			'labels' => $song_labels,
			'capabilities' => $tax_capabilities,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'update_count_callback' => '_update_post_term_count',
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'titre', 'hierarchical' => true )
		);

		register_taxonomy( $this->post_type.'_song', $this->post_type, $song_args );

		// Add label taxonomy
		$label_labels = array(
			'name' => __( 'Labels' ),
			'singular_name' => __( 'Label' ),
			'search_items' =>  __( 'Rechercher un label' ),
			'popular_items' => __( 'Labels populaires' ),
			'all_items' => __( 'Tous les labels' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Modifier le label' ),
			'update_item' => __( 'Mettre à jour le label' ),
			'add_new_item' => __( 'Ajouter un nouvel label' ),
			'new_item_name' => __( 'New Label Name' ),
			'separate_items_with_commas' => __( 'Séparer les labels par des virgules' ),
			'add_or_remove_items' => __( 'Ajouter ou supprimer des labels' ),
			'choose_from_most_used' => __( 'Choisir parmis les labels les plus utilisés' ),
			'menu_name' => __( 'Labels' ),
		);

		$label_args = array(
			'hierarchical' => false,
			'labels' => $label_labels,
			'capabilities' => $tax_capabilities,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'update_count_callback' => '_update_post_term_count',
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'label', 'hierarchical' => true )
		);

		register_taxonomy( $this->post_type.'_label', $this->post_type, $label_args );


		// Add genre taxonomy
		$genre_labels = array(
			'name' => __( 'Genres' ),
			'singular_name' => __( 'Genre' ),
			'search_items' =>  __( 'Rechercher un genre' ),
			'popular_items' => __( 'Genres populaires' ),
			'all_items' => __( 'Tous les genres' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Modifier le genre' ),
			'update_item' => __( 'Mettre à jour le genre' ),
			'add_new_item' => __( 'Ajouter un nouvel genre' ),
			'new_item_name' => __( 'New Genre Name' ),
			'separate_items_with_commas' => __( 'Séparer les genres par des virgules' ),
			'add_or_remove_items' => __( 'Ajouter ou supprimer des genres' ),
			'choose_from_most_used' => __( 'Choisir parmis les genres les plus utilisés' ),
			'menu_name' => __( 'Genres' ),
		);

		$genre_args = array(
			'hierarchical' => false,
			'labels' => $genre_labels,
			'capabilities' => $tax_capabilities,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'update_count_callback' => '_update_post_term_count',
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'genre', 'hierarchical' => true )
		);

		register_taxonomy( $this->post_type.'_genre', $this->post_type, $genre_args );


		// Add playlist taxonomy
		$playlist_labels = array(
			'name' => __( 'Playlists' ),
			'singular_name' => __( 'Playlist' ),
			'search_items' =>  __( 'Rechercher une playlist' ),
			'popular_items' => __( 'Playlists populaires' ),
			'all_items' => __( 'Toutes les playlists' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Modifier la playlist' ),
			'update_item' => __( 'Mettre à jour la playlist' ),
			'add_new_item' => __( 'Ajouter une nouvelle playlist' ),
			'new_item_name' => __( 'New Playlist Name' ),
			'separate_items_with_commas' => __( 'Séparer les playlists par des virgules' ),
			'add_or_remove_items' => __( 'Ajouter ou supprimer des playlists' ),
			'choose_from_most_used' => __( 'Choisir parmis les playlists les plus utilisés' ),
			'menu_name' => __( 'Playlists' ),
		);

		$playlist_args = array(
			'hierarchical' => true,
			'labels' => $playlist_labels,
			'capabilities' => $tax_capabilities,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'update_count_callback' => '_update_post_term_count',
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'playlist', 'hierarchical' => true )
		);

		register_taxonomy( $this->post_type.'_playlist', $this->post_type, $playlist_args );

	}

	private function set_options() {
		$this->options = array(
			'external_link' => array(
				'type' => 'url',
				'title' => 'Lien externe',
				'description' => 'Soundcloud, Bandcamp, Youtube...',
				'default' => ''
			)
		);
	}

	/**
	 * Add meta boxes to the custom post type page
	 */
	public function add_meta_boxes() {

		//remove_meta_box( 'tagsdiv-'.$this->cpt_name.'_skill', $this->cpt_name, 'side' );

		/*
add_meta_box(
			$this->cpt_name.'_skill',
			__( 'Compétences', 'gkcpt' ),
			array( &$this, 'meta_box_skill' ),
			$this->cpt_name,
			'normal',
			'default'
		);
*/

		add_meta_box(
			'album_optionsdiv',
			__( 'Options' ),
			array( $this, 'meta_box_album_options' ),
			$this->post_type,
			'advanced',
			'default'
		);

		// add_meta_box(
		// 	'playlist_orderdiv',
		// 	__( 'Ordre' ),
		// 	array( &$this, 'meta_box_playlist_order' ),
		// 	$this->post_type,
		// 	'side',
		// 	'default'
		// );
	}

	/**
	 * Display album options metabox
	 */
	function meta_box_album_options( $post ) {

		?>
		<table class="form-table">
		<?php

		foreach( $this->options as $name => $option ) {

			$value = get_post_meta( $post->ID, '_' . $post->post_type . '_' . $name, true );

			printf( '<tr><th><label>%s</label></th><td><input type="%s" name="%s" value="%s" class="widefat" /><p class="description">%s</p></td></tr>',
				$option['title'],
				$option['type'],
				$post->post_type . '_' . $name,
				$value ? $value : $option['default'],
				$option['description']
			);
		}

		?>
		</table>
		<?php

	}

	/**
	 * Display playlist order meta box
	 */
	function meta_box_playlist_order( $post ) {
		$taxonomy = $this->post_type . '_playlist';
		$terms = get_terms( $taxonomy, 'hide_empty=0&orderby=slug&order=desc' );
		$post_terms = get_the_terms( $post->ID, $taxonomy );

		if( $terms ) {
		    foreach( $terms as $term ) {
		    	$display = ' style="display: none;"';
		    	$name = str_replace('-', '_', $term->slug);
		    	$value = get_post_meta( $post->ID, $this->post_type . '_playlist_order_' . $name, true );
		    	if( $post_terms ) {
		    		foreach( $post_terms as $post_term ) {
		    			if( $term->term_id == $post_term->term_id ) {
		    				$display = '';
		    				break;
		    			}
		    		}
		    	}

		    	$input = ' id="'.$this->post_type . '_playlist_order_'.$term->term_id.'" name="'.$this->post_type . '_playlist_order'.'['.$name.']"';
		    	echo '<p id="playlist_wrap_'.$term->term_id.'" class="'.$name.'"'.$display.'><strong>'.$term->name.'</strong>&nbsp;&nbsp;<input type="text" '.$input.'value="'.$value.'" size="4" /></p>';
		    }
		}

		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			//gerer l'ajout de l'ordre des albums
			$('#album_playlistchecklist input, #album_playlistchecklist-pop input, .album_playlist-checklist input').change(function() {

				var term_id = $(this).val();
				var playlist_wrap = '#playlist_wrap_'+term_id;
				var slug = $(playlist_wrap).attr('class');

				if( $(this).attr('checked') == 'checked' ) {

					$(playlist_wrap).show();

				} else if( $(this).attr('checked') != 'checked' ) {

					$(playlist_wrap).hide();
					$(playlist_wrap+' input').val('');
				}
			});
		});
		</script>
		<?php
	}


	public function add_post_columns( $columns ) {
    //$columns['playlist_order'] = __( 'Ordre' );

		$columns = array_slice( $columns, 0, -1, true )
			+ array( 'album_options' => '<span class="dashicons-before dashicons-plus"></span>' )
			+ array_slice( $columns, -1, 1, true );

    return $columns;
	}


	public function render_post_columns( $column_name, $post_id ) {

    switch ($column_name) {
    	case 'playlist_order':
    		echo '-';
    	    break;

			case 'album_options':

				foreach( $this->options as $name => $options ) {
					$meta = get_post_meta( $post_id, '_' . get_post_type( $post_id ) . '_' . $name, true );

					if( $meta ) {

						if( $name === 'external_link' ) {

							// Get supported social icons.
							$social_icons = campus_social_links_icons();
							$icon = 'external';

							foreach ( $social_icons as $attr => $value ) {
								if ( false !== strpos( $meta, $attr ) ) {
									$icon = $value;
									break;
								}
							}

							printf( '<a href="%1$s" title="%1$s" target="_blank">%2$s</a><input type="hidden" class="%3$s" value="%1$s" /> ',
								$meta,
								campus_get_svg( array( 'icon' => esc_attr( $icon ) ) ),
								'option-' . get_post_type( $post_id ) . '-' . $name
							);
						}
					}
				}
				break;
		}
	}

	public function quick_edit_custom_box( $column_name, $post_type ) {
    global $post;

    switch ($column_name) {
    	case 'playlist_order':
    		?>
    		<fieldset class="inline-edit-col-right">
    		  <div class="inline-edit-col column-<?php echo $column_name; ?>">
    		    <div class="inline-edit-group">
    		    	<label class="alignleft"><span class="title">Ordre</span></label>
    		    	<div class="input-text-wrap alignleft"><?php $this->meta_box_playlist_order( $post ); ?></div>
    		    	<br class="clear">
    		    </div>
    		  </div>
    		</fieldset>
    		<?php
    		break;
    	case 'album_options':
    		?>
    		<fieldset class="inline-edit-col-left">
    		  <div class="inline-edit-col column-<?php echo $column_name; ?>">
						<?php
						foreach( $this->options as $name => $option ) {
							printf( '<label><span class="title">%s</span><span class="input-text-wrap"><input type="%s" name="%s" value="" class="widefat" /></span></label>',
								$option['title'],
								$option['type'],
								$post->post_type . '_' . $name
							);
						}
						?>
    		  </div>
    		</fieldset>
    		<?php
    		break;
    }
	}

	public function quick_edit_javascript() {
    global $current_screen;

    if( $current_screen->id != 'edit-' . $this->post_type || $current_screen->post_type != $this->post_type )
    	return;

    ?>
    <script type="text/javascript">
		<?php ob_start(); ?>
    (function($) {

			// we create a copy of the WP inline edit post function
			var $wp_inline_edit = inlineEditPost.edit;

			// and then we overwrite the function with our own code
			inlineEditPost.edit = function( id ) {

				// "call" the original WP edit function
				// we don't want to leave WordPress hanging
				$wp_inline_edit.apply( this, arguments );

				// now we take care of our business

				// get the post ID
				var $post_id = 0;
				if ( typeof( id ) == 'object' ) {
					$post_id = parseInt( this.getId( id ) );
				}

				if ( $post_id > 0 ) {
					// define the edit row
					var $edit_row = $( '#edit-' + $post_id );
					var $post_row = $( '#post-' + $post_id );

					<?php foreach( $this->options as $name => $option ) { ?>
						// get the data
						var $<?php echo $name; ?> = $( '.option-<?php echo $this->post_type . '-' . $name; ?>', $post_row ).val();
						// populate the data
						$( ':input[name="<?php echo $this->post_type . '_' . $name; ?>"]', $edit_row ).val( $<?php echo $name; ?> );
					<?php } ?>
				}
			};

		})(jQuery);
		<?php $script = ob_get_clean(); ?>
    </script>
    <?php
		wp_add_inline_script( 'inline-edit-post', $script );
	}


	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The ID of the post.
	 */
	function save_post( $post_id ) {

		// If this is just a revision, don't do anything.
		if ( wp_is_post_revision( $post_id ) )
			return;

		// Check the user's permissions.
		if ( empty( $_POST['post_type'] ) || $_POST['post_type'] != $this->post_type && ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;


		if( isset( $_POST[ $this->post_type . '_playlist_order' ] ) ) {
			$playlist_order_fields = $_POST[ $this->post_type . '_playlist_order' ];
			foreach( $playlist_order_fields as $name => $value ) {
			    if( $value !== false ) {
			    	update_post_meta( $post_id, $this->post_type . '_playlist_order'.'_'.$name, $value );
			    } else {
			    	delete_post_meta( $post_id, $this->post_type . '_playlist_order'.'_'.$name );
			    }
			}
		}

		foreach( $this->options as $name => $option ) {
			if( isset( $_POST[ $this->post_type . '_' . $name ] ) ) {
				$value = trim( $_POST[ $this->post_type . '_' . $name ] );

				if( $option['type'] === 'url' && ! filter_var( $value, FILTER_VALIDATE_URL ) )
					continue;

				update_post_meta( $post_id, '_' . $this->post_type . '_' . $name, $value );
			} else {
				delete_post_meta( $post_id, '_' . $this->post_type . '_' . $name );
			}
		}

	}


	/**
	 * Save current_term_slug before term is edited.
	 * Used with Campus_Album::edited_term function
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	function edit_term( $term_id, $tt_id, $taxonomy ) {

		$term = get_term( $term_id, $taxonomy );
		$this->current_term_slug = $term->slug;

	}


	/**
	 * Update post metadata when term is edited.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	function edited_term( $term_id, $tt_id, $taxonomy ) {
		global $wpdb;

		$term  			   = get_term( $term_id, $taxonomy );
		$post_type		   = $this->post_type;
		$current_term_slug = $this->current_term_slug;

		if( ! $term || ! $current_term_slug || $current_term_slug == $term->slug )
			return;

		$name 	  = str_replace( '-', '_', $this->current_term_slug );
		$new_name = str_replace( '-', '_', $term->slug );

		$wpdb->update( $wpdb->postmeta, array( 'meta_key' => $post_type . '_playlist_order_' . str_replace('-', '_', $new_name) ), array( 'meta_key' => $post_type . '_playlist_order_' . str_replace('-', '_', $name) ) );

	}


	/**
	 * Add filter to get_terms and get_the_terms
	 *
	 */
	function album_get_terms( $terms, $taxonomies, $args ) {
		//print_r($terms);
		//print_r($taxonomies);
		//print_r($args);
		if( is_admin() )
			return $terms;

		$output_terms = $terms;

		if( $terms ) {
			foreach( $terms as $k => $term ) {
				if( $term->taxonomy == 'album_playlist' && !campus_get_term_meta( $term->term_id, 'album_playlist_visibility', true ) )	{
					unset($output_terms[$k]);

				} elseif( $term->taxonomy == 'album_label' ||
					$term->taxonomy == 'album_artist' ||
					$term->taxonomy == 'album_genre' ) {

					$unset = true;

					$ps = get_posts( array( 'post_type' => 'album', $term->taxonomy => $term->slug, 'posts_per_page' => '-1' ) );
					if( $ps ) {
						foreach( $ps as $p ) {

							$ts = wp_get_post_terms( $p->ID, 'album_playlist' );
							if( $ts ) {
								foreach( $ts as $t ) {
									if( is_playlist_active( $t->term_id ) ) {
										$unset = false;
										break;
									}
								}

							}
						}

					}

					if( $unset )
						unset($output_terms[$k]);

				}
			}
		}

		return array_values($output_terms);
	}

	static function get_albums( $args ) {

		$tax_query = array();

		if( isset( $args['artist'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'album_artist',
				'field'    => 'term_id',
				'terms'    => $args['artist'],
			);
		}

		if( isset( $args['title'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'album_song',
				'field'    => 'term_id',
				'terms'    => $args['title'],
			);
		}

		if( isset( $args['playlist'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'album_playlist',
				'field'    => 'term_id',
				'terms'    => $args['playlist'],
			);
		}

		if( isset( $args['label'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'album_label',
				'field'    => 'term_id',
				'terms'    => $args['label'],
			);
		}

		if( isset( $args['genre'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'album_genre',
				'field'    => 'term_id',
				'terms'    => $args['genre'],
			);
		}

		if( empty( $tax_query ) )
			return false;

		$albums = get_posts( array(
			'post_type' => 'album',
			'posts_per_page' => '-1',
			'tax_query' => array(
				$tax_query,
				'relation' => isset( $args['relation'] ) ? $args['relation'] : 'OR',
			)
		) );

		return $albums;
	}

} // End Campus_Album Class

new Campus_Album();
