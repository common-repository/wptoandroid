<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class WTA_REST_Options_Aux_WTA_Controller extends WTA_REST_Aux_WTA_Controller {

	public function __construct() {
		$this->namespace = 'wp-android';
		$this->rest_base = 'options';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function wta_register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'args'            => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/token', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_item' ),
			'schema' => array( $this, 'get_public_item_schema' ),
		));
	}

	/**
	 * Get one options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_item( $request ) {
		$data = array();
		
		$value = get_option('wp_to_android_token', '');
		$data[ 'token' ] = $value;
		
		return rest_ensure_response( $data );
	}
	
	/**
	 * Get all options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_items( $request ) {
		$data = array();
		$options = array('galleries_menu','users_menu','categories_menu','pages_menu','posts_menu', 'ad_dfp_middle_id', 'ad_dfp_header_id', 'ad_dfp_footer_id', 'app_admob', 'ad_footer_id', 'ad_middle_id', 'ad_header_id', 'app_ads', 'siteurl', 'keystore', 'store_password', 'key_alias','key_password', 'app_email', 'app_name', 'app_host', 'app_anspress','app_woocommerce','app_bbpress', 'app_back_color', 'app_pri_color', 'app_pri_dark_color', 'app_accent_color', 'app_font_color', 'app_icon_48', 'app_splash_48', 'app_navigation_48');

		foreach ( $options as $option ) {
			$value = get_option(WTA_TO_ANDROID_TEXT.$option, '');
			$data[ WTA_TO_ANDROID_TEXT.$option ] = $value;
		}
		return rest_ensure_response( $data );
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context'      => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

}
