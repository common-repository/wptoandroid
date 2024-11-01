<?php

/**
 * Plugin Name: WP to Android
 * Description: Plugin that allows you to generate an APK native for your entire Wordpress.
 * Author: Martin De Gregorio
 * Author URI: http://www.infuy.com
 * Version: 2.0-beta12
 * Plugin URI:
 * License: GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

// I will make the control of the tokens
define("WTA_TO_ANDROID_TEXT", "wta_wp_to_android_");

/**
 * WP_REST_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-controller.php';
}

/**
 * WP_REST_Options_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Options_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-options-controller.php';
}

/**
 * WP_REST_Posts_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Posts_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-posts-controller.php';
}

/**
 * WP_REST_WooCommerce_Controller class.
 */
if ( ! class_exists( 'WTA_REST_WooCommerce_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-woocommerce-controller.php';
}

/**
 * WP_REST_Contact_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Contact7_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-contact7-controller.php';
}

/**
 * WP_REST_Attachments_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Attachments_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-attachments-controller.php';
}

/**
 * WP_REST_Post_Types_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Post_Types_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-post-types-controller.php';
}

/**
 * WP_REST_Post_Statuses_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Post_Statuses_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-post-statuses-controller.php';
}

/**
 * WP_REST_Revisions_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Revisions_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-revisions-controller.php';
}

/**
 * WP_REST_Taxonomies_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Taxonomies_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-taxonomies-controller.php';
}

/**
 * WP_REST_Terms_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Terms_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-terms-controller.php';
}

/**
 * WP_REST_Users_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Users_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-users-controller.php';
}

/**
 * WP_REST_Comments_Controller class.
 */
if ( ! class_exists( 'WTA_REST_Comments_Aux_WTA_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-wta-wp-rest-comments-controller.php';
}

add_filter( 'init', 'wta_add_extra_api_post_type_arguments', 11 );
add_action( 'init', 'wta_add_extra_api_taxonomy_arguments', 11 );
add_action( 'rest_api_init', 'wta_create_initial_rest_aux_routes', 0 );

/**
 * Adds extra post type registration arguments.
 *
 * These attributes will eventually be committed to core.
 *
 * @since 4.4.0
 *
 * @global array $wp_taxonomies Registered taxonomies.
 */
function wta_add_extra_api_post_type_arguments() {
	global $wp_post_types;

	if ( isset( $wp_post_types['post'] ) ) {
		$wp_post_types['post']->show_in_rest = true;
		$wp_post_types['post']->rest_base = 'posts';
		$wp_post_types['post']->rest_controller_class = 'WTA_REST_Posts_Aux_WTA_Controller';
	}

	if ( isset( $wp_post_types['page'] ) ) {
		$wp_post_types['page']->show_in_rest = true;
		$wp_post_types['page']->rest_base = 'pages';
		$wp_post_types['page']->rest_controller_class = 'WTA_REST_Posts_Aux_WTA_Controller';
	}

	if ( isset( $wp_post_types['attachment'] ) ) {
		$wp_post_types['attachment']->show_in_rest = true;
		$wp_post_types['attachment']->rest_base = 'media';
		$wp_post_types['attachment']->rest_controller_class = 'WTA_REST_Attachments_Aux_WTA_Controller';
	}
}

/**
 * Adds extra taxonomy registration arguments.
 *
 * These attributes will eventually be committed to core.
 *
 * @since 4.4.0
 *
 * @global array $wp_taxonomies Registered taxonomies.
 */
function wta_add_extra_api_taxonomy_arguments() {
	global $wp_taxonomies;

	if ( isset( $wp_taxonomies['category'] ) ) {
		$wp_taxonomies['category']->show_in_rest = true;
		$wp_taxonomies['category']->rest_base = 'categories';
		$wp_taxonomies['category']->rest_controller_class = 'WTA_REST_Terms_Aux_WTA_Controller';
	}

	if ( isset( $wp_taxonomies['post_tag'] ) ) {
		$wp_taxonomies['post_tag']->show_in_rest = true;
		$wp_taxonomies['post_tag']->rest_base = 'tags';
		$wp_taxonomies['post_tag']->rest_controller_class = 'WTA_REST_Terms_Aux_WTA_Controller';
	}
}

if ( ! function_exists( 'wta_create_initial_rest_aux_routes' ) ) {
	/**
	 * Registers default REST API routes.
	 *
	 * @since 4.4.0
	 */
	function wta_create_initial_rest_aux_routes() {

		foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
			$class = ! empty( $post_type->rest_controller_class ) ? $post_type->rest_controller_class : 'WTA_REST_Posts_Aux_WTA_Controller';

			if ( ! class_exists( $class ) ) {
				continue;
			}
			$controller = new $class( $post_type->name );
			if ( ! is_subclass_of( $controller, 'WTA_REST_Aux_WTA_Controller' ) ) {
				continue;
			}

			$controller->wta_register_routes();

			if ( post_type_supports( $post_type->name, 'revisions' ) ) {
				$revisions_controller = new WTA_REST_Revisions_Aux_WTA_Controller( $post_type->name );
				$revisions_controller->wta_register_routes();
			}
		}

		// Post types.
		$controller = new WTA_REST_Post_Types_Aux_WTA_Controller;
		$controller->wta_register_routes();

		// Post statuses.
		$controller = new WTA_REST_Post_Statuses_Aux_WTA_Controller;
		$controller->wta_register_routes();

		// Taxonomies.
		$controller = new WTA_REST_Taxonomies_Aux_WTA_Controller;
		$controller->wta_register_routes();

		// Terms.
		foreach ( get_taxonomies( array( 'show_in_rest' => true ), 'object' ) as $taxonomy ) {
			$class = ! empty( $taxonomy->rest_controller_class ) ? $taxonomy->rest_controller_class : 'WTA_REST_Terms_Aux_WTA_Controller';

			if ( ! class_exists( $class ) ) {
				continue;
			}
			$controller = new $class( $taxonomy->name );
			if ( ! is_subclass_of( $controller, 'WTA_REST_Aux_WTA_Controller' ) ) {
				continue;
			}

			$controller->wta_register_routes();
		}

		// Users.
		$controller = new WTA_REST_Users_Aux_WTA_Controller;
		$controller->wta_register_routes();

		// Comments.
		$controller = new WTA_REST_Comments_Aux_WTA_Controller;
		$controller->wta_register_routes();

		// Options.
		$controller = new WTA_REST_Options_Aux_WTA_Controller;
		$controller->wta_register_routes();

		// WooCommerce.
		$controller = new WTA_REST_WooCommerce_Aux_WTA_Controller;
		$controller->wta_register_routes();

		// Contact7.
		$controller = new WTA_REST_Contact7_Aux_WTA_Controller;
		$controller->wta_register_routes();
	}
}

/**
 * Returns a contextual HTTP error code for authorization failure.
 *
 * @return integer
 */
function wta_rest_authorization_required_code() {
	return is_user_logged_in() ? 403 : 401;
}

/**
 * Registers a new field on an existing WordPress object type.
 *
 * @global array $wp_rest_additional_fields Holds registered fields, organized
 *                                          by object type.
 *
 * @param string|array $object_type Object(s) the field is being registered
 *                                  to, "post"|"term"|"comment" etc.
 * @param string $attribute         The attribute name.
 * @param array  $args {
 *     Optional. An array of arguments used to handle the registered field.
 *
 *     @type string|array|null $get_callback    Optional. The callback function used to retrieve the field
 *                                              value. Default is 'null', the field will not be returned in
 *                                              the response.
 *     @type string|array|null $update_callback Optional. The callback function used to set and update the
 *                                              field value. Default is 'null', the value cannot be set or
 *                                              updated.
 *     @type string|array|null $schema          Optional. The callback function used to create the schema for
 *                                              this field. Default is 'null', no schema entry will be returned.
 * }
 */
function wta_register_rest_field( $object_type, $attribute, $args = array() ) {
	$defaults = array(
		'get_callback'    => null,
		'update_callback' => null,
		'schema'          => null,
	);

	$args = wp_parse_args( $args, $defaults );

	global $wp_rest_additional_fields;

	$object_types = (array) $object_type;

	foreach ( $object_types as $object_type ) {
		$wp_rest_additional_fields[ $object_type ][ $attribute ] = $args;
	}
}

/**
 * Backwards compat shim
 */
function wta_register_api_field( $object_type, $attributes, $args = array() ) {
	_deprecated_function( 'register_api_field', 'WPAPI-2.0', 'wta_register_rest_field' );
	wta_register_rest_field( $object_type, $attributes, $args );
}

/**
 * Validate a request argument based on details registered to the route.
 *
 * @param  mixed            $value
 * @param  WP_REST_Request  $request
 * @param  string           $param
 * @return WP_Error|boolean
 */
function wta_rest_validate_request_arg( $value, $request, $param ) {

	$attributes = $request->get_attributes();
	if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
		return true;
	}
	$args = $attributes['args'][ $param ];

	if ( ! empty( $args['enum'] ) ) {
		if ( ! in_array( $value, $args['enum'] ) ) {
			return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not one of %s' ), $param, implode( ', ', $args['enum'] ) ) );
		}
	}

	if ( 'integer' === $args['type'] && ! is_numeric( $value ) ) {
		return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not of type %s' ), $param, 'integer' ) );
	}

	if ( 'string' === $args['type'] && ! is_string( $value ) ) {
		return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not of type %s' ), $param, 'string' ) );
	}

	if ( isset( $args['format'] ) ) {
		switch ( $args['format'] ) {
			case 'date-time' :
				if ( ! rest_parse_date( $value ) ) {
					return new WP_Error( 'rest_invalid_date', __( 'The date you provided is invalid.' ) );
				}
				break;

			case 'email' :
				if ( ! is_email( $value ) ) {
					return new WP_Error( 'rest_invalid_email', __( 'The email address you provided is invalid.' ) );
				}
				break;
		}
	}

	if ( in_array( $args['type'], array( 'numeric', 'integer' ) ) && ( isset( $args['minimum'] ) || isset( $args['maximum'] ) ) ) {
		if ( isset( $args['minimum'] ) && ! isset( $args['maximum'] ) ) {
			if ( ! empty( $args['exclusiveMinimum'] ) && $value <= $args['minimum'] ) {
				return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be greater than %d (exclusive)' ), $param, $args['minimum'] ) );
			} else if ( empty( $args['exclusiveMinimum'] ) && $value < $args['minimum'] ) {
				return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be greater than %d (inclusive)' ), $param, $args['minimum'] ) );
			}
		} else if ( isset( $args['maximum'] ) && ! isset( $args['minimum'] ) ) {
			if ( ! empty( $args['exclusiveMaximum'] ) && $value >= $args['maximum'] ) {
				return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be less than %d (exclusive)' ), $param, $args['maximum'] ) );
			} else if ( empty( $args['exclusiveMaximum'] ) && $value > $args['maximum'] ) {
				return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be less than %d (inclusive)' ), $param, $args['maximum'] ) );
			}
		} else if ( isset( $args['maximum'] ) && isset( $args['minimum'] ) ) {
			if ( ! empty( $args['exclusiveMinimum'] ) && ! empty( $args['exclusiveMaximum'] ) ) {
				if ( $value >= $args['maximum'] || $value <= $args['minimum'] ) {
					return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (exclusive) and %d (exclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
				}
			} else if ( empty( $args['exclusiveMinimum'] ) && ! empty( $args['exclusiveMaximum'] ) ) {
				if ( $value >= $args['maximum'] || $value < $args['minimum'] ) {
					return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (inclusive) and %d (exclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
				}
			} else if ( ! empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
				if ( $value > $args['maximum'] || $value <= $args['minimum'] ) {
					return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (exclusive) and %d (inclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
				}
			} else if ( empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
				if ( $value > $args['maximum'] || $value < $args['minimum'] ) {
					return new WP_Error( 'rest_invalid_param', sprintf( __( '%s must be between %d (inclusive) and %d (inclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
				}
			}
		}
	}

	return true;
}

/**
 * Sanitize a request argument based on details registered to the route.
 *
 * @param  mixed            $value
 * @param  WP_REST_Request  $request
 * @param  string           $param
 * @return mixed
 */
function wta_rest_sanitize_request_arg( $value, $request, $param ) {

	$attributes = $request->get_attributes();
	if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
		return $value;
	}
	$args = $attributes['args'][ $param ];

	if ( 'integer' === $args['type'] ) {
		return (int) $value;
	}

	if ( isset( $args['format'] ) ) {
		switch ( $args['format'] ) {
			case 'date-time' :
				return sanitize_text_field( $value );

			case 'email' :
				/*
				 * sanitize_email() validates, which would be unexpected
				 */
				return sanitize_text_field( $value );

			case 'uri' :
				return esc_url_raw( $value );
		}
	}

	return $value;
}

	function wta_wp_to_android_plugin_menu() {

	    add_menu_page(
    		'Wp to Android Settings',
			'WP to Android Settings',
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_settings',
			WTA_TO_ANDROID_TEXT.'plugin_main_aboutus_page');

	    add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">settings</i> Main'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_main_settings',
			WTA_TO_ANDROID_TEXT.'plugin_main_settings_page'
	    );

	    add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">tag_faces</i> Icons'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_icon_settings',
			WTA_TO_ANDROID_TEXT.'plugin_icon_settings_page'
	    );

	    add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">wallpaper</i> Splash'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_splash_settings',
			WTA_TO_ANDROID_TEXT.'plugin_splash_settings_page'
	    );

	    add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">bubble_chart</i> Navigation Header'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_headernav_settings',
			WTA_TO_ANDROID_TEXT.'plugin_headernav_settings_page'
	    );

	   	add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">build</i> Menues'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_menues_settings',
			WTA_TO_ANDROID_TEXT.'plugin_menues_settings_page'
	    );

	    add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">android</i> Generate APK'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_generate_settings',
			WTA_TO_ANDROID_TEXT.'plugin_generate_settings_page'
	    );

     	add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">info</i> About us'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_aboutus_settings',
			WTA_TO_ANDROID_TEXT.'plugin_aboutus_settings_page'
	    );

	    add_submenu_page(
	    	WTA_TO_ANDROID_TEXT.'plugin_settings',
			__('WP to Android Settings'),
			__('<i class="material-icons">email</i> <span style="color: #ff5a00;">Tell a friend</span>'),
			'activate_plugins',
			WTA_TO_ANDROID_TEXT.'plugin_tellfriend_settings',
			WTA_TO_ANDROID_TEXT.'plugin_tellfriend_settings_page'
	    );
	}

	function wta_wp_to_android_plugin_generate_settings_page() {
		$optionUrl = get_option(WTA_TO_ANDROID_TEXT.'app_host');
		include 'wta-wp-to-android-plugin-admin-generate-apk.php';
	}

	function wta_wp_to_android_plugin_menues_settings_page() {
		include 'wta-wp-to-android-plugin-admin-menues-settings.php';
	}

	function wta_wp_to_android_plugin_aboutus_settings_page() {
		include 'wta-wp-to-android-plugin-admin-aboutus-settings.php';
	}

	function wta_wp_to_android_plugin_tellfriend_settings_page() {
		include 'wta-wp-to-android-plugin-admin-tellfriend-settings.php';
	}

	function wta_wp_to_android_plugin_headernav_settings_page() {
		include 'wta-wp-to-android-plugin-admin-headernav-settings.php';
	}

	function wta_wp_to_android_plugin_splash_settings_page() {
		include 'wta-wp-to-android-plugin-admin-splash-settings.php';
	}

	function wta_wp_to_android_plugin_icon_settings_page() {
		include 'wta-wp-to-android-plugin-admin-icon-settings.php';
	}

	function wta_wp_to_android_plugin_main_aboutus_page() {
		include 'wta-wp-to-android-plugin-admin-aboutus-settings.php';
	}

	function wta_wp_to_android_plugin_main_settings_page() {
		include 'wta-wp-to-android-plugin-admin-main-settings.php';
	}

	function wta_display_appname_element() {
		?>
	    	<input type="text" class="form-control" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_name" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_name" maxlength="20" value="<?php echo get_option(WTA_TO_ANDROID_TEXT.'app_name'); ?>" />
	    <?php
	}

	function wta_display_appemail_element() {
		?>
	    	<input type="text" class="form-control" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_email" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_email" size="40" maxlength="100" value="<?php echo get_option(WTA_TO_ANDROID_TEXT.'app_email'); ?>" />
	    <?php
	}

	function wta_display_apphost_element()	{
		?>
	    	<input type="text" class="form-control" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_host" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_host" value="<?php echo get_option(WTA_TO_ANDROID_TEXT.'app_host'); ?>" size="40" />
	    <?php
	}

	function wta_display_app_anspress_element()	{
		?>

	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_anspress" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_anspress" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'app_anspress' ) ); ?> />
	    <?php
	}

	function wta_display_app_bbpress_element()	{
		?>
	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_bbpress" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_bbpress" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'app_bbpress' ) ); ?> />
	    <?php
	}

	function wta_display_app_woocommerce_element()	{
		?>
	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_woocommerce" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_woocommerce" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'app_woocommerce' ) ); ?> />
	    <?php
	}

	function wta_display_app_back_color_element()	{
		if (get_option(WTA_TO_ANDROID_TEXT.'app_back_color') == '') $wp_to_android_app_back_color = "#ECECEC";
		else $wp_to_android_app_back_color = get_option(WTA_TO_ANDROID_TEXT.'app_back_color');
		?>
	    	<input type="text" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_back_color" class="app_main_color" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_back_color" value="<?php echo $wp_to_android_app_back_color; ?>" />
	    <?php
	}
	function wta_display_app_pri_color_element()	{
		if (get_option(WTA_TO_ANDROID_TEXT.'app_pri_color') == '') $wp_to_android_app_pri_color = "#3F51B5";
		else $wp_to_android_app_pri_color = get_option(WTA_TO_ANDROID_TEXT.'app_pri_color');
		?>
	    	<input type="text" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_pri_color" class="app_main_color" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_pri_color" value="<?php echo $wp_to_android_app_pri_color; ?>" />
	    <?php
	}
	function wta_display_app_pri_dark_color_element()	{
		if (get_option(WTA_TO_ANDROID_TEXT.'app_pri_dark_color') == '') $wp_to_android_app_pri_dark_color = "#303F9F";
		else $wp_to_android_app_pri_dark_color = get_option(WTA_TO_ANDROID_TEXT.'app_pri_dark_color');
		?>
	    	<input type="text" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_pri_dark_color" class="app_main_color" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_pri_dark_color" value="<?php echo $wp_to_android_app_pri_dark_color; ?>" />
	    <?php
	}
	function wta_display_app_accent_color_element()	{
		if (get_option(WTA_TO_ANDROID_TEXT.'app_accent_color') == '') $wp_to_android_app_accent_color = "#3F51B5";
		else $wp_to_android_app_accent_color = get_option(WTA_TO_ANDROID_TEXT.'app_accent_color');
		?>
	    	<input type="text" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_accent_color" class="app_main_color" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_accent_color" value="<?php echo $wp_to_android_app_accent_color; ?>" />
	    <?php
	}
	function wta_display_app_font_color_element()	{
		if (get_option(WTA_TO_ANDROID_TEXT.'app_font_color') == '') $wp_to_android_app_font_color = "#FFFFFF";
		else $wp_to_android_app_font_color = get_option(WTA_TO_ANDROID_TEXT.'app_font_color');
		?>
	    	<input type="text" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_font_color" class="app_main_color" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_font_color" value="<?php echo $wp_to_android_app_font_color; ?>" />
	    <?php
	}

	function wta_display_icon_48_element()	{
		?>
	    	<input type="file" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_icon_48" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_icon_48"/>
	    	<img src="<?php echo get_option(WTA_TO_ANDROID_TEXT.'app_icon_48');?>"/>
	    <?php
	}

	function wta_display_splash_48_element()	{
		?>
	    	<input type="file" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_splash_48" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_splash_48" />
	    	<img src="<?php echo get_option(WTA_TO_ANDROID_TEXT.'app_splash_48');?>"/>
	    <?php
	}

	function wta_display_navigation_48_element()	{
		?>
	    	<input type="file" name="<?php echo WTA_TO_ANDROID_TEXT;?>app_navigation_48" id="<?php echo WTA_TO_ANDROID_TEXT;?>app_navigation_48" />
	    	<img src="<?php echo get_option(WTA_TO_ANDROID_TEXT.'app_navigation_48');?>"/>
	    <?php
	}

	function handle_upload_icon_48() {

		if ($_FILES[WTA_TO_ANDROID_TEXT.'app_icon_48']["type"] == "image/png") {
			if($_FILES[WTA_TO_ANDROID_TEXT."app_icon_48"]["tmp_name"] != '') {
				$urls = wp_handle_upload($_FILES[WTA_TO_ANDROID_TEXT."app_icon_48"], array('test_form' => FALSE));
				if (isset($urls["error"])) {
					return "";
				}
			    $temp = $urls["url"];
			    return $temp;
			}
		}
	}

	function handle_upload_splash_48() {

		if ($_FILES[WTA_TO_ANDROID_TEXT.'app_splash_48']["type"] == "image/png") {
			if(!empty($_FILES[WTA_TO_ANDROID_TEXT."app_splash_48"]["tmp_name"])) {
				$urls = wp_handle_upload($_FILES[WTA_TO_ANDROID_TEXT."app_splash_48"], array('test_form' => FALSE));
			    if (isset($urls["error"])) {
					return "";
				}
			    $temp = $urls["url"];
			    return $temp;
			}
		}
	}

	function handle_upload_app_nav_48() {

		if ($_FILES[WTA_TO_ANDROID_TEXT.'app_navigation_48']["type"] == "image/png") {
			if(!empty($_FILES[WTA_TO_ANDROID_TEXT."app_navigation_48"]["tmp_name"])) {
				$urls = wp_handle_upload($_FILES[WTA_TO_ANDROID_TEXT."app_navigation_48"], array('test_form' => FALSE));
				if (isset($urls["error"])) {
					return "";
				}
			    $temp = $urls["url"];
			    return $temp;
			}
		}
	}

	function wta_display_posts_element()	{
		?>

	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>posts_menu" id="<?php echo WTA_TO_ANDROID_TEXT;?>posts_menu" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'posts_menu' ) ); ?> />
	    <?php
	}
	function wta_display_users_element() {
		?>

	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>users_menu" id="<?php echo WTA_TO_ANDROID_TEXT;?>users_menu" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'users_menu' ) ); ?> />
	    <?php
	}
	function wta_display_categories_element() {
		?>

	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>categories_menu" id="<?php echo WTA_TO_ANDROID_TEXT;?>categories_menu" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'categories_menu' ) ); ?> />
	    <?php
	}
	function wta_display_pages_element() {
		?>

	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>pages_menu" id="<?php echo WTA_TO_ANDROID_TEXT;?>pages_menu" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'pages_menu' ) ); ?> />
	    <?php
	}
	function wta_display_galleries_element() {
		?>

	    	<input type="checkbox" name="<?php echo WTA_TO_ANDROID_TEXT;?>galleries_menu" id="<?php echo WTA_TO_ANDROID_TEXT;?>galleries_menu" value="1" <?php checked( '1', get_option( WTA_TO_ANDROID_TEXT.'galleries_menu' ) ); ?> />
	    <?php
	}

	function wta_sanitize_option_admin_email($email) {
		return sanitize_email($email);
	}
	function wta_sanitize_option_siteurl($url) {
		return sanitize_url($url);
	}
	function wta_sanitize_option_titles($title) {
		$title = str_replace("-", "_", $title);
		return sanitize_title($title);
	}

	function wta_display_theme_panel_fields(){
		add_settings_section("section", "", null, "wp-android-options");

		add_settings_field(WTA_TO_ANDROID_TEXT."app_name", "Application Name", "wta_display_appname_element", "wp-android-options", "section");
		add_settings_field(WTA_TO_ANDROID_TEXT."app_email", "Email (for sending your apk)", "wta_display_appemail_element", "wp-android-options", "section");
	    add_settings_field(WTA_TO_ANDROID_TEXT."app_host", "Application Host <span style='color: lightgrey;font-style:italic;font-weight:normal;'>e.g(http://".$_SERVER['HTTP_HOST']."/)</span>", "wta_display_apphost_element", "wp-android-options", "section");
		add_settings_field(WTA_TO_ANDROID_TEXT."app_anspress", "Enable Anspress Plugin", "wta_display_app_anspress_element", "wp-android-options", "section");
	    add_settings_field(WTA_TO_ANDROID_TEXT."app_bbpress", "Enable BBpress Plugin", "wta_display_app_bbpress_element", "wp-android-options", "section");
	   	add_settings_field(WTA_TO_ANDROID_TEXT."app_woocommerce", "Enable WooCommerce Plugin", "wta_display_app_woocommerce_element", "wp-android-options", "section");

	   	register_setting("section", WTA_TO_ANDROID_TEXT."app_name", "wta_sanitize_option_titles");
	   	register_setting("section", WTA_TO_ANDROID_TEXT."app_email", "wta_sanitize_option_admin_email");
	    register_setting("section", WTA_TO_ANDROID_TEXT."app_host", "wta_sanitize_option_siteurl");
	    register_setting("section", WTA_TO_ANDROID_TEXT."app_anspress");
	    register_setting("section", WTA_TO_ANDROID_TEXT."app_bbpress");
	    register_setting("section", WTA_TO_ANDROID_TEXT."app_woocommerce");

	    add_settings_section("section-colors", "", null, "wp-android-colors-options");
	    add_settings_field(WTA_TO_ANDROID_TEXT."app_back_color", "Windows Background Color", "wta_display_app_back_color_element", "wp-android-colors-options", "section-colors");
	    add_settings_field(WTA_TO_ANDROID_TEXT."app_pri_color", "Primary Main Color", "wta_display_app_pri_color_element", "wp-android-colors-options", "section-colors");
	    add_settings_field(WTA_TO_ANDROID_TEXT."app_pri_dark_color", "Primary Dark Color", "wta_display_app_pri_dark_color_element", "wp-android-colors-options", "section-colors");
	    add_settings_field(WTA_TO_ANDROID_TEXT."app_accent_color", "Accent Color", "wta_display_app_accent_color_element", "wp-android-colors-options", "section-colors");
	    add_settings_field(WTA_TO_ANDROID_TEXT."app_font_color", "Font Color For Primary Background", "wta_display_app_font_color_element", "wp-android-colors-options", "section-colors");

	    register_setting("section-colors", WTA_TO_ANDROID_TEXT."app_back_color");
	    register_setting("section-colors", WTA_TO_ANDROID_TEXT."app_pri_color");
	    register_setting("section-colors", WTA_TO_ANDROID_TEXT."app_pri_dark_color");
	    register_setting("section-colors", WTA_TO_ANDROID_TEXT."app_accent_color");
	    register_setting("section-colors", WTA_TO_ANDROID_TEXT."app_font_color");

	    add_settings_section("section-icon", "Only PNG images", null, "wp-android-icon-options");
		add_settings_field(WTA_TO_ANDROID_TEXT."app_icon_48", "Application Icon (144x144)", "wta_display_icon_48_element", "wp-android-icon-options", "section-icon");
	   	register_setting("section-icon", WTA_TO_ANDROID_TEXT."app_icon_48", "handle_upload_icon_48");

	    add_settings_section("section-splash", "Only PNG images", null, "wp-android-splash-options");
		add_settings_field(WTA_TO_ANDROID_TEXT."app_splash_48", "Application Splash (1440 x 960)", "wta_display_splash_48_element", "wp-android-splash-options", "section-splash");
	   	register_setting("section-splash", WTA_TO_ANDROID_TEXT."app_splash_48", "handle_upload_splash_48");

 		add_settings_section("section-navigation", "Only PNG images", null, "wp-android-navigation-options");
		add_settings_field(WTA_TO_ANDROID_TEXT."app_navigation_48", "Application Navigation Header (960 x 540)", "wta_display_navigation_48_element", "wp-android-navigation-options", "section-navigation");
	   	register_setting("section-navigation", WTA_TO_ANDROID_TEXT."app_navigation_48", "handle_upload_app_nav_48");

	   	add_settings_section("section-menues", "", null, "wp-android-menues");
		add_settings_field(WTA_TO_ANDROID_TEXT."posts_menu", "Posts Menu", "wta_display_posts_element", "wp-android-menues", "section-menues");
		add_settings_field(WTA_TO_ANDROID_TEXT."categories_menu", "Categories Menu", "wta_display_categories_element", "wp-android-menues", "section-menues");
		add_settings_field(WTA_TO_ANDROID_TEXT."pages_menu", "Pages Menu", "wta_display_pages_element", "wp-android-menues", "section-menues");
		add_settings_field(WTA_TO_ANDROID_TEXT."users_menu", "Users Menu", "wta_display_users_element", "wp-android-menues", "section-menues");
		add_settings_field(WTA_TO_ANDROID_TEXT."galleries_menu", "Galleries Menu", "wta_display_galleries_element", "wp-android-menues", "section-menues");

	   	register_setting("section-menues", WTA_TO_ANDROID_TEXT."posts_menu");
	   	register_setting("section-menues", WTA_TO_ANDROID_TEXT."categories_menu");
	   	register_setting("section-menues", WTA_TO_ANDROID_TEXT."pages_menu");
	   	register_setting("section-menues", WTA_TO_ANDROID_TEXT."users_menu");
	   	register_setting("section-menues", WTA_TO_ANDROID_TEXT."galleries_menu");
	}

	$pluginpath = plugins_url( '/', __FILE__ );

	function wta_wp_to_android_install() {
		$plugin_data = $plugin_data = get_plugin_data( __FILE__  , false, false);
		$version = strval($plugin_data['Version']);
		update_option( WTA_TO_ANDROID_TEXT.'plugin_version',"WP_".$version  );
		update_option( WTA_TO_ANDROID_TEXT.'token', md5(uniqid(rand(), true)) );

		add_option( WTA_TO_ANDROID_TEXT."posts_menu", 1);
		add_option( WTA_TO_ANDROID_TEXT."users_menu", 1);
		add_option( WTA_TO_ANDROID_TEXT."pages_menu", 1);
		add_option( WTA_TO_ANDROID_TEXT."categories_menu", 1);
		add_option( WTA_TO_ANDROID_TEXT."galleries_menu", 1);

		add_option( WTA_TO_ANDROID_TEXT."app_splash_48", " ");
		add_option( WTA_TO_ANDROID_TEXT."app_navigation_48", " ");
		add_option( WTA_TO_ANDROID_TEXT."app_icon_48", " ");
	}

	function wta_wp_to_android_uninstall() {
		delete_option( WTA_TO_ANDROID_TEXT.'plugin_version' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_navigation_48' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_splash_48' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_email' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_icon_48' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_font_color' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_accent_color' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_pri_dark_color' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_pri_color' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_back_color' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_host' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_name' );
		delete_option( WTA_TO_ANDROID_TEXT.'token' );
		delete_option( WTA_TO_ANDROID_TEXT.'users_menu' );
		delete_option( WTA_TO_ANDROID_TEXT.'posts_menu' );
		delete_option( WTA_TO_ANDROID_TEXT.'categories_menu' );
		delete_option( WTA_TO_ANDROID_TEXT.'galleries_menu' );
		delete_option( WTA_TO_ANDROID_TEXT.'pages_menu' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_woocommerce' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_bbpress' );
		delete_option( WTA_TO_ANDROID_TEXT.'app_anspress' );
	}

	//install/uninstall function calls
	register_activation_hook( __FILE__, WTA_TO_ANDROID_TEXT.'install' );
	register_uninstall_hook( __FILE__, WTA_TO_ANDROID_TEXT.'uninstall' );

	add_action("admin_init", "wta_display_theme_panel_fields");
	add_action('admin_menu', WTA_TO_ANDROID_TEXT.'plugin_menu');

	add_action( 'admin_enqueue_scripts', 'wptuts_add_color_picker' );

	/**
	 * This example will work at least on WordPress 2.6.3, 
	 * but maybe on older versions too.
	 */
	add_action( 'admin_menu', 'wta_wp_plugin_admin_menu' );   

	function wta_wp_plugin_admin_menu() {

		 wp_deregister_script('jquery');
		 wp_enqueue_script( 'jquery' );
		 wp_enqueue_script( 'jquery-ui-core' );
		 wp_enqueue_script('jquery-ui-progressbar');

		 wp_enqueue_script( 'wp-color-picker');
		 wp_enqueue_script( 'mainpreview', plugins_url('/js/preview.js', __FILE__) , array(), '1.0.0', true );
		 wp_enqueue_script( 'generate-apk', plugins_url('/js/generate.js', __FILE__), array('jquery'), '1.0.0', true );

		 wp_enqueue_style( 'stylesheet', plugins_url('/css/stylesheet.css', __FILE__) );
		 wp_enqueue_style( 'font-awesome-custom', plugins_url('/css/fonts.css', __FILE__) ); 
		 wp_enqueue_style( 'bootstrap', plugins_url('/css/bootstrap.min.css', __FILE__) );
		 wp_enqueue_style( 'jquery-ui-custom-css', plugins_url('/css/jquery-ui.css', __FILE__) );
	}

	function wptuts_add_color_picker( $hook ) {
	    if( is_admin() ) {
	        wp_enqueue_style( 'wp-color-picker' );
	    }
	}

?>
