<?php

namespace wp_gdpr\model;

use wp_gdpr\lib\Gdpr_Customtables;
use wp_gdpr\lib\Gdpr_Container;

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

		$table_name        = $wpdb->prefix . Gdpr_Customtables::REQUESTS_TABLE_NAME;
		$single_address    = sanitize_email( $_REQUEST['email'] );
		$time_of_insertion = current_time( 'mysql' );

		$wpdb->insert(
			$table_name,
			array(
				'email'     => $single_address,
				'status'    => 1,
				'timestamp' => $time_of_insertion
			)
		);

		$this->send_email( $single_address, $time_of_insertion );
	}

	/**
	 * @param $single_address
	 * @param $time_of_insertion
	 */
	public function send_email( $single_address, $time_of_insertion ) {
		$to         = $single_address;
		$to         = $this->add_administrator_to_receivers( $to );
		$subject    = __( 'Data request', 'wp_gdpr' );
		$controller = Gdpr_Container::make( 'wp_gdpr\controller\Controller_Menu_Page' );
		$content    = $controller->get_email_content( $single_address, $time_of_insertion );
		$headers    = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $to, $subject, $content, $headers );
	}

	public function add_administrator_to_receivers( $to ) {
		$admin_email = get_option( 'admin_email', true );
		if ( $admin_email ) {
			return $to . ',' . $admin_email;
		} else {
			return $to;
		}
	}

	/**
	 *  do nothing when validation fail
	 */
	public function after_failure_validation( $list_of_inputs ) {
		//do nothing
	}
}
