<?php
/**
 * @package Search Plus Export Users
 */
/**
 * Plugin Name: Search Plus Export Users (SPEU)
 * Plugin URI: http://www.i2ivision.com
 * Description: Search (filter) for users based on specific keywords then export results in CSV file
 * Author: i2ivision ( PHPdev5 )
 * Version: 1.0
 * Author URI: http://www.i2ivision.com
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Description of SPEU [Search (filter) for users based on specific keywords then export results in CSV file]
 *
 * @author i2ivision ( PHPdev5 )
 */
final class SPEU {
	public $role;
	// SPEU Capability for Administrator
	private $speu_cap = 'manage_search_export_users_plugin';
	// SPEU main template
	public $plugin_template ;
	// Drop-Down Custom meta key
	public $custom_MetaKey = array();
	// All Users table fields
	private $actual_fields = array();
	// Default Users Search Table Fields 
	public $speu_fields = array();
	public $fieldsSearch = array();
	public $expected_fields = array();
	// SPEU Fields Search
	public $final_fields = array();
	public $final_fields_key = array();
	public $final_fields_value = array();
	// Users Args. 
	public $args = array();
	public $count = 0;
	// Users operation param
	public $operation;
	// Users key inside meta_query param
	public $meta_key;
	// Users value inside meta_query param
	public $meta_value;
	// Users compare inside meta_query param
	public $meta_compare;
	// Users role param
	public $role_data;
	public $users_id = array();
	public $export_by_ID;
	private $nonce;
	public $csv_name;
	public $file_extension = ".csv";
	public $all_roles = array();
	private static $instance;


	public function __construct() {
		$this->actual_fields = array( 'ID','user_login','user_nicename','user_email','user_url','user_registered','user_status','display_name' );
		$this->speu_fields = array( 'ID'            => 'ID',
	                          	 	'display_name'  => 'User Name',
	                             	'user_email'    => 'E-mail'
	                        	   );
		$this->csv_name = "export-results_" . date( 'Y-m-d' );
		add_action( 'admin_init', array( $this , 'add_role_caps' ) );
		add_action( 'admin_menu', array( $this , 'export_users_actions' ) );
		add_action( 'admin_init', array( $this , 'plugin_helper_scripts' ) );
		add_action( 'wp_ajax_get_all_users', array( $this , 'view_all_users' ) );
		add_action( "admin_init", array( $this , 'generate_csv' ) );
		add_action( 'wp_ajax_append_meta', array( $this , 'append_all_meta' ) );
		add_action( 'wp_ajax_load-meta', array( $this , 'load_meta_key' ) );
		add_action( 'wp_ajax_user-role', array( $this , 'users_roles' ) );
		add_action( "admin_init", array( $this , 'setting_notes' ) );
		add_action('admin_footer', array( $this , 'add_custom_bulk_action_for_users' ) );
		add_filter('user_row_actions', array( $this , 'user_action_links' ), 10, 2);
		add_action( 'admin_action_export', array( $this , 'selected_bulk_action_handler' ) );
		add_action( 'wp_ajax_export-users-lists', array( $this , 'export_users_lists_action' ) );
	}

	// Prevent cloning of the instance
	private function __clone() {
		
	}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * add_role_caps Get the administrator role then add cap. to manage plugin
	 * @return None
	 */
	public function add_role_caps() {
		// gets the administrator role
    	$this->role = get_role( 'administrator' );
	    // add capability to administrator
	    $this->role->add_cap( $this->speu_cap ,true ); 
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * export_users_actions Add menu page [Export Users] for our plugin
	 * @return None
	 */
	public function export_users_actions() {
	//add menu page for [Search plus Export Users] plugin
    add_menu_page( "Search Plus Export Users", "Export Users", $this->speu_cap, "export_users", array( $this, 'export_users_fn' ), "dashicons-format-aside", 26 );
    
    //add submenu page for setting page
    add_submenu_page( 'export_users', 'SPEU Setting', 'Setting', $this->speu_cap, 'setting', array( $this, 'setting_fn' ) );
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * export_users_fn Build the Back-End of our plugin
	 * @return None
	 */
	public function export_users_fn() {
	    /**
	     * $html_template filter hook to allow users for changing plugin's template page
	     */
	    $this->plugin_template = apply_filters( 'export_users_template','includes/export_users_admin.php' );
	    require_once( $this->plugin_template );
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * setting_fn Build the Setting page of our plugin
	 * @return None
	 */
	public function setting_fn() {
	    require_once( 'includes/setting.php' );
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * plugin_helper_scripts Enqueue Scripts & Styles
	 * @return None
	 */
	public function plugin_helper_scripts() {
	    wp_enqueue_script( 'script_get_users', plugins_url( 'assets/js/get_users.js', __FILE__ ) );
	    wp_enqueue_script( 'script_Block_UI', plugins_url( 'assets/js/jquery.blockUI.js', __FILE__ ) );
	    wp_enqueue_style( 'script_get_users', plugins_url( 'assets/css/styles.css', __FILE__ ) );
	    wp_localize_script( 'script_get_users', 'MyAjax', array( 'ajaxurl'   => admin_url( 'admin-ajax.php' ) ) );
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * load_meta_key load all meta_key in drop down menu
	 * @return Array
	 */
	public function load_meta_key() {
	    check_ajax_referer( 'bk-ajax-nonce', 'security' );
	    global $wpdb;
	    $this->custom_MetaKey[] = $wpdb->get_results( "SELECT DISTINCT meta_key FROM $wpdb->usermeta", ARRAY_A );
	    foreach ( $this->custom_MetaKey as $customvalue ) {
	        $customvalue;
	    }
	    wp_send_json( $customvalue );
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * getFieldSearch Get fields for get_users() arguments
	 * @return Array
	 */
	public function getFieldSearch() {
	    /**
	     * Fires immediately after the plugin loads.
	     * @since 0.1.5 beta
	     * @param Array    $all_fields array of fields in WP_USER.
	     */
	    $this->fieldsSearch = apply_filters( 'speu_add_fields' , $this->speu_fields ); 
	    $fields_keys = array_keys( $this->fieldsSearch );
	    $this->expected_fields = array_diff( $fields_keys ,$this->actual_fields );
	    if( empty( $this->expected_fields ) ) {
	        return $this->fieldsSearch;
	    }
	    else {
	        foreach( $this->expected_fields as $keys => $expected_value ) {
	            unset( $fields_keys[$keys] );
	            unset( $this->fieldsSearch[$expected_value] );
	        }
	        return $this->fieldsSearch;
	    }
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * view_all_users Get all users on site
	 * @return Array
	 */
	public function view_all_users() {
	    check_ajax_referer( 'bk-ajax-nonce', 'security' );
	    $this->final_fields = $this->getFieldSearch();
		$this->final_fields_key = array_keys( $this->final_fields );
	    $this->args = array( 'fields'   => $this->final_fields_key , 
	                   		 'orderby'  => 'ID', 
	                   		 'order'    => 'ASC'
	                 		);
	    $all_users = get_users( $this->args );
	    foreach ( $all_users as $e_user ) {
	        $content[] = $e_user;
	    }
	    wp_send_json( $content );
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * generate_csv Generate CSV file including Search Results
	 * @return None
	 */
	public function generate_csv() {
	    if ( isset( $_POST['download_csv'] ) ) {
	    	$this->nonce = $_REQUEST['_wpnonce'];
	        if ( !wp_verify_nonce( $this->nonce, 'export-form' ) ) {
	            die( 'Security check' );
	        }
	        ob_end_clean();
	        /**
	         * Fires immediately after the user generates CSV file.
	         * @since 0.1.5 beta
	         * @param string    $file_name The name of CSV file generated.
	         */
	        $file_name = apply_filters( 'speu_csv_file_name', $this->csv_name  );
	        $name = $file_name.$this->file_extension;
	        header( 'Content-Type: text/csv; charset=utf-8' );
	        header( 'Content-Disposition: attachment; filename=' . $name );
	        header("Pragma: no-cache");
	        header("Expires: 0");  
	        $this->final_fields = $this->getFieldSearch();
			$this->final_fields_key = array_keys( $this->final_fields );
			$this->final_fields_value = array_values( $this->final_fields );  
	        $op = fopen( 'php://output', 'a' );
	        $time_now = fputcsv($op, array( "", "Created File at :".date( "Y-m-d h:i:s:a" ) ) );
	        $this->meta_key = $_REQUEST["meta_name"];
			$this->users_id = $_POST['idusers'];
			$this->export_by_ID = $_POST['export_by_id'];
	        if( !empty( $this->export_by_ID ) ) {
	            $by_ID = explode( ",", $this->export_by_ID );
	            $this->args = array( 'include'    => $by_ID ,
	                           		 'fields'     => $this->final_fields_key , 
	                           		 'orderby'    => 'ID', 
	                          		 'order'      => 'ASC' 
	                         );
	        }
	        else if( !empty( $this->users_id ) ) {
	            $by_ID = explode( ",", $this->users_id ); 
	            $this->args = array( 'include'    => $by_ID , 
	                           'fields'     => $this->final_fields_key , 
	                           'orderby'    => 'ID', 
	                           'order'      => 'ASC'
	                         );             
	        }
	        else if( empty( $this->export_by_ID ) ) {
	            $this->args = array( 'include'    => array() , 
	                           		 'fields'     => $this->final_fields_key , 
	                           		 'orderby'    => 'ID', 
	                           		 'order'      => 'ASC'
	                          		);
	            $this->meta_key = array();
	        }     
	            if ( $_REQUEST["hidden_val"] == "D" ) {
	                $finalHeader = $this->final_fields_value;
	            } else {
	            	$this->meta_key = $_REQUEST["meta_name"];
	                if( in_array( "no_value", $this->meta_key ) ) {
	                    $finalHeader = $this->final_fields_value;
	                } else {
	                    $headerArray = $this->final_fields_value;
	                    $headermerge = array_merge( $headerArray, $this->meta_key );
	                    $finalHeader = array_unique( $headermerge );
	                }
	            }
	            fputcsv( $op, $finalHeader );
	            $export_those_users = get_users( $this->args );
	            foreach ( $export_those_users as $user ) {
	                $content_keys = array();
	                $content = array();
	                $metakey = array_unique( $this->meta_key );
	                foreach ( $metakey as $single_key ) {
	                    $content_keys[] = get_user_meta( $user->ID, $single_key, true );
	                }
	            reset( $user );
	            while( list($key, $val) = each( $user ) ) {
	                $content[] = $val;
	            }
	                $en_active_export[] = array_merge( $content, $content_keys );             
	            }
	            foreach( $en_active_export as $value ) {
	                fputcsv( $op, $value ); 
	            } 
	        fclose( $op );
	        die();
	    }
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * append_all_meta Get users based on Specific Values  
	 * @return Array
	 */
	public function append_all_meta() {
	    check_ajax_referer( 'bk-ajax-nonce', 'security' );
	    $this->final_fields = $this->getFieldSearch();
		$this->final_fields_key = array_keys( $this->final_fields );
		$this->final_fields_value = array_values( $this->final_fields );
	    $this->operation = $_REQUEST["op_args"];
		$this->meta_key = $_REQUEST["meta_name"];
		$this->meta_value = $_REQUEST["meta_value"];
		$this->meta_compare = $_REQUEST["meta_compare"];
		$this->role_data = $_REQUEST["user_role"];
	    if ( $this->role_data == 'no_role' ) {
	        $this->role_data = '';
	    }
	    // Allow Duplicate Keys or Values for Associative Array
	    function duplicateKeys( $key, $val ) {
	        return array( $key => $val );
	    }
	    $arrayResult = array_map( 'duplicateKeys', $this->meta_key, $this->meta_value );
	    //End
	    $queryArray = array( 'relation' => $this->operation );
	    $this->count = 0;
	    foreach ( $arrayResult as $key => $value ) {
	        foreach ( $value as $Metakey => $Metavalue ) {
	            $metaArray = array( "key"     => $Metakey,
	                                "value"   => $Metavalue,
	                                "compare" => $this->meta_compare[$this->count] );
	            array_push( $queryArray, $metaArray );
	            $this->count++;
	        }
	    }
	    $this->args = array(
	                   'fields'     => $this->final_fields_key,
	                   'orderby'    => 'ID',
	                   'order'      => 'ASC',
	                   'role'       => $this->role_data,
	                   'meta_query' => $queryArray
	                  );
	    $all_users = get_users( $this->args );
	    if ( $all_users ) {
	        foreach ( $all_users as $each_user ) {
	            $resultSearch[] = $each_user;
	            $IdsArray[] = array( $each_user->ID );
	        }
	        $FinalArray = array( "dataTable" => $resultSearch,
	                             "IDs"       => $IdsArray,
	                             "Header"    => $this->final_fields_value
	                            );
	        wp_send_json( $FinalArray );
	    } else {
	        $FinalArray = -1;
	        wp_send_json( $FinalArray );
	    }
	    die;
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * users_roles Get Users'Role in Drop-Down Menu 
	 * @return string
	 */
	public function users_roles() {
	    check_ajax_referer( 'bk-ajax-nonce', 'security' );
	    global $wp_roles;
	    /**
	     * Fires immediately after the user generates CSV file.
	     * @since 0.1.5 beta
	     * @param Array    $all_roles Users Roles.
	     */
	    $this->all_roles = apply_filters( 'speu_roles_search' , $wp_roles->role_names ); 
	    $role_data = '<option value="no_role">All</option>';
	    foreach ( $this->all_roles as $role => $name ) :
	        $role_data .='<option value="' . $role . '">' . $name . '</option>';
	    endforeach;
	    echo $role_data;
	    die();
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * setting_notes Add Notes for Users that used the plugin (old)
	 * @return None
	 */
	public function setting_notes() {
	    if ( isset($_POST["save_notes"] ) ) {
	        $this->nonce = $_REQUEST['_wpnonce'];
	        if ( !wp_verify_nonce( $this->nonce, 'notes-users' ) ) {
	            die( 'Security check' );
	        }
	        $notes = $_POST["notes_users"];
	        update_option( 'notes-users', $notes );
	    }
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * add_custom_bulk_action_for_users Add Bulk Action for Users Table
	 * @return None
	 */
	public function add_custom_bulk_action_for_users() {
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


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * user_action_links Add Row Action for Users Table List
	 * @return Array
	 */
	public function user_action_links($actions, $user_object) {
	    $actions['export_user'] = "<a class='export_this_user' user-id='". $user_object->ID. "' href='" . wp_nonce_url(admin_url( "admin.php?page=export_users&ids=$user_object->ID"), "export_this_user" ,"wp_http_referer" ) ."'>Export</a>";
	    return $actions;
	}


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * selected_bulk_action_handler Process Selected Bulk Action
	 * @return None
	 */
	public function selected_bulk_action_handler() {
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


	/**
	 * @author: i2ivision ( PHPdev5 )
	 * export_users_lists_action Add Bulk Action (export user) on Admin Users Page
	 * @return Array
	 */
	public function export_users_lists_action() {
	    $this->users_id = $_REQUEST['ids'];
	    $this->final_fields = $this->getFieldSearch();
		$this->final_fields_key = array_keys( $this->final_fields );
		$this->final_fields_value = array_values( $this->final_fields );
	    if( !empty( $this->users_id ) ) {
	        if( preg_match( "," , $this->users_id )  ) {
	            $e_id = explode( ',', $this->users_id );
	            array_pop( $e_id );
	            $this->args = array( 'include'    => $e_id , 
	                           		 'fields'     => $this->final_fields_key , 
	                           		 'orderby'    => 'ID', 
	                           		 'order'      => 'ASC' 
	                        		);
	        }
	        else {
	           $this->args = array( 'include'    => $this->users_id , 
	                          		'fields'     => $this->final_fields_key , 
	                          		'orderby'    => 'ID', 
	                          		'order'      => 'ASC' 
	                        	   ); 
	        }
	        $usersData = get_users( $this->args );
	        // print_r($this->users_id);die();
	        foreach( $usersData as $user ) {
	            $content[] = $user;
	        }
	        wp_send_json( $content );
	    } else {
	        echo -1;
	        die();
	    }
	}


}

$speu_obj = new SPEU();