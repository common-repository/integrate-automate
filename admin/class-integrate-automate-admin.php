<?php

/**
 * The admin-specific functionality of the plugin.
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 * @link       javmah.com
 * @since      1.0.0
 * @package    Integrate_Automate
 * @subpackage Integrate_Automate/admin
 * @author     javmah <jaedmah@gmail.com>
 */
class Integrate_Automate_Admin
{
    /**
     * The ID of this plugin.
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private  $version ;
    /**
     * The active_plugins of this plugin.
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private  $active_plugins ;
    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->active_plugins = get_option( 'active_plugins' );
    }
    
    /**
     * Register the stylesheets for the admin area.
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/integrate-automate-admin.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * For enqueue scripts
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/integrate-automate-admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }
    
    /**
     * For Menu Pages
     * @since    1.0.0
     */
    public function integrate_automate_menu_page()
    {
        # for main plugin landing Page
        add_menu_page(
            'WP Integrate Automate',
            'WP Integrate Automate',
            'manage_options',
            'integrate-automate',
            array( $this, 'integrate_automate_request_dispatcher' ),
            'dashicons-networking',
            30
        );
        # For log page submenu Page
        add_submenu_page(
            'integrate-automate',
            'Log',
            'Log',
            'manage_options',
            'integrate-automate-log',
            array( $this, 'integrate_automate_log_page' )
        );
    }
    
    /**
     * URL routers for main landing Page 
     * @since    	1.0.0
     * @return 	   	array 		Integrations details  .
     */
    public function integrate_automate_request_dispatcher()
    {
        # getting URL param
        $action = ( isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list' );
        $id = ( isset( $_GET['id'] ) ? intval( sanitize_text_field( $_GET['id'] ) ) : 0 );
        # routing to the Pages
        switch ( $action ) {
            case 'new':
                $this->integrate_automate_new_integration();
                break;
            case 'edit':
                ( $id ? $this->integrate_automate_edit_integration( $id ) : $this->integrate_automate_new_integration() );
                break;
            case 'delete':
                $this->integrate_automate_delete_intermigration( $id );
                break;
            case 'status':
                ( $id ? $this->integrate_automate_integration_status( $id ) : $this->integrate_automate_integrations() );
                break;
            default:
                $this->integrate_automate_integrations();
                break;
        }
    }
    
    /**
     * for Testing And Debug 
     * @since    1.0.0
     */
    public function integrate_automate_admin_notices()
    {
        // echo"<pre>";
        // echo"</pre>";
    }
    
    /**
     * Landing Page of integrate automate
     * @since    1.0.s
     */
    public function integrate_automate_integrations()
    {
        # Deleting Log starts
        $integrate_automate_logs = get_posts( array(
            'post_type'      => 'integrate_automate_l',
            'posts_per_page' => -1,
        ) );
        if ( count( $integrate_automate_logs ) > 100 ) {
            foreach ( $integrate_automate_logs as $key => $log ) {
                if ( $key > 100 ) {
                    wp_delete_post( $log->ID, true );
                }
            }
        }
        # Deleting Log Ends
        # Adding List table
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-integrate-automate-list-table.php';
        # Creating view Page layout
        echo  "<div class='wrap'>" ;
        # if credentials is empty; Show this message to create credential.
        echo  "<h1 class='wp-heading-inline'> Integrations </h1>" ;
        echo  "<a href=" . admin_url( 'admin.php?page=integrate-automate&action=new' ) . " class='page-title-action'>Add new integration</a>" ;
        # Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions
        echo  "<form id='new_intermigration' method='get'>" ;
        # For plugins, we also need to ensure that the form posts back to our current page
        echo  "<input type='hidden' name='page' value='" . sanitize_text_field( $_REQUEST['page'] ) . "' />" ;
        echo  "<input type='hidden' name='integrate_automate_nonce' value='" . wp_create_nonce( 'integrate_automate_nonce_bulk_action' ) . "' />" ;
        # Now we can render the completed list table
        $integrate_automate_table = new Integrate_Automate_List_Table( "Empty" );
        $integrate_automate_table->prepare_items();
        $integrate_automate_table->display();
        echo  "</form>" ;
        echo  "</div>" ;
    }
    
    /**
     * For Create New integration
     * @since    1.0.0
     */
    public function integrate_automate_new_integration()
    {
        # Get All the Event Source And Display Them
        $events = array();
        $eventPlatform = array();
        # wardPress Post and page
        $integrate_automate_wp_post_and_page = $this->integrate_automate_wp_post_and_page();
        
        if ( $integrate_automate_wp_post_and_page[0] and is_array( $integrate_automate_wp_post_and_page[1] ) ) {
            $events = array_merge( $events, $integrate_automate_wp_post_and_page[1] );
            $eventPlatform['postPage'] = $integrate_automate_wp_post_and_page[1];
        }
        
        # wardPress user
        $integrate_automate_wp_user = $this->integrate_automate_wp_user();
        
        if ( $integrate_automate_wp_user[0] and is_array( $integrate_automate_wp_user[1] ) ) {
            $events = array_merge( $events, $integrate_automate_wp_user[1] );
            $eventPlatform['user'] = $integrate_automate_wp_user[1];
        }
        
        # wardPress Comments
        $integrate_automate_wp_comment = $this->integrate_automate_wp_comment();
        
        if ( $integrate_automate_wp_comment[0] and is_array( $integrate_automate_wp_comment[1] ) ) {
            $events = array_merge( $events, $integrate_automate_wp_comment[1] );
            $eventPlatform['comment'] = $integrate_automate_wp_comment[1];
        }
        
        # WooCommerce Products
        $integrate_automate_woocommerce_product_events = $this->integrate_automate_woocommerce_product_events();
        
        if ( $integrate_automate_woocommerce_product_events[0] and is_array( $integrate_automate_woocommerce_product_events[1] ) ) {
            $events = array_merge( $events, $integrate_automate_woocommerce_product_events[1] );
            $eventPlatform['wcProduct'] = $integrate_automate_woocommerce_product_events[1];
        }
        
        # Setting Data source and Events to transient
        set_transient( 'eventPlatform', $eventPlatform );
        # HTML page for New integration
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/integrate-automate-new-integration-display.php';
    }
    
    /**
     * For Edit integration
     * @since    1.0.0
     */
    public function integrate_automate_edit_integration( $id )
    {
        # Get All the Event Source And Display Them
        $events = array();
        $eventPlatform = array();
        # wardPress Post and page
        $integrate_automate_wp_post_and_page = $this->integrate_automate_wp_post_and_page();
        
        if ( $integrate_automate_wp_post_and_page[0] and is_array( $integrate_automate_wp_post_and_page[1] ) ) {
            $events = array_merge( $events, $integrate_automate_wp_post_and_page[1] );
            $eventPlatform['postPage'] = $integrate_automate_wp_post_and_page[1];
        }
        
        # wardPress user
        $integrate_automate_wp_user = $this->integrate_automate_wp_user();
        
        if ( $integrate_automate_wp_user[0] and is_array( $integrate_automate_wp_user[1] ) ) {
            $events = array_merge( $events, $integrate_automate_wp_user[1] );
            $eventPlatform['user'] = $integrate_automate_wp_user[1];
        }
        
        # wardPress Post and page
        $integrate_automate_wp_comment = $this->integrate_automate_wp_comment();
        
        if ( $integrate_automate_wp_comment[0] and is_array( $integrate_automate_wp_comment[1] ) ) {
            $events = array_merge( $events, $integrate_automate_wp_comment[1] );
            $eventPlatform['comment'] = $integrate_automate_wp_comment[1];
        }
        
        # WooCommerce Products
        $integrate_automate_woocommerce_product_events = $this->integrate_automate_woocommerce_product_events();
        
        if ( $integrate_automate_woocommerce_product_events[0] and is_array( $integrate_automate_woocommerce_product_events[1] ) ) {
            $events = array_merge( $events, $integrate_automate_woocommerce_product_events[1] );
            $eventPlatform['wcProduct'] = $integrate_automate_woocommerce_product_events[1];
        }
        
        #  Setting Data source and Events to transient
        set_transient( 'eventPlatform', $eventPlatform );
        # getting Data from Saved integration
        $get_integration = get_post( $id );
        $integrationID = $get_integration->ID;
        $integrationTitle = $get_integration->post_title;
        $webHookUrl = $get_integration->post_content;
        $eventSource = $get_integration->post_excerpt;
        # Converting Content to JSON
        $customFieldsInHead = get_post_meta( $get_integration->ID, "_integrate_automate_head", TRUE );
        $customFieldsInBody = get_post_meta( $get_integration->ID, "_integrate_automate_body", TRUE );
        $eventsPlatform = get_post_meta( $get_integration->ID, "_integrate_automate_eventsPlatform", TRUE );
        # for Custom Head
        
        if ( !empty($customFieldsInHead) and is_array( $customFieldsInHead ) ) {
            $customFieldsInHead = @json_encode( $customFieldsInHead );
        } else {
            $customFieldsInHead = "";
        }
        
        # for Custom body
        
        if ( !empty($customFieldsInBody) and is_array( $customFieldsInBody ) ) {
            $customFieldsInBody = @json_encode( $customFieldsInBody );
        } else {
            $customFieldsInBody = "";
        }
        
        # if Successful then set the value
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/integrate-automate-edit-integration-display.php';
    }
    
    /**
     * Saving New And Edited integration 
     * @since    1.0.0
     */
    public function integrate_automate_save_integration()
    {
        # Setting error status
        $errorStatus = TRUE;
        # Empty holder for customFieldsInHead
        $customFieldsInHead = array();
        # Empty holder for customFieldsInBody
        $customFieldsInBody = array();
        # Empty Holder for URL
        $URL = "";
        # Title is set or empty test
        
        if ( !isset( $_POST['integrationTitle'] ) and empty($_POST['integrationTitle']) ) {
            $errorStatus = FALSE;
            $this->integrate_automate_log(
                get_class( $this ),
                __METHOD__,
                "501",
                "ERROR: IntegrationTitle is Empty. "
            );
            wp_redirect( admin_url( '/admin.php?page=integrate-automate&action=new&rms=fail_empty_IntegrationTitle' ) );
        }
        
        # Data source empty and is set test
        
        if ( !isset( $_POST['eventSource'] ) and empty($_POST['eventSource']) ) {
            $errorStatus = FALSE;
            $this->integrate_automate_log(
                get_class( $this ),
                __METHOD__,
                "502",
                "ERROR: DataSource is not in the Event List."
            );
            wp_redirect( admin_url( '/admin.php?page=integrate-automate&action=new&rms=fail_empty_DataSource' ) );
        }
        
        # URL empty and Not Set test
        
        if ( !isset( $_POST['webHookUrl'] ) and empty($_POST['webHookUrl']) ) {
            $errorStatus = FALSE;
            $this->integrate_automate_log(
                get_class( $this ),
                __METHOD__,
                "503",
                "ERROR: DataSourceID is Empty."
            );
            wp_redirect( admin_url( '/admin.php?page=integrate-automate&action=new&rms=fail_empty_DataSourceID' ) );
        }
        
        # URL Validation Test
        
        if ( filter_var( $_POST['webHookUrl'], FILTER_VALIDATE_URL ) ) {
            $URL = esc_url( $_POST['webHookUrl'] );
        } else {
            $this->integrate_automate_log(
                get_class( $this ),
                __METHOD__,
                "504",
                "ERROR: URL is not valid."
            );
            wp_redirect( admin_url( '/admin.php?page=integrate-automate&action=new&rms=url_is_not_valid' ) );
        }
        
        # JSON Custom data
        # For Valid JSON  and Sanitizes
        
        if ( isset( $_POST['customFieldsInHead'] ) and !empty($_POST['customFieldsInHead']) ) {
            $TMPcustomFieldsInHead = @json_decode( stripslashes( $_POST['customFieldsInHead'] ), TRUE );
            
            if ( is_array( $TMPcustomFieldsInHead ) ) {
                # Sanitizes the Array
                foreach ( $TMPcustomFieldsInHead as $key => $value ) {
                    $customFieldsInHead[sanitize_text_field( $key )] = sanitize_text_field( $value );
                }
            } else {
                $this->integrate_automate_log(
                    get_class( $this ),
                    __METHOD__,
                    "505",
                    "ERROR: customFieldsInHead is Not Valid JSON. " . $_POST['customFieldsInHead']
                );
            }
        
        }
        
        # For Valid JSON  and Sanitizes
        
        if ( isset( $_POST['customFieldsInBody'] ) and !empty($_POST['customFieldsInBody']) ) {
            $TMPcustomFieldsInBody = @json_decode( stripslashes( $_POST['customFieldsInBody'] ), TRUE );
            
            if ( is_array( $TMPcustomFieldsInBody ) and !empty($TMPcustomFieldsInBody) ) {
                # Sanitizes the Array
                foreach ( $TMPcustomFieldsInBody as $key => $value ) {
                    $customFieldsInBody[sanitize_text_field( $key )] = sanitize_text_field( $value );
                }
            } else {
                $this->integrate_automate_log(
                    get_class( $this ),
                    __METHOD__,
                    "506",
                    "ERROR: customFieldsInBody is Not Valid JSON. " . $_POST['customFieldsInBody']
                );
            }
        
        }
        
        # Getting  eventPlatform
        $eventsPlatform = "";
        # getting eventPlatform and events from transient
        $eventPlatforms = get_transient( 'eventPlatform' );
        # Looping to see is set or Not
        if ( is_array( $eventPlatforms ) and !empty($_POST['eventSource']) ) {
            # foreach
            foreach ( $eventPlatforms as $Platform => $eventsArray ) {
                # $sourceArray
                if ( isset( $eventsArray[$_POST['eventSource']] ) ) {
                    $eventsPlatform = $Platform;
                }
            }
        }
        # Testing Event Platform is Empty or Not
        
        if ( empty($eventsPlatform) ) {
            $errorStatus = FALSE;
            $this->integrate_automate_log(
                get_class( $this ),
                __METHOD__,
                "507",
                "ERROR: eventsPlatform is Empty. "
            );
            wp_redirect( admin_url( '/admin.php?page=integrate-automate&action=new&rms=fail_empty_eventsPlatform' ) );
        }
        
        # For New integration
        
        if ( isset( $_POST['status'] ) and $_POST['status'] == 'new_integration' ) {
            # Creating Custom Post  Argument
            $customPost = array(
                'ID'           => '',
                'post_title'   => sanitize_text_field( $_POST['integrationTitle'] ),
                'post_status'  => 'publish',
                'post_content' => $URL,
                'post_excerpt' => sanitize_text_field( $_POST['eventSource'] ),
                'post_name'    => '',
                'post_type'    => 'integrate_automate',
            );
            # Inserting New integration custom Post type
            $post_id = wp_insert_post( $customPost );
            # inserting Post meta
            
            if ( $post_id ) {
                add_post_meta(
                    $post_id,
                    '_integrate_automate_head',
                    $customFieldsInHead,
                    true
                );
                add_post_meta(
                    $post_id,
                    '_integrate_automate_body',
                    $customFieldsInBody,
                    true
                );
                add_post_meta(
                    $post_id,
                    '_integrate_automate_eventsPlatform',
                    $eventsPlatform,
                    true
                );
            }
        
        }
        
        # Now save the Edited integration
        
        if ( isset( $_POST['status'], $_POST['integrationID'] ) and $_POST['status'] == 'edit_integration' ) {
            # if Empty
            
            if ( empty($_POST['integrationID']) ) {
                $errorStatus = FALSE;
                $this->integrate_automate_log(
                    get_class( $this ),
                    __METHOD__,
                    "508",
                    "ERROR: DataSourceID is Empty."
                );
                wp_redirect( admin_url( '/admin.php?page=integrate-automate&action=new&rms=fail_empty_DataSourceID' ) );
            }
            
            # Creating Custom Post Argument
            $customPost = array(
                'ID'           => sanitize_text_field( $_POST['integrationID'] ),
                'post_title'   => sanitize_text_field( $_POST['integrationTitle'] ),
                'post_status'  => 'publish',
                'post_content' => $URL,
                'post_excerpt' => sanitize_text_field( $_POST['eventSource'] ),
                'post_name'    => '',
                'post_type'    => 'integrate_automate',
            );
            # Inserting New integration custom Post type
            $post_id = wp_update_post( $customPost );
            # Updating Post meta
            
            if ( $post_id ) {
                # Deleting Current Post Meta
                delete_post_meta( $post_id, '_integrate_automate_head' );
                delete_post_meta( $post_id, '_integrate_automate_body' );
                delete_post_meta( $post_id, '_integrate_automate_eventsPlatform' );
                # Adding Edited Post mana
                add_post_meta(
                    $post_id,
                    '_integrate_automate_head',
                    $customFieldsInHead,
                    true
                );
                add_post_meta(
                    $post_id,
                    '_integrate_automate_body',
                    $customFieldsInBody,
                    true
                );
                add_post_meta(
                    $post_id,
                    '_integrate_automate_eventsPlatform',
                    $eventsPlatform,
                    true
                );
            }
        
        }
        
        # if There is a Post Id , That Means Post is success fully saved s
        
        if ( $post_id and $errorStatus ) {
            # inserting on log
            $this->integrate_automate_log(
                get_class( $this ),
                __METHOD__,
                "200",
                "SUCCESS: Integration saved. " . json_encode( $customPost )
            );
            # Caching integrations to wp set_transient
            # Redirecting
            wp_redirect( admin_url( '/admin.php?page=integrate-automate&rms=success' ) );
            // Redirect User With Success Note is not With Error Note
        } else {
            # Inserting on log
            $this->integrate_automate_log(
                get_class( $this ),
                __METHOD__,
                "509",
                "ERROR: Integration didn't saved. Integration insert fail. " . json_encode( $customPost )
            );
            # redirecting
            wp_redirect( admin_url( '/admin.php?page=integrate-automate&rms=fail_insert' ) );
            // Redirect User With Success Note is not With Error Note
        }
    
    }
    
    /**
     * For Delete the Intermigration [ single or list ]
     * @since    1.0.0
     */
    public function integrate_automate_delete_intermigration( $id )
    {
        # For Array, Bulk delete
        if ( isset( $_GET['id'] ) and is_array( $_GET['id'] ) ) {
            # looping and Deleting the Integrations
            foreach ( $_GET['id'] as $post_id ) {
                $r = wp_delete_post( sanitize_text_field( $post_id ) );
                # Deleting Intermigration meta
                delete_post_meta( $post_id, '_integrate_automate_head' );
                delete_post_meta( $post_id, '_integrate_automate_body' );
                delete_post_meta( $post_id, '_integrate_automate_eventSources' );
                if ( $r ) {
                    $this->integrate_automate_log(
                        get_class( $this ),
                        __METHOD__,
                        "200",
                        "SUCCESS: Integration Successfully Deleted  Integration ID :" . $post_id
                    );
                }
            }
        }
        # if not array, individual integration
        
        if ( isset( $_GET['id'] ) and !is_array( $_GET['id'] ) ) {
            $r = wp_delete_post( sanitize_text_field( $_GET['id'] ) );
            # Deleting Intermigration meta
            delete_post_meta( $_GET['id'], '_integrate_automate_head' );
            delete_post_meta( $_GET['id'], '_integrate_automate_body' );
            delete_post_meta( $_GET['id'], '_integrate_automate_eventSources' );
            if ( $r ) {
                $this->integrate_automate_log(
                    get_class( $this ),
                    __METHOD__,
                    "200",
                    "SUCCESS: Integration Successfully Deleted  Integration ID :" . $_GET['id']
                );
            }
        }
        
        # Redirect
        wp_redirect( admin_url( '/admin.php?page=integrate-automate' ) );
    }
    
    /**
     * It will Change THE status of Integration 
     * @since    1.0.0
     */
    public function integrate_automate_integration_status( $id )
    {
        # check the Post type status
        if ( get_post( $id )->post_status == 'publish' ) {
            $custom_post = array(
                'ID'          => $id,
                'post_status' => 'pending',
            );
        }
        if ( get_post( $id )->post_status == 'pending' ) {
            $custom_post = array(
                'ID'          => $id,
                'post_status' => 'publish',
            );
        }
        #
        $post_id = wp_update_post( $custom_post );
        # Keeping Log
        $this->integrate_automate_log(
            get_class( $this ),
            __METHOD__,
            "200",
            "SUCCESS: ID " . $post_id . " Integration status  change to ." . get_post( $id )->post_status
        );
        # redirect
        ( wp_update_post( $custom_post ) ? wp_redirect( admin_url( '/admin.php?page=integrate-automate&rms=success_from_status_change' ) ) : wp_redirect( admin_url( '/admin.php?page=integrate-automate&rms=fail' ) ) );
    }
    
    /**
     *  WordPress log page
     *  @since    1.0.0
     */
    public function integrate_automate_log_page()
    {
        # Deleting Log starts
        $integrate_automate_logs = get_posts( array(
            'post_type'      => 'integrate_automate_l',
            'posts_per_page' => -1,
        ) );
        if ( count( $integrate_automate_logs ) > 100 ) {
            foreach ( $integrate_automate_logs as $key => $log ) {
                if ( $key > 100 ) {
                    wp_delete_post( $log->ID, true );
                }
            }
        }
        # Deleting Log Ends
        echo  "<div class='wrap'>" ;
        echo  "<h1 class='wp-heading-inline'>  Log Page <code>Last 200 log</code> &#32;&#32; <code>V" . $this->version . "</code> &#32;&#32;  </h1>" ;
        $integrate_automate_logs = get_posts( array(
            'post_type'      => 'integrate_automate_l',
            'order'          => 'DESC',
            'posts_per_page' => -1,
        ) );
        $i = 1;
        foreach ( $integrate_automate_logs as $key => $log ) {
            $post_excerpt = json_decode( $log->post_excerpt );
            
            if ( $log->post_title == 200 ) {
                echo  "<div class='notice notice-success inline'>" ;
            } else {
                echo  "<div class='notice notice-error inline'>" ;
            }
            
            echo  "<p><span class='integrate_automate-circle'>" . $log->ID ;
            echo  " .</span>" ;
            echo  "<code>" . $log->post_title . "</code>" ;
            echo  "<code>" ;
            if ( isset( $post_excerpt->file_name, $post_excerpt->function_name ) ) {
                echo  $post_excerpt->file_name . " | " . $post_excerpt->function_name ;
            }
            echo  "</code>" ;
            echo  $log->post_content ;
            echo  " <code>" . $log->post_date . "</code>" ;
            echo  "</p>" ;
            echo  "</div>" ;
            $i++;
        }
        echo  "</div>" ;
    }
    
    /**
     *  WordPress Default Page and Posts 
     *  @since    1.0.0
     */
    public function integrate_automate_wp_post_and_page()
    {
        # Post Event array
        $wordpressPostEvents = array(
            'wordpress_newPost'    => 'Wordpress New Post',
            'wordpress_editPost'   => 'Wordpress Edit Post',
            'wordpress_deletePost' => 'Wordpress Delete Post',
            'wordpress_newPage'    => 'Wordpress New Page',
        );
        # return
        return array( TRUE, $wordpressPostEvents );
    }
    
    /**
     *  WordPress user 
     *  @since    1.0.0
     */
    public function integrate_automate_wp_user()
    {
        # Wordpress User Events
        $wpUserEvents = array(
            'wordpress_newUser'           => 'Wordpress New User',
            'wordpress_UserProfileUpdate' => 'Wordpress Update User Profile',
            'wordpress_deleteUser'        => 'Wordpress Delete User',
            'wordpress_userLogin'         => 'Wordpress User Log in',
            'wordpress_userLogout'        => 'Wordpress User Log out',
        );
        # return
        return array( TRUE, $wpUserEvents );
    }
    
    /**
     *  WordPress comment 
     *  @since    1.0.0
     */
    public function integrate_automate_wp_comment()
    {
        # Comment Starts
        $wpCommentEvents = array(
            'wordpress_comment'      => 'Wordpress Comment',
            'wordpress_edit_comment' => 'Wordpress Edit Comment',
        );
        # return
        return array( TRUE, $wpCommentEvents );
    }
    
    /**
     *  woocommerce order events
     *  @since    1.0.0
     */
    public function integrate_automate_woocommerce_order_events()
    {
    }
    
    /**
     *  woocommerce product events
     *  @since    1.0.0
     */
    public function integrate_automate_woocommerce_product_events()
    {
        # if WooCommerce is Installed
        
        if ( in_array( 'woocommerce/woocommerce.php', $this->active_plugins ) ) {
            $wooCommerceProductEvents = array(
                'wc-publish_product' => 'WooCommerce Publish Product',
                'wc-update_product'  => 'WooCommerce Update Product',
                'wc-trash_product'   => 'WooCommerce Delete Product',
            );
            # return
            return array( TRUE, $wooCommerceProductEvents );
        } else {
            # return
            return array( FALSE, "ERROR: wooCommerce is not installed." );
        }
    
    }
    
    /**
     *  Contact form 7,  form  fields 
     *  @since    1.0.0
     */
    public function integrate_automate_cf7_forms()
    {
        # is there CF7
        if ( !in_array( 'contact-form-7/wp-contact-form-7.php', $this->active_plugins ) or !$this->integrate_automate_dbTableExists( 'posts' ) ) {
            return array( FALSE, "ERROR:  Contact form 7 is Not installed or DB Table is Not Exist  " );
        }
        # Empty Holder
        $cf7forms = array();
        global  $wpdb ;
        $cf7Forms = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE {$wpdb->posts}.post_type = 'wpcf7_contact_form' AND {$wpdb->postmeta}.meta_key = '_form'" );
        # Looping the Forms
        foreach ( $cf7Forms as $form ) {
            # Inserting Fields
            $cf7forms["cf7_" . $form->ID] = "Cf7 - " . $form->post_title;
        }
        # Loop ends
        # return
        return array( TRUE, $cf7forms );
    }
    
    /**
     *  Ninja  form  fields 
     *  @since    1.0.0
     */
    public function integrate_automate_ninja_forms()
    {
    }
    
    /**
     * formidable form  fields 
     * @since    1.0.0
     */
    public function integrate_automate_formidable_forms()
    {
        # Inserting Data to the Main [$eventsAndTitles ] Array
    }
    
    /**
     *  wpforms fields 
     *  @since    1.0.0
     */
    public function integrate_automate_wpforms_forms()
    {
    }
    
    /**
     *  WE forms fields 
     *  @since    1.0.0
     */
    public function integrate_automate_weforms_forms()
    {
    }
    
    /**
     *  gravity forms fields 
     *  @since    1.0.0
     */
    public function integrate_automate_gravity_forms()
    {
    }
    
    /**
     * This Function will All Custom Post types 
     * @since      1.0.0
     * @return     array   First one is CPS and Second one is CPT's Field source.
     */
    public function integrate_automate_allCptEvents()
    {
    }
    
    /**
     * This is a Helper function to check Table is Exist or Not 
     * If DB table Exist it will return True if Not it will return False
     * @since      1.0.0
     * @param      string    $data_source    Which platform call this function s
     */
    public function integrate_automate_dbTableExists( $tableName = null )
    {
        if ( empty($tableName) ) {
            return FALSE;
        }
        # yep DB
        global  $wpdb ;
        $r = $wpdb->get_results( "SHOW TABLES LIKE '" . $wpdb->prefix . $tableName . "'" );
        #  if / else
        
        if ( $r ) {
            return TRUE;
        } else {
            return FALSE;
        }
    
    }
    
    /**
     * LOG ! For Good , This the log Method 
     * @since      1.0.0
     * @param      string    $file_name       	File Name . Use  [ get_class($this) ]
     * @param      string    $function_name     Function name.	 [  __METHOD__  ]
     * @param      string    $status_code       The name of this plugin.
     * @param      string    $status_message    The version of this plugin.
     */
    public function integrate_automate_log(
        $file_name = '',
        $function_name = '',
        $status_code = '',
        $status_message = ''
    )
    {
        # Check and Balance
        
        if ( empty($status_code) or empty($status_message) ) {
            $status_code = 420;
            $status_message = "ERROR: status_code OR status_message is Empty. this is from integrate_automate_log function";
        }
        
        # insert the post
        $r = wp_insert_post( array(
            'post_content' => sanitize_text_field( $status_message ),
            'post_title'   => sanitize_text_field( $status_code ),
            'post_status'  => "publish",
            'post_excerpt' => json_encode( array(
            "file_name"     => sanitize_text_field( $file_name ),
            "function_name" => sanitize_text_field( $function_name ),
        ) ),
            'post_type'    => "integrate_automate_l",
        ) );
        # if $r is true
        if ( $r ) {
            # return
            return array( TRUE, "SUCCESS: Successfully inserted to the Log" );
        }
    }

}
//			 | To do list |
// ===================================
// 2. Add 3rd Party Support. it will at last.[]
// 3. Add do_action() in Multiple Places.    []
// 3. Delete Log After Some Time of 200 log. [x]