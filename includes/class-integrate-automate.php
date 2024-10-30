<?php

/**
 * The core plugin class.
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Integrate_Automate
 * @subpackage Integrate_Automate/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Integrate_Automate
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     * @since    1.0.0
     * @access   protected
     * @var      Integrate_Automate_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected  $plugin_name ;
    /**
     * The current version of the plugin.
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        
        if ( defined( 'INTEGRATE_AUTOMATE_VERSION' ) ) {
            $this->version = INTEGRATE_AUTOMATE_VERSION;
        } else {
            $this->version = '1.0.1';
        }
        
        $this->plugin_name = 'integrate-automate';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Integrate_Automate_Loader. Orchestrates the hooks of the plugin.
     * - Integrate_Automate_i18n. Defines internationalization functionality.
     * - Integrate_Automate_Admin. Defines all hooks for the admin area.
     * - Integrate_Automate_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-integrate-automate-loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-integrate-automate-i18n.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-integrate-automate-admin.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-integrate-automate-event.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-integrate-automate-public.php';
        $this->loader = new Integrate_Automate_Loader();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     * Uses the Integrate_Automate_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Integrate_Automate_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality Of the plugin.
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        # Admin object
        $integrate_automate_admin = new Integrate_Automate_Admin( $this->get_plugin_name(), $this->get_version() );
        # enqueue_styles
        $this->loader->add_action( 'admin_enqueue_scripts', $integrate_automate_admin, 'enqueue_styles' );
        # enqueue_scripts
        $this->loader->add_action( 'admin_enqueue_scripts', $integrate_automate_admin, 'enqueue_scripts' );
        # Admin menu
        $this->loader->add_action( 'admin_menu', $integrate_automate_admin, 'integrate_automate_menu_page' );
        # Testing
        $this->loader->add_action( 'admin_notices', $integrate_automate_admin, 'integrate_automate_admin_notices' );
        # Saving integration
        $this->loader->add_action( 'admin_post_integrate_automate_integration', $integrate_automate_admin, 'integrate_automate_save_integration' );
        # event object
        $integrate_automate_event = new Integrate_Automate_Event( $this->get_plugin_name(), $this->get_version() );
        # Testing Area for Events
        $this->loader->add_action( 'admin_notices', $integrate_automate_event, 'integrate_automate_event_admin_notices' );
        # New User Event [user_register]
        $this->loader->add_action(
            'user_register',
            $integrate_automate_event,
            'integrate_automate_wp_newUser',
            100,
            1
        );
        # Update User Event [profile_update]
        $this->loader->add_action(
            'profile_update',
            $integrate_automate_event,
            'integrate_automate_wp_profileUpdate',
            100,
            2
        );
        # Delete User Event [delete_user]
        $this->loader->add_action(
            'delete_user',
            $integrate_automate_event,
            'integrate_automate_wp_deleteUser',
            100,
            1
        );
        # User Logged In  [wp_login]
        $this->loader->add_action(
            'wp_login',
            $integrate_automate_event,
            'integrate_automate_wp_userLogin',
            100,
            2
        );
        # User Logged Out [wp_logout]
        $this->loader->add_action(
            'clear_auth_cookie',
            $integrate_automate_event,
            'integrate_automate_wp_userLogout',
            100,
            1
        );
        # Wordpress Post  || Fires once a post has been saved. 3 param 1.post_id 2.post 3.updates
        $this->loader->add_action(
            'save_post',
            $integrate_automate_event,
            'integrate_automate_wp_post',
            100,
            3
        );
        # Wordpress comment_post  || Fires once a comment_post has been saved TO DB.
        $this->loader->add_action(
            'comment_post',
            $integrate_automate_event,
            'integrate_automate_wp_comment',
            100,
            3
        );
        # Wordpress comment_post  || Fires once a comment_post has been saved TO DB.
        $this->loader->add_action(
            'edit_comment',
            $integrate_automate_event,
            'integrate_automate_wp_edit_comment',
            100,
            2
        );
        # WooCommerce  Product save_post_product
        $this->loader->add_action(
            'transition_post_status',
            $integrate_automate_event,
            'integrate_automate_woocommerce_product',
            100,
            3
        );
        # query
        # testFire Hook || this is a testing Hook
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Integrate_Automate_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     * @since     1.0.0
     * @return    Integrate_Automate_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    /**
     * Retrieve the version number of the plugin.
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}