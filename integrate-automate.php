<?php

/**
 * @since             1.0.0
 * @package           Integrate_Automate
 * @wordpress-plugin
 * Plugin Name:       integrate automate
 * Plugin URI:        https://wordpress.org/plugins/integrate-automate
 * Description:       Integrate Automate - WordPress, WooCommerce, Contact form 7 for Zapier, IFTTT, Automate.io other API glue Platforms.
 * Version:           1.0.1
 * Author:            javmah
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       integrate_automate
 * Domain Path:       /languages
*/
# If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
# Hello from Here !

if ( function_exists( 'ia_fs' ) ) {
    ia_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'ia_fs' ) ) {
        # Freemius integration snippet ||  Create a helper function for easy SDK access.
        function ia_fs()
        {
            global  $ia_fs ;
            
            if ( !isset( $ia_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/includes/freemius/start.php';
                $ia_fs = fs_dynamic_init( array(
                    'id'             => '7959',
                    'slug'           => 'integrate-automate',
                    'premium_slug'   => 'integrate-automate-professional',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_d2902a780189ac1cead2949b58d62',
                    'is_premium'     => false,
                    'premium_suffix' => 'Professional',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                    'menu'           => array(
                    'slug'       => 'integrate-automate',
                    'first-path' => 'admin.php?page=integrate-automate',
                    'support'    => false,
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $ia_fs;
        }
        
        # Init Freemius.
        ia_fs();
        # Signal that SDK was initiated.
        do_action( 'ia_fs_loaded' );
    }
    
    # Your plugin's main file logic
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'INTEGRATE_AUTOMATE_VERSION', '1.0.1' );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-integrate-automate-activator.php
     */
    function activate_integrate_automate()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-integrate-automate-activator.php';
        Integrate_Automate_Activator::activate();
    }
    
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-integrate-automate-deactivator.php
     */
    function deactivate_integrate_automate()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-integrate-automate-deactivator.php';
        Integrate_Automate_Deactivator::deactivate();
    }
    
    register_activation_hook( __FILE__, 'activate_integrate_automate' );
    register_deactivation_hook( __FILE__, 'deactivate_integrate_automate' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-integrate-automate.php';
    /**
     * Begins execution of the plugin.
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     * @since    1.0.0
     */
    function run_integrate_automate()
    {
        $plugin = new Integrate_Automate();
        $plugin->run();
    }
    
    run_integrate_automate();
}
