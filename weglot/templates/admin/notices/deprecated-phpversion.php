<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="error settings-error notice is-dismissible">
	<p>
		<?php
		/* translators:
		 * %1$s: Plugin name.
		 * %2$s: Minimum PHP version (displayed with <code> tags).
		 * %3$s: Recommended PHP version (displayed with <code> tags).
		 * %4$s: Opening <a href> tag for the link to the official PHP website.
		 * %5$s: Closing </a> tag.
		 */
		$main_text = __( '%1$s has detected that your server is running a PHP version below %2$s. As of version 5.0.0, we no longer support PHP versions under %2$s. For optimal security and performance, please upgrade to PHP %3$s or higher. If you need assistance with upgrading, please contact your hosting provider or visit the %4$sofficial PHP website%5$s.', 'weglot' );

		/* translators: This is a URL. Only change it if your language has a localized version of the PHP website. */
		$php_url = __( 'https://www.php.net/', 'weglot' );

		echo wp_kses_post(
			sprintf(
				$main_text,
				'<strong>Weglot Translate</strong>',
				'<code>7.4</code>',
				'<code>8.3</code>',
				'<a href="' . esc_url( $php_url ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			)
		);

		?>
	</p>
</div>
