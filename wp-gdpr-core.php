<?php
/**
 * WP GDPR
 *
 * Help to handle gdpr regulations
 *
 * @package   WP GDPR CORE
 * @author    AppSaloon
 * @license   proprietary
 * @link      https://wp-gdpr.eu
 * @copyright 2017 wp-gdpr
 *
 * @wordpress-plugin
 * Plugin Name:       WP GDPR
 * Description:       Help to handle gdpr regulations.
 * Version:           1.0.0
 * Text Domain:       wp_gdpr
 * Domain Path:       /languages
 * Author:            AppSaloon
 * Author URI:        https://www.appsaloon.be
 */

namespace wp_gdpr;

define( 'GDPR_DIR', plugin_dir_path( __FILE__ ) );
define( 'GDPR_URL', plugin_dir_url( __FILE__ ) );

require_once GDPR_DIR . 'lib/gdpr-autoloader.php';

//include to register custom table on plugin activation
include_once GDPR_DIR . 'lib/gdpr-customtables.php';

use wp_gdpr\lib\Gdpr_Container;


class Wp_Gdpr_Core {

	const FORM_SHORTCODE_NAME = 'REQ_CRED_FORM';

	public $request_form_inputs;

	public function __construct() {
		//list of inputs in request form
		$this->request_form_inputs = array(
			'email'    => 'required',
			'gdpr_req' => 'required'
		);
		$this->run();
		$this->execute_on_plugin_activation();
	}

	public function run() {
		Gdpr_Container::make( 'wp_gdpr\config\Startup_Config' );
		Gdpr_Container::make( 'wp_gdpr\controller\Controller_Credentials_Request', self::FORM_SHORTCODE_NAME );
		Gdpr_Container::make( 'wp_gdpr\controller\Controller_Comments' );
		Gdpr_Container::make( 'wp_gdpr\controller\Controller_Form_Submit', $this->request_form_inputs );
		Gdpr_Container::make( 'wp_gdpr\controller\Controller_Menu_Page' );
	}

	public function execute_on_plugin_activation() {
		register_activation_hook( __FILE__, array( 'wp_gdpr\lib\Gdpr_Customtables', 'create_custom_tables' ) );
	}

}


new Wp_Gdpr_Core();
