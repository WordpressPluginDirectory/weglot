<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Exception;
use WeglotWP\Helpers\Helper_API;
use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ServerConfigProvider;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;


/**
 * Parser abstraction
 *
 * @since 2.0
 */
class Parser_Service_Weglot {
	/**
	 * @var Option_Service_Weglot
	 */
	private $option_services;
	/**
	 * @var Regex_Checkers_Service_Weglot
	 */
	private $regex_checkers_services;
	/**
	 * @var Dom_Checkers_Service_Weglot
	 */
	private $dom_checkers_services;

	/** @var array<string,string>  token → original value */
	private $preserved = [];

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services         = weglot_get_service( Option_Service_Weglot::class );
		$this->dom_checkers_services   = weglot_get_service( Dom_Checkers_Service_Weglot::class );
		$this->regex_checkers_services = weglot_get_service( Regex_Checkers_Service_Weglot::class );
	}

	/**
	 * @return Client
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function get_client() {
		$api_key            = $this->option_services->get_api_key( true );
		$version            = $this->option_services->get_version();
		$translation_engine = $this->option_services->get_translation_engine();
		if ( empty( $translation_engine ) ) {
			$translation_engine = 3;
		}

		$client = new Client(
			$api_key,
			$translation_engine,
			$version,
			array(
				'host' => Helper_API::get_api_url(),
			)
		);
		$client->getHttpClient()->addHeader( 'weglot-integration: WordPress Plugin' );

		return $client;
	}

	/**
	 * @return Parser
	 * @throws Exception
	 * @since 2.0
	 * @version 2.2.2
	 */
	public function get_parser() {

		$exclude_blocks   = $this->option_services->get_exclude_blocks();
		$whitelist_blocks = apply_filters(
			'weglot_parser_whitelist',
			array()
		);
		$custom_switchers = $this->option_services->get_switchers_editor_button();
		$translate_inside_exclusions_blocks   = $this->option_services->get_translate_inside_exclusions_blocks();
		$config           = apply_filters( 'weglot_parser_config_provider', new ServerConfigProvider() );
		if ( ! ( $config instanceof ConfigProviderInterface ) ) {
			$config = new ServerConfigProvider();
		}

		if ( method_exists( $config, 'loadFromServer' ) ) {
			$config->loadFromServer();
		}

		$client = $this->get_client();
		$safe_custom_switchers = is_array( $custom_switchers ) ? $custom_switchers : [];
		$parser = new Parser( $client, $config, $exclude_blocks, $safe_custom_switchers, $whitelist_blocks, $translate_inside_exclusions_blocks );

		$parser->getDomCheckerProvider()->addCheckers( $this->dom_checkers_services->get_dom_checkers() );
		$parser->getRegexCheckerProvider()->addCheckers( $this->regex_checkers_services->get_regex_checkers() );
		$ignored_nodes = apply_filters( 'weglot_get_parser_ignored_nodes', $parser->getIgnoredNodesFormatter()->getIgnoredNodes() );
		$parser->getIgnoredNodesFormatter()->setIgnoredNodes( $ignored_nodes );

		$media_enabled    = $this->option_services->get_option_button( 'media_enabled' );
		$external_enabled = $this->option_services->get_option_button( 'external_enabled' );

		// remove media and/or externalLink checker if not enable.
		$remove_checker = array();
		if ( ! $external_enabled ) {
			$remove_checker[] = '\Weglot\Parser\Check\Dom\ExternalLinkHref';
		}

		if ( ! $media_enabled ) {
			$remove_checker[] = '\Weglot\Parser\Check\Dom\ImageDataSource';
			$remove_checker[] = '\Weglot\Parser\Check\Dom\ImageSource';
		}

		if ( ! empty( $remove_checker ) ) {
			$parser->getDomCheckerProvider()->removeCheckers( $remove_checker );
		}

		return $parser;
	}

	/**
	 * Escape Vue.js attributes so that simple_html_dom does not break.
	 *
	 * @param string $content The HTML content to be processed.
	 * @return string Processed content with Vue.js attributes replaced.
	 */
	public function escape_vue_attributes( $content ) {
		// Escape attributes that start with "v-" (e.g. v-for, v-bind:src, etc.)
		$content = preg_replace( '/\bv-([\w-]+)=/', 'data-vue-v-$1=', $content );

		// Escape shorthand Vue.js directives starting with ":" by ensuring we match the start of the attribute.
		// This regex looks for either the beginning of the string (^) or any whitespace (\s)
		// followed by ":" and then the attribute name.
		return preg_replace( '/(^|\s):([\w-]+)=/', '$1data-vue-bind-$2=', $content );
	}

	/**
	 * Restore the original Vue.js attributes after translation.
	 *
	 * @param string $content The HTML content with escaped Vue attributes.
	 * @return string Content with the original Vue.js attributes restored.
	 */
	public function restore_vue_attributes( $content ) {
		// Restore attributes replaced for "v-" directives.
		$content = preg_replace( '/\bdata-vue-v-([\w-]+)=/', 'v-$1=', $content );

		// Restore the shorthand directives for attributes starting with a colon.
		return preg_replace( '/(^|\s)data-vue-bind-([\w-]+)=/', '$1:$2=', $content );
	}


	/**
	 * Preserves specified attributes in the given HTML string by replacing
	 * their inner values with tokens, storing these values for later restoration.
	 *
	 * @param string $html The HTML content where attributes should be preserved.
	 *
	 * @return string The HTML content with specified attributes replaced by tokens.
	 */
	public function preserve_attributes( string $html ): string {
		$attrs = apply_filters( 'weglot_escape_attributes', [] );
		if ( empty( $attrs ) ) {
			return $html;
		}

		// build alternation like '(d|foo|bar)'
		$list = implode( '|', array_map( 'preg_quote', $attrs ) );

		return preg_replace_callback(
		// 1: attr name, 2: optional slash, 3: quote, 4: value
			'/\b(' . $list . ')=(\\\\?)([\'"])(.*?)\2\3/s',
			function( $m ) {
				static $i = 0;
				$i++;
				$attr  = $m[1];
				$token = "__WG_ATTR_{$attr}_{$i}__";

				// store the raw inner value (with any backslashes)
				$this->preserved[ $token ] = $m[4];

				// re-emit attr=slash+quote+token+slash+quote
				return sprintf(
					'%s=%s%s%s%s%s',
					$attr,
					$m[2],
					$m[3],
					$token,
					$m[2],
					$m[3]
				);
			},
			$html
		);
	}


	/**
	 * Restores preserved attributes in the provided HTML string by replacing tokens with their original values.
	 *
	 * @param string $html The HTML string where preserved attributes need to be restored.
	 *
	 * @return string The HTML string with preserved attributes restored to their original values.
	 */
	public function restore_preserved_attributes( string $html ): string {
		foreach ( $this->preserved as $token => $original ) {
			// match quote+token+same-quote, preserving any leading slash
			$html = preg_replace_callback(
				'/(\\\\?)([\'"])' . preg_quote( $token, '/' ) . '\1\2/',
				function( $m ) use ( $original ) {
					return $m[1] . $m[2] . $original . $m[1] . $m[2];
				},
				$html
			);
		}
		// clear stash if you reuse this instance
		$this->preserved = [];
		return $html;
	}

}
