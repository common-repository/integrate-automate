<?php
/**
 * Define the internationalization functionality.
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Integrate_Automate
 * @subpackage Integrate_Automate/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Integrate_Automate_i18n {
	/**
	 * Load the plugin text domain for translation.
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'integrate-automate',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}
