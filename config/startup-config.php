<?php

namespace wp_gdpr\config;


use wp_gdpr\lib\Gdpr_Container;

class Startup_Config {

	public function __construct() {
		$this->execute_on_script_shutdown();
		$this->basic_config();
	}

	/**
	 * add Logging when shutdown script
	 */
	public function execute_on_script_shutdown() {
		if ( ! has_action( 'shutdown', array( 'wp_gdpr\lib\Appsaloon_Log', 'log_to_database' ) ) ) {

			add_action( 'shutdown', array( 'wp_gdpr\lib\Appsaloon_Log', 'log_to_database' ) );
		}
	}

	public function basic_config() {
		Gdpr_Container::make( 'wp_gdpr\lib\Appsaloon_Menu_Backend' );

		$this->create_page();
	}

	/**
	 * create page with shortcode
	 */
	public function create_page() {
		if ( true  === get_option( 'gdpr_page' , true) ) {
			add_action( 'init', function () {
				wp_insert_post( array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'GDPR - Request personal data',
					'post_content' => '[REQ_CRED_FORM]'
				) );
			} );

			update_option( 'gdpr_page', 1 );
		}
	}
}
