<?php
/*
Plugin Name: Author Category
Plugin URI: http://en.bainternet.info
Description: simple plugin limit authors to post just in one category.
Version: 0.7
Author: Bainternet
Author URI: http://en.bainternet.info
*/
/*
        *   Copyright (C) 2012 - 2013 Ohad Raz
        *   http://en.bainternet.info
        *   admin@bainternet.info

        This program is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 2 of the License, or
        (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program; if not, write to the Free Software
        Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Disallow direct access to the plugin file */
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

/** Walker_Category_Checklist class */
require_once( ABSPATH . 'wp-admin/includes/class-walker-category-checklist.php' );

class Author_Category_Walker_Category_Checklist extends Walker_Category_Checklist {

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract($args);
		$taxonomy = 'category';
		$name = 'author_cat';

		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
	}

}

if( ! class_exists( 'Author_Category' ) ) {

    class Author_Category {
        /**
         * $txtDomain
         *
         * Holds textDomain
         * @since  0.7
         * @var string
         */
        public  $txtDomain = 'author_cat';

        /**
         * class constractor
         * @author Ohad Raz
         * @since 0.1
         */
        public function __construct(){

            $this->hooks();

            if (is_admin()){
                $this->adminHooks();
            }
        }

        /**
         * hooks add all action and filter hooks
         * @since 0.6
         * @return void
         */
        public function hooks(){

            // save user field
            add_action( 'personal_options_update', array( $this,'save_extra_user_profile_fields' ));
            add_action( 'edit_user_profile_update', array( $this,'save_extra_user_profile_fields' ));
            add_action( 'user_register', array( $this,'save_extra_user_profile_fields' ));
            // add user field
            add_action( 'show_user_profile', array( $this,'extra_user_profile_fields' ));
            add_action( 'edit_user_profile', array( $this,'extra_user_profile_fields' ));
			add_action( 'user_new_form', array( $this,'extra_user_profile_fields' ));


            //xmlrpc post insert hook and quickpress
            add_filter('xmlrpc_wp_insert_post_data', array($this, 'user_default_category'),2);
            add_filter('pre_option_default_category',array($this, 'user_default_category_option'));
            add_filter('user_can_edit_program_infos',array($this, 'user_can_edit_cat'), 10, 3);

            //post by email cat
            add_filter( 'publish_phone',array($this,'post_by_email_cat'));

            // Manage users columns
            add_filter( 'manage_users_columns', array($this, 'add_author_cat_column' ) );
            add_filter( 'manage_users_custom_column', array($this, 'author_cat_column_html' ), 10, 3 );

            // Manage categories columns
            add_filter( 'manage_edit-category_columns', array($this, 'add_category_column' ) );
            add_filter( 'manage_category_custom_column', array($this, 'author_cat_column_html' ), 10, 3 );
        }

        /**
         * hooks add all action and filter hooks for admin side
         *
         * @since 0.7
         * @return void
         */
        public function adminHooks(){
            //translations
            add_action('plugins_loaded', array($this,'load_translation'));
            //remove quick and bulk edit
            global $pagenow;
            if ('edit.php' == $pagenow)
                add_action('admin_print_styles',array(&$this,'remove_quick_edit'));

            //add metabox
            add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

            //add admin panel
            /*
if (!class_exists('SimplePanel')){
                require_once(dirname( __FILE__ ) . '/inc/Simple_Panel_class.php');
                require_once(dirname( __FILE__ ) . '/inc/author_category_Panel_class.php');
            }
*/
        }

        function add_author_cat_column( $columns ) {
	        $columns['user_author_cat'] = __( 'Categories' );
			return $columns;
        }

        function add_category_column( $columns ) {
	        $columns['category_author_cat'] = __( 'Presenters', 'campus' );
			return $columns;
        }

        function author_cat_column_html( $output, $column_name, $object_id ) {

	        switch( $column_name ) {
	        	case 'user_author_cat':

	        		$cat = $this->get_user_cat( $object_id );

	        		if( $cat ) {
		        		if( ! is_array( $cat ) )
		        			$cat = array( $cat );

		    		    $cat_link = array();
		    		    foreach( $cat as $cat_id ) {

					        $category = get_category( $cat_id );

					        if( $category ) {
						        $cat_link[] = sprintf( '<a href="%s">%s</a>',
						        	add_query_arg(
									    array(
									        'taxonomy' 	 => 'category',
									        'tag_ID' 	 => $cat_id,
									        'post_type'	 => 'post',
									        'wp_http_referer' => campus_current_page_url()
									    ),
									    admin_url( 'term.php' )
									),
						        	$category->name
						        );
					        }
		    		    }
					    return join( ', ', $cat_link );

	        		} else {
		    		    return '<span aria-hidden="true">—</span><span class="screen-reader-text">Aucune catégorie</span>';
	        		}
					break;
	        	case 'category_author_cat':

	        		$users = get_term_meta( $object_id, 'users', true );

	        		if( $users ) {

		    		    $users_link = array();

		    		    foreach( $users as $user_id ) {

					        $user = get_user_by( 'ID', $user_id );

					        if( $user ) {
						        $users_link[] = sprintf( '<a href="%s">%s</a>',
						        	add_query_arg(
										    array(
										        'user_id' 	 => $user_id,
										        'wp_http_referer' => campus_current_page_url()
										    ),
										    admin_url( 'user-edit.php' )
											),
						        	$user->display_name
						        );
					        }
		    		    }
					    return join( ', ', $users_link );

	        		} else {
		    		    return '<span aria-hidden="true">—</span><span class="screen-reader-text">Aucun utilisateur</span>';
	        		}
					break;
	        }

	        return $output;
        }

        /**
         * user_default_category_option
         *
         * function to overwrite the defult category option per user
         *
         * @author Ohad   Raz
         * @since 0.3
         *
         * @param  boolea $false
         * @return mixed category id if user as a category set and false if he doesn't
         */
        public function user_default_category_option($false){
            $cat = $this->get_user_cat();
            if (!empty($cat) && count($cat) > 0){
                return $cat;
            }
            return false;
        }

        /**
         * user_default_category
         *
         * function to handle XMLRPC calls
         *
         * @author Ohad   Raz
         * @since 0.3
         *
         * @param  array $post_data  post data
         * @param  array $con_stactu xmlrpc post data
         * @return array
         */
        public function user_default_category($post_data,$con_stactu){
            $cat = $this->get_user_cat($post_data['post_author']);
            if (!empty($cat) && $cat > 0){
                $post_data['tax_input']['category'] = array($cat);
            }
            return $post_data;
        }

        /**
         * post_by_email_cat
         *
         * @author Ohad   Raz
         * @since 0.5
         *
         * @param  int $post_id
         * @return void
         */
        public function post_by_email_cat($post_id){
            $p = get_post($post_id);
            $cat = $this->get_user_cat($p['post_author']);
            if ($cat){
                $email_post = array();
                $email_post['ID'] = $post_id;
                $email_post['post_category'] = array($cat);
                wp_update_post($email_post);
            }
        }

        /**
         * remove_quick_edit
         * @author Ohad   Raz
         * @since 0.1
         * @return void
         */
        public function remove_quick_edit(){
			$current_user = wp_get_current_user();
            $cat = $this->get_user_cat($current_user->ID);
            if (!empty($cat) && count($cat) > 0){
                echo '<style>.inline-edit-categories{display: none !important;}</style>';
            }
        }

        /**
         * Adds the meta box container
         * @author Ohad Raz
         * @since 0.1
         */
        public function add_meta_box(){
			$current_user = wp_get_current_user();

            //get author categories
            $cat = $this->get_user_cat($current_user->ID);
            if (!empty($cat) && count($cat) > 0){
                //remove default metabox
                remove_meta_box('categorydiv', 'post', 'side');
                //add user specific categories
                add_meta_box(
                     'author_cat'
                    ,__( 'Categorie',$this->txtDomain )
                    ,array( &$this, 'render_meta_box_content' )
                    ,'post'
                    ,'side'
                    ,'low'
                );
            }
        }


        /**
         * Render Meta Box content
         * @author Ohad   Raz
         * @since 0.1
         * @return Void
         */
        public function render_meta_box_content(){
			$current_user = wp_get_current_user();
            $cats = get_user_meta($current_user->ID,'_author_cat',true);
            $cats = (array)$cats;
            // Use nonce for verification
            wp_nonce_field( basename( __FILE__ ), 'author_cat_noncename' );
            if (!empty($cats) && count($cats) > 0){
                if (count($cats) == 1){
                    $c = get_category($cats[0]);
                    echo __('L\'article sera posté dans : <strong>',$this->txtDomain) . $c->name .'</strong>';
                    echo '<input name="post_category[]" type="hidden" value="'.$c->term_id.'">';
                }else{
                    echo '<span style="color: #f00;">'.__('Make Sure you select only the categories you want: <strong>',$this->txtDomain).'</span><br />';
                    $options = get_option('author_cat_option');
                    $checked =  (!isset($options['check_multi']))? ' checked="checked"' : '';

                    foreach($cats as $cat ){
                        $c = get_category($cat);
                        echo '<label><input name="post_category[]" type="checkbox"'.$checked.' value="'.$c->term_id.'"> '.$c->name .'</label><br />';
                    }
                }
            }
            do_action('in_author_category_metabox',$current_user->ID);
        }

        /**
         * This will generate the category field on the users profile
         * @author Ohad   Raz
         * @since 0.1
         * @param  (object) $user
         * @return void
         */
         public function extra_user_profile_fields( $user ){
            //only admin and editors can see and save the categories
            if ( !current_user_can( 'manage_term_users' ) || is_object($user) && user_can( $user->ID, 'manage_term_users' ) ) { return false; }

			$current_user = wp_get_current_user();

            if (is_object($user) && $current_user->ID == $user->ID) { return false; }

            $select = wp_dropdown_categories(array(
                            'orderby'      => 'name',
                            'show_count'   => 0,
                            'hierarchical' => 1,
                            'hide_empty'   => 0,
                            'echo'         => 0,
                            'name'         => 'author_cat[]'));

            $walker = new Author_Category_Walker_Category_Checklist;

            $saved = is_object($user) ? get_user_meta($user->ID, '_author_cat', true ) : array();

            foreach((array)$saved as $c){
                $select = str_replace('value="'.$c.'"','value="'.$c.'" selected="selected"',$select);
            }
            $select = str_replace('<select','<select multiple="multiple"',$select);
            echo '<h3>'.__('Categories', 'author_cat').'</h3>
            <table class="form-table">
                <tr>
                    <th><label for="author_cat">Catégories dans lesquelles cette utilisateur peut poster</label></th>
                    <td>
						<div id="taxonomy-category" class="categorydiv">
						<div id="category-all" class="tabs-panel">
							<input type="hidden" value="" name="author_cat[]">
							<ul id="categorychecklist" class="categorychecklist form-no-clear">';
							echo wp_category_checklist( 0, 0, $saved, false, $walker );
							echo '</ul>
						</div>
						</div>
                    	<span class="description">'.__('Select a category to limit an author to post just in that category.',$this->txtDomain).'</span>
                    </td>
                </tr>
            </table>';
        }


        /**
         * This will save category field on the users profile
         * @author Ohad   Raz
         * @since 0.1
         * @param  (int) $user_id
         * @return VOID
         */
        public function save_extra_user_profile_fields( $user_id ) {

            //only admin and editors can see and save the categories
            if ( ! current_user_can( 'manage_term_users') || user_can( $user_id, 'manage_term_users' ) ) {
	            return false;
	        }

            if( isset( $_POST['author_cat'] ) ) {

            	// Remove first entry because it's empty
            	if( empty( $_POST['author_cat'][0] ) )
            		unset($_POST['author_cat'][0]);

            	// Check if the new entry is different from old
            	$current_author_cat = (array) get_user_meta( $user_id, '_author_cat', true );
            	$diff_old = array_diff( $current_author_cat, $_POST['author_cat'] );
            	if( $diff_old ) {
            		// Update old entry by deleting user_id
            		foreach( $diff_old as $old_val ) {
            			$old_users = (array) get_term_meta( $old_val, 'users', true );
            			if( ($key = array_search($user_id, $old_users)) !== false)
							unset($old_users[$key]);
            			update_term_meta( $old_val, 'users', $old_users );
            		}
            	}

            	// Save this user in term_users meta
            	foreach( $_POST['author_cat'] as $cat ) {
            	    $term_users = get_term_meta( $cat, 'users', true );
            	    if( is_array($term_users) ) {
            	    	if( in_array( $user_id, $term_users ) ) continue;
            	    	$new_term_users = $term_users;
            	    	$new_term_users[] = $user_id;
            	    	update_term_meta( $cat, 'users', $new_term_users );
            	    } else {
            	    	update_term_meta( $cat, 'users', array( $user_id ) );
            	    }
            	}

				if( !empty( $_POST['author_cat'] ) )
            		update_user_meta( $user_id, '_author_cat', array_values($_POST['author_cat']) );
            	else
            		delete_user_meta( $user_id, '_author_cat' );
            }


        }

        /**
         * save category on post
         * @author Ohad   Raz
         * @since 0.1
         * @deprecated 0.3
         * @param  (int) $post_id
         * @return Void
         */
        public function author_cat_save_meta( $post_id ) {
        }

        static public function get_user_cat( $user_id = null ) {

            if( $user_id === null ) {

				$current_user = wp_get_current_user();
                $user_id = $current_user->ID;
            }

            $cat = get_user_meta( $user_id, '_author_cat', true );

            if( empty( $cat ) || count( $cat ) <= 0 || ! is_array( $cat ) )
                return false;
            else
                return $cat[0];

        }

        public function user_can_edit_cat( $state, $category_id, $user_id = null){
            if ($user_id === null){
				$current_user = wp_get_current_user();
                $user_id = $current_user->ID;
            }

            if( ! is_admin() )
            	return false;

            if( current_user_can( 'manage_categories' ) )
            	return true;

            $cat = get_user_meta( $user_id, '_author_cat', true );

            if( is_array( $cat ) && in_array( $category_id, $cat ) )
            	return true;

            return $state;
        }

        /**
         * load_translation
         *
         * Loads translations
         *
         * @author Ohad Raz <admin@bainternet.info>
         * @since 0.7
         *
         * @return void
         */
        public function load_translation(){
            load_textdomain( $this->txtDomain, false, dirname( basename( __FILE__ ) ) . '/languages/' );
        }
    }//end class
}//end if

//initiate the class on admin pages only
if( is_admin() ) {
    $ac = new Author_Category();
}
