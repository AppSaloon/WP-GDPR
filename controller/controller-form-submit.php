<?php

namespace wp_gdpr\controller;

use wp_gdpr\lib\Gdpr_Container;

class Controller_Form_Submit {

	public function __construct() {
		add_action( 'init', array( $this, 'post_request' ) );
	}

	public function validate_data( $submited_data ) {

	}

	public function save_data_in_db( $sanitized_data ) {

	}

	public function post_request() {

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( ! isset( $_REQUEST['gdpr_req'] ) ) {
			return;
		}

		//TODO save in database
	}

}
