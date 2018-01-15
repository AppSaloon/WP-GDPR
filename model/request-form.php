<?php

namespace wp_gdpr\model;

class Request_Form extends Form_Validation_Model {
	public function after_successful_validation( $list_of_inputs ) {
		//save in database
	}

	public function after_failure_validation( $list_of_inputs ) {
		//do nothing
	}
}
