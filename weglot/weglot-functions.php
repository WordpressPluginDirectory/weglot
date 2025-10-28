<?php

use Weglot\Client\Api\LanguageCollection;
use Weglot\Client\Api\LanguageEntry;
use WeglotWP\Services\Button_Service_Weglot;
use WeglotWP\Services\Language_Service_Weglot;
use WeglotWP\Services\Option_Service_Weglot;
use WeglotWP\Services\Request_Url_Service_Weglot;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a service Weglot.
 *
 * @since 2.0
 * @throws Exception
 * @template T of object
 * @param class-string<T> $service The class name of the service to retrieve.
 * @return T The instance of the requested service.
 */

function weglot_get_service( $service ) {
	/** @var T $instance */
	$instance = Context_Weglot::weglot_get_context()->get_service( $service );
	return $instance;

}

/**
 * Get all options
 * @return array<int|string, mixed>
 * @throws Exception
 * @since 2.0
 *
 */
function weglot_get_options() {
	/** @var \WeglotWP\Services\Option_Service_Weglot $option_service */
	$option_service = Context_Weglot::weglot_get_context()->get_service( Option_Service_Weglot::class );
	return $option_service->get_options();

}

/**
 * Get option
 * @param string $key
 * @return mixed
 * @throws Exception
 * @since 2.0
 */
function weglot_get_option( $key ) {
	/** @var \WeglotWP\Services\Option_Service_Weglot $option_service */
	$option_service = Context_Weglot::weglot_get_context()->get_service( Option_Service_Weglot::class );
	return $option_service->get_option( $key );
}

/**
 * Get original language
 * @return string
 * @throws Exception
 * @since 2.0
 */
function weglot_get_original_language() {
	return weglot_get_option( 'original_language' );
}

/**
 * Get current language
 * @return string
 * @throws Exception
 * @since 2.0
 */
function weglot_get_current_language() {
	/** @var \WeglotWP\Services\Request_Url_Service_Weglot $request_url_service */
	$request_url_service = Context_Weglot::weglot_get_context()->get_service( Request_Url_Service_Weglot::class );
	return $request_url_service->get_current_language()->getInternalCode();
}

/**
 * Get current language
 * @return string
 * @throws Exception
 * @since 2.0
 */
function weglot_get_current_language_custom() {
	/** @var \WeglotWP\Services\Request_Url_Service_Weglot $request_url_service */
	$request_url_service = Context_Weglot::weglot_get_context()->get_service( Request_Url_Service_Weglot::class );
	return $request_url_service->get_current_language()->getExternalCode();
}

/**
 * Get current language code from custom language
 * @return string
 * @throws Exception
 * @since 2.0
 */
function weglot_get_current_language_code_from_custom_language() {
	/** @var \WeglotWP\Services\Request_Url_Service_Weglot $request_url_service */
	$request_url_service = Context_Weglot::weglot_get_context()->get_service( Request_Url_Service_Weglot::class );
	return $request_url_service->get_current_language()->getExternalCode();
}


/**
 * Get destination languages available for translation
 *
 * This method retrieves the destination languages available for translation from Weglot service.
 *
 * @return string[] An array of destination languages
 * @throws Exception If unable to retrieve destination languages
 */
function weglot_get_destination_languages() {
	/** @var \WeglotWP\Services\Option_Service_Weglot $option_service */
	$option_service = Context_Weglot::weglot_get_context()->get_service( Option_Service_Weglot::class );
	return $option_service->get_destination_languages();
}

/**
 * Get Request Url Service
 * @return Request_Url_Service_Weglot
 * @throws Exception
 * @since 2.0
 */
function weglot_get_request_url_service() {
	/** @var \WeglotWP\Services\Request_Url_Service_Weglot $request_url_service */
	$request_url_service = Context_Weglot::weglot_get_context()->get_service( Request_Url_Service_Weglot::class );
	return $request_url_service;
}

/**
 * Get languages available on Weglot
 * @return LanguageCollection
 * @throws Exception
 * @since 2.0
 */
function weglot_get_languages_available() {
	/** @var \WeglotWP\Services\Language_Service_Weglot $language_service */
	$language_service = Context_Weglot::weglot_get_context()->get_service( Language_Service_Weglot::class );
	return $language_service->get_languages_available();
}

/**
 * Get button selector HTML
 *
 * @param string $add_class
 *
 * @return string
 * @throws Exception
 * @since 2.0
 */
function weglot_get_button_selector_html( $add_class = '' ) {
	/** @var \WeglotWP\Services\Button_Service_Weglot $button_service */
	$button_service = Context_Weglot::weglot_get_context()->get_service( Button_Service_Weglot::class );
	return $button_service->get_html( $add_class );
}


/**
 * Get exclude urls
 * @return array<int, mixed>
 * @throws Exception
 * @since 2.0
 */
function weglot_get_exclude_urls() {
	/** @var \WeglotWP\Services\Option_Service_Weglot $option_service */
	$option_service = Context_Weglot::weglot_get_context()->get_service( Option_Service_Weglot::class );
	return $option_service->get_exclude_urls();
}

/**
 * Get translate AMP option
 * @return string|null
 * @throws Exception
 * @since 2.0
 */
function weglot_get_translate_amp_translation() {
	/** @var \WeglotWP\Services\Option_Service_Weglot $option_service */
	$option_service = Context_Weglot::weglot_get_context()->get_service( Option_Service_Weglot::class );
	return $option_service->get_option_custom_settings( 'translate_amp' );
}

/**
 * Get current full url
 * @since 2.0
 * @return bool|string
 */
function weglot_get_current_full_url() {
	return weglot_create_url_object( weglot_get_request_url_service()->get_full_url() )->getForLanguage( weglot_get_request_url_service()->get_current_language() );
}

/**
 * Is eligible url
 *
 * @param string $url
 *
 * @return boolean
 * @throws Exception
 * @since 2.0
 */
function weglot_is_eligible_url( $url ) {
	/** @var \WeglotWP\Services\Request_Url_Service_Weglot $request_url_service */
	$request_url_service = Context_Weglot::weglot_get_context()->get_service( Request_Url_Service_Weglot::class );
	return $request_url_service->is_eligible_url( $url );
}

/**
 * Get API KEY Weglot
 * @return string
 * @throws Exception
 * @since 2.0
 * @version 3.0.0
 */
function weglot_get_api_key() {
	return weglot_get_option( 'api_key_private' );
}

/**
 * Get auto redirect option
 * @return boolean
 * @throws Exception
 * @since 2.0
 */
function weglot_has_auto_redirect() {
	return weglot_get_option( 'auto_redirect' );
}

/**
 * @since 2.0.4
 * @param string $url
 * @return Weglot\Util\Url
 */
function weglot_create_url_object( $url ) {
	return weglot_get_request_url_service()->create_url_object( $url );
}

/**
 * @return bool|string
 * @throws Exception
 * @since 2.0.4
 *
 */
function weglot_get_full_url_no_language() {
	return weglot_create_url_object( weglot_get_request_url_service()->get_full_url() )->getForLanguage( weglot_get_service(Language_Service_Weglot::class)->get_original_language() );
}

/**
 * @return int
 * @throws Exception
 * @since 2.0.4
 *
 */
function weglot_get_postid_from_url() {
	$url = weglot_get_full_url_no_language();

	if( ! is_string($url)){
		return 0;
	}
	return url_to_postid( $url ); //phpcs:ignore
}
/**
 * @since 2.4.0
 * @return string
 */
function weglot_get_rest_current_url_path() {
	$current_url = wp_parse_url( add_query_arg( array() ) );
	$path        = '';
	if ( is_array( $current_url ) && isset( $current_url['path'] ) ) {
		$path = $current_url['path'];
	}

	return apply_filters( 'weglot_get_rest_current_url_path', $path );
}
