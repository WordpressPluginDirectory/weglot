<?php

namespace WeglotWP\Actions\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Services\Option_Service_Weglot;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class Cache_Purge_Rest_Weglot implements Hooks_Interface_Weglot {
	/**
	 * @var Option_Service_Weglot
	 */
	private $option_services;

	/**
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->option_services = weglot_get_service( Option_Service_Weglot::class );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 * @return void
	 */
	public function hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the REST routes
	 * @return void
	 */
	public function register_routes() {
		register_rest_route( 'weglot/v1', '/cache/purge', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'weglot_cache_purge' ),
			'permission_callback' => array( $this, 'weglot_permission_check' ),
		) );
	}

	/**
	 * The callback to purge the cache.
	 *
	 * @param WP_REST_Request<array<string, mixed>>$request
	 * @return WP_REST_Response
	 */
	public function weglot_cache_purge( $request ) {

		$idempotency_key = $request->get_header( 'x_weglot_idempotency_key' );
		if ( $idempotency_key && false !== get_transient( 'weglot_idem_' . sanitize_key( $idempotency_key ) ) ) {
			return new WP_REST_Response( array(
				'code'    => 'success',
				'message' => 'Duplicate ignored.',
			), 200 );
		}

		$signature_header = $request->get_header( 'x_weglot_signature' );
		if ( $signature_header ) {
			$signature_hash = md5( $signature_header );
			if ( false !== get_transient( 'weglot_sig_' . $signature_hash ) ) {
				return new WP_REST_Response( array(
					'code'    => 'success',
					'message' => 'Cache already purged.',
				), 200 );
			}
		}

		delete_transient( 'weglot_cache_cdn' );
		delete_transient( 'weglot_slugs_cache' );

		$signature_header = $request->get_header( 'x_weglot_signature' );
		if ( $signature_header ) {
			$signature_hash = md5( $signature_header );
			set_transient( 'weglot_sig_' . $signature_hash, true, 10 * MINUTE_IN_SECONDS );
		}

		if ( $idempotency_key ) {
			set_transient( 'weglot_idem_' . sanitize_key( $idempotency_key ), true, 10 * MINUTE_IN_SECONDS );
		}

		return new WP_REST_Response( array(
			'code'    => 'success',
			'message' => 'Weglot cache purged.',
		), 200 );
	}

	/**
	 * The permission check for the route.
	 *
	 * @param WP_REST_Request<array<string, mixed>>$request
	 * @return boolean|WP_Error|WP_REST_Response
	 */
	public function weglot_permission_check( WP_REST_Request $request ) {
		$api_key = $this->option_services->get_api_key_private();

		if ( ! $api_key ) {
			return new WP_Error( 'weglot_rest_invalid', 'Bad Request.', array( 'status' => 400 ) );
		}

		$signature_header = $request->get_header( 'x_weglot_signature' );
		if ( ! $signature_header ) {
			return new WP_Error( 'weglot_rest_invalid', 'Bad Request.', array( 'status' => 400 ) );
		}

		$parts = explode( '=', $signature_header, 2 );
		if ( count( $parts ) !== 2 ) {
			return new WP_Error( 'weglot_rest_invalid', 'Bad Request.', array( 'status' => 400 ) );
		}
		list( $algo, $provided_signature ) = $parts;

		if ( 'sha256' !== $algo ) {
			return new WP_Error( 'weglot_rest_invalid', 'Bad Request.', array( 'status' => 400 ) );
		}
		$request_timestamp = $request->get_header( 'x_weglot_timestamp' );
		$body = $request->get_body();

		$body_data = json_decode( $body, true );

		if (null === $body_data && json_last_error() !== JSON_ERROR_NONE) {
			return new WP_Error('weglot_rest_invalid', 'Invalid JSON payload.', array('status' => 400));
		}

		if ( isset( $body_data['at'] ) ) {
			if ( (int) $body_data['at'] !== (int) $request_timestamp ) {
				return new WP_Error( 'weglot_rest_invalid', 'Bad Request.', array( 'status' => 400 ) );
			}
		}

		$expected_signature = base64_encode( hash_hmac( 'sha256', $body, $api_key, true ) );

		if ( ! preg_match( '/^[A-Za-z0-9+\/=]+$/', $provided_signature ) ) {
			return new WP_Error( 'weglot_rest_invalid', 'Bad Request.', array( 'status' => 400 ) );
		}

		if ( ! hash_equals( $expected_signature, $provided_signature ) ) {
			return new WP_Error( 'weglot_rest_invalid', 'Bad Request.', array( 'status' => 400 ) );
		}

		return true;
	}
}
