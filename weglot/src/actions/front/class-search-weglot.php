<?php

namespace WeglotWP\Actions\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Is_Admin;
use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Services\Language_Service_Weglot;
use WeglotWP\Services\Option_Service_Weglot;
use WeglotWP\Services\Request_Url_Service_Weglot;
use WeglotWP\Services\Translate_Service_Weglot;
use WP_Query;

/**
 * @since 2.4.0
 */
class Search_Weglot implements Hooks_Interface_Weglot {
	/**
	 * @var string
	 */
	protected $old_search = null;
	/**
	 * @var string
	 */
	protected $new_search = null;
	/**
	 * @var Option_Service_Weglot
	 */
	private $option_services;
	/**
	 * @var Request_Url_Service_Weglot
	 */
	private $request_url_services;
	/**
	 * @var Language_Service_Weglot
	 */
	private $language_services;
	/**
	 * @var Translate_Service_Weglot
	 */
	private $translate_services;


	/**
	 * @since 2.4.0
	 */
	public function __construct() {
		$this->option_services      = weglot_get_service( Option_Service_Weglot::class );
		$this->request_url_services = weglot_get_service( Request_Url_Service_Weglot::class );
		$this->language_services    = weglot_get_service( Language_Service_Weglot::class );
		$this->translate_services   = weglot_get_service(Translate_Service_Weglot::class);
	}

	/**
	 * @return void
	 * @since 2.4.0
	 * @see Hooks_Interface_Weglot
	 *
	 */
	public function hooks() {

		if ( Helper_Is_Admin::is_wp_admin() ) {
			return;
		}

		$search_active = $this->option_services->get_option( 'active_search' );

		if ( $search_active ) {
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts_translate' ), 9 );
			add_filter( 'get_search_query', array( $this, 'get_search_query_translate' ) );
		}
	}

	/**
	 * @param WP_Query $query
	 *
	 * @return void
	 * @since 2.4.0
	 */
	public function pre_get_posts_translate( $query ) {

		if ( ! $query->is_search() || ! $query->is_main_query() ) {
			return;
		}

		$query_vars_check = apply_filters( 'weglot_query_vars_check', 's' );
		if ( empty( $query->query_vars[ $query_vars_check ] ) ) {
			return;
		}

		$original_language = $this->language_services->get_original_language()->getInternalCode();
		$current_language  = $this->request_url_services->get_current_language()->getInternalCode();

		$prevent_translation = apply_filters( 'weglot_prevent_search_translation', false, $current_language, $query );
		if ( $original_language === $current_language || $prevent_translation ) {
			return;
		}

		try {
			$api_key_private = $this->option_services->get_api_key_private();
			$this->new_search = $this->translate_services->reverseTranslate($api_key_private, $current_language, $original_language, home_url('/'), $query->query_vars[ $query_vars_check ], 1);

			if ( empty( $this->new_search ) ) {
				return;
			}

			$query->set( $query_vars_check, $this->new_search ); //phpcs:ignore
			if ( isset( $query->query[ $query_vars_check ] ) ) {
				$query->query[ $query_vars_check ] = $this->new_search;
			}
		} catch ( \Exception $th ) {
			return;
		}
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 * @since 2.4.0
	 */
	public function get_search_query_translate( $string ) {
		return ( $this->old_search ) ? $this->old_search : $string;
	}

}
