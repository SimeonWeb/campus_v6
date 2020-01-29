<?php

class SMN_FAQ {

	var $slug = 'faq';

	var $page;

	function __construct() {
		add_action( 'init', array( $this, 'init_post_type' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue scripts
	 */
	function admin_scripts( $hook ) {

		$screen = get_current_screen();

		if( in_array( $screen->id, array( 'toplevel_page_smn_faq' ) ) ) {
			wp_enqueue_script( 'post' );
		}
	}


	function init_post_type() {

		$p_labels = array(
			'name' => 'F.A.Q.',
			'singular_name' => 'F.A.Q.',
			'add_new' => 'Ajouter',
			'add_new_item' => 'Ajouter une question',
			'edit_item' => 'Modifier une question',
			'new_item' => 'Nouvelle question',
			'all_items' => 'Toutes les questions',
			'view_item' => 'Voir la question',
			'search_items' => 'Rechercher une question',
			'not_found' =>  'Aucune question trouvée',
			'not_found_in_trash' => 'Aucune question dans la corbeille',
			'parent_item_colon' => '',
			'menu_name' => 'F.A.Q.'
		);

		$p_args = array(
			'labels' => $p_labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'page',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 10000,
			'menu_icon' => 'dashicons-admin-settings',
			'supports' => array( 'title', 'editor' )
		);

		register_post_type( $this->slug, $p_args );

	}


	/**
	 * Register playlist page
	 *
	 */
	function admin_menu() {

		$this->page = add_menu_page(
			'F.A.Q.',
			'F.A.Q.',
			'read',
			'smn_faq',
			array( $this, 'page' ),
			'dashicons-format-chat',
			3
		);
	}

	function page() {

		$q_a = new WP_Query( array(
			'post_type' => $this->slug,
			'posts_per_page' => '-1',
			'orderby' => isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'date',
			'order' => isset( $_GET['order'] ) ? $_GET['order'] : 'ASC'
		) );

		$classes = array( 'postbox' );
		$expanded = true;

		if( $q_a->post_count > 1 ) {
			$classes['closed'] = 'closed';
			$expanded = false;
		}
		$selected = isset( $_GET['selected'] ) ? $_GET['selected'] : false;
		?>
		<div class="wrap">

			<h2>F.A.Q.</h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">

					<div id="normal-sortables" class="meta-box-sortables">

						<?php while( $q_a->have_posts() ) : $q_a->the_post();
							$this_classes = $classes;

							if( $selected == get_the_ID() ) {
								unset( $this_classes['closed'] );
							}
						?>

							<div id="faq-<?php the_ID(); ?>" class="<?php echo join( ' ', $this_classes ); ?>">
								<button type="button" class="handlediv" aria-expanded="<?php echo $expanded ? 'true' : 'false'; ?>">
									<span class="screen-reader-text">Ouvrir/fermer le bloc</span><span class="toggle-indicator" aria-hidden="true"></span>
								</button>
								<h2 class="hndle ui-sortable-handle">
									<span><?php the_title(); ?></span>
								</h2>
								<div class="inside">
									<div class="inside-entry-content">
										<?php the_content(); ?>
									</div>
								</div>
							</div>

						<?php endwhile; ?>

					</div>

				</div>
			</div>

			<div class="clear"></div>
		</div>
		<?php
	}
}

new SMN_FAQ();
