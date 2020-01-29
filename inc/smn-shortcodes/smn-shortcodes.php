<?php

$smn_enabled_shortcodes = apply_filters( 'smn_enabled_shortcodes', array(
    'the_title',
    'button',
    // 'insert',
    'column',
    'svg',
    'accordion',
    'termlist',
    'podcastlist'
) );

/**
 * Shortcodes
 * ------------------------------------------------------------------------------------
 */

class SMN_Shortcodes {

	public $version = '0.2';

 	function __construct( $enabled_shortcodes ) {

 		// Add shortcodes
 		foreach( $enabled_shortcodes as $shortcode ) {
 			add_shortcode( $shortcode, array( $this, $shortcode . '_shortcode' ) );

            if( $shortcode == 'column' ) {
 				add_shortcode( $shortcode . 's', array( $this, $shortcode . 's_shortcode' ) );
 			}
		}
		// Add shortcodes to text widget
		add_filter( 'widget_text', 'do_shortcode' );

		// Is it realy secure?
		//remove_filter( 'the_content', 'do_shortcode', 11 );
		//add_filter( 'the_content', 'do_shortcode', 9 );

		//add_filter( 'the_content', array( $this, 'fix_unwrap_a' ), 11 );
	}

	function fix_unwrap_a( $content ) {
		// Remove </p> after <br> in <a>
		$content = preg_replace( '/(<br[^>]+>)<\/p>/', '$1', $content );

		// Remove </p> at the end of line
		$content = preg_replace( '/^(?!<p).+(<\/p>)$/', '$1', $content );

		// Add </p> at the end of <a>
		$content = preg_replace( '/(<\/a>)\n/', '$1</p>', $content );

		// Add <p> around <a> on each line
		$content = preg_replace( '/\n(<a.+>)/' , '<p>$1</p>', $content );

		return $content;
	}

	function fix_content_shortcode($content) {
		$content = trim( $content );

		//---------------------------------------
		// Strip <p> tags around content
		if( substr( $content, 0, 4 ) == '</p>' )
			$content = substr($content, 4);

		if( substr( $content, -3 ) == '<p>' ) {
			$len = strlen( $content );
			$content = substr( $content, 0, $len-3 );
		}
		//---------------------------------------

		//---------------------------------------
		// Kill any <p> tags touching inner shortcode constructs [...]
		$content = preg_replace( '@</?p>\s*\[@', '[', $content );
		$content = preg_replace( '@\]\s*</?p>@', ']', $content );
		//---------------------------------------

		return $content;
	}

	function the_title_shortcode( $atts ) {

		return sprintf( '<header class="entry-header">%s</header><!-- .entry-header -->',
            the_title( '<h1 class="entry-title">', '</h1>', false )
        );
	}

	/**
	 * Create button shortcode
	 *
	 */
	function insert_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'color' => false, // primary, secondary, tertiary...
			'bgcolor' => false, // primary, secondary, tertiary...
			'valign' => false,
			'class' => '',
			'aspectratio' => false,
		), $atts ) );

		$content = $this->fix_content_shortcode( $content );

		$classes = array( 'insert' );
		$classes_alt = array();

		if( $color )
			$classes[] = 'color-' . $color;

		if( $bgcolor )
			$classes[] = 'background-color-' . $bgcolor;

		if( $valign )
			$classes_alt[] = 'valign' . $valign;

		if( $class )
			$classes[] = $class;

		if( $aspectratio ) {
			$aspectratio = str_replace( array( '/', '-' ), ':', $aspectratio );
			$classes_alt[] = 'aspect-ratio-wrap';
			$content = '<div class="aspect-ratio-container" data-aspect-ratio="' . $aspectratio . '"><div class="' . join( ' ', $classes_alt ) . '"><div class="insert-content">' . $content . '</div></div></div>';
		} else {
			$classes += $classes_alt;
			$content = '<div class="insert-content">' . $content . '</div>';
		}

		return '<div class="' . join( ' ', $classes ) . '">' . do_shortcode( $content ) . '</div>';
	}

	/**
	 * Create column row shortcode
	 *
	 */
	function columns_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
            'row' => false,
			'valign' => false,
			'class' => '',
			'aspectratio' => false,
		), $atts ) );

		$available_sizes = array( 'xs','sm','md','lg','xl' );
        $count_size = count( $available_sizes );

		$content = $this->fix_content_shortcode( $content );

		$classes = array( 'smn-row row' );

		if( $row ) {

            $sizes = explode( ',', $row );
			if( count( $sizes ) == $count_size )
				foreach( $sizes as $i => $size )
					if( isset( $available_sizes[$i] ) )
						$classes[] = 'row-' . $available_sizes[$i] . '-' . $size;
		}

		if( $valign )
			$classes[] = 'valign' . $valign;

		if( $class )
			$classes[] = $class;

		if( $aspectratio ) {
			$aspectratio = str_replace( array( '/', '-' ), ':', $aspectratio );
			$content = '<div data-aspect-ratio="' . $aspectratio . '">' . $content . '</div>';
		}

		return '<div class="' . join( ' ', $classes ) . '">' . do_shortcode( $content ) . '</div>';
	}

	/**
	 * Create column shortcode
	 *
	 */
	function column_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'col' => false, // xs,sm,md,lg,xl
			'pull' => false, // xs,sm,md,lg,xl
			'push' => false, // xs,sm,md,lg,xl
			'offset' => false, // xs,sm,md,lg,xl
			'valign' => false,
			'class' => '',
			'aspectratio' => false,
		), $atts ) );

		$available_sizes = array( 'xs','sm','md','lg','xl' );
        $count_size = count( $available_sizes );

		$content = $this->fix_content_shortcode( $content );

		$classes = array( 'smn-col col' );

		if( $col ) {

			$sizes = explode( ',', $col );
			if( count( $sizes ) == $count_size )
				foreach( $sizes as $i => $size )
					if( isset( $available_sizes[$i] ) )
						$classes[] = 'col-' . $available_sizes[$i] . '-' . $size;
		}

		if( $pull ) {
			$sizes = explode( ',', $pull );
			if( count( $sizes ) == $count_size )
				foreach( $sizes as $i => $size )
					if( isset( $available_sizes[$i] ) )
						$classes[] = 'col-' . $available_sizes[$i] . '-pull-' . $size;
		}

		if( $push ) {
			$sizes = explode( ',', $push );
			if( count( $sizes ) == $count_size )
				foreach( $sizes as $i => $size )
					if( isset( $available_sizes[$i] ) )
						$classes[] = 'col-' . $available_sizes[$i] . '-push-' . $size;
		}

		if( $offset ) {
			$sizes = explode( ',', $offset );
			if( count( $sizes ) == $count_size )
				foreach( $sizes as $i => $size )
					if( isset( $available_sizes[$i] ) )
						$classes[] = 'col-' . $available_sizes[$i] . '-offset-' . $size;
		}

		if( $valign )
			$classes[] = 'valign' . $valign;

		if( $class )
			$classes[] = $class;

		if( $aspectratio ) {
			$aspectratio = str_replace( array( '/', '-' ), ':', $aspectratio );
			$content = '<div data-aspect-ratio="' . $aspectratio . '">' . $content . '</div>';
		}

		return '<div class="' . join( ' ', $classes ) . '"><div class="inner-col">' . do_shortcode( $content ) . '</div></div>';
	}

	/**
	 * Create spacer shortcode
	 *
	 */
	function svg_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'icon'  => false,
			'size'  => false,
			'class'  => false,
			'style' => false
		), $atts ) );

        $classes = array();

        if( $class )
            $classes[] = $class;

        if( $size )
            $classes[] = 'icon-' . $size;

		if( $icon )
			return campus_get_svg( array( 'icon' => $icon, 'class' => join( ' ', $classes ), 'style' => $style ) );

		return false;
	}

	/**
	 * Create accordion shortcode
	 *
	 */
	function accordion_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'attr' => '[h1|h2|h3|h4|h5|h6]{2}',
			'active' => false,
            'help' => 'Cliquez pour ouvrir',
		), $atts ) );

        $icon = $help ? '<span class="acc-icon-help">' . campus_get_svg( array( 'icon' => 'arrow-down', 'class' => 'icon-small' ) ) . '</span>' : '';

        $content = $this->fix_content_shortcode( $content );

        $content = preg_replace( "/(<$attr>.+<\/$attr>)/i", "</div><!-- .acc-content-inner --></div><!-- .acc-content --><a href='#' class='acc-title' title='$help'>$1$icon</a><!-- .acc-title --><div class='acc-content'><div class='acc-content-inner'>", $content . '</div><!-- .acc-content-inner --></div><!-- .acc-content -->' );
        // Remove first close braket
        $content = substr( $content, 61 );

        $output = '<div class="smn-acc acc"' . ( $active ? ' data-active="' . $active . '"' : '' ) . '>';
			$output .= do_shortcode( $content );
        $output .= '</div><!-- .acc -->';

        return $output;
	}

	/**
	 * Create hidden shortcode
	 *
	 */
	function hidden_on_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'page' => false
		), $atts ) );

		if( $page && is_page_template( $page ) )
			return;

		return $this->fix_content_shortcode( do_shortcode( $content ) );

	}

    /**
     * Create terms list shortcode
     *
     */
    function termlist_shortcode( $atts ) {
    	extract( shortcode_atts( array(
    		'taxonomy' => null,
    		'ids' => null, // Old name
    		'slugs' => null // Old name
    	), $atts ) );

    	if( is_null( $taxonomy ) )
    		return;

        $args = $atts;
        $args['taxonomy'] = explode( ',', $taxonomy );

        if( ! isset( $args['hide_empty'] ) )
            $args['hide_empty'] = false;

        if( ! is_null( $ids ) )
            $args['object_ids'] = explode( ',', $ids );

        if( ! is_null( $slugs ) )
            $args['slug'] = explode( ',', $slugs );

        $terms = get_terms( $args );

    	if( ! $terms )
    		return;

        ob_start();

        global $wp_query;
        $queried_object = $wp_query->queried_object;
        $queried_object_id = $wp_query->queried_object_id;

        echo '<section class="term-list">';

        	foreach( $terms as $term ) {
                $wp_query->queried_object = $term;
                $wp_query->queried_object_id = $term->term_id;
                $the_term = $term;
        		get_template_part( 'template-parts/list/term', $term->taxonomy );
        	}

        echo '</section>';

        $wp_query->queried_object = $queried_object;
        $wp_query->queried_object_id = $queried_object_id;
        wp_reset_query();
        $output = ob_get_clean();

    	return $output;
    }

    /**
     * Create terms list shortcode
     *
     */
    function podcastlist_shortcode( $atts ) {
    	extract( shortcode_atts( array(
    		'season' => false // current / old
    	), $atts ) );

      $args = array(
        'taxonomy' => 'category',
        'hide_empty' => false,
				// 'child_of' => get_option( 'category_by_priority' ),
				// 'exclude' => array(
				// 	get_option( 'category_other_id' )
				// ),
        'meta_key' => 'itunes',
        'meta_compare' => 'EXISTS'
      );

			// $excluded = array(
			// 	get_option( 'category_talk_id' ),
			// 	get_option( 'category_music_id' ),
			// );

      $terms = get_terms( $args );

    	if( ! $terms )
    		return;

      ob_start();

      global $wp_query;
      $queried_object = $wp_query->queried_object;
      $queried_object_id = $wp_query->queried_object_id;

      echo '<section class="term-list podcast-list">';

      	foreach( $terms as $term ) {

					// if( in_array( $term->term_id, $excluded ) )
					// 	continue;

          // Filter by season
          if( $season ) {
            $post = get_posts( array(
              'category' => $term->term_id,
              'posts_per_page' => 1
            ) );

            if( isset( $post[0] ) ) {
              $term->in_current_season = $in_current_season = campus_in_current_season( $post[0]->post_date );

              if( $season == 'current' && ! $in_current_season || $season == 'old' && $in_current_season )
                continue;
            } else {
              continue;
            }
          }

          $wp_query->queried_object = $term;
          $wp_query->queried_object_id = $term->term_id;
          $the_term = $term;

      		get_template_part( 'template-parts/list/podcasts' );
      	}

      echo '</section>';

      $wp_query->queried_object = $queried_object;
      $wp_query->queried_object_id = $queried_object_id;
      wp_reset_query();
      $output = ob_get_clean();

    	return $output;
    }
}


/**
 * Add button to visual editor
 * ------------------------------------------------------------------------------------
 */

class SMN_Shortcodes_TinyMCE {

	public $version = '0.2';

    private $enabled_shortcodes;

	function __construct( $enabled_shortcodes ) {

        $this->enabled_shortcodes = $enabled_shortcodes;

		add_action( 'init', array( $this, 'filters' ) );
		add_action( 'wp_ajax_smn_shortcodes_dialog', array( $this, 'request' ) );

		add_action( 'load-post.php', array( $this, 'load_scripts_on_post' ) );
		add_action( 'load-post-new.php', array( $this, 'load_scripts_on_post' ) );
	}

	/**
	 * Add scripts & styles
	 *
	 */
	function load_scripts_on_post() {
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue inline scripts & styles
	 *
	 */
	function admin_head() {
		?>
		<style type="text/css">

			/* TinyMCE */

			.mce-ico.mce-i-fa {
			    font: 400 20px/1 "fontAwesome";
			}
			.mce-ico.mce-i-dashicons {
			    font: 400 20px/1 "dashicons";
			}

			.mce-container .mce-container-body p {
				margin-bottom: 1em;
			}

			.mce-container .mce-container-body .mce-textbox.widefat {
				width: 100%;
				display: block;
			}
		</style>
		<?php
	}

	/**
	 * Enqueue scripts & styles
	 *
	 */
	function admin_scripts() {
		wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), '4.6.3' );
	}

	/**
	 * Add filters
	 *
	 */
	public function filters() {
	    add_filter( 'tiny_mce_before_init', array( $this, 'customize_text_sizes' ) );
	    add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_js' ) );
	    add_filter( 'mce_buttons_2', array( $this, 'register_buttons_2' ) );
	    add_filter( 'mce_buttons_3', array( $this, 'register_buttons_3' ) );

	    if( class_exists( 'JPB_Visual_Shortcodes' ) )
	    	add_filter( 'jpb_visual_shortcodes', array( $this, 'add_visual_shortcode_image') );
	}


	/**
	 * Register buttons in tiny mce
	 *
	 */
	public function register_buttons_2( $buttons ) {
		array_shift( $buttons );
        array_unshift( $buttons, 'fontsizeselect');

	    return $buttons;
	}


	/**
	 * Register buttons in tiny mce
	 *
	 */
	public function register_buttons_3( $buttons ) {

		foreach( $this->enabled_shortcodes as $shortcode )
			$buttons[] = 'smnButton_' . $shortcode;

	    return $buttons;
	}


	/**
	 * Register buttons in tiny mce
	 *
	 */
	public function customize_text_sizes( $initArray ){
		$initArray['fontsize_formats'] = '0.75em 0.875em 1em 1.125em 1.25em 1.5em';

		return $initArray;
	}


	/**
	 * Register js
	 *
	 */
	public function register_tinymce_js( $plugin_array ) {
	    $plugin_array['smn_shortcodes'] = get_template_directory_uri() . '/inc/smn-shortcodes/smn-shortcodes-buttons.js';

	    return $plugin_array;
	}


	/**
	 * Add support for shortcode image
	 *
	 * Plugin Visual Shortcodes
	 */
	public function add_visual_shortcode_image( $shortcodes ) {

		$shortcodes[] = array(
			'shortcode' => 'insert',
			'image' => get_bloginfo('url') . '/wp-includes/js/tinymce/plugins/wpgallery/img/t.gif',
			'command' => null,
		);
		return $shortcodes;
	}

	public function request() {

		if(  empty( $_REQUEST['action'] ) || $_REQUEST['action'] != 'smn_shortcodes_dialog' )
			die();

		$dialog_html = array();

		// Insert
		$insert_html = array();
		$insert_html[] = $this->get( 'color' );
		$insert_html[] = $this->get( 'bgcolor' );
		$insert_html[] = $this->get( 'valign' );
		$insert_html[] = $this->get( 'aspectratio' );
		$insert_html[] = $this->get( 'class' );

		$dialog_html['insert'] = join( "\n", $insert_html );

		// Button
		$button_html = array();
		$button_html[] = '<p><label>URL</label><input name="href" placeholder="http://" class="smn_sc_input widefat mce-textbox" type="url"></p>';
		$button_html[] = $this->get( 'color' );
		$button_html[] = $this->get( 'size' );
		$button_html[] = $this->get( 'class' );

		$dialog_html['button'] = join( "\n", $button_html );

		// column
		$column_html = array();
		$column_html[] = '<p><label><input class="smn_sc_input" type="checkbox" data-suffix="s" value="1" name="row"> ' . __( 'Create a new row', 'campus' ) . '</label></p>';
		$column_html[] = $this->get( 'device_size_columns', array( 'name' =>'row', 'label' => 'Hauteur de la ligne', 'hidden' => true ) );
		$column_html[] = $this->get( 'device_size_columns', array( 'name' =>'col', 'label' => 'Largeur de la colonne', 'selected' => array( 'xs' => 12, 'sm' => '6', 'md' => '6', 'lg' => '6', 'xl' => '6' ) ) );
		$column_html[] = $this->get( 'device_size_columns', array( 'name' =>'offset', 'label' => 'Marge gauche' ) );
		$column_html[] = $this->get( 'valign' );
		$column_html[] = $this->get( 'aspectratio' );
		$column_html[] = $this->get( 'class' );

		$dialog_html['column'] = join( "\n", $column_html );

		// Svg
		$svg_html = array();
        $svg_html[] = campus_get_svg( array( 'icon' => 'default', 'id' => 'smnShortcode_icon', 'style' => 'max-width:100%;' ) );
        $svg_html[] = $this->get( 'icon', array( 'id' => 'smnShortcode_icon_select' ) );
		$svg_html[] = $this->get( 'size' );
		$svg_html[] = $this->get( 'class' );
		$svg_html[] = $this->get( 'style' );

		$dialog_html['svg'] = join( "\n", $svg_html );

		// Accordion
        $accordion_html = array();
        $accordion_html[] = '<p><label>Attribut des titres</label><input name="attr" placeholder="h2,h3..." class="smn_sc_input widefat mce-textbox" type="text"></p>';
		$accordion_html[] = '<p><label>Sélectionné</label><input name="active" class="smn_sc_input widefat mce-textbox" type="number"></p>';
		$accordion_html[] = '<p><label>Message d\'aide</label><input name="help" placeholder="Ex : Cliquez pour ouvrir" class="smn_sc_input widefat mce-textbox" type="text"></p>';

		$dialog_html['accordion'] = join( "\n", $accordion_html );

		// terms list
		$termlist_html = array();
		$termlist_html[] = $this->get( 'taxonomy' );
		$termlist_html[] = '<p><label>meta_key</label><input name="meta_key" class="smn_sc_input widefat mce-textbox" type="text"></p>';
		$termlist_html[] = '<p><label>meta_value</label><input name="meta_value" class="smn_sc_input widefat mce-textbox" type="text"></p>';

		$dialog_html['termlist'] = join( "\n", $termlist_html );


		echo json_encode( $dialog_html );
		die();

	}

	private function get( $input, $args = array() ) {
        extract( array_merge( array(
            'id' => false,
            'name' => false,
            'label' => false,
            'suffix' => false,
            'group' => false,
            'hidden' => false
        ), $args ) );

        $device_sizes = array( 'xs' => 'Mobile', 'sm' => 'Tablette (verticale)', 'md' =>  'Tablette (horizontale)', 'lg' => 'Ordinateur', 'xl' => 'Ordinateur (HD+)' );

        $size_values = array( 'normal' => 'Normal', 'small' => 'Petit', 'medium' => 'Gros', 'large' => 'Très gros' );

		$column_values = array( '0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12' );

		$aspectratio_values = array( '-1' => 'Auto', '3:1' => '3:1', '2:1' => '2:1', '16:9' => '16:9', '3:2' => '3:2', '4:3' => '4:3', '1:1' => '1:1', '3:4' => '3:4', '2:3' => '2:3', '9:16' => '9:16', '1:2' => '1:2', '1:3' => '1:3' );

		$valign_values = array( '-1' => __( 'Top (default)', 'campus' ), 'middle' => __( 'Middle', 'campus' ), 'bottom' => __( 'Bottom', 'campus' ) );

		$color_values = array( 'normal' => __( 'Choose...', 'campus' ), 'primary' => __( 'Primary', 'campus' ), 'secondary' => __( 'Secondary', 'campus' ), 'tertiary' => __( 'Tertiary', 'campus' ) );

		$pattern_values = array( '-1' => __( 'Choose...', 'campus' ), 'circle-primary' => sprintf( '%s (%s)', __( 'Circles', 'campus' ), __( 'Primary', 'campus' ) ), 'secondary' => sprintf( '%s (%s)', __( 'Circles', 'campus' ), __( 'Secondary', 'campus' ) ), 'tertiary' => sprintf( '%s (%s)', __( 'Circles', 'campus' ), __( 'Tertiary', 'campus' ) ), 'neutral' => sprintf( '%s (%s)', __( 'Circles', 'campus' ), __( 'Neutral (White)', 'campus' ) ) );

		$animation_values = array( '-1' => __( 'Choose...', 'campus' ), 'parallax' => __( 'Parallax', 'campus' ), 'slide-from-right' => __( 'Slide from right', 'campus' ), 'slide-from-left' => __( 'Slide from left', 'campus' ), 'slide-from-top' => __( 'Slide from top', 'campus' ), 'slide-from-bottom' => __( 'Slide from bottom', 'campus' ), 'slide-from-top-right' => __( 'Slide from top right', 'campus' ), 'slide-from-top-left' => __( 'Slide from top left', 'campus' ), 'slide-from-bottom-right' => __( 'Slide from bottom right', 'campus' ), 'slide-from-bottom-left' => __( 'Slide from bottom left', 'campus' ) );

        $device_size_columns = '<label>' . $label . '</label><br>';
        foreach( $device_sizes as $size => $size_label ) {
            $selected_size = ( is_array( $selected ) && isset( $selected[$size] ) ) ? $selected[$size] : false;
            $device_size_columns .= '<label>' . $size . '</label> ' . $this->select( $name . '[' . $size . ']', $column_values, array( 'classes' => false, 'suffix' => $suffix, 'selected' => $selected_size, 'group' => $name ) );
        }

        $icon_values = array_merge( campus_icons(), array_flip( campus_social_links_icons() ) );

        $id_html = $id ? ' id="' . $id . '"' : '';
        $suffix_html = $suffix ? ' data-suffix="' . $suffix . '"' : '';

		$inputs = array(

            'icon' => '<label>' . __( 'Icon', 'campus' ) . '</label><br>' . $this->select( 'icon', $icon_values, array( 'id' => $id, 'suffix' => $suffix ) ),
            'taxonomy' => '<label>' . __( 'Taxonomy', 'campus' ) . '</label><br>' . $this->select( 'taxonomy', get_taxonomies( array( 'public' => true ) ), array( 'id' => $id, 'suffix' => $suffix ) ),

            'device_size_columns' => $device_size_columns,

			'aspectratio' 	=> '<label>' . __( 'Aspect Ratio', 'campus' ) . '</label><br>' . $this->select( 'aspectratio', $aspectratio_values, array( 'id' => $id, 'suffix' => $suffix ) ),

			'valign'		=> '<label>' . __( 'Vertical Alignment', 'campus' ) . '</label><br>' . $this->select( 'valign', $valign_values, array( 'id' => $id, 'suffix' => $suffix ) ),

			'class'			=> '<label>' . __( 'Css classes', 'campus' ) . '</label><input' . $id_html . ' class="smn_sc_input widefat mce-textbox" type="text" name="class"' . $suffix_html . '></p>',

			'style'			=> '<label>' . __( 'Custom css styles', 'campus' ) . '</label><textarea' . $id_html . ' class="smn_sc_input widefat mce-textbox" name="style"' . $suffix_html . '></textarea></p>',

			'size'   		=> '<label>' . __( 'Size', 'campus' ) . '</label>' . $this->select( 'size', $size_values, array( 'id' => $id, 'suffix' => $suffix ) ),

			// Colors
			'color'   		=> '<label>' . __( 'Text color', 'campus' ) . '</label>' . $this->select( 'color', $color_values, array( 'id' => $id, 'suffix' => $suffix ) ),
			'bgcolor'		=> '<label>' . __( 'Background color', 'campus' ) . '</label>' . $this->select( 'bgcolor', $color_values, array( 'id' => $id, 'suffix' => $suffix ) ),
			'bdcolor'		=> '<label>' . __( 'Border color', 'campus' ) . '</label>' . $this->select( 'bdcolor', $color_values, array( 'id' => $id, 'suffix' => $suffix ) ),

			// Scroll animation
			'animation'		=> '<label>' . __( 'Scroll animation', 'campus' ) . '</label>' . $this->select( 'animation', $animation_values, array( 'id' => $id, 'suffix' => $suffix ) ),
		);

		if( array_key_exists( $input , $inputs ) )
			return '<p class="smn_sc_input_wrap smn_sc_input_wrap-' . $name . '"' . ( $hidden ? ' style="display: none;"' : '' ) . '>' . $inputs[ $input ] . '</p>';

		return false;
	}

	private function select( $name, $values = array(), $args = array() ) {
        extract( array_merge( array(
            'id' => false,
            'selected' => false,
            'classes' => true,
            'suffix' => false,
            'group' => false
        ), $args ) );

        $id_html = $id ? ' id="' . $id . '"' : '';
        $suffix_html = $suffix ? ' data-suffix="' . $suffix . '"' : '';
        $group_html = $group ? ' data-group="' . $group . '"' : '';

		$output = '<select' . $id_html . ' name="' . $name . '" class="smn_sc_input' . ( $classes ? ' widefat mce-textbox' : '' ) . '"' . $suffix_html . $group_html . '>';
			foreach( $values as $key => $value )
				$output .= '<option value="' . $key . '"' . selected( $key, $selected, false ) . '>' . $value . '</option>';
		$output .= '</select>';

		return $output;
	}

}


/**
 * Class load
 * ------------------------------------------------------------------------------------
 */

new SMN_Shortcodes( $smn_enabled_shortcodes );

if( is_admin() )
	new SMN_Shortcodes_TinyMCE( $smn_enabled_shortcodes );


/**
 * Help
 * ------------------------------------------------------------------------------------
 */


// Add contextual help button tabs on post/page/product/faq...
//add_action( 'load-post.php', 'add_help_buttons' );


/**
 * Add contextual help tab about custom buttons.
 *
 * @access public
 * @return void
 */
function add_help_buttons() {
    // We are in the correct screen because we are taking advantage of the load-* action (below)

    $screen = get_current_screen();
    //$screen->remove_help_tabs();
    $screen->add_help_tab( array(
    	'id'       => 'shorcode-button',
    	'title'    => __( 'Boutons' ),
    	'content'  => '
    		<p><strong>Vous pouvez ajouter des boutons dans le contenu, il vous suffit d\'ajouter le code suivant :</strong></p>
    		<p><code>[button]Texte du bouton[/button]</code></p>
    		<p>Vous pouvez également ajouter des paramètres pour customiser vos boutons :</p>
    		<p><strong>Lien :</strong> url <code>link="http://..."</code></p>
    		<p><strong>Taille :</strong> small, large, big <code>size="large"</code></p>
    		<p><strong>Thème :</strong> alt, strong <code>theme="alt"</code></p>
    		<p><strong>Couleur :</strong> bleu, vert, orange, violet, rose <code>color="bleu"</code></p>
    		<p><strong>Picto :</strong> meditation, myaccount, hp, gift, faq, security, newsletter, remove, view, pay, play, pause, more, less, new, info, notice, tag, checked, prev, next, up, down, time, invoice, place, menu, facebook, twitter, googleplus, pinterest, linkedin <code>picto="faq"</code>
    			<br>Attention : Si vous utilisez le paramètre "picto", le texte du bouton n\'apparaîtra plus, il sera placé en champ titre.</p>
    		<p><strong>Titre :</strong> texte <code>title="En savoir plus"</code></p>
    		<p><strong>Cible :</strong> _blank, _self, _parent, _top  <code>target="_blank"</code></p>
    		<p><strong>Exemples de bouton :</strong></p>
    		<p><a href="'.get_bloginfo('url').'" title="En savoir plus" target="_blank"><img src="'.get_template_directory_uri().'/images/exemple-button.png"></a></p>
    		<p><code>[button link="'.get_bloginfo('url').'" theme="alt" title="En savoir plus" target="_blank"]Essayez dès maintenant[/button]</code></p>
    		<p><a href="'.get_bloginfo('url').'" target="_blank"><img src="'.get_template_directory_uri().'/images/exemple-button-2.png"></a></p>
    		<p><code>[button link="'.get_bloginfo('url').'" size="large" theme="strong" color="bleu" target="_blank"]Essayez dès maintenant[/button]</code></p>
    	'
    ));
    //add more help tabs as needed with unique id's
}
