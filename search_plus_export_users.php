<?php

/**
 * @package Search plus Export Users
 */
/**
 * Plugin Name: Search plus Export Users
 * Plugin URI: http://www.i2ivision.com
 * Description: Search (filter) for users based on specific keywords then export results in CSV file
 * Author: i2ivision ( PHPdev5 )
 * Version: 0.1.5 beta
 * Author URI: http://www.i2ivision.com
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<?php

/**
 * @author: PHPdev5
 * [add_role_caps Get the administrator role then add cap. to manage plugin]
 * @return [None]
 */
function add_role_caps() {
    // gets the administrator role
    $role = get_role( 'administrator' );

    $role->add_cap( 'manage_search_export_users_plugin',true ); 
}
add_action( 'admin_init', 'add_role_caps');

/**
 * @author: PHPdev5
 * [export_users_actions Add menu page [Export Users] for our plugin]
 * @return [None]
 */
function export_users_actions() {
    //add menu page for [Search plus Export Users] plugin
    add_menu_page( "Export Users", "Export Users", 'manage_search_export_users_plugin', "export_users", "export_users_fn", "dashicons-format-aside", 26 );
    
    //add submenu page for setting page
    add_submenu_page( 'export_users', 'Notes Setting', 'Notes', 'manage_search_export_users_plugin', 'notes', 'setting_fn' );
}

add_action( 'admin_menu', 'export_users_actions' );

/**
 * @author: PHPdev5
 * [export_users_fn Build the Back-End of our plugin]
 * @return [None]
 */
function export_users_fn() {
    /**
     * [$html_template filter hook to allow users for changing plugin's template page]
     */
    $html_template = apply_filters( 'export_users_table_template','export_users_admin.php' );
    require_once( $html_template );
}

/**
 * @author: PHPdev5
 * [setting_fn Build the Setting page of our plugin]
 * @return [None]
 */
function setting_fn() {
    require_once( 'setting.php' );
}

/**
 * @author: PHPdev5
 * [plugin_helper_scripts Enqueue Scripts & Styles]
 * @return [None]
 */
function plugin_helper_scripts() {
    wp_enqueue_script( 'script_get_users', plugins_url( '/js/get_users.js', __FILE__ ) );
    wp_enqueue_script( 'script_Block_UI', plugins_url( '/js/jquery.blockUI.js', __FILE__ ) );
    wp_enqueue_style( 'script_get_users', plugins_url( '/css/styles.css', __FILE__ ) );
    wp_localize_script( 'script_get_users', 'MyAjax', array( 'ajaxurl'   => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'admin_init', 'plugin_helper_scripts' );

/**
 * @author: PHPdev5
 * [my_load_meta_key load all meta_key in drop down menu]
 * @return [Array]
 */
function load_meta_key() {
    check_ajax_referer( 'bk-ajax-nonce', 'security' );
    global $wpdb;
    $customkeys = array();
    $customkeys[] = $wpdb->get_results( "SELECT DISTINCT meta_key FROM $wpdb->usermeta", ARRAY_A );
    foreach ( $customkeys as $customvalue ) {
        $customvalue;
    }
    wp_send_json( $customvalue );
}

add_action( 'wp_ajax_nopriv_load-meta', 'load_meta_key' );
add_action( 'wp_ajax_load-meta', 'load_meta_key' );

/**
 * @author: PHPdev5
 * [view_all_users Get all users on site]
 * @return [Array]
 */
function view_all_users() {
    check_ajax_referer( 'bk-ajax-nonce', 'security' );
    $content = array();
    $all_users = get_users();
    foreach ( $all_users as $e_user ) {
        $content[] = array( "userid" => $e_user->ID, "username" => $e_user->display_name, "useremail" => $e_user->user_email );
    }
    wp_send_json( $content );
}

add_action( 'wp_ajax_nopriv_get_all_users', 'view_all_users' );
add_action( 'wp_ajax_get_all_users', 'view_all_users' );


/**
 * @author: PHPdev5
 * [generate_csv Generate CSV file including Search Results]
 * @return [None]
 */
function generate_csv() {
    if ( isset( $_POST['download_csv'] ) ) {
        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce( $nonce, 'export-form' ) ) {
            die( 'Security check' );
        }
        ob_end_clean();
        $name = "export-results_" . date('Y-m-d') . ".csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $name );
        $op = fopen( 'php://output', 'a' );
        $en_active_export = append_all_meta( 1 );
        $count = 0;
        if ( !empty( $en_active_export ) ) {
            if ( $_REQUEST["hidden_val"] == "D" ) {
                $finalHeader = array( 'ID', 'User Name', 'E-mail' );
            } else {
                $meta_key = $_REQUEST["meta_name"];
                $headerArray = array( 'ID', 'User Name', 'E-mail' );
                $headermerge = array_merge( $headerArray, $meta_key );
                $finalHeader = array_unique( $headermerge );
            }
            fputcsv( $op, $finalHeader );
            foreach ( $en_active_export as $row ) {
                fputcsv( $op, $row );
                $count++;
            }
        } else {
            fputcsv( $op, "" );
        }
        fclose( $op );
        die();
    }
}

add_action( "admin_init", 'generate_csv' );

/**
 * @author: PHPdev5
 * @Description: Get users based on Specific Values  
 * @return [Array]
 */
function append_all_meta( $check = 0 ) {
    $operation = $_REQUEST["op_args"];
    $meta_key = $_REQUEST["meta_name"];
    $meta_value = $_REQUEST["meta_value"];
    $meta_compare = $_REQUEST["meta_compare"];
    $role_data = $_REQUEST["user_role"];
    if ( $role_data == 'no_role' ) {
        $role_data = '';
    }
    if ( $_REQUEST["hidden_val"] == "D" ) {
        $content = array();
        $all_users = get_users();
        foreach ( $all_users as $e_user ) {
            $content[] = array( $e_user->ID, $e_user->display_name, $e_user->user_email );
        }
        return $content;
    }

    // Allow Duplicate Keys or Values for Associative Array
    function duplicateKeys( $key, $val ) {
        return array( $key => $val );
    }
    $arrayResult = array_map( 'duplicateKeys', $meta_key, $meta_value );
    //End

    $queryArray = array( 'relation' => $operation );
    $count = 0;
    foreach ( $arrayResult as $key => $value ) {
        foreach ( $value as $Metakey => $Metavalue ) {
            $metaArray = array( "key"     => $Metakey,
                                "value"   => $Metavalue,
                                "compare" => $meta_compare[$count] );
            array_push( $queryArray, $metaArray );
            $count++;
        }
    }
    $args = array(
                   'role'       => $role_data,
                   'meta_query' => $queryArray
                );
    $all_users = get_users( $args );
    //To return users in CSV file
    if ( $check == 1 ) {
        $content = array();
        foreach ( $all_users as $user ) {
            $content_keys = array();
            $metakey = array_unique( $meta_key );
            foreach ( $metakey as $single_key ) {
                $content_keys[] = get_user_meta( $user->ID, $single_key, true );
            }
            $content = array( $user->ID, $user->display_name, $user->user_email );
            $finalcontent[] = array_merge( $content, $content_keys );
        }
        return $finalcontent;
    }
    //End
    check_ajax_referer( 'bk-ajax-nonce', 'security' );
    $resultSearch = array();
    if ( $all_users ) {
        foreach ( $all_users as $each_user ) {
            $resultSearch[] = array( "id" => $each_user->ID, "username" => $each_user->display_name, "email" => $each_user->user_email );
        }
        wp_send_json( $resultSearch );
    } else {
        $resultSearch = -1;
        wp_send_json( $resultSearch );
    }
    die;
}

add_action( 'wp_ajax_nopriv_append_meta', 'append_all_meta' );
add_action( 'wp_ajax_append_meta', 'append_all_meta' );

/**
 * @author: PHPdev5
 * @Description: Get Users'Role in Drop-Down Menu 
 * @return [string]  
 */
function users_roles() {
    check_ajax_referer( 'bk-ajax-nonce', 'security' );
    global $wp_roles;
    $role_data = '<option value="no_role">All</option>';
    foreach ( $wp_roles->role_names as $role => $name ) :
        $role_data .='<option value="' . $role . '">' . $role . '</option>';
    endforeach;
    echo $role_data;
    die();
}
add_action( 'wp_ajax_nopriv_user-role', 'users_roles' );
add_action( 'wp_ajax_user-role', 'users_roles' );

/**
 * @author: PHPdev5
 * [setting_notes Add Notes for Users that used the plugin]
 * @return [None]
 */
function setting_notes() {
    if ( isset($_POST["save_notes"] ) ) {
        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce( $nonce, 'notes-users' ) ) {
            die( 'Security check' );
        }
        $notes = $_POST["notes_users"];
        update_option( 'notes-users', $notes );
    }
}

add_action( "admin_init", 'setting_notes' );
?>