<?php

/**
 * The event-specific functionality of the plugin.
 * @link       javmah.com
 * @since      1.0.0
 * @package    Integrate_Automate
 * @subpackage Integrate_Automate/Event
 * @author     javmah <jaedmah@gmail.com>
 */
class Integrate_Automate_Event
{
    /**
     * The ID of this plugin.
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name   The ID of this plugin.
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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->active_plugins = get_option( 'active_plugins' );
    }
    
    /**
     * For Testing & Debug
     * @since    1.0.0
     */
    public function integrate_automate_event_admin_notices()
    {
        // echo"<pre>";
        // echo"</pre>";
    }
    
    /**
     *  WordPress new User Registered  HOOK's callback function || wordpress_newUser
     *  @param     int     $user_id     username
     *  @param     int     $old_user_data     username
     *  @since     1.0.0
     */
    public function integrate_automate_wp_newUser( $user_id )
    {
        # if There is a integration on User profile update
        $userData = get_userdata( $user_id );
        $userMeta = get_user_meta( $user_id );
        # Converting data to JSON
        foreach ( $userMeta as $metaKey => $metaValue ) {
            $unserializeValue = @unserialize( $metaValue[0] );
            
            if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                $userMeta[$metaKey] = json_encode( $unserializeValue );
            } else {
                $userMeta[$metaKey] = $metaValue[0];
            }
        
        }
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'wordpress_newUser' );
        # If there are integrations on the DB
        if ( !empty($eventSavedIntegrations[2]) ) {
            # Looping the integrations
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # removing password from User data
                if ( isset( $userData->data->user_pass ) ) {
                    unset( $userData->data->user_pass );
                }
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # Testing Dual Submission
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wordpress_newUser',
                            $integration['post_content'],
                            array(
                            "status"   => "wordpress_newUser",
                            "userData" => $userData,
                            "userMeta" => $userMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "707",
                            "ERROR: Fired 7 second Ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "701",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    }
    
    /**
     *  wp new User Profile Update HOOK's callback function
     *  @param     int     $user_id     		user ID
     *  @param     int     $old_user_data     	user Data
     *  @since     1.0.0 
     */
    public function integrate_automate_wp_profileUpdate( $user_id, $old_user_data )
    {
        # if There is a integration on User profile update
        $userData = get_userdata( $user_id );
        $userMeta = get_user_meta( $user_id );
        # Converting data to JSON
        foreach ( $userMeta as $metaKey => $metaValue ) {
            $unserializeValue = @unserialize( $metaValue[0] );
            
            if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                $userMeta[$metaKey] = json_encode( $unserializeValue );
            } else {
                $userMeta[$metaKey] = $metaValue[0];
            }
        
        }
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'wordpress_UserProfileUpdate' );
        # If there are integrations on the DB
        if ( !empty($eventSavedIntegrations[2]) ) {
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # removing password from User data
                if ( isset( $userData->data->user_pass ) ) {
                    unset( $userData->data->user_pass );
                }
                # removing password from old User data
                if ( isset( $old_user_data->data->user_pass ) ) {
                    unset( $old_user_data->data->user_pass );
                }
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # Testing Dual Submission
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wordpress_UserProfileUpdate',
                            $integration['post_content'],
                            array(
                            "status"      => "wordpress_UserProfileUpdate",
                            "userData"    => $userData,
                            "userOldData" => $old_user_data,
                            "userMeta"    => $userMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "707",
                            "ERROR: Fired 7 second Ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "702",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    }
    
    /**
     *  wp Delete User HOOK's callback function || wordpress_deleteUser
     *  @param    int     $user_id     user ID
     *  @since    1.0.0
     */
    public function integrate_automate_wp_deleteUser( $user_id )
    {
        # if There is a integration on User profile update
        $userData = get_userdata( $user_id );
        $userMeta = get_user_meta( $user_id );
        # Converting data to JSON
        if ( !empty($userMeta) ) {
            foreach ( $userMeta as $metaKey => $metaValue ) {
                $unserializeValue = @unserialize( $metaValue[0] );
                
                if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                    $userMeta[$metaKey] = json_encode( $unserializeValue );
                } else {
                    $userMeta[$metaKey] = $metaValue[0];
                }
            
            }
        }
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'wordpress_deleteUser' );
        # If there are integrations on the DB
        if ( !empty($eventSavedIntegrations[2]) ) {
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # removing password from User data
                if ( isset( $userData->data->user_pass ) ) {
                    unset( $userData->data->user_pass );
                }
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # Testing Dual Submission
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wordpress_deleteUser',
                            $integration['post_content'],
                            array(
                            "status"   => "wordpress_UserProfileUpdate",
                            "userData" => $userData,
                            "userMeta" => $userMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "707",
                            "ERROR: Fired 7 second Ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "703",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    }
    
    /**
     * User Logged in  HOOK's callback function || wordpress_userLogin
     * @param     int     $username     username
     * @param     int     $user     	user
     * @since     1.0.0
     */
    public function integrate_automate_wp_userLogin( $username, $user )
    {
        # getting user data
        $userMeta = get_user_meta( $user->ID );
        # Converting data to JSON
        foreach ( $userMeta as $metaKey => $metaValue ) {
            $unserializeValue = @unserialize( $metaValue[0] );
            
            if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                $userMeta[$metaKey] = json_encode( $unserializeValue );
            } else {
                $userMeta[$metaKey] = $metaValue[0];
            }
        
        }
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'wordpress_userLogin' );
        # If there are integrations on the DB
        if ( !empty($eventSavedIntegrations[2]) ) {
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # Testing Dual Submission
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wordpress_userLogin',
                            $integration['post_content'],
                            array(
                            "status"   => "wordpress_userLogin",
                            "username" => $username,
                            "userData" => $user,
                            "userMeta" => $userMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "707",
                            "ERROR: Fired 7 second Ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "704",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    }
    
    /**
     * User wp_logout  HOOK's callback function || wordpress_userLogout
     * @since   1.0.0
     */
    public function integrate_automate_wp_userLogout( $userInfo )
    {
        # getting User Information
        $user = wp_get_current_user();
        # getting user data
        $userMeta = get_user_meta( $user->ID );
        # Converting data to JSON
        if ( !empty($userMeta) ) {
            foreach ( $userMeta as $metaKey => $metaValue ) {
                $unserializeValue = @unserialize( $metaValue[0] );
                
                if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                    $userMeta[$metaKey] = json_encode( $unserializeValue );
                } else {
                    $userMeta[$metaKey] = $metaValue[0];
                }
            
            }
        }
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'wordpress_userLogout' );
        # If there are integrations on the DB
        if ( !empty($eventSavedIntegrations[2]) ) {
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # JSON decode
                $post_content = @json_decode( $integration->post_content, TRUE );
                # Validating the DB data
                
                if ( !empty($post_content) and is_array( $post_content ) ) {
                    # if URL is Exist
                    
                    if ( isset( $post_content['webHookUrl'] ) ) {
                        # Dispatching the request
                        $this->integrate_automate_postman(
                            'wordpress_userLogout',
                            $post_content['webHookUrl'],
                            array(
                            "status"   => "wordpress_userLogout",
                            "userData" => $userInfo,
                            "userMeta" => $userMeta,
                        ),
                            $post_content['customFieldsInHead'],
                            $post_content['customFieldsInBody']
                        );
                    } else {
                        # There is No URL so it should be error
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "705",
                            "ERROR: There is no URL in the integration. From User Logout."
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "706",
                        "ERROR: bad intermigration data in the DB. from User Logout"
                    );
                }
                
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # Testing Dual Submission
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wordpress_userLogout',
                            $integration['post_content'],
                            array(
                            "status"   => "wordpress_userLogout",
                            "userData" => $userInfo,
                            "userMeta" => $userMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "707",
                            "ERROR: Fired 7 second Ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "707",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    }
    
    /**
     * WordPress Post   HOOK's callback function
     * @since     1.0.0  
     * @param     int     $post_id      Order ID
     * @param     int     $post    		Order ID
     * @param     int     $update     	Product Post 
     */
    public function integrate_automate_wp_post( $post_id, $post, $update )
    {
        # post_event Holder;
        $post_event = "";
        # if Post type is Post
        
        if ( $post->post_type == 'post' ) {
            # getting Time Difference
            if ( !empty($post->post_date) and !empty($post->post_modified) ) {
                $post_time_diff = strtotime( $post->post_modified ) - strtotime( $post->post_date );
            }
            # New Post,
            if ( $post->post_status == 'publish' and $post_time_diff <= 1 ) {
                $post_event = "wordpress_newPost";
            }
            # Updated post
            if ( $post->post_status == 'publish' and $post_time_diff > 1 ) {
                $post_event = "wordpress_editPost";
            }
            # Post Is trash  || If Post is Trashed This Will fired
            if ( $post->post_status == 'trash' ) {
                $post_event = "wordpress_deletePost";
            }
        }
        
        if ( $post->post_type == 'page' and !in_array( $post->post_status, array( 'auto-draft', 'draft', 'trash' ) ) ) {
            $post_event = "wordpress_newPage";
        }
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( $post_event );
        # If there are integrations on the DB
        if ( !empty($eventSavedIntegrations[2]) ) {
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # Getting post meta;
                $postMeta = get_post_meta( $post_id );
                # Converting data to JSON;
                if ( !empty($postMeta) ) {
                    # Loop starts
                    foreach ( $postMeta as $metaKey => $metaValue ) {
                        # Unserialize TRY
                        $unserializeValue = @unserialize( $metaValue[0] );
                        # Success or Failed
                        
                        if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                            $postMeta[$metaKey] = json_encode( $unserializeValue );
                        } else {
                            $postMeta[$metaKey] = $metaValue[0];
                        }
                    
                    }
                }
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # request Fired Status
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            $post_event,
                            $integration['post_content'],
                            array(
                            "status"   => $post_event,
                            'post_id'  => $post_id,
                            'post'     => $post,
                            'postMeta' => $postMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "708",
                            "ERROR: This Hook Already Fired 7 second Ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "708",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    }
    
    /**
     * WordPress New Comment   HOOK's callback function
     * @since     1.0.0
     * @param     int     $commentID     			Order ID
     * @param     int     $commentApprovedStatus    Order ID
     * @param     int     $commentData     	  		Product Post 
     */
    public function integrate_automate_wp_comment( $commentID, $approvedStatus, $commentData )
    {
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'wordpress_comment' );
        # If there are integrations on the DB
        
        if ( !empty($eventSavedIntegrations[2]) ) {
            # Getting Comment Meta
            $commentMeta = get_comment_meta( $commentID );
            # Converting data to JSON
            foreach ( $commentMeta as $metaKey => $metaValue ) {
                $unserializeValue = @unserialize( $commentMeta[0] );
                
                if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                    $commentMeta[$metaKey] = json_encode( $unserializeValue );
                } else {
                    $commentMeta[$metaKey] = $metaValue[0];
                }
            
            }
            # Looping the integrations
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # Testing Dual Submission
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wordpress_comment',
                            $integration['post_content'],
                            array(
                            "status"         => "wordpress_comment",
                            'commentID'      => $commentID,
                            "approvedStatus" => $approvedStatus,
                            'commentData'    => $commentData,
                            'commentMeta'    => $commentMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "709",
                            "ERROR: This Hook already fired 7 second ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "709",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    
    }
    
    # There should be an Edit Comment Hook Function in Here !
    # Create the Function and The Code for Edit product
    /**
     * WordPress Edit Comment   HOOK's callback function
     * @since     1.0.0
     * @param     int     $commentID     			Order ID
     * @param     int     $commentData     	  		Product Post 
     */
    public function integrate_automate_wp_edit_comment( $commentID, $commentData )
    {
        
        if ( empty($commentID) and empty($commentData) ) {
            $this->integrate_automate_event_log(
                get_class( $this ),
                __METHOD__,
                "715",
                "ERROR: comment ID or Comment Data is Empty."
            );
            return;
        }
        
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'wordpress_edit_comment' );
        # If there are integrations on the DB
        
        if ( !empty($eventSavedIntegrations[2]) ) {
            # Getting Comment Meta
            $commentMeta = get_comment_meta( $commentID );
            # Converting data to JSON
            foreach ( $commentMeta as $metaKey => $metaValue ) {
                $unserializeValue = @unserialize( $commentMeta[0] );
                
                if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                    $commentMeta[$metaKey] = json_encode( $unserializeValue );
                } else {
                    $commentMeta[$metaKey] = $metaValue[0];
                }
            
            }
            # Loop starts
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # request Fired Status
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wordpress_edit_comment',
                            $integration['post_content'],
                            array(
                            'status'      => 'wordpress_edit_comment',
                            'commentID'   => $commentID,
                            'commentData' => $commentData,
                            'commentMeta' => $commentMeta,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "709",
                            "ERROR: This Hook already fired 7 second ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "710",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    
    }
    
    /**
     * Woocommerce  Products  HOOK's callback function
     * @since     1.0.0
     * @param     int     $new_status     Order ID
     * @param     int     $old_status     Order ID
     * @param     int     $post     	  Product Post 
     */
    public function integrate_automate_woocommerce_product( $new_status, $old_status, $post )
    {
        # If Post type is Not product
        if ( $post->post_type !== 'product' or empty($post->ID) ) {
            return;
        }
        # getting Product events status
        
        if ( $new_status == 'publish' && $old_status !== 'publish' ) {
            $product_event = 'wc-publish_product';
        } elseif ( $new_status == 'trash' ) {
            $product_event = 'wc-trash_product';
        } elseif ( $new_status != 'auto-draft' and $new_status != 'draft' ) {
            $product_event = 'wc-update_product';
        } else {
            $product_event = '';
        }
        
        # Getting WooCommerce Product Information Starts
        $productInfo = array();
        $product_id = $post->ID;
        # Getting product information
        $product = wc_get_product( $product_id );
        $productInfo['id'] = ( method_exists( $product, 'get_id' ) ? $product->get_id() : "" );
        $productInfo['type'] = ( method_exists( $product, 'get_type' ) ? $product->get_type() : "" );
        $productInfo['name'] = ( method_exists( $product, 'get_name' ) ? $product->get_name() : "" );
        $productInfo['slug'] = ( method_exists( $product, 'get_slug' ) ? $product->get_slug() : "" );
        $productInfo['date_created'] = ( method_exists( $product, 'get_date_created' ) ? $product->get_date_created() : "" );
        $productInfo['date_modified'] = ( method_exists( $product, 'get_date_modified' ) ? $product->get_date_modified() : "" );
        $productInfo['status'] = ( method_exists( $product, 'get_status' ) ? $product->get_status() : "" );
        $productInfo['featured'] = ( method_exists( $product, 'get_featured' ) ? $product->get_featured() : "" );
        $productInfo['catalog_visibility'] = ( method_exists( $product, 'get_catalog_visibility' ) ? $product->get_catalog_visibility() : "" );
        $productInfo['description'] = ( method_exists( $product, 'get_description' ) ? $product->get_description() : "" );
        $productInfo['short_description'] = ( method_exists( $product, 'get_short_description' ) ? $product->get_short_description() : "" );
        $productInfo['sku'] = ( method_exists( $product, 'get_sku' ) ? $product->get_sku() : "" );
        $productInfo['menu_order'] = ( method_exists( $product, 'get_menu_order' ) ? $product->get_menu_order() : "" );
        $productInfo['virtual'] = ( method_exists( $product, 'get_virtual' ) ? $product->get_virtual() : "" );
        // Get Product Prices
        $productInfo['price'] = ( method_exists( $product, 'get_price' ) ? $product->get_price() : "" );
        $productInfo['regular_price'] = ( method_exists( $product, 'get_regular_price' ) ? $product->get_regular_price() : "" );
        $productInfo['sale_price'] = ( method_exists( $product, 'get_sale_price' ) ? $product->get_sale_price() : "" );
        $productInfo['date_on_sale_from'] = ( method_exists( $product, 'get_date_on_sale_from' ) ? $product->get_date_on_sale_from() : "" );
        $productInfo['date_on_sale_to'] = ( method_exists( $product, 'get_date_on_sale_to' ) ? $product->get_date_on_sale_to() : "" );
        $productInfo['total_sales'] = ( method_exists( $product, 'get_total_sales' ) ? $product->get_total_sales() : "" );
        // Get Product Tax, Shipping & Stock
        $productInfo['tax_status'] = ( method_exists( $product, 'get_tax_status' ) ? $product->get_tax_status() : "" );
        $productInfo['tax_class'] = ( method_exists( $product, 'get_tax_class' ) ? $product->get_tax_class() : "" );
        $productInfo['manage_stock'] = ( method_exists( $product, 'get_manage_stock' ) ? $product->get_manage_stock() : "" );
        $productInfo['stock_quantity'] = ( method_exists( $product, 'get_stock_quantity' ) ? $product->get_stock_quantity() : "" );
        $productInfo['stock_status'] = ( method_exists( $product, 'get_stock_status' ) ? $product->get_stock_status() : "" );
        $productInfo['backorders'] = ( method_exists( $product, 'get_backorders' ) ? $product->get_backorders() : "" );
        $productInfo['sold_individually'] = ( method_exists( $product, 'get_sold_individually' ) ? $product->get_sold_individually() : "" );
        $productInfo['purchase_note'] = ( method_exists( $product, 'get_purchase_note' ) ? $product->get_purchase_note() : "" );
        $productInfo['shipping_class_id'] = ( method_exists( $product, 'get_shipping_class_id' ) ? $product->get_shipping_class_id() : "" );
        // Get Product Dimensions
        $productInfo['weight'] = ( method_exists( $product, 'get_weight' ) ? $product->get_weight() : "" );
        $productInfo['length'] = ( method_exists( $product, 'get_length' ) ? $product->get_length() : "" );
        $productInfo['width'] = ( method_exists( $product, 'get_width' ) ? $product->get_width() : "" );
        $productInfo['height'] = ( method_exists( $product, 'get_height' ) ? $product->get_height() : "" );
        $productInfo['dimensions'] = ( method_exists( $product, 'get_dimensions' ) ? $product->get_dimensions() : "" );
        // Get Linked Products
        $productInfo['upsell_ids'] = ( method_exists( $product, 'get_upsell_ids' ) ? $product->get_upsell_ids() : "" );
        $productInfo['cross_sell_ids'] = ( method_exists( $product, 'get_cross_sell_ids' ) ? $product->get_cross_sell_ids() : "" );
        $productInfo['parent_id'] = ( method_exists( $product, 'get_parent_id' ) ? $product->get_parent_id() : "" );
        // Get Product Variations and Attributes
        $productInfo['children'] = ( method_exists( $product, 'get_children' ) ? $product->get_children() : "" );
        $productInfo['attributes'] = ( method_exists( $product, 'get_attributes' ) ? $product->get_attributes() : "" );
        $productInfo['default_attributes'] = ( method_exists( $product, 'get_default_attributes' ) ? $product->get_default_attributes() : "" );
        // Get Product Taxonomies
        $productInfo['categories'] = ( method_exists( $product, 'wc_get_product_category_list' ) ? $product->wc_get_product_category_list() : "" );
        $productInfo['category_ids'] = ( method_exists( $product, 'get_category_ids' ) ? $product->get_category_ids() : "" );
        $productInfo['tag_ids'] = ( method_exists( $product, 'get_tag_ids' ) ? $product->get_tag_ids() : "" );
        // Get Product Downloads
        $productInfo['downloads'] = ( method_exists( $product, 'get_downloads' ) ? $product->get_downloads() : "" );
        $productInfo['download_expiry'] = ( method_exists( $product, 'get_download_expiry' ) ? $product->get_download_expiry() : "" );
        $productInfo['downloadable'] = ( method_exists( $product, 'get_downloadable' ) ? $product->get_downloadable() : "" );
        $productInfo['download_limit'] = ( method_exists( $product, 'get_download_limit' ) ? $product->get_download_limit() : "" );
        // Get Product Images
        $productInfo['image_id'] = ( method_exists( $product, 'get_image_id' ) ? $product->get_image_id() : "" );
        $productInfo['image'] = ( method_exists( $product, 'get_image' ) ? $product->get_image() : "" );
        $productInfo['gallery_image_ids'] = ( method_exists( $product, 'get_gallery_image_ids' ) ? $product->get_gallery_image_ids() : "" );
        // Get Product Reviews
        $productInfo['reviews_allowed'] = ( method_exists( $product, 'get_reviews_allowed' ) ? $product->get_reviews_allowed() : "" );
        $productInfo['rating_counts'] = ( method_exists( $product, 'get_rating_counts' ) ? $product->get_rating_counts() : "" );
        $productInfo['average_rating'] = ( method_exists( $product, 'get_average_rating' ) ? $product->get_average_rating() : "" );
        $productInfo['review_count'] = ( method_exists( $product, 'get_review_count' ) ? $product->get_review_count() : "" );
        # for product info Ends
        # Getting WooCommerce Product Information Ends
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( $product_event );
        # If there are integrations on the DB
        
        if ( !empty($eventSavedIntegrations[2]) ) {
            # getting order meta data
            $productMeta = get_post_meta( $post->ID );
            # Converting data to JSON
            foreach ( $productMeta as $metaKey => $metaValue ) {
                $unserializeValue = @unserialize( $productMeta[0] );
                
                if ( !empty($unserializeValue) and is_array( $unserializeValue ) ) {
                    $productMeta[$metaKey] = json_encode( $unserializeValue );
                } else {
                    $productMeta[$metaKey] = $metaValue[0];
                }
            
            }
            # loop
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    #getting Last Fired Time;
                    $lastFired = get_post_meta( $integration["id"], '_integrate_automate_lastFired', TRUE );
                    # Testing Dual Submission
                    
                    if ( empty($lastFired) or $lastFired + 7 < time() ) {
                        # request dispatch
                        $this->integrate_automate_postman(
                            'wcProduct',
                            $integration['post_content'],
                            array(
                            'status'      => $product_event,
                            'productID'   => $product_id,
                            'productData' => $productInfo,
                            'productMeta' => $productMeta,
                            'newStatus'   => $new_status,
                            'oldStatus'   => $old_status,
                        ),
                            $integration['_integrate_automate_head'],
                            $integration['_integrate_automate_body']
                        );
                        # Updating the Last firing Time
                        update_post_meta( $integration["id"], '_integrate_automate_lastFired', time() );
                    } else {
                        $this->integrate_automate_event_log(
                            get_class( $this ),
                            __METHOD__,
                            "709",
                            "ERROR: This Hook already fired 7 second ago " . json_encode( $integration )
                        );
                    }
                
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "711",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    
    }
    
    /**
     * WooCommerce Order  HOOK's callback function
     * @since    1.0.0
     * @param    int     $order_id     Order ID
     */
    public function integrate_automate_woocommerce_order_status_changed( $order_id, $this_status_transition_from, $this_status_transition_to )
    {
    }
    
    /**
     * WooCommerce Checkout PAge Order CallBack Function 
     * @since     1.0.0
     * @param     int     $order_id     Order ID
     */
    public function integrate_automate_woocommerce_checkout_new_order( $order_id )
    {
    }
    
    /**
     * CF7 Form Submission Event || its a HOOK  callback function of Contact form 7 form
     * Contact form 7 is a Disgusting Code || Noting is good of this Plugin || 
     * @since    1.0.0
     * @param    array     $form_data     data_array
     */
    public function integrate_automate_cf7_submission( $contact_form )
    {
        $id = $contact_form->id();
        $submission = WPCF7_Submission::get_instance();
        $posted_data = $submission->get_posted_data();
        # getting the integrations
        $eventSavedIntegrations = $this->integrate_automate_savedIntegrations( 'cf7_' . $id );
        # If there are integrations on the DB
        if ( !empty($eventSavedIntegrations[2]) ) {
            foreach ( $eventSavedIntegrations[2] as $integration ) {
                # Request init
                
                if ( !empty($integration["post_content"]) and $integration["post_status"] == 'publish' ) {
                    # request dispatch
                    $this->integrate_automate_postman(
                        'cf7_' . $id,
                        $integration['post_content'],
                        array(
                        "status"      => 'cf7_' . $id,
                        'id'          => $id,
                        'posted_data' => $posted_data,
                    ),
                        $integration['_integrate_automate_head'],
                        $integration['_integrate_automate_body']
                    );
                } else {
                    # Saving log for invalid data
                    $this->integrate_automate_event_log(
                        get_class( $this ),
                        __METHOD__,
                        "714",
                        "ERROR: bad intermigration data in the DB " . json_encode( $integration )
                    );
                }
            
            }
        }
    }
    
    /**
     * ninja after saved entry to DB || its a HOOK  callback function of ninja form
     * @since    1.0.0
     * @param    array     $form_data     data_array
     */
    public function integrate_automate_ninja_forms_after_submission( $form_data )
    {
    }
    
    /**
     * formidable after saved entry to DB || its a HOOK  callback function of formidable form
     * @since    1.0.0
     * @param    array    $entry_id    Which platform call this function 
     * @param    array    $form_id     event_name 
     */
    public function integrate_automate_formidable_after_save( $entry_id, $form_id )
    {
    }
    
    /**
     * wpforms Submit Action Handler || its a HOOK  callback function of WP form
     * @since      1.0.0
     * @param      array    $fields    		Which platform call this function 
     * @param      array    $entry     		event_name 
     * @param      array    $form_data     	data_array
     */
    public function integrate_automate_wpforms_process_complete(
        $fields,
        $entry,
        $formData,
        $entry_id
    )
    {
    }
    
    /**
     * weforms forms_after_submission 
     * @param    string   $entry_id   		entry_id;
     * @param    string   $form_id   		form_id;
     * @param    string   $page_id     		page_id;
     * @param    array    $form_settings    form_data;
     * @since    1.0.0
     */
    public function integrate_automate_weforms_entry_submission(
        $entry_id,
        $form_id,
        $page_id,
        $form_settings
    )
    {
    }
    
    /**
     * gravityForms gform_after_submission 
     * @param    array   $entry     All the Entries with Some Extra;
     * @param    array   $formObj   Submitted form Object ;
     * @since    1.0.0
     */
    public function integrate_automate_gravityForms_after_submission( $entry, $formObj )
    {
    }
    
    /**
     * This Function Will Send Data and Keep the Log POSTMAN POSTMAN
     * @since    1.0.0
     */
    public function integrate_automate_postman(
        $eventSource = null,
        $url = null,
        $eventData = null,
        $headerCustomData = null,
        $bodyCustomData = null
    )
    {
        # Add Hook for Before action
        # If event Source is empty!
        
        if ( empty($eventSource) ) {
            $this->integrate_automate_event_log(
                get_class( $this ),
                __METHOD__,
                "720",
                "ERROR: Event Source is empty !"
            );
            return array( FALSE, "Event Source is empty!" );
        }
        
        # if URL is empty!
        
        if ( empty($url) ) {
            $this->integrate_automate_event_log(
                get_class( $this ),
                __METHOD__,
                "721",
                "ERROR: URL is empty !"
            );
            return array( FALSE, "URL is empty!" );
        }
        
        # if Event Data is empty!
        
        if ( empty($eventData) ) {
            $this->integrate_automate_event_log(
                get_class( $this ),
                __METHOD__,
                "722",
                "ERROR: Event Data is empty !"
            );
            return array( FALSE, "Event Data is empty!" );
        }
        
        # header Custom Data
        $unserializeHeader = @unserialize( $headerCustomData );
        
        if ( is_array( $unserializeHeader ) and !empty($unserializeHeader) ) {
            $headers = array_merge( array(
                "Content-Type" => "application/json",
            ), $unserializeHeader );
        } else {
            $headers = array(
                "Content-Type" => "application/json",
            );
        }
        
        # Body Custom Data
        $unserializeBody = @unserialize( $bodyCustomData );
        
        if ( is_array( $unserializeBody ) and !empty($unserializeBody) ) {
            $data['bodyCustomData'] = $unserializeBody;
            $data['eventData'] = $eventData;
        } else {
            $data['bodyCustomData'] = FALSE;
            $data['eventData'] = $eventData;
        }
        
        # Converted to JSON
        $body = @json_encode( $data, TRUE );
        # Error Checking
        if ( empty($body) ) {
            $this->integrate_automate_event_log(
                get_class( $this ),
                __METHOD__,
                "723",
                "ERROR: request body is Not converted in JSON on Event source " . $eventSource
            );
        }
        # Add Hook for Before request
        # remote request init
        $response = wp_remote_post( $url, array(
            'method'   => 'POST',
            'blocking' => true,
            'headers'  => $headers,
            'body'     => $body,
            'cookies'  => array(),
        ) );
        # Add Hook for After request
        # keeping the log
        
        if ( is_wp_error( $response ) ) {
            if ( is_array( $response ) and @json_encode( $response, TRUE ) ) {
                $this->integrate_automate_event_log(
                    get_class( $this ),
                    __METHOD__,
                    "724",
                    "ERROR: remote request failed, event source " . $eventSource . "---" . $url . "---" . @json_encode( $headers, TRUE ) . "---" . $body . "---" . @json_encode( $response, TRUE )
                );
            }
            if ( is_object( $response ) and @json_encode( $response, TRUE ) ) {
                $this->integrate_automate_event_log(
                    get_class( $this ),
                    __METHOD__,
                    "725",
                    "ERROR: remote request failed, event source " . $eventSource . "---" . $url . "---" . @json_encode( $headers, TRUE ) . $body . "---" . @json_encode( $response, TRUE )
                );
            }
            if ( is_string( $response ) ) {
                $this->integrate_automate_event_log(
                    get_class( $this ),
                    __METHOD__,
                    "726",
                    "ERROR: remote request failed, event source " . $eventSource . "---" . $url . "---" . @json_encode( $headers, TRUE ) . $body . "---" . $response
                );
            }
            return array( FALSE, array(
                "URL"          => $url,
                "request_head" => $headers,
                "request_body" => $body,
                "response"     => $response,
            ) );
        } else {
            if ( is_array( $response ) and @json_encode( $response, TRUE ) ) {
                $this->integrate_automate_event_log(
                    get_class( $this ),
                    __METHOD__,
                    "200",
                    "SUCCESS: remote request success, event source " . $eventSource . "---" . $url . "---" . @json_encode( $headers, TRUE ) . $body . "---" . @json_encode( $response, TRUE )
                );
            }
            if ( is_object( $response ) and @json_encode( $response, TRUE ) ) {
                $this->integrate_automate_event_log(
                    get_class( $this ),
                    __METHOD__,
                    "200",
                    "SUCCESS: remote request success, event source " . $eventSource . "---" . $url . "---" . @json_encode( $headers, TRUE ) . $body . "---" . @json_encode( $response, TRUE )
                );
            }
            if ( is_string( $response ) ) {
                $this->integrate_automate_event_log(
                    get_class( $this ),
                    __METHOD__,
                    "200",
                    "SUCCESS: remote request success, event source " . $eventSource . "---" . $url . "---" . @json_encode( $headers, TRUE ) . $body . "---" . $response
                );
            }
            return array( TRUE, array(
                "URL"          => $url,
                "request_head" => $headers,
                "request_body" => $body,
                "response"     => $response,
            ) );
        }
    
    }
    
    /**
     * Getting the Saved Integrations
     * @since      1.0.0
     * @param      string    $data_source    Which platform call this functions
     */
    public function integrate_automate_savedIntegrations( $source = null )
    {
        # Holders
        $Integrations = array();
        $sourceIntegrations = array();
        # DB object and SQL query
        global  $wpdb ;
        $r = $wpdb->get_results( " SELECT {$wpdb->prefix}posts.id, {$wpdb->prefix}posts.post_title, {$wpdb->prefix}posts.post_content, {$wpdb->prefix}posts.post_excerpt, {$wpdb->prefix}posts.post_status, {$wpdb->prefix}postmeta.meta_key, {$wpdb->prefix}postmeta.meta_value FROM {$wpdb->prefix}posts, {$wpdb->prefix}postmeta WHERE {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_ID AND {$wpdb->prefix}posts.post_type = 'integrate_automate' ", ARRAY_A );
        # if not empty Loop
        if ( !empty($r) and is_array( $r ) ) {
            #
            foreach ( $r as $valve ) {
                #
                $Integrations[$valve['id']]['id'] = $valve['id'];
                $Integrations[$valve['id']]['post_title'] = $valve['post_title'];
                $Integrations[$valve['id']]['post_content'] = $valve['post_content'];
                $Integrations[$valve['id']]['post_excerpt'] = $valve['post_excerpt'];
                $Integrations[$valve['id']]['post_status'] = $valve['post_status'];
                #
                if ( $valve['meta_key'] == '_integrate_automate_eventSources' ) {
                    $Integrations[$valve['id']]['eventSources'] = $valve['meta_value'];
                }
                #
                if ( $valve['meta_key'] == '_integrate_automate_head' ) {
                    $Integrations[$valve['id']]['_integrate_automate_head'] = $valve['meta_value'];
                }
                #
                if ( $valve['meta_key'] == '_integrate_automate_body' ) {
                    $Integrations[$valve['id']]['_integrate_automate_body'] = $valve['meta_value'];
                }
                #
            }
        }
        # populating $sourceIntegrations
        if ( !empty($source) and !empty($Integrations) ) {
            foreach ( $Integrations as $integrationKey => $integrateArray ) {
                if ( $integrateArray['post_excerpt'] == $source ) {
                    $sourceIntegrations[$integrationKey] = $integrateArray;
                }
            }
        }
        # return
        
        if ( empty($Integrations) ) {
            return array( FALSE, "Integrations array is Empty" );
        } else {
            return array( TRUE, $Integrations, $sourceIntegrations );
        }
    
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
        global  $wpdb ;
        $r = $wpdb->get_results( "SHOW TABLES LIKE '" . $wpdb->prefix . $tableName . "'" );
        
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
    public function integrate_automate_event_log(
        $file_name = '',
        $function_name = '',
        $status_code = '',
        $status_message = ''
    )
    {
        # Check and Balance
        
        if ( empty($status_code) or empty($status_message) ) {
            $status_code = 420;
            $status_message = "ERROR: status_code OR status_message is Empty. this is from integrate_automate_event_log function";
        }
        
        # inserting Log to the DB
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
        if ( $r ) {
            return array( TRUE, "SUCCESS: Successfully inserted to the Log" );
        }
    }

}