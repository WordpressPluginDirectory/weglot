<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Client\Api\LanguageEntry;
use Weglot\Util\Url;
use Weglot\Util\Server;
use WeglotWP\Third\Amp\Amp_Service_Weglot;


/**
 * Request URL
 *
 * @since 2.0
 */
class Request_Url_Service_Weglot {
	/**
	 * @since 2.0
	 *
	 * @var Url
	 */
	protected $weglot_url = null;

	/**
	 * @var Language_Service_Weglot
	 */
	private $language_services;

	/**
	 * @var Option_Service_Weglot
	 */
	private $option_services;


	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services   = weglot_get_service( Option_Service_Weglot::class );
		$this->language_services = weglot_get_service( Language_Service_Weglot::class );
	}

	/**
	 * Use for abstract \Weglot\Util\Url
	 *
	 * @param string $url
	 *
	 * @return Url
	 */
	public function create_url_object( $url ) {
		$default_path = '/';
		$use_custom_path = apply_filters('use_custom_path_for_path_check', false);

		if ($use_custom_path) {
			// Apply the filter to allow modification of the path to check
			$path_to_check = apply_filters('custom_path_to_check', $default_path);

			// Parse the URL path
			$parsed_url_path = wp_parse_url($url, PHP_URL_PATH);

			// Check if the URL path is valid and contains the specified path
			if(!is_string($parsed_url_path)){
				$contains_path = false;
			}else{
				$contains_path = $parsed_url_path !== null && strpos($parsed_url_path, $path_to_check) !== false;
			}

			if ($contains_path) {
				$home_directory = $this->get_home_wordpress_directory($path_to_check);
			} else {
				$home_directory = $this->get_home_wordpress_directory();
			}
		} else {
			// Default behavior if the filter is not set or returns false
			$home_directory = $this->get_home_wordpress_directory();
		}


		return new Url(
			$url,
			$this->language_services->get_original_language(),
			$this->language_services->get_destination_languages($this->is_allowed_private()),
			$home_directory,
			$this->option_services->get_exclude_urls(),
			$this->option_services->get_option('custom_urls')
		);
	}


	/**
	 * @return Request_Url_Service_Weglot
	 * @since 2.0
	 *
	 */
	public function init_weglot_url() {
		$this->weglot_url = $this->create_url_object( $this->get_full_url() );

		return $this;
	}

	/**
	 * Get request URL in process
	 * @return Url
	 * @since 2.0
	 */
	public function get_weglot_url() {
		if ( null === $this->weglot_url ) {
			$this->init_weglot_url();
		}

		return apply_filters( 'weglot_url_object', $this->weglot_url );
	}

	/**
	 * @return boolean
	 * @since 2.4.1
	 */
	public function is_rest() {
		$prefix = rest_get_url_prefix();
		if (
			defined( 'REST_REQUEST' ) && REST_REQUEST || isset( $_GET['rest_route'] ) && // phpcs:ignore
			                                             strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 ) === 0 ) { // phpcs:ignore
			return true;
		}
		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );

		$rest_path = isset($rest_url['path']) && is_string($rest_url['path']) ? $rest_url['path'] : '';
		$current_path = isset($current_url['path']) && is_string($current_url['path']) ? $current_url['path'] : '';

		return strpos($current_path, $rest_path, 0) === 0;
	}

	/**
	 * Abstraction of \Weglot\Util\Url
	 * @return LanguageEntry
	 * @version 3.2.0
	 * @since 2.0
	 */
	public function get_current_language() {
		$current_language = $this->get_weglot_url()->getCurrentLanguage();

		if ( ( wp_doing_ajax() || $this->is_rest() ) && isset( $_SERVER['HTTP_REFERER'] ) ) { //phpcs:ignore
			$current_language = $this->create_url_object( $_SERVER['HTTP_REFERER'] )->getCurrentLanguage(); //phpcs:ignore
		} else {
			if ( strpos( $this->get_full_url(), 'wp-comments-post.php' ) !== false ) {
				$current_language = $this->create_url_object( $this->get_full_url() )->getCurrentLanguage(); //phpcs:ignore
			}
		}

		if ( !$current_language ) {
			return apply_filters( 'weglot_default_current_language_empty', $this->language_services->get_original_language() );
		}

		return $current_language;
	}


	/**
	 * @param mixed $use_forwarded_host
	 *
	 * @return string
	 * @since 2.0
	 *
	 */
	public function get_full_url( $use_forwarded_host = false ) {
		return Server::fullUrl( $_SERVER, $use_forwarded_host ); //phpcs:ignore
	}

	/**
	 * @param string|null $allow_custom_path
	 * @return string|null
	 * @since 2.0
	 *
	 */
	public function get_home_wordpress_directory($allow_custom_path = '') {
		$opt_siteurl = trim( get_option( 'siteurl' ), '/' );
		$opt_home    = trim( get_option( 'home' ), '/' );
		if ( empty( $opt_siteurl ) || empty( $opt_home ) ) {
			return null;
		}

		if (
			(substr($opt_home, 0, 7) === 'http://' && strpos(substr($opt_home, 7), '/') !== false) ||
			(substr($opt_home, 0, 8) === 'https://' && strpos(substr($opt_home, 8), '/') !== false)
		) {
			$parsed_url = parse_url($opt_home); // phpcs:ignore
			$path = $parsed_url['path'] ?? '/';

			$use_custom_path = apply_filters('use_custom_path_for_path_check', false);

			if ($use_custom_path) {
				if (empty($allow_custom_path)) {
					return '';
				}
			}

			return $path;
		}


		return null;
	}


	/**
	 * Returns true if the URL is translated in at least one language
	 *
	 * @param string $url
	 * @param bool $even_excluded
	 *
	 * @return boolean
	 * @since 2.0
	 */
	public function is_eligible_url( $url = null, $even_excluded = false ) {

		if ( ! $url ) {
			$weglot_url = $this->get_weglot_url();
		} else {
			$weglot_url = $this->create_url_object( $url );
		}

		if ( empty( $weglot_url->availableInLanguages( $even_excluded ) ) ) {
			return apply_filters( 'weglot_is_eligible_url', false, $weglot_url );
		} elseif ( ! $weglot_url->isTranslableInLanguage( $this->get_weglot_url()->getCurrentLanguage(), $even_excluded ) ) {
			return apply_filters( 'weglot_is_eligible_url', false, $weglot_url );
		} else {
			return apply_filters( 'weglot_is_eligible_url', true, $weglot_url );
		}
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 * @since 2.0
	 */

	public function url_to_relative( $url ) {
		if ( ( substr( $url, 0, 7 ) === 'http://' ) || ( substr( $url, 0, 8 ) === 'https://' ) ) {
			// the current link is an "absolute" URL - parse it to get just the path.
			$parsed   = wp_parse_url( $url );
			$path     = isset( $parsed['path'] ) ? $parsed['path'] : '';
			$query    = isset( $parsed['query'] ) ? '?' . $parsed['query'] : '';
			$fragment = isset( $parsed['fragment'] ) ? '#' . $parsed['fragment'] : '';

			if ( $this->get_home_wordpress_directory() ) {
				$relative = str_replace( $this->get_home_wordpress_directory(), '', $path );

				return ( empty( $relative ) ) ? '/' : $relative;
			}

			return $path . $query . $fragment;
		}

		return $url;
	}

	/**
	 *
	 * @return bool
	 * @since 2.0
	 */
	public function is_allowed_private() {

		if ( current_user_can( 'administrator' )
		     || strpos( $this->get_full_url(), 'weglot-private=1' ) !== false
		     || isset( $_COOKIE['weglot_allow_private'] )
		     || ( isset( $_SERVER['HTTP_WEGLOT_PRIVATE'] ) && '1' === $_SERVER['HTTP_WEGLOT_PRIVATE'] )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the canonical URL for the current page.
	 *
	 * @return string The canonical URL.
	 */
	public function get_current_canonical_url() {
		// If this is a singular post or page.
		if ( is_singular() ) {
			$url = get_permalink( get_queried_object_id() );
			if ( $url ) {
				return $url;
			}
		}

		// If this is a post type archive.
		if ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			// If multiple post types are set, take the first.
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			$url = get_post_type_archive_link( $post_type );
			if ( $url ) {
				return $url;
			}
		}

		// Additional checks for archive types, if needed.
		if ( is_category() ) {
			$cat_id = get_query_var( 'cat' );
			$url    = get_category_link( $cat_id );
			if ( $url ) {
				return $url;
			}
		}

		if ( is_tag() ) {
			$tag_id = get_query_var( 'tag_id' );
			$url    = get_tag_link( $tag_id );
			if ( $url ) {
				return $url;
			}
		}

		if ( is_author() ) {
			$author_id = get_query_var( 'author' );
			$url       = get_author_posts_url( $author_id );
			if ( $url ) {
				return $url;
			}
		}

		// For search results, you might want to include the search query.
		if ( is_search() ) {
			return add_query_arg( 's', get_search_query(), home_url( '/' ) );
		}

		// For date archives (year, month, day), a canonical URL might be less straightforward.
		// You could build something here if needed. For now, we fall back to home_url.

		// If nothing else matches, fall back to the home URL.
		return home_url( '/' );
	}

	/**
	 * Cleans up a URL by replacing redundant slashes with a single slash,
	 * while preserving the double slashes after the protocol (e.g. http:// or https://).
	 *
	 * This function uses a regular expression with a negative lookbehind to ensure that
	 * only the unintended multiple slashes in the path are replaced.
	 *
	 * @param string $url The URL to be cleaned.
	 * @return string The cleaned URL.
	 */
	public function clean_url_slashes( $url ) {
		return preg_replace( '#(?<!:)/{2,}#', '/', $url );
	}
}


