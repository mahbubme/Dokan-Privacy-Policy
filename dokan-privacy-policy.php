<?php 
/**
 * Plugin Name: Dokan Privacy POlicy
 * Plugin URI: https://mahbub.me
 * Description: This plugin adds privacy policy tab on seller store page
 * Version: 1.0.0
 * Author: Mahbubur Rahman
 * Author URI: https://mahbub.me
 * License: GPL2
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DOKAN_PRIVACY_POLICY_VERSION', '1.0.0' );
define( 'DOKAN_PRIVACY_POLICY_DIR', dirname( __FILE__ ) );

/**
 * Dokan_Privacy_Policy class
 *
 * @class Dokan_Privacy_Policy The class that holds the entire Dokan_Privacy_Policy plugin
 */
class Dokan_Privacy_Policy {

    /**
     * Constructor for the Dokan_Privacy_Policy class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {

		add_action( 'dokan_rewrite_rules_loaded', array( $this, 'load_rewrite_rules' ) ); 
    	add_filter( 'dokan_query_var_filter', array( $this, 'register_query_var' ), 10, 2 ); 
    	add_filter( 'dokan_store_tabs', array( $this, 'dokan_custom_store_tabs' ), 10, 2 );
    	add_filter( 'template_include', array( $this, 'store_pp_template' ), 99 );
    	add_action( 'dokan_settings_form_bottom', array( $this, 'dokan_seller_custom_setting_fields' ), 10, 2 );
    	add_action( 'dokan_store_profile_saved', array( $this, 'save_dokan_seller_fields_data' ), 10, 2 );

    }

    /**
     * Initializes the Dokan_Store_Support() class
     *
     * Checks for an existing Dokan_Store_Support() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Privacy_Policy();
        }

        return $instance;
    }

    /**
     * Load Rewrite Rules for store page
     */
    public function load_rewrite_rules( $custom_store_url ) {
        add_rewrite_rule( $custom_store_url.'/([^/]+)/pp?$', 'index.php?'.$custom_store_url.'=$matches[1]&store_pp=true', 'top' );
        add_rewrite_rule( $custom_store_url.'/([^/]+)/pp/page/?([0-9]{1,})/?$', 'index.php?'.$custom_store_url.'=$matches[1]&paged=$matches[2]&store_pp=true', 'top' );
    }

    // add privacy policy field on seller dashbaord settings page
	public function dokan_seller_custom_setting_fields( $current_user, $profile_info ) {
		// error_log(print_r($profile_info, true));
		//var_dump($profile_info);
		$store_pp  = get_user_meta($current_user, 'store_privacy_policy', true);
	  
	   	$output  = '<div class="dokan-form-group" id="store_privacy_policy">';
	   	$output .= '<label class="dokan-w3 dokan-control-label" for="store_privacy_policy">Privacy Policy</label>';
	   	$output .= '<div class="dokan-w8 dokan-text-left">';
	   	$output .= '<textarea rows="10" cols="50" name="store_privacy_policy">';
	   	$output .= $store_pp;
	   	$output .= '</textarea>';
	   	$output .= '</div>';
	   	$output .= '</div>';

	   	echo $output;
	}


	public function save_dokan_seller_fields_data($store_id, $dokan_settings) {
	    $value = isset( $_POST['store_privacy_policy'] ) ? $_POST['store_privacy_policy'] : '';
	    update_user_meta( $store_id, 'store_privacy_policy', $value );
	}


	public function dokan_custom_store_tabs($tabs, $store_id) {
	    $tabs['pp'] = array(
	         'title' => __( 'Privacy Policy', 'dokan-lite' ),
	         'url'   => $this->dokan_get_privacy_policy_url( $store_id )
	    );
	   
	   return $tabs;
	}

	/**
	 * Get privacy policy page
	 */
	public function dokan_get_privacy_policy_url( $user_id ) {
	    $userstore = dokan_get_store_url( $user_id );
	    return apply_filters( 'dokan_get_privacy_policy_url', $userstore ."pp" );
	}

	/**
	 * Returns the privacy policy template
	 */
	public function store_pp_template( $template ) {
	    if (!function_exists('WC')) {
	        return $template;
	    }

	    if ( get_query_var( 'store_pp' ) ) {
	        return dokan_locate_template( 'store-pp.php', '', DOKAN_PRIVACY_POLICY_DIR. '/templates/' );
	    }

	    return $template;
	}

	/**
	 * Register the query var
	 */
	public function register_query_var( $vars ) {
	    $vars[] = 'store_pp';

	    return $vars;
	}

}

// Dokan_Store_Support
Dokan_Privacy_Policy::init();
