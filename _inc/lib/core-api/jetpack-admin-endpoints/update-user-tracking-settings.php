<?php

class Jetpack_Admin_REST_API_V2_Endpoint_Update_User_Tracking_Settings {
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'jetpack/v6', '/tracking/settings', array(
			array(
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'update_user_tracking_settings' ),
				'permission_callback' => 'Jetpack_Core_Json_Api_Endpoints::view_admin_page_permission_check',
				'args' => array(
					'tracks_opt_out' => array( 'type' => 'boolean' ),
				),
			),
		) );
	}

	public function update_user_tracking_settings( $request ) {
		return Jetpack_Core_Json_Api_Endpoints::update_user_tracking_settings( $request );
	}
}

wpcom_rest_api_v2_load_plugin( 'Jetpack_Admin_REST_API_V2_Endpoint_Update_User_Tracking_Settings' );
