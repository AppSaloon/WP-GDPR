<?php
/**
 * WP GDPR CORE
 *
 * Help to handle gdpr regulations
 *
 * @package   WP GDPR CORE
 * @author    AppSaloon - Sebastian Kurzynowski
 * @license   proprietary
 * @link      https://appsaloon.be
 * @copyright 2017 AppSaloon Belgium
 *
 * @wordpress-plugin
 * Plugin Name:       WP GDPR CORE
 * Description:       Help to handle gdpr regulations.
 * Version:           1.0.0
 * Author:            Appsaloon - Sebastian Kurzynowski
 * Author URI:        https://www.appsaloon.be
 */

namespace wp_gdpr;

//TODO add dependency injection container
//TODO add log system
//TODO add shortcode
//TODO add frontend form

define( 'GDPR_DIR', plugin_dir_path( __FILE__ ) );
define( 'GDPR_URL', plugin_dir_url( __FILE__ ) );

require_once GDPR_DIR . 'lib/appsaloon-autoloader.php';

use wp_gdpr\lib\Gdpr_Container;



class Wp_Gdpr_Core {

	const FORM_SHORTCODE_NAME = 'REQ_CRED_FORM';

	public function __construct() {
		$this->run();
	}

	public function run() {
		Gdpr_Container::make('wp_gdpr\config\Startup_Config');
		Gdpr_Container::make('wp_gdpr\controller\Controller_Credentials_Request',  self::FORM_SHORTCODE_NAME );
	}

	public function execute_on_plugin_activation() {

	}
}

new Wp_Gdpr_Core();
