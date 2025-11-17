<?php
/**
 * Local mock API endpoint for testing sync.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Mock_Api {
	const REST_NAMESPACE    = 'frb/v1';
	const ROUTE_RESOURCES   = '/mock-resources';

	public static function register() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::ROUTE_RESOURCES,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_resources' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function get_resources( WP_REST_Request $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$data = array(
			array(
				'id'          => 'demo-1',
				'title'       => 'Demo Resource One',
				'excerpt'     => 'First demo resource from local mock endpoint.',
				'url'         => home_url( '/demo-resource-one' ),
			),
			array(
				'id'          => 'demo-2',
				'title'       => 'Demo Resource Two',
				'excerpt'     => 'Second demo resource from local mock endpoint.',
				'url'         => home_url( '/demo-resource-two' ),
			),
			array(
				'id'          => 'demo-3',
				'title'       => 'Demo Resource Three',
				'excerpt'     => 'Third demo resource from local mock endpoint.',
				'url'         => home_url( '/demo-resource-three' ),
			),
		);

		return rest_ensure_response( $data );
	}
}
