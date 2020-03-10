<?php

/**
 * Plugin Name: Speleo.se Grottan Shortcode
 * Description: Ansluter till Grottan index ifrån Google Drive och gör det tillgänligt via short codes
 *
 *
 * Läs: https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
 *
 *
 * Hur man kopplar ihop Google Drive:
 * https://www.youtube.com/watch?v=iTZyuszEkxI
 *
 * https://wordpress.stackexchange.com/questions/162240/custom-pages-with-plugin
 *
 * https://www.twilio.com/blog/create-google-sheets-database-php-app-sms-notifications
 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class SpeleoSeGrottanShortcode {
	const SHORTCODE_GROTTAN = 'grottan';
	private $numbers = [];

	public function __construct() {
		require 'testdata/data.php';

		// When the plugin has loaded initialize.
		add_action( 'plugins_loaded', [$this, 'initialize' ] );
	}

	/**
	 * Handles the initialization of the plugin. (Seems to be called on every page request)
	 */
	public function initialize() {
		// https://developer.wordpress.org/plugins/shortcodes/basic-shortcodes/
		// https://developer.wordpress.org/plugins/shortcodes/shortcodes-with-parameters/#complete-example
		add_shortcode(self::SHORTCODE_GROTTAN, [$this, 'shortcodeGrottan']);
	}

	function shortcodeGrottan( $atts = [], $content = null) {
		// normalize attribute keys, lowercase
		$atts = array_change_key_case((array)$atts, CASE_LOWER);

		// override default attributes with user attributes
		$wporg_atts = shortcode_atts([
					'title' => 'WordPress.org',
					]
					, $atts, self::SHORTCODE_GROTTAN);

		// start output
		$o = 'Här kommer alla Grottan på en och samma gång.<br><pre>'.print_r($this->numbers, true).'</pre>';
		// do something to $content
		// always return
		return $o;
	}
}

new SpeleoSeGrottanShortcode();

// plugin activation
//register_activation_hook( __FILE__, [ new CiviCRMSpeleoSeConfig(), 'configureCiviCRM' ] );
