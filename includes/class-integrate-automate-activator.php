<?php
/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Integrate_Automate
 * @subpackage Integrate_Automate/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Integrate_Automate_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		# setting the Plugin version on Activation 
		update_option( 'INTEGRATE_AUTOMATE_VERSION', INTEGRATE_AUTOMATE_VERSION );
		# setting the Timestamp on Activation 
		update_option( 'INTEGRATE_AUTOMATE_INSTALL', time() );
		# 
	}
}
