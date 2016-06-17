<?php
/*
Plugin Name: Salesforce REST API
Description: Defines an API that enables WordPress to interact with the Salesforce REST API.
Version: 0.0.1
Author: Jonathan Stegall
Author URI: http://code.minnpost.com
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: salesforce-rest-api
*/

// Start up the plugin
class Salesforce_Rest_API {

	/**
	* @var object
	*/
	private $wpdb;

	/**
	* @var array
	*/
	private $login_credentials;

	/**
	* @var string
	*/
	private $text_domain;

	/**
	* @var string
	*/
	private $version;

	/**
	 * Static property to hold our singleton instance
	 * todo: figure out what to do with this
	 *
	 */
	//static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	public function __construct() {
		require_once( plugin_dir_path( __FILE__ ) . 'classes/salesforce.php' );
		global $wpdb;

		$this->wpdb = &$wpdb;
		$this->version = '0.0.1';
		$this->login_credentials = $this->get_login_credentials();
		$this->text_domain = 'salesforce-rest-api';

		$this->activate( $this->wpdb, $this->version, $this->text_domain );
		$this->deactivate( $this->wpdb, $this->version, $this->text_domain );
		$this->load_admin( $this->wpdb, $this->version, $this->login_credentials, $this->text_domain );

		//add_action		( 'plugins_loaded', 					array( $this, 'textdomain'				) 			);
		//add_action		( 'admin_enqueue_scripts',				array( $this, 'admin_scripts'			)			);
		//add_action		( 'wp_enqueue_scripts',					array( $this, 'front_scripts'			),	10		);
	}

	/**
     * What to do upon activation of the plugin
     */
	function activate( &$wpdb, $version, $text_domain ) {
		require_once plugin_dir_path( __FILE__ ) . 'classes/activate.php';
		$activate = new Wordpress_Salesforce_Activate( $wpdb, $version, $text_domain );
	}

	/**
     * What to do upon deactivation of the plugin
     */
	function deactivate( &$wpdb, $version, $text_domain ) {
		require_once plugin_dir_path( __FILE__ ) . 'classes/deactivate.php';
		$deactivate = new Wordpress_Salesforce_Deactivate( $wpdb, $version, $text_domain );
	}

	/**
	* load the admin class
	* also creates admin menu, unless the plugin that calls this library has indicated that it has its own menu
	*
	* @param array $login_credentials
	* @param array $parent_settings
	* @throws \Exception
	*/
    private function load_admin( &$wpdb, $version, $login_credentials, $text_domain ) {
    	require_once( plugin_dir_path( __FILE__ ) . 'classes/admin.php' );
    	$admin = new Wordpress_Salesforce_Admin( $wpdb, $version, $login_credentials, $text_domain );
    	add_action( 'admin_menu', array( $admin, 'create_admin_menu' ) );
    }

	/**
	* Admin styles
	*
	* @return void
	*/
	public function admin_scripts() {
		//wp_enqueue_style( 'sfrest-admin', plugins_url('lib/css/admin.css', __FILE__), array(), WPCMN_VER, 'all' );
	}

	/**
	 * load textdomain
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( $this->text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
     * Get the pre-login Salesforce credentials
     * These depend on WordPress settings or constants
     * todo: need to investigate if the naming makes sense
     *
     * @return array $login_credentials
     * @throws \Exception
     */
    private function get_login_credentials() {

    	$consumer_key = defined('SALESFORCE_CONSUMER_KEY') ? SALESFORCE_CONSUMER_KEY : get_option( 'salesforce_api_consumer_key', '' );
    	$consumer_secret = defined('SALESFORCE_CONSUMER_SECRET') ? SALESFORCE_CONSUMER_SECRET : get_option( 'salesforce_api_consumer_secret', '' );
    	$callback_url = defined('SALESFORCE_CALLBACK_URL') ? SALESFORCE_CALLBACK_URL : get_option( 'salesforce_api_callback_url', '' );
    	$login_base_url = defined('SALESFORCE_LOGIN_BASE_URL') ? SALESFORCE_LOGIN_BASE_URL : get_option( 'salesforce_api_login_base_url', '' );
    	$authorize_url_path = defined('SALESFORCE_AUTHORIZE_URL_PATH') ? SALESFORCE_AUTHORIZE_URL_PATH : get_option( 'salesforce_api_authorize_url_path', '' );
    	$token_url_path = defined('SALESFORCE_TOKEN_URL_PATH') ? SALESFORCE_TOKEN_URL_PATH : get_option( 'salesforce_api_token_url_path', '' );
    	$api_version = defined('SALESFORCE_API_VERSION') ? SALESFORCE_API_VERSION : get_option( 'salesforce_api_version', '' );

    	$login_credentials = array(
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
            'callback_url' => $callback_url,
            'login_url' => $login_base_url,
            'authorize_path' => $authorize_url_path,
            'token_path' => $token_url_path,
            'rest_api_version' => $api_version
        );

    	return $login_credentials;

    }

/// end class
}
// Instantiate our class
$Salesforce_Rest_API = new Salesforce_Rest_API();