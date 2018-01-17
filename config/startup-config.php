<?php

namespace wp_gdpr\config;


use wp_gdpr\lib\Gdpr_Container;

class Startup_Config {

	public function __construct() {
		$this->execute_on_script_shutdown();
		$this->basic_config();
	}

	public function basic_config( ) {
		Gdpr_Container::make('wp_gdpr\lib\Appsaloon_Menu_Backend');
	}

	/**
	 * add Logging when shutdown script
	 */
	public function execute_on_script_shutdown() {
		if ( ! has_action( 'shutdown', array( 'wp_gdpr\lib\Appsaloon_Log', 'log_to_database' ) ) ) {

			add_action( 'shutdown', array( 'wp_gdpr\lib\Appsaloon_Log', 'log_to_database' ) );
		}
	}
}
