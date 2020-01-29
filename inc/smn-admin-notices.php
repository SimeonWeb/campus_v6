<?php

/**
 * Class SMN Admin Notices
 */
class SMN_Admin_Notices {

    static $option_name = 'smn_admin_notices';

    static $roles = array( 'administrator', 'editor' );

    static $capability = 'smn_read_admin_notices';

    function __construct() {

        add_action( 'admin_init', array( $this, 'set_capability' ) );

        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
    }

    public function set_capability() {

        foreach( self::$roles as $role ) {
            $role = get_role( $role );
            $role->add_cap( self::$capability );
        }
    }

    public function add_dashboard_widget() {

        if( current_user_can( self::$capability ) ) {

            wp_add_dashboard_widget(
                     self::$option_name . '_dashboard_widget',         // Widget slug.
                     'Admin Notices',         // Title.
                     array( $this, 'dashboard_widget' ) // Display function.
            );
        }
    }

    public function dashboard_widget() {
        echo self::get_messages();
    }

    static function get_messages() {
        $logs = get_option( self::$option_name );

        if( ! $logs )
            return '<pre>Aucune donnée pour l\'instant</pre>';

        return '<pre style="overflow: auto; max-height: 14em;">' . $logs . '</pre>';
    }

    static function save_message( $message = '' ) {
        $logs = get_option( self::$option_name, '' );

        $new_logs = current_time( 'mysql' ) . ' | ' . trim( $message ) . "\n" . $logs;

        update_option( self::$option_name, $new_logs );
    }
}

new SMN_Admin_Notices();
