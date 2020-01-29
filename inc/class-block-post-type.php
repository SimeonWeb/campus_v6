<?php

class Campus_Blocks {

	var $slug = 'block';


	function __construct() {
		add_action( 'init', array( $this, 'init_post_type' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 100 );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_meta' ) );

		//add_action( 'wp_enqueue_scripts', array( $this, 'prepare_blocks' ), 11 );
		//add_filter( 'campus_script_vars', array( $this, 'prepare_blocks' ) );

		// Custom columns
		add_filter( 'manage_'.$this->slug.'_posts_columns' , array( $this, 'cpt_columns' ) );
		add_action( 'manage_'.$this->slug.'_posts_custom_column' , array( $this, 'custom_column' ), 10, 2 );

		// Submitbox actions
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );

		// Quick edit
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_custom_box' ), 10, 2 );

		// Post class
		add_filter( 'post_class', array( $this, 'post_classes' ) );

		// Query filters
		add_action( 'pre_get_posts', array( $this, 'add_to_loop' ) );
		add_filter( 'grid_post_type', array( $this, 'grid_post_type' ) );
	}


	function grid_post_type() {
		return array( 'post', $this->slug );
	}


	function init_post_type() {

		$p_labels = array(
			'name' => 'Blocs',
			'singular_name' => 'Bloc',
			'add_new' => 'Ajouter',
			'add_new_item' => 'Ajouter un bloc',
			'edit_item' => 'Modifier le bloc',
			'new_item' => 'Nouveau bloc',
			'all_items' => 'Tous les blocs',
			'view_item' => 'Voir le bloc',
			'search_items' => 'Rechercher un bloc',
			'not_found' =>  'Aucun bloc trouvé',
			'not_found_in_trash' => 'Aucun bloc dans la corbeille',
			'parent_item_colon' => '',
			'menu_name' => 'Blocs'
		);

		$p_args = array(
			'labels' => $p_labels,
			'public' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'page',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'menu_icon' => 'dashicons-layout',
			'supports' => array( 'title' )
		);

		register_post_type( $this->slug, $p_args );

	}

	/**
	 * Enqueue scripts
	 */
	function admin_enqueue_scripts( $hook ) {

		$screen = get_current_screen();

		if( in_array( $screen->id, array( 'block' ) ) ) {
			wp_enqueue_script( 'jquery-ui-tabs' );
		}
	}

	function cpt_columns($columns) {

		unset($columns['date']);

		$new_columns = array(
			'type' => __('Type'),
			'date' => __('Date'),
		);

	  return array_merge($columns, $new_columns);
	}


	function custom_column( $column, $post_id ) {
		switch ( $column ) {

			case 'type':
				$meta = get_post_meta( $post_id, $this->slug.'_type', true );
				$type = $this->tabs_type($meta);
				if ( is_string( $type ) )
				echo $type;
				break;

		}
	}

	function post_submitbox_misc_actions( $post ) {

		if( get_post_type( $post->ID ) !== $this->slug )
			return;

		$sticky = get_option( 'sticky_posts', array() );
		?>
		<div class="misc-pub-section smn-sticky">
			<span id="sticky-span"><input id="sticky" name="sticky" value="sticky" type="checkbox"<?php checked( in_array( $post->ID, $sticky ), true ); ?>> <label for="sticky" class="selectit">Mettre ce block en avant sur la page d’accueil</label><br></span>
		</div>
		<?php
	}

	function quick_edit_custom_box( $column_name, $post_type ) {
		if( $post_type !== $this->slug )
			return;
		?>
		<fieldset class="inline-edit-col-right inline-edit-<?php echo $post_type; ?>">
      <div class="inline-edit-col column-sticky">
        <label class="inline-edit-group wp-clearfix">
					<label class="alignleft"><input name="sticky" value="sticky" type="checkbox"> <span class="checkbox-title">Mettre ce contenu en avant</span></label>
        </label>
      </div>
    </fieldset>
		<?php
	}


	function tabs_type( $single = '' ) {

		$tabs = array(
			'album_playlist' => 'Playlist',
			'category' => 'Catégorie',
			'post_tag' => 'Étiquette'
		);

		if( array_key_exists( $single, $tabs ) )
			return $tabs[$single];
		else
			return $tabs;
	}

	function add_meta_boxes() {

		add_meta_box(
			'block_typediv',
			__( 'Type' ),
			array( $this, 'meta_box_type' ),
			$this->slug,
			'normal',
			'high'
		);

	}

	function meta_box_type( $post ) {
		$current_type = get_post_meta( $post->ID, $this->slug . '_type', true );

		echo '<div class="type-tabs">';

		echo '<ul class="nav-tab-wrapper wp-clearfix">';

		$active = 0;
		$count = 0;
		foreach( $this->tabs_type() as $type => $title ) {
			printf( '<li class="nav-tab%1$s"><a href="#%2$s"><label><input type="radio" class="screen-reader-text" value="%2$s" name="%3$s_type"%4$s />%5$s</label></a></li>',
				$current_type == $type || ! $current_type && $count === 0 ? ' nav-tab-active' : '',
				$type,
				$this->slug,
				checked( $current_type, $type, false ),
				$title
			);
			if( $current_type == $type ) $active = $count;
			$count++;
		}
		echo '</ul>';

		foreach( $this->tabs_type() as $type => $title ) {
			echo '<div id="'.$type.'" class="tab-content">';
				call_user_func( array( $this, 'tab_type_' . $type ) );
			echo '</div>';
		}

		echo '</div>';

		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {

			$('#block_typediv .type-tabs').tabs({
				active: <?php echo $active; ?>,
				activate: function( event, ui ) {
					ui.newTab.addClass('nav-tab-active').siblings().removeClass('nav-tab-active')
					ui.newTab.find('input').attr('checked', 'checked');
				}
			});
		});
		</script>
		<?php

	}

	function tab_type_album_playlist() {
		$meta = $this->get_playlist_meta();

		$rows = sprintf( '<tr><th>%s</th><td> <input type="text" class="widefat" value="%s" name="%s_type_%s[title]" /> <p class="description">%s</p></td></tr>',
			'Titre',
			$meta['title'],
			$this->slug,
			'album_playlist',
			'Par défaut, la description de la playlist est utilisée'
		);

		$rows .= sprintf( '<tr><th>%s</th><td>%s</td></tr>',
			'Playlist',
			wp_dropdown_categories( array( 'class' => 'widefat', 'name' => $this->slug . '_type_album_playlist[term_id]', 'taxonomy' => 'album_playlist', 'selected' => $meta['term_id'], 'show_option_none' => 'Choisir une playlist', 'orderby' => 'slug', 'order' => 'desc', 'hide_empty' => 0, 'hierarchical' => false, 'echo' => false ) )
		);

		printf( '<table class="form-table">%s</table>', $rows );
	}

	function tab_type_category() {
		$meta = $this->get_category_meta();

		$rows = sprintf( '<tr><th>%s</th><td> <input type="text" class="widefat" value="%s" name="%s_type_%s[title]" /> <p class="description">%s</p></td></tr>',
			'Titre',
			$meta['title'],
			$this->slug,
			'category',
			'Par défaut, la description de la catégorie est utilisée'
		);

		$rows .= sprintf( '<tr><th>%s</th><td>%s</td></tr>',
			'Catégorie',
			wp_dropdown_categories( array( 'class' => 'widefat', 'name' => $this->slug . '_type_category[term_id]', 'taxonomy' => 'category', 'selected' => $meta['term_id'], 'show_option_none' => 'Choisir une catégorie', 'orderby' => 'name', 'order' => 'asc', 'hide_empty' => 0, 'hierarchical' => true, 'echo' => false ) )
		);
		
		$rows .= sprintf( '<tr><th>%s</th><td>%s%s</td></tr>',
			'Options',
			sprintf( '<p><label><input type="checkbox" name="%s_type_%s[invert]"%s /> %s</label></p>', $this->slug, 'category', checked( 'on', $meta['invert'], false ), 'Inverser le titre et la catégorie' ),
			sprintf( '<p><label><input type="checkbox" name="%s_type_%s[no_parent]"%s /> %s</label></p>', $this->slug, 'category', checked( 'on', $meta['no_parent'], false ), 'Ne pas afficher le parent de la catégorie' ),
		);

		printf( '<table class="form-table">%s</table>', $rows );

	}

	function tab_type_post_tag() {
		$meta = $this->get_post_tag_meta();

		$rows = sprintf( '<tr><th>%s</th><td> <input type="text" class="widefat" value="%s" name="%s_type_%s[title]" /> <p class="description">%s</p></td></tr>',
			'Titre',
			$meta['title'],
			$this->slug,
			'post_tag',
			'Par défaut, la description de l\'étiquette est utilisée'
		);

		$rows .= sprintf( '<tr><th>%s</th><td>%s</td></tr>',
			'Étiquette',
			wp_dropdown_categories( array( 'class' => 'widefat', 'name' => $this->slug . '_type_post_tag[term_id]', 'taxonomy' => 'post_tag', 'selected' => $meta['term_id'], 'show_option_none' => 'Choisir une étiquette', 'orderby' => 'name', 'order' => 'asc', 'hide_empty' => 1, 'hierarchical' => true, 'echo' => false ) )
		);

		printf( '<table class="form-table">%s</table>', $rows );
	}

	function save_meta( $post_id ) {

		// If this is just a revision, don't do anything.
		if ( wp_is_post_revision( $post_id ) )
			return;

		// If this isn't a 'album' post, don't update it.
    	if ( !isset($_POST['post_type']) || $this->slug != $_POST['post_type'] )
    	    return;

		if( !isset($_POST[ $this->slug . '_type' ]) )
			return;


		$type = $_POST[ $this->slug . '_type' ];

		if( $type == 'album_playlist' ) {
			update_post_meta( $post_id, $this->slug . '_type_album_playlist', $_POST[ $this->slug . '_type_album_playlist' ] );
		} else if( $type == 'category' ) {
			update_post_meta( $post_id, $this->slug . '_type_category', $_POST[ $this->slug . '_type_category' ] );
		} else if( $type == 'post_tag' ) {
			update_post_meta( $post_id, $this->slug . '_type_post_tag', $_POST[ $this->slug . '_type_post_tag' ] );
		}

		update_post_meta( $post_id, $this->slug . '_type', $type );

		$sticky = get_option( 'sticky_posts', array() );
		if( isset( $_POST['sticky'] ) ) {
			if( ! in_array( $post_id, $sticky ) ) {
				$sticky = array_push( $sticky, $post_id );
			}
		} else {
			if( in_array( $post_id, $sticky ) ) {
				$key = array_search( $post_id, $sticky );
				unset( $sticky[$key] );
			}
		}
	}

	function get_playlist_meta() {
		global $post;

		$meta = (array) get_post_meta( $post->ID, $this->slug . '_type_album_playlist', true );
		$default = array(
			'term_id' => '',
			'title' => '',
		);

		return array_merge( $default, $meta );
	}

	function get_category_meta() {
		global $post;

		$meta = (array) get_post_meta( $post->ID, $this->slug . '_type_category', true );
		$default = array(
			'term_id'   => '',
			'title'     => '',
			'invert'    => null,
			'no_parent' => null,
		);

		return array_merge( $default, $meta );
	}

	function get_post_tag_meta() {
		global $post;

		$meta = (array) get_post_meta( $post->ID, $this->slug . '_type_post_tag', true );
		$default = array(
			'term_id' => '',
			'title' => '',
		);

		return array_merge( $default, $meta );
	}

	function prepare_blocks( $vars ) {

		$args = array(
			'post_type' => $this->slug,
			'posts_per_page' => -1,
			'orderby' => 'rand',
			/* 'fields' => 'ids' */
		);

		$blocks = get_posts( $args );

		$vars['blocks'] = $blocks;

		return $vars;

		//wp_localize_script( 'campus-script', 'blocks', $blocks );

	}

	function add_to_loop( $query ) {
		if ( $query->is_home() && $query->is_main_query() ) {
			$query->set( 'post_type', $this->grid_post_type() );
		}
	}


	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 * @return array
	 */
	function post_classes( $classes ) {

		$classes[] = 'type-block-' . campus_get_block_type();

		// Add sticky class to the playlist block of the current month
		if( ! is_home() ) {
			$meta = $this->get_playlist_meta();

			if( ! empty( $meta['term_id'] ) ) {
				$term = get_term( $meta['term_id'], 'album_playlist' );

				if( $term && $term->slug == date( 'Y-m' ) ) {
					$classes[] = 'sticky';
				}
			}
		}
		return $classes;
	}

}

new Campus_Blocks();

/**
 * Block functions
 *
 */

function campus_get_block_type() {
	$type = get_post_meta( get_the_ID(), 'block_type', true );

	if( $type )
		return (string) $type;

	return false;
}
