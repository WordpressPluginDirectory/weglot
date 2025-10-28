<?php

namespace WeglotWP\Helpers;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 3.0.0
 */
abstract class Helper_API {

	const API_BASE                   = 'https://api.weglot.com';
	const API_BASE_STAGING           = 'https://api.weglot.dev';
	// New API endpoint for the sandbox environment
	const API_BASE_US           = 'https://api.weglot.us';

	const ROOT_CDN_BASE              = 'https://cdn.weglot.com';
	const ROOT_CDN_BASE_STAGING      = 'https://cdn.weglot.dev';
	const API_CDN_BASE               = 'https://cdn-api-weglot.com';
	const API_CDN_BASE_STAGING       = 'https://cdn-api-weglot.dev';
	const CDN_BASE                   = 'https://cdn.weglot.com/projects-settings/';
	const CDN_BASE_STAGING                   = 'https://cdn.weglot.dev/projects-settings/';
	const CDN_BASE_US                   = 'https://cdn.weglot.us/projects-settings/';
	const CDN_BASE_SWITCHERS_TPL     = 'https://cdn.weglot.com/switchers/';
	const CDN_BASE_SWITCHERS_TPL_STAGING = 'https://cdn.weglot.dev/switchers/';

	/**
	 * Get the current environment.
	 *
	 * This method uses the 'weglot_environment' filter, so you can override the environment.
	 *
	 * By default, it checks for a defined constant WEGLOT_ENV.
	 * If that's not set, it falls back to 'staging' if WEGLOT_DEV is true, otherwise 'production'.
	 *
	 * @return string 'production', 'staging', or any custom value (e.g., 'sandbox').
	 */
	public static function get_environment() {
		// If WEGLOT_ENV is defined, use that value.
		if ( defined( 'WEGLOT_ENV' ) ) {
			return apply_filters( 'weglot_environment', WEGLOT_ENV );
		}

		if ( defined( 'WEGLOT_DEV' ) && WEGLOT_DEV ) {
			return apply_filters( 'weglot_environment', 'staging' );
		}

		// Default to 'production'.
		return apply_filters( 'weglot_environment', 'production' );
	}


	/**
	 * Get the CDN URL based on the current environment.
	 *
	 * @return string
	 */
	public static function get_cdn_url() {
		$env = self::get_environment();

		if ( 'env_us' === $env ) {
			return self::CDN_BASE_US;
		}

		if ( 'staging' === $env ) {
			return self::CDN_BASE_STAGING;
		}

		return self::CDN_BASE;
	}


	/**
	 * Get the API URL based on the current environment.
	 *
	 * @return string
	 */
	public static function get_api_url() {
		$env = self::get_environment();

		if ( 'env_us' === $env ) {
			return self::API_BASE_US;
		}

		if ( 'staging' === $env ) {
			return self::API_BASE_STAGING;
		}

		return self::API_BASE;
	}

	/**
	 * Get the switchers template URL based on the current environment.
	 *
	 * @return string
	 */
	public static function get_tpl_switchers_url() {
		$env = self::get_environment();

		if ( 'staging' === $env ) {
			return self::CDN_BASE_SWITCHERS_TPL_STAGING;
		}

		return self::CDN_BASE_SWITCHERS_TPL;
	}

	/**
	 * Wrapper around wp_remote_get() which can be moved into VIP-safe context.
	 *
	 * @param string             $url  The URL to retrieve.
	 * @param array<string,mixed> $args Optional WP HTTP args.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function vip_safe_wp_remote_get( string $url, array $args = [] ) {
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
		return wp_remote_get( $url, $args );
	}

}
