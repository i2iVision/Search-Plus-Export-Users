<?php

/**
 * @package Search plus Export Users
 */
/**
 * Plugin Name: Search plus Export Users (SPEU)
 * Plugin URI: http://www.i2ivision.com
 * Description: Search (filter) for users based on specific keywords then export results in CSV file
 * Author: i2ivision ( PHPdev5 )
 * Version: 0.1.5 beta
 * Author URI: http://www.i2ivision.com
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );



/**
 * @author: PHPdev5
 * add_role_caps Get the administrator role then add cap. to manage plugin
 * @return None
 */
function add_role_caps() {
    // gets the administrator role
    $role = get_role( 'administrator' );
    // add capability to administrator
    $role->add_cap( 'manage_search_export_users_plugin',true ); 
}
add_action( 'admin_init', 'add_role_caps');

/**
 * @author: PHPdev5
 * export_users_actions Add menu page [Export Users] for our plugin
 * @return None
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
 * export_users_fn Build the Back-End of our plugin
 * @return None
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
 * setting_fn Build the Setting page of our plugin
 * @return None
 */
function setting_fn() {
    require_once( 'setting.php' );
}

/**
 * @author: PHPdev5
 * plugin_helper_scripts Enqueue Scripts & Styles
 * @return None
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
 * load_meta_key load all meta_key in drop down menu
 * @return Array
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

add_action( 'wp_ajax_load-meta', 'load_meta_key' );

/**
 * @author: PHPdev5
 * view_all_users Get all users on site
 * @return Array
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

add_action( 'wp_ajax_get_all_users', 'view_all_users' );


/**
 * @author: PHPdev5
 * generate_csv Generate CSV file including Search Results
 * @return None
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
        $meta_key = $_REQUEST["meta_name"];
        $users_id = $_POST['idusers'];
        $export_by_ID = $_POST['export_by_id'];
        if( !empty( $export_by_ID ) ) {
            $by_ID = explode( ",", $export_by_ID );
            $args = array( 'include'  => $by_ID );
        }
        else if( !empty( $users_id ) ) {
            $by_ID = explode( ",", $users_id ); 
            $args = array( 'include'  => $by_ID );             
        }
        else if( empty( $export_by_ID ) ) {
            $args = array( 'include'  => array() );
        }     
            if ( $_REQUEST["hidden_val"] == "D" ) {
                $finalHeader = array( 'ID', 'User Name', 'E-mail' );
            } else {
                $meta_key = $_REQUEST["meta_name"];
                if( in_array( "no_value", $meta_key ) ) {
                    $finalHeader = array( 'ID', 'User Name', 'E-mail' );
                } else {
                    $headerArray = array( 'ID', 'User Name', 'E-mail' );
                    $headermerge = array_merge( $headerArray, $meta_key );
                    $finalHeader = array_unique( $headermerge );
                }
            }
            fputcsv( $op, $finalHeader );
            $export_those_users = get_users( $args );
            foreach ( $export_those_users as $user ) {
                $content_keys = array();
                $metakey = array_unique( $meta_key );
                foreach ( $metakey as $single_key ) {
                    $content_keys[] = get_user_meta( $user->ID, $single_key, true );
                }
                $content = array( $user->ID, $user->display_name, $user->user_email );
                $en_active_export[] = array_merge( $content, $content_keys );
            }
            foreach( $en_active_export as $final_export ) {
                fputcsv( $op, $final_export ); 
            } 
        fclose( $op );
        die();
    }
}

add_action( "admin_init", 'generate_csv' );

/**
 * @author: PHPdev5
 * append_all_meta Get users based on Specific Values  
 * @return Array
 */
function append_all_meta() {
    $operation = $_REQUEST["op_args"];
    $meta_key = $_REQUEST["meta_name"];
    $meta_value = $_REQUEST["meta_value"];
    $meta_compare = $_REQUEST["meta_compare"];
    $role_data = $_REQUEST["user_role"];
    if ( $role_data == 'no_role' ) {
        $role_data = '';
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
    check_ajax_referer( 'bk-ajax-nonce', 'security' );
    $resultSearch = array();
    $IdsArray = array();
    if ( $all_users ) {
        foreach ( $all_users as $each_user ) {
            $resultSearch[] = array( "userid" => $each_user->ID, "username" => $each_user->display_name, "email" => $each_user->user_email );
            $IdsArray[] = array($each_user->ID);
        }
        $FinalArray = array("dataTable" => $resultSearch,
                            "IDs"       => $IdsArray
                            );
        wp_send_json( $FinalArray );
    } else {
        $FinalArray = -1;
        wp_send_json( $FinalArray );
    }
    die;
}

add_action( 'wp_ajax_append_meta', 'append_all_meta' );

/**
 * @author: PHPdev5
 * users_roles Get Users'Role in Drop-Down Menu 
 * @return string
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

add_action( 'wp_ajax_user-role', 'users_roles' );

/**
 * @author: PHPdev5
 * setting_notes Add Notes for Users that used the plugin
 * @return None
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


/**
 * @author: PHPdev5
 * add_custom_bulk_action_for_users Add Bulk Action for Users Table
 * @return None
 */
function add_custom_bulk_action_for_users() {
    global $WP_Screen;
    $screen_id = get_current_screen();
    if($screen_id->id == 'users') {
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('<option>').val('export').text('Export').appendTo("select[name='action']");
                jQuery('<option>').val('export').text('Export').appendTo("select[name='action2']");
            });
        </script>
    <?php
    }
}
add_action('admin_footer', 'add_custom_bulk_action_for_users');

/**
 * @author: PHPdev5
 * user_action_links Add Row Action for Users Table List
 * @return Array
 */
function user_action_links($actions, $user_object) {
    $actions['export_user'] = "<a class='export_this_user' user-id='". $user_object->ID. "' href='" . wp_nonce_url(admin_url( "admin.php?page=export_users&ids=$user_object->ID"), "export_this_user" ,"wp_http_referer" ) ."'>Export</a>";
    return $actions;
}

add_filter('user_row_actions', 'user_action_links', 10, 2);


/**
 * @author: PHPdev5
 * selected_bulk_action_handler Process Selected Bulk Action
 * @return None
 */
function selected_bulk_action_handler() {
    $ids = $_GET["users"];
    if( empty( $ids ) ) {
        return 0;
    }
    else {
        $url = 'admin.php?page=export_users&ids=';
        foreach( $ids as $this_id ) {
            $url .= $this_id.',';
        }
        wp_redirect( $url );
        exit(); 
    }
}

add_action( 'admin_action_export', 'selected_bulk_action_handler' );


/**
 * @author: PHPdev5
 * export_users_lists_action Add Bulk Action (export user) on Admin Users Page
 * @return Array
 */
function export_users_lists_action() {
    $ids = $_REQUEST['ids'];
    if( !empty( $ids ) ) {
        if( strlen( $ids ) > 1 ) {
            $e_id = explode( ',', $ids );
            array_pop( $e_id );
            $args = array( 'include'  => $e_id );
        }
        else {
           $args = array( 'include'  => $ids ); 
        }
        $usersData = get_users( $args );
        $content = array();
        foreach( $usersData as $user ) {
            $content[] = array( "userid"    => $user->ID,
                                "username"  => $user->display_name,
                                "useremail" => $user->user_email 
                               );
        }
        wp_send_json( $content );
    } else {
        echo -1;
        die();
    }
}

add_action( 'wp_ajax_export-users-lists', 'export_users_lists_action' );

?>