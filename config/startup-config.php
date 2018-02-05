<?php

namespace wp_gdpr\config;


use wp_gdpr\lib\Gdpr_Container;
use wp_gdpr\lib\Gdpr_Translation;

class Startup_Config {

	public function __construct() {
	    $this->include_translation();
		$this->execute_on_script_shutdown();
		$this->basic_config();
	}

    /**
     * include translation
     */
	public function include_translation()
    {
        new Gdpr_Translation();
    }

	/**
	 * add Logging when shutdown script
	 */
	public function execute_on_script_shutdown() {
		if ( ! has_action( 'shutdown', array( 'wp_gdpr\lib\Gdpr_Log', 'log_to_database' ) ) ) {

			add_action( 'shutdown', array( 'wp_gdpr\lib\Gdpr_Log', 'log_to_database' ) );
		}
	}

	public function basic_config() {
		Gdpr_Container::make( 'wp_gdpr\lib\Gdpr_Menu_Backend' );

		add_action('admin_init', array(  $this, 'create_page'), 1);
	}

	/**
	 * create page with shortcode
	 */
	public function create_page() {
		if ( false === get_option( 'gdpr_page' ) ) {
			add_action( 'admin_init', function () {
				wp_insert_post( array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => __('GDPR - Request personal data', 'wp_gdpr'),
					'post_content' => '[REQ_CRED_FORM]'
				) );
			}, 100 );

			update_option( 'gdpr_page', 1 );
		}
	}
}


