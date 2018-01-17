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

	/**
	 * @param $input_value
	 *
	 * @return string
	 *
	 * this filter is triggered post_request function in form_validation_model
	 */
	public function sanitize_email( $input_value ) {
		return sanitize_email( $input_value );
	}

	/**
	 * @param $list_of_inputs
	 *
	 * save request info in custom table
	 */
	public function after_successful_validation( $list_of_inputs ) {
		//save in database
		global $wpdb;

		$table_name = $wpdb->prefix . Appsaloon_Customtables::REQUESTS_TABLE_NAME;

		$wpdb->insert(
			$table_name,
			array(
				'email'     => $_REQUEST['email'],
				'status'     => 0,
				'timestamp' => current_time( 'mysql' )
			)
		);
	}

	/**
	 *  do nothing when validation fail
	 */
	public function after_failure_validation( $list_of_inputs ) {
		//do nothing
	}
}
