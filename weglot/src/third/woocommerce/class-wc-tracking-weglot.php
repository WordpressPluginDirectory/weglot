<?php

namespace WeglotWP\Third\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Is_Admin;
use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Third\Woocommerce\Wc_Active;


/**
 * Wc_Tracking
 *
 * @since 3.1.4
 */
class Wc_Tracking_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @since 3.1.4
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * @since 3.1.4
	 * @see Hooks_Interface_Weglot
	 * @return void
	 */
	public function hooks() {
		if ( ! Helper_Is_Admin::is_wp_admin() ) {
			return;
		}

		if ( ! WEGLOT_WOOCOMMERCE) {
			return;
		}

		add_filter( 'weglot_tabs_admin_options_available', array( $this, 'weglot_wc_tracking' ) );
	}


	/**
	 * @param array<string,string>$options_available
	 * @return array<string,string>
	 * @since 3.1.4
	 */
	public function weglot_wc_tracking( $options_available ) {

		if ( isset( $options_available['api_key_private']['description'] ) ) {

			$register_link         = 'https://dashboard.weglot.com/register-wordpress';
			$register_link_tracked = 'https://dashboard.weglot.com/register?fp_ref=woocommerce';

			$options_available['api_key_private']['description'] = \str_replace( $register_link, $register_link_tracked, $options_available['api_key_private']['description'] );
		}

		return $options_available;
	}

}
