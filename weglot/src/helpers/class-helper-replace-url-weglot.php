<?php

namespace WeglotWP\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @since 2.3.0
 */
class Helper_Replace_Url_Weglot {

	/**
	 * @since 2.3.0
	 * @return array<string,string>
	 */
	public static function get_replace_modify_link() {
		$data = array(
			'a'         => '/<a(?![^>]*wg-excluded-link)([^\>]+?)?href=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/',
			'datalink'  => '/<([^\>]+?)?(?!wg-excluded-link)data-link=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/',
			'dataurl'   => '/<([^\>]+?)?(?!wg-excluded-link)data-url=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/',
			'datacart'  => '/<([^\>]+?)?(?!wg-excluded-link)data-cart-url=(\"|\')([^\s\>]+?)(\"|\')([^\>]+?)?>/',
			'form'      => '/<form(?![^>]*wg-excluded-link)([^\>]+?)?action=(\"|\')([^\s\>]+?)(\"|\')/',
			'canonical' => '/<link(?![^>]*wg-excluded-link) rel="canonical"(.*?)?href=(\"|\')([^\s\>]+?)(\"|\')/',
			'amp'       => '/<link(?![^>]*wg-excluded-link) rel="amphtml"(.*?)?href=(\"|\')([^\s\>]+?)(\"|\')/',
			'meta'      => '/<meta(?![^>]*wg-excluded-link) property="og:url"(.*?)?content=(\"|\')([^\s\>]+?)(\"|\')/',
			'next'      => '/<link(?![^>]*wg-excluded-link) rel="next"(.*?)?href=(\"|\')([^\s\>]+?)(\"|\')/',
			'prev'      => '/<link(?![^>]*wg-excluded-link) rel="prev"(.*?)?href=(\"|\')([^\s\>]+?)(\"|\')/',
		);

		return apply_filters( 'weglot_get_replace_modify_link', $data );
	}

	/**
	 * @since 2.3.0
	 * @return array<string,string>
	 */
	public static function get_replace_modify_link_in_xml() {
		$data = array(
			'loc' => '/<loc>(.*?)<\/loc>/'
		);

		return apply_filters( 'get_replace_modify_link_in_xml', $data );
	}
}
