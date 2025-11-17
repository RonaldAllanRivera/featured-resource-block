<?php
/**
 * Sync service for the Featured Resource Block plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Sync_Service {
	/**
	 * Transient key for cached API data.
	 */
	const CACHE_KEY = 'frb_resource_sync_cache';

	/**
	 * Last sync time option.
	 */
	const OPTION_LAST_SYNC_TIME = 'frb_last_sync_time';

	/**
	 * Last sync error option.
	 */
	const OPTION_LAST_SYNC_ERROR = 'frb_last_sync_error';

	/**
	 * Mock API endpoint.
	 */
	const API_ENDPOINT = 'https://mocki.io/v1/0c7b33d3-2996-4d7f-a009-4ef34a27c7e9';

	/**
	 * Entry point for cron.
	 */
	public static function run() {
		if ( ! class_exists( 'FRB_Settings_Page' ) ) {
			return;
		}

		$settings = FRB_Settings_Page::get_options();

		if ( empty( $settings['enable_sync'] ) ) {
			FRB_Logger::debug( 'Sync skipped because enable_sync is disabled.' );
			return;
		}

		$api_key      = isset( $settings['api_key'] ) ? trim( $settings['api_key'] ) : '';
		$api_endpoint = isset( $settings['api_endpoint'] ) ? trim( $settings['api_endpoint'] ) : self::API_ENDPOINT;

		if ( '' === $api_endpoint ) {
			$api_endpoint = self::API_ENDPOINT;
		}

		if ( '' === $api_key ) {
			self::record_error( 'Sync skipped because API key is missing.' );
			return;
		}

		try {
			$data = get_transient( self::CACHE_KEY );

			if ( false === $data ) {
				$data = self::fetch_remote_data( $api_key, $api_endpoint );

				if ( null === $data ) {
					// Error already recorded in fetch_remote_data.
					return;
				}

				set_transient( self::CACHE_KEY, $data, 5 * MINUTE_IN_SECONDS );
			}

			self::process_items( $data );
			self::record_success();
		} catch ( Exception $e ) {
			self::record_error( sprintf( 'Unexpected sync exception: %s', $e->getMessage() ) );
		}
	}

	/**
	 * Fetch remote data from the API.
	 *
	 * @param string $api_key      API key.
	 * @param string $api_endpoint API endpoint URL.
	 *
	 * @return array|null
	 */
	protected static function fetch_remote_data( $api_key, $api_endpoint ) {
		$endpoint = $api_endpoint;

		if ( '' === $endpoint ) {
			$endpoint = self::API_ENDPOINT;
		}

		$url = add_query_arg( 'api_key', rawurlencode( $api_key ), $endpoint );

		$args = array(
			'timeout' => 15,
			'headers' => array(
				'Accept' => 'application/json',
			),
		);

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			self::record_error( sprintf( 'HTTP request failed: %s', $response->get_error_message() ) );
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== (int) $code ) {
			self::record_error( sprintf( 'Unexpected HTTP status code: %d', $code ) );
			return null;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( '' === $body ) {
			self::record_error( 'Empty response body from API.' );
			return null;
		}

		$data = json_decode( $body, true );

		if ( null === $data || ! is_array( $data ) ) {
			self::record_error( 'Failed to decode JSON from API.' );
			return null;
		}

		FRB_Logger::debug( 'Fetched data from API.', array( 'item_count' => count( $data ) ) );

		return $data;
	}

	/**
	 * Process remote items into local posts.
	 *
	 * @param array $data Decoded data.
	 */
	protected static function process_items( array $data ) {
		if ( isset( $data['resources'] ) && is_array( $data['resources'] ) ) {
			$items = $data['resources'];
		} else {
			$items = $data;
		}

		if ( empty( $items ) ) {
			FRB_Logger::info( 'No items to process from API.' );
			return;
		}

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			self::upsert_resource_from_item( $item );
		}
	}

	/**
	 * Create or update a Resource post from a remote item.
	 *
	 * @param array $item Remote item.
	 */
	protected static function upsert_resource_from_item( array $item ) {
		$remote_id = self::extract_first_non_empty( $item, array( 'id', 'remote_id', 'uuid' ) );
		$title     = self::extract_first_non_empty( $item, array( 'title', 'name' ) );
		$excerpt   = self::extract_first_non_empty( $item, array( 'excerpt', 'summary', 'description' ) );
		$url       = self::extract_first_non_empty( $item, array( 'url', 'link', 'resource_url' ) );

		if ( empty( $remote_id ) || empty( $title ) ) {
			FRB_Logger::debug( 'Skipping item due to missing required fields.', array( 'item' => $item ) );
			return;
		}

		$post_id = self::find_post_id_by_remote_id( $remote_id );

		$post_data = array(
			'post_title'   => wp_strip_all_tags( $title ),
			'post_excerpt' => is_string( $excerpt ) ? $excerpt : '',
			'post_type'    => FRB_Post_Type_Resources::POST_TYPE,
			'post_status'  => 'publish',
		);

		if ( $post_id ) {
			$post_data['ID'] = $post_id;
			$result         = wp_update_post( $post_data, true );
		} else {
			$result  = wp_insert_post( $post_data, true );
			$post_id = is_wp_error( $result ) ? 0 : (int) $result;
		}

		if ( is_wp_error( $result ) || ! $post_id ) {
			FRB_Logger::error( 'Failed to insert/update Resource post.', array( 'remote_id' => $remote_id ) );
			return;
		}

		update_post_meta( $post_id, 'mist_remote_id', $remote_id );

		if ( ! empty( $url ) ) {
			update_post_meta( $post_id, 'mist_resource_url', esc_url_raw( $url ) );
		}
	}

	/**
	 * Find a Resource post by its mist_remote_id.
	 *
	 * @param string $remote_id Remote ID.
	 *
	 * @return int Post ID or 0.
	 */
	protected static function find_post_id_by_remote_id( $remote_id ) {
		$query = new WP_Query(
			array(
				'post_type'      => FRB_Post_Type_Resources::POST_TYPE,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => 'mist_remote_id',
						'value' => $remote_id,
					),
				),
			)
		);

		if ( empty( $query->posts ) ) {
			return 0;
		}

		return (int) $query->posts[0];
	}

	/**
	 * Extract the first non-empty value from an array using a list of keys.
	 *
	 * @param array $item Item.
	 * @param array $keys Keys.
	 *
	 * @return string
	 */
	protected static function extract_first_non_empty( array $item, array $keys ) {
		foreach ( $keys as $key ) {
			if ( isset( $item[ $key ] ) && '' !== $item[ $key ] && null !== $item[ $key ] ) {
				return (string) $item[ $key ];
			}
		}

		return '';
	}

	/**
	 * Record a successful sync.
	 */
	protected static function record_success() {
		update_option( self::OPTION_LAST_SYNC_TIME, current_time( 'mysql' ) );
		update_option( self::OPTION_LAST_SYNC_ERROR, '' );
	}

	/**
	 * Record an error and log it.
	 *
	 * @param string $message Error message.
	 */
	protected static function record_error( $message ) {
		FRB_Logger::error( $message );
		update_option( self::OPTION_LAST_SYNC_TIME, current_time( 'mysql' ) );
		update_option( self::OPTION_LAST_SYNC_ERROR, $message );
	}
}
