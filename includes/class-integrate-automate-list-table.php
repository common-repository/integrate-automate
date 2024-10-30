<?php
/**
 * Define the internationalization functionality.
 * Loads and defines the internationalization files for this plugin
 *
 * @since      1.0.0
 * @package    Wpgsi
 * @subpackage Wpgsi/includes
 * @author     javmah <jaedmah@gmail.com>
 */

if(!class_exists('WP_List_Table')) require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

// Plugin class.
class Integrate_Automate_List_Table extends WP_List_Table {
  /**
   * Construct function
   * Set default settings.
   */
    function __construct(  ) {
        global $status, $page;
        //Set parent defaults
        parent::__construct(array(
            'ajax'     => FALSE,
            'singular' => 'user',
            'plural'   => 'users',
        ));
    }
    
  /**
   * Renders the columns.
   * @since 1.0.0
   */
    public function column_default( $item, $column_name ) {
        $post_excerpt =  $item->post_excerpt ;
        $post_content = '';
        
        switch ($column_name) {
            case 'id':
                $value = $item->ID;
                break;
            case 'IntegrationTitle':
                $value = $item->post_title;
                break;
            case 'eventSource': 
                $value =  $post_excerpt ;
                break;
            case 'customKeys':
                $value = $post_excerpt->Spreadsheet ; 
                break;
            case 'URL':
                $value = '';
                break;
            case 'status':
                $value =  $item->post_status;
                break;
            default:
                $value = '--';
        }
    }

    /**
     * Retrieve the table columns.
     * @since 1.0.0
     * @return array $columns Array of all the list table columns.
     */
    public function get_columns() {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'ID'                => esc_html__( 'ID', 'integrate_automate' ),
            'IntegrationTitle'  => esc_html__( 'Title', 'integrate_automate' ),
            'eventSource'       => esc_html__( 'Event source', 'integrate_automate' ),
            'customKeyValue'    => esc_html__( 'Custom key & value', 'integrate_automate' ),
            'URL'               => esc_html__( 'URL', 'integrate_automate' ),
            'status'            => esc_html__( 'Status', 'integrate_automate' )
        );
        # return
        return $columns;
    }

    # Render the checkbox column.
    public function column_cb( $item ) {
        return '<input type="checkbox" name="id[]" value="' . absint( $item->ID ) . '" />';
    }

    # For Intermigration ID
    public function column_ID( $item ) {
        $ID = ! empty( $item->ID ) ? $item->ID : '--';
        $ID  = sprintf( '<span><strong>%s</strong></span>', esc_html__( $ID  ) );
        # Build all of the row action links.
        $row_actions = array();
        # Edit.
        $row_actions['edit'] = sprintf(
            '<a href="%s" title="%s">%s</a>',
            add_query_arg(
                array(
                    'action' => 'edit',
                    'id'     => $item->ID,
                ),
                admin_url( 'admin.php?page=integrate-automate' )
            ),
            esc_html__( 'Edit This Relation', 'integrate_automate' ),
            esc_html__( 'Edit', 'integrate_automate' )
        );

        # Delete.
        $row_actions['delete'] = sprintf(
            '<a href="%s" class="relation-delete" title="%s">%s</a>',
            wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => 'delete',
                        'id'     => $item->ID,
                    ),
                    admin_url( 'admin.php?page=integrate-automate' )
                ),
                'integrate_automate_delete_relation_nonce'
            ),
            esc_html__( 'Delete this relation', 'integrate_automate' ),
            esc_html__( 'Delete', 'integrate_automate' )
        );

        # Build the row action links and return the value.
        return $ID . $this->row_actions( apply_filters( 'fts_relation_row_actions', $row_actions, $item ) );
    }

    # For Integration Title
    public function column_IntegrationTitle( $item ) {
        $name = ! empty( $item->post_title ) ? $item->post_title : '--';
        $name = sprintf( '<span><strong>%s</strong></span>', esc_html__( $name ) );
        # Build the row action links and return the value.
        return $name;
    }

    # For Event Platform and Event source Column
    public function column_eventSource( $item ) {
        $eventSources  = $item->post_excerpt;
        $eventPlatform   =  get_post_meta( $item->ID, "_integrate_automate_eventsPlatform", TRUE );
        return  "<strong>".  $eventPlatform . "</strong> <br> <hr><strong>".  $eventSources . "</strong>";
    }

    # Key value Column
    public function column_customKeyValue( $item ) {
        $str  = "";
        $head =  get_post_meta( $item->ID, "_integrate_automate_head", TRUE );
        $body =  get_post_meta( $item->ID, "_integrate_automate_body", TRUE );

        if ( ! empty( $head ) ){
            foreach ( $head as $key =>  $value) {
                $str .= $key ." : ". $value . "<br>";
            }
        }

        if ( ! empty( $head ) AND ! empty( $body ) ){
            $str .= "<hr>";
        }
        
        if ( ! empty( $body ) ){
            foreach ( $body as $key => $value) {
                $str .= $key ." : ". $value . "<br>";
            }
        }

       return $str ;
    }

    # Remote URL Column
    public function column_URL( $item ) {
        if ( empty( $item->post_content ) ) {
            _e( "URL is empty !" , "integrate_automate" );
        } else {
            return $item->post_content ;
        }
    }

    # Status Column 
    public function column_status( $item ) {
        if ( $item->post_status == 'publish' ) {
            $actions = "<br> <span title='Enable or Disable the Integrations' onclick='window.location=\"admin.php?page=integrate-automate&action=status&id=".$item->ID."\"'  class='a_activation_checkbox' > <a class='a_activation_checkbox' href='?page=integrate_automate&action=edit&id=".$item->ID."'> <input type='checkbox' name='status' checked=checked > </a></span>" ;
        } else {
            $actions = "<br> <span title='Enable or Disable the Integrations' onclick='window.location=\"admin.php?page=integrate-automate&action=status&id=".$item->ID." \"' class='a_activation_checkbox' > <a class='a_activation_checkbox' href='?page=integrate_automate&action=edit&id=".$item->ID."'> <input type='checkbox' name='status' > </a></span>" ;
        }

        # return
        return   $actions ;
    }

    # Define bulk actions available for our table listing.
    public function get_bulk_actions() {
        $actions = array(
            'delete' => esc_html__( 'Delete', 'integrate_automate' ),
        );
        # return
        return $actions;
    }
    
    # Message to be displayed when there are no relations.
    public function no_items() {
        printf(
            wp_kses(
                __( 'Whoops, you haven\'t created a Integration yet. Want to <a href="%s">give it a go</a>?', 'wpgsi' ),
                array(
                    'a' => array(
                        'href' => array(),
                    ),
                )
            ),
            admin_url( 'admin.php?page=integrate-automate&action=new' )
        );
    }

    # Sortable settings.
    public function get_sortable_columns() {
        return array(
            'ID'  => array( 'ID', TRUE),
        );
    }

    # Query, filter data, handle sorting, pagination, and any other data-manipulation required prior to rendering
    public function prepare_items() {
        # Defining values
        $per_page              = 20;
        $count                 = $this->count();
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $table_data            = get_posts( array( 
                                    'post_type'     => 'integrate_automate',
                                    'post_status'   => 'any',
                                    'posts_per_page'=> -1,
                                    'order' => @$_GET["order"] ? 'ASC' : 'DESC',
                                )); 
        $this->items           = $table_data;
        $this->admin_header();
        # QR
        $this->set_pagination_args(
            array(
                'total_items' => $count,
                'per_page'    => $per_page,
                'total_pages' => ceil( $count / $per_page ),
            )
        );
    }

    # Count Items for Pagination 
    public function count() {
        $integrate_automate_posts = get_posts( array( 
            'post_type'     => 'integrate_automate',
            'post_status'   => 'any',
            'posts_per_page'=> -1,
        )); 
        return count($integrate_automate_posts);
    }

    /**
	 * This Function Will return all the Save integrations from database 
	 * @since      3.4.0
	 * @return     array   	 This Function Will return an array 
	*/
	public function integrate_automate_getIntegrations( ) {
		# Setting Empty Array
		$integrationsArray 		= array();
		# Getting All Posts
		$listOfConnections   	= get_posts( array(
			'post_type'   	 	=> 'integrate_automate',
			'post_status' 		=> array('publish', 'pending'),
			'posts_per_page' 	=> -1
		));
		# integration loop starts
		foreach ( $listOfConnections as $key => $value ) {
			# Compiled to JSON String 
			$post_excerpt = json_decode( $value->post_excerpt, TRUE );
			# if JSON Compiled successfully 
			if ( is_array( $post_excerpt ) AND ! empty( $post_excerpt ) ) {
				$integrationsArray[$key]["IntegrationID"] 	= $value->ID;
				$integrationsArray[$key]["DataSource"] 		= $post_excerpt["DataSource"];
				$integrationsArray[$key]["DataSourceID"] 	= $post_excerpt["DataSourceID"];
				$integrationsArray[$key]["Spreadsheet"] 	= $post_excerpt["Spreadsheet"];
				$integrationsArray[$key]["SpreadsheetID"] 	= $post_excerpt["SpreadsheetID"];
				$integrationsArray[$key]["Status"] 			= $value->post_status;
			} else {
				# Display Error, Because Data is corrected or Empty 
			}
		}
		# integration loop Ends
		# return  array with First Value as Bool and second one is integrationsArray array
		if ( count( $integrationsArray ) ) {
			return array( TRUE, $integrationsArray );
		} else {
			return array( FALSE, $integrationsArray );
		}
	}

    # Check this Function! may be useless 
    public function admin_header() {
        $page = ( isset($_GET['page'] ) ) ? esc_url( $_GET['page'] ) : false;
        # if another page redirect user;
        if ( 'integrate-automate' != $page ){
            return;
        }
        # Column style 
        echo '<style type="text/css">';
        echo '.wp-list-table .column-ID { width: 8%; }';
        echo '.wp-list-table .column-IntegrationTitle  { width: 20%; }';
        echo '.wp-list-table .column-eventSource { width: 15%; }';
        echo '.wp-list-table .column-customKeyValue { width: 25%; }';
        echo '.wp-list-table .column-URL { width: 40%; }';
        echo '.wp-list-table .column-status { width: 5%; }';
        echo '</style>';
    }
}
