<?php

namespace WeglotWP\Actions\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Flag_Type;
use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Services\Option_Service_Weglot;

/**
 * Enqueue CSS / JS on front
 *
 * @since 2.0
 */
class Front_Enqueue_Weglot implements Hooks_Interface_Weglot {
	/**
	 * @var Option_Service_Weglot
	 */
	private $option_services;

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services = weglot_get_service( Option_Service_Weglot::class );
	}

	/**
	 * @return void
	 * @since 2.0
	 * @see Hooks_Interface_Weglot
	 *
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'weglot_wp_enqueue_scripts' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'weglot_wp_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'weglot_pageviews_script' ) );
		add_filter( 'style_loader_tag', array( $this, 'add_custom_inline_style_tag' ), 10, 2 );
	}

	/**
	 *
	 * @return void
	 * @since 2.0
	 */
	public function weglot_pageviews_script() {
		$options = $this->option_services->get_options();
		if ( $options['page_views_enabled'] ) { ?>
			<script>
				(function(){let request = new XMLHttpRequest();
					let url = 'ht' + 'tps:' + '//' + 'api.weglot.com/' + 'pageviews?api_key=' + '<?= esc_js( $options['api_key'] ); ?>';
					let data = JSON.stringify({
							url: location.protocol + '//' + location.host + location.pathname,
							language: document.getElementsByTagName('html')[0].getAttribute('lang'),
							browser_language: (navigator.language || navigator.userLanguage)
						}
					);
					request.open('POST', url, true);
					request.send(data);
				})();
			</script>
		<?php }
	}

	/**
	 * @return void
	 * @since 2.0
	 *
	 * @see wp_enqueue_scripts
	 */
	public function weglot_wp_enqueue_scripts() {
		// Add JS.
		wp_register_script( 'wp-weglot-js', WEGLOT_URL_DIST . '/front-js.js', array(), WEGLOT_VERSION, false );
		wp_enqueue_script( 'wp-weglot-js' );

		// Add CSS
		wp_register_style( 'weglot-css', WEGLOT_URL_DIST . '/css/front-css.css', array(), WEGLOT_VERSION, 'all' );
		wp_enqueue_style( 'weglot-css' );

		//display new flags
		Helper_Flag_Type::get_new_flags();
		wp_add_inline_style( 'weglot-css', $this->option_services->get_flag_css() );
		wp_add_inline_style( 'weglot-css', $this->option_services->get_switcher_editor_css() );
	}

	/**
	 * Adds custom inline CSS style tag to the provided HTML for a specific handle.
	 *
	 * @param string $html Existing HTML to which the custom inline style will be appended.
	 * @param string $handle Identifier for the stylesheet, checks if it matches 'weglot-css'.
	 *
	 * @return string Updated HTML string with the custom inline style tag appended if applicable.
	 */
	public function add_custom_inline_style_tag( $html, $handle ) {
		if ( 'weglot-css' === $handle ) {
			$custom_css = $this->option_services->get_css_custom_inline();
			if ( ! empty( $custom_css ) ) {
				$custom_style_tag = sprintf( '<style id="weglot-custom-style">%s</style>', $custom_css );
				$html .= $custom_style_tag;
			}
		}
		return $html;
	}
}
