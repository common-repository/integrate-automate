<?php

/**
 * Provide a admin area view for the plugin.
 * This file is used to markup the admin-facing aspects of the plugin.
 * @link       www.javmah.com
 * @since      1.0.0
 * @package    integrate_automate
 * @subpackage integrate_automate/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1>
        <span class="dashicons dashicons-networking" style="vertical-align: middle;" ></span> 
        <?php esc_attr_e( 'New Intermigration.', 'integrate_automate' ); ?>
    </h1>

    <div id="new_connection">
        <div id="post-body" class="metabox-holder ">
            <div id="vuejs-app-div" >
                <br>
                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" >
                   
                    <input type="hidden" name="action" value="integrate_automate_integration">
                    <input type="hidden" name="status" value="new_integration" />

                    <div id='FirstPart'>
                        <!-- <h2><?php esc_attr_e( 'Select Event & Spreadsheet :', 'integrate_automate' ); ?></h2> -->
                        <table class="widefat">
                            <tbody>
                                <tr class="alternate">
                                    <td class="row-title">  <label for="tablecell"> <?php esc_attr_e( 'Intermigration title *', 'integrate_automate' ); ?> </label>  </td>
                                    <td> <input type="text" name="integrationTitle" id='integrationTitle' class="large-text" placeholder="<?php _e( 'Enter intermigration title here', 'integrate_automate'); ?>" required="required"> </td>
                                </tr>

                                <tr>
                                    <td class="row-title"> <label for="tablecell"> <?php esc_attr_e('Event source *', 'integrate_automate'); ?> </label> </td>
                                    <td>
                                        <select name="eventSource" id="eventSource" required="required" style="width:79%;" >
                                            <option value="" disabled selected > <?php _e('Select event ...', 'integrate_automate'); ?> </option>
                                            <?php
                                                foreach ( $events as $key => $value ) {
                                                    echo"<option value='" . $key . "'>" . $value . "</option>";
                                                }
                                            ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="alternate">
                                    <td class="row-title"> <label for="tablecell"> Custom data for request Head <sup>1</sup> </label> </td>
                                    <td>
                                        <input type="text" class="large-text"  name="customFieldsInHead"  placeholder="<?php _e( 'Please insert valid JSON  e.g. { &#8220;Accept-Charset&#8221; : &#8220;utf-8&#8221;, &#8220;If-Match&#8221; : &#8220;737060cd8c284d8a&#8221; } ', 'integrate_automate'); ?>"> 
                                    </td>
                                </tr>

                                <tr>
                                    <td class="row-title"> <label for="tablecell"> <?php esc_attr_e('Custom data for request Body', 'integrate_automate'); ?> </label> </td>
                                    <td>
                                        <input type="text" class="large-text"  name="customFieldsInBody"  placeholder="<?php _e( 'Please insert valid JSON  e.g. { &#8220;ID&#8221; : &#8220;182943&#8221;, &#8220;location&#8221; : &#8220;TX&#8221; } ', 'integrate_automate'); ?>"> 
                                    </td>
                                </tr>

                                <tr class="alternate">
                                    <td class="row-title">
                                        <label for="tablecell"> <?php esc_attr_e( 'Request Webhook URL *', 'integrate_automate' ); ?> </label>
                                    </td>
                                    <td id="webHookUrl">
                                       <input type="url" class="large-text"  name="webHookUrl" id='webHookUrl' placeholder="<?php _e( 'Intermigration Webhook URL', 'integrate_automate'); ?>" required="required"> 
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    <br class="clear">
                    <!-- Save integration Starts -->
                    <p>
                        <input class="button-primary" type="submit" name="saveRelation" value="<?php esc_attr_e( 'Save intermigration', 'integrate_automate' ); ?>" />
                        <a class="button-secondary" style="color: red" href="<?php echo admin_url('admin.php?page=integrate_automate')?>" class="button-secondary"> <?php esc_attr_e( 'Cancel', 'integrate_automate' ); ?></a>
                    </p>
                    <!-- Save integration Ends -->
                </form>
            </div>                         
        </div>
        <!-- #post-body .metabox-holder .columns-2 -->
        <br class="clear">
    </div>
    <!-- #poststuff -->



    
    <!--  This line should be removed from General Installation  -->
    <br><br>
    <p><i>1. Please use a <a href='https://en.wikipedia.org/wiki/List_of_HTTP_header_fields' style='text-decoration: none;'> valid HTTP header </a> for custom header data or keep it empty, Invalid header data may cause Failure in remote requests. </i></p>

</div> <!-- .wrap -->


