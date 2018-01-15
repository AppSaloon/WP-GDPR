<?php

namespace wp_gdpr\model;

use wp_gdpr\lib\Appsaloon_Customtables;

class Request_Form extends Form_Validation_Model {

	/**
	 * Request_Form constructor.
	 */
	public function __construct( $list_of_inputs ) {
		//here add functions to sanitize every input
		add_filter( 'gdpr_sanitize_email', array( $this, 'sanitize_email' ), 10 );

		parent::__construct( $list_of_inputs );
	}

	public function sanitize_email( $input_value ) {
		return sanitize_email( $input_value );
	}

	public function after_successful_validation( $list_of_inputs ) {
		//save in database
		global $wpdb;

		$table_name = $wpdb->prefix . Appsaloon_Customtables::REQUESTS_TABLE_NAME;

		$wpdb->insert(
			$table_name,
			array(
				'email'     => $_REQUEST['email'],
				'name'      => $_REQUEST['username'],
				'timestamp' => current_time( 'mysql' )
			)
		);
	}

	public function after_failure_validation( $list_of_inputs ) {
		//do nothing
	}
}
