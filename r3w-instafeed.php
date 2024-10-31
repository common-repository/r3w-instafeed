<?php
/**
 * Plugin Name: R3Web InstaFeed
 * Plugin URI: http://mypluginuri.com/
 * Description: R3Web InstaFeed Instagram
 * Version: 1.0
 * Author: Rodrigo Barreto
 * Author URI: r3web.com.br
 * License: GPLv2 or later
 * Text Domain: instafeed
 */

require_once 'r3w-instafeed-widget.php';

class R3wInstaFeedPlugin{

    public function __construct() {
        add_action( 'admin_init', array($this, 'settings') );
        add_action( 'admin_menu', array($this, 'createMenuItem') );
        add_action( 'wp_enqueue_scripts', array($this, 'addScripts') );
        add_action( 'widgets_init', array($this, 'load_widget') );
    }

    public function setDefaults(){
        $content_username = get_option('r3wif-username');
        $content_userId = get_option('r3wif-userId');

        if( empty($content_userId) && !empty($content_username) )
            $this->getUserId($content_username);

    }


    function load_widget() {
        register_widget( 'R3wInstaFeedWidget' );
    }


    public function getUserId($userName){

        $request  = wp_remote_get( 'https://www.instagram.com/'. $userName .'/?__a=1' );
        $response = wp_remote_retrieve_body( $request );

        $obj = json_decode($response);

        update_option('r3wif-userId', stripslashes($obj->user->id));

    }

    public function getUserInfo($accessToken){
        $endpoint = "https://api.instagram.com/v1/users/self/?access_token=". $accessToken;

        $request  = wp_remote_get( $endpoint );
        $response = wp_remote_retrieve_body( $request );

        $obj = json_decode($response);

        update_option('r3wif-username', stripslashes($obj->data->username));
        update_option('r3wif-userId', stripslashes($obj->data->id));

    }

    public function settings(){

        register_setting('r3wif', 'r3wif-accessToken');
        register_setting('r3wif', 'r3wif-username');
        register_setting('r3wif', 'r3wif-userId');

        $this->setDefaults();

    }

    public function addScripts() {
        wp_register_style( 'r3wif-admin-styles', plugins_url('assets/css/instafeed.css', __FILE__), array(), null, 'all' );
        wp_enqueue_style( 'r3wif-admin-styles' );

        wp_register_script( 'r3wif-admin-script', plugins_url('assets/js/instafeed.min.js', __FILE__), array( 'jquery' ), null, true );
        wp_enqueue_script( 'r3wif-admin-script' );
    }

    public function createMenuItem() {
        add_options_page( 'InstaFeed Options', 'InstaFeed', 'manage_options', 'r3wif', array($this, 'createOptionsPage') );
    }

    public function createOptionsPage() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $new_url = urlencode(admin_url('admin.php?page=r3wif')) . '&response_type=token';

        $accessToken = $_GET['access_token'];

        if(!empty($accessToken)){
            $r3wif_accessToken = $accessToken;
            update_option('r3wif-accessToken', stripslashes($accessToken));
            $this->getUserInfo($accessToken);
        }else{
            $r3wif_accessToken = esc_attr(get_option('r3wif-accessToken'));
        }

        ?>

        <div class="wrap">
            <h2>Settings Page</h2>
            <form method="post" action="options.php">
                <?php settings_fields('r3wif'); ?>
                <?php do_settings_sections('r3wif'); ?>

                <table class="form-table">

                    <p>&nbsp</p>

                    <tr valign="top">
                        <th scope="row">
                            <label for="r3wif-accessToken">Authorize:</label>
                        </th>
                        <td>
                            <a class="button button-primary" href="https://api.instagram.com/oauth/authorize/?client_id=eecaa514331a41098dd39144ca4fb37a&scope=basic+public_content&redirect_uri=http://www.r3web.com.br/instafeed/?return_url=<?php echo $new_url;?>">Login with Instagram</a>
                        </td>
                    </tr>

                    <!-- access token -->
                    <tr valign="top">
                        <th scope="row">
                            <label for="r3wif-accessToken"><?php _e('Access Token', LJMM_PLUGIN_DOMAIN); ?></label>
                        </th>
                        <td>
                            <input readonly="readonly" type="text" id="r3wif-accessToken" name="r3wif-accessToken" class="regular-text" style="width: 400px;" value="<?php echo $r3wif_accessToken ?>">
                        </td>
                    </tr>

                    <!-- username -->
                    <tr valign="top">
                        <th scope="row">
                            <label for="r3wif-username"><?php _e('Username', LJMM_PLUGIN_DOMAIN); ?></label>
                        </th>
                        <td>
                            <?php $r3wif_username = esc_attr(get_option('r3wif-username')); ?>
                            <input readonly="readonly" type="text" id="r3wif-username" name="r3wif-username" class="regular-text" style="width: 400px;" value="<?php echo $r3wif_username ?>">
                        </td>
                    </tr>

                    <!-- userid -->
                    <tr valign="top">
                        <th scope="row">
                            <label for="r3wif-userId"><?php _e('UserId', LJMM_PLUGIN_DOMAIN); ?></label>
                        </th>
                        <td>
                            <?php $r3wif_userId = esc_attr(get_option('r3wif-userId')); ?>
                            <input readonly="readonly" type="text" id="r3wif-userId" name="r3wif-userId" class="regular-text" style="width: 400px;" value="<?php echo $r3wif_userId ?>">
                        </td>
                    </tr>

                </table>
                <?php submit_button(); ?>
            </form>
        </div>

        <?php

    }


    function r3wfi_set_content(){
        // If content is not set, set the default content.
        $content_accessToken = get_option('r3wif-accessToken');
        $content_username = get_option('r3wif-username');
        $content_userid = get_option('r3wif-userId');

        update_option('r3wif-accessToken', stripslashes($content_accessToken));
        update_option('r3wif-username', stripslashes($content_username));
        update_option('r3wif-userId', stripslashes($content_userid));
    }

}


new R3wInstaFeedPlugin;
?>