<?php


namespace wp_gdpr\controller;

use wp_gdpr\lib\Gdpr_Customtables;
use wp_gdpr\lib\Gdpr_Table_Builder;
use wp_gdpr\lib\Gdpr_Container;

class Controller_Menu_Page {

	/**
	 * Controller_Menu_Page constructor.
	 */
	public function __construct() {
		if ( ! has_action( 'init', array( $this, 'send_email' ) ) ) {
			add_action( 'init', array( $this, 'send_email' ) );
		}
		if ( ! has_action( 'init', array( $this, 'delete_comments' ) ) ) {
			add_action( 'init', array( $this, 'delete_comments' ) );
		}
	}

	/**
	 * delete all comments selected in admin menu in form
	 */
	public function delete_comments() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_REQUEST['gdpr_delete_comments'] ) && isset( $_REQUEST['gdpr_requests'] ) && is_array( $_REQUEST['gdpr_requests'] ) ) {
			foreach ( $_REQUEST['gdpr_requests'] as $single_request_id ) {
				$single_request_id  = sanitize_text_field( $single_request_id );
				$comments_to_delete = $this->find_delete_request_by_id( $single_request_id );

				$this->unserialize_array_and_delete_comments( $comments_to_delete['comments'] );
				$this->update_status_delete_request( $single_request_id, 1 );
				$this->set_notice( __( 'Comments deleted', 'wp_gdpr' ) );
				$to      = $comments_to_delete['email'];
				$subject = __( 'We confirm Your comments deletion request', 'wp_gdpr' );
				$content = $this->get_confirmation_email_content( $comments_to_delete );
				$headers = array( 'Content-Type: text/html; charset=UTF-8' );
				wp_mail( $to, $subject, $content, $headers );
			}
		}
	}

	/**
	 * @param $id
	 *
	 * @return array
	 *
	 * search for request by id in del_request table in db
	 */
	public function find_delete_request_by_id( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . Gdpr_Customtables::DELETE_REQUESTS_TABLE_NAME;

		$query  = "SELECT * FROM $table_name WHERE ID='$id'";
		$result = $wpdb->get_results( $query, ARRAY_A );

		//check if record with this id exists in database
		if ( isset( $result[0] ) ) {
			return $result[0];
		} else {
			return array();
		}
	}

	/**
	 * @param $serialized_comments
	 *
	 * unserialize serialized array with comments_ids
	 */
	public function unserialize_array_and_delete_comments( $serialized_comments ) {
		$comments_to_delete = unserialize( $serialized_comments );
		foreach ( $comments_to_delete as $comment_id ) {
			wp_delete_comment( $comment_id, true );
		}
	}

	/**
	 * delete row by id from table with delete_requests
	 */
	public function update_status_delete_request( $request_id, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . Gdpr_Customtables::DELETE_REQUESTS_TABLE_NAME;
		$where      = array( 'ID' => $request_id );
		$data       = array( 'status' => $status );
		$wpdb->update( $table_name, $data, $where );
	}

	public function set_notice( $message ) {
		/**
		 * set notice
		 */
		$notice = Gdpr_Container::make( 'wp_gdpr\lib\Gdpr_Notice' );
		$notice->set_message( $message );
		$notice->register_notice();
	}

	public function get_confirmation_email_content( $comment_to_delete ) {
		ob_start();
		$date_of_request = $comment_to_delete['timestamp'];
		include_once GDPR_DIR . 'view/admin/email-confirmation-content.php';

		return ob_get_clean();
	}

	/**
	 * build table in menu admin
	 */
	public function build_table_with_requests() {
		$requesting_users = $this->get_requests_from_gdpr_table();

		if ( ! is_array( $requesting_users ) ) {
			return;
		}

		$form_content = $this->get_form_content( $requesting_users );

		//map status from number to string
		$requesting_users = array_map( array( $this, 'map_request_status' ), $requesting_users );
		//add checkbox input in every element with e-mail address
		$requesting_users = array_map( array( $this, 'map_checkboxes_send_email' ), $requesting_users );
		//show table object
		$table = new Gdpr_Table_Builder(
			array(
				__( 'id', 'wp_gdpr' ),
				__( 'e-mail', 'wp_gdpr' ),
				__( 'requested at', 'wp_gdpr' ),
				__( 'status', 'wp_gdpr' ),
				__( 'resend e-mail', 'wp_gdpr' )
			),
			$requesting_users
			, array( $form_content ) );

		//execute
		$table->print_table();
	}

	/**
	 * @return array|null|object
	 * get all records from gdpr_requests table
	 */
	public function get_requests_from_gdpr_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . Gdpr_Customtables::REQUESTS_TABLE_NAME;

		$query = "SELECT * FROM $table_name";

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * @param $requesting_users
	 *
	 * @return string
	 */
	public function get_form_content( $requesting_users ) {
		ob_start();
		$controller = $this;
		include_once GDPR_DIR . 'view/admin/small-form.php';

		return ob_get_clean();
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 * add checkbox element in array
	 */
	public function map_checkboxes_send_email( $data ) {

		$data['checkbox'] = $this->create_single_input_with_email( $data['email'] );

		return $data;
	}

	/**
	 *  create checkbox as delegate of gdpr_form
	 */
	public function create_single_input_with_email( $email ) {

		return '<input type="checkbox" form="gdpr_form"  name="gdpr_emails[]" value="' . $email . '">';
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 *
	 * callback to map status from int to string
	 */
	public function map_request_status( $data ) {

		switch ( $data['status'] ) {
			case 0:
				$data['status'] = __( 'waiting for e-mail', 'wp_gdpr' );
				break;
			case 1:
				$data['status'] = __( 'e-mail send', 'wp_gdpr' );
				break;
			case 2:
				$data['status'] = __( 'url is visited', 'wp_gdpr' );
				break;
		}

		return $data;
	}

	/**
	 * this function is not in use
	 */
	public function print_inputs_with_emails() {
		global $wpdb;

		$table_name = $wpdb->prefix . Gdpr_Customtables::REQUESTS_TABLE_NAME;

		$query = "SELECT * FROM $table_name";

		$requesting_users = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $requesting_users as $user ) {
			/**
			 * if status is 0
			 * e-mail is not send
			 *
			 */
			if ( $user['status'] == 0 ) {
				echo '<input hidden name="gdpr_emails[]" value="' . $user['email'] . '">';
			}
		}

	}

	/**
	 * send e-mails when POST request
	 */
	public function send_email() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_REQUEST['gdpr_emails'] ) && is_array( $_REQUEST['gdpr_emails'] ) ) {
			foreach ( $_REQUEST['gdpr_emails'] as $single_address ) {
				$single_address = sanitize_email( $single_address );
				$to             = $single_address;
				$to             = $this->add_administrator_to_receivers( $to );
				$subject        = __( 'Data request', 'wp_gdpr' );
				$request        = $this->get_request_gdpr_by_email( $single_address );
				$headers        = array( 'Content-Type: text/html; charset=UTF-8' );

				if ( ! $request ) {
					return;
				}

				$content = $this->get_email_content( $request[0]['email'], $request[0]['timestamp'] );

				$this->set_notice( __( 'E-mail send', 'wp_gdpr' ) );

				wp_mail( $to, $subject, $content, $headers );

				$this->update_gdpr_request_status( $single_address );
			}
		}
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
	 * @return array|null|object
	 * get all records from gdpr_requests table
	 */
	public function get_request_gdpr_by_email( $email ) {
		global $wpdb;

		if ( ! $email = sanitize_email( $email ) ) {
			return;
		}


		$query = "SELECT * FROM {$wpdb->prefix}gdpr_requests WHERE email='$email'";

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * @param $single_adress
	 *
	 * @return string content of e-mail
	 *
	 */
	public function get_email_content( $email, $timestamp ) {
		ob_start();
		$url = $this->create_unique_url( $email, $timestamp );
		include GDPR_DIR . 'view/front/email-template.php';

		return ob_get_clean();
	}

	/**
	 * @param $email_address
	 *
	 * @return string
	 * create url
	 * encode gdpr#example@email.com into base64
	 */
	public function create_unique_url( $email, $timestamp ) {
		return home_url() . '/' . base64_encode( 'gdpr#' . $email . '#' . base64_encode( $timestamp ) );
	}

	public function update_gdpr_request_status( $email ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'gdpr_requests';

		$wpdb->update( $table_name, array( 'status' => 1 ), array( 'email' => $email ) );
	}

	/**
	 * search for plugins
	 */
	public function build_table_with_delete_requests() {

		global $wpdb;
		$table_name = $wpdb->prefix . Gdpr_Customtables::DELETE_REQUESTS_TABLE_NAME;

		$query = "SELECT * FROM $table_name";

		$requests = $wpdb->get_results( $query, ARRAY_A );
		$requests = array_map( array( $this, 'add_delete_checkbox' ), $requests );
		$requests = array_map( array( $this, 'reduce_comments_to_string' ), $requests );
		$requests = array_map( array( $this, 'map_status' ), $requests );

		$table = new Gdpr_Table_Builder(
			array(
				__( 'id', 'wp_gdrp' ),
				__( 'e-mail', 'wp_gdrp' ),
				__( 'comments(ID)', 'wp_gdrp' ),
				__( 'requested at', 'wp_gdrp' ),
				__( 'status', 'wp_gdrp' ),
				__( 'delete', 'wp_gdrp' )
			),
			$requests
			, array( $this->get_delete_form_content() ) );

		$table->print_table();

	}

	/**
	 *
	 * @return string
	 */
	public function get_delete_form_content() {
		ob_start();
		$controller = $this;
		include_once GDPR_DIR . 'view/admin/delete-comments-form.php';

		return ob_get_clean();
	}

	public function map_status( $request ) {
		switch ( $request['status'] ) {
			case 0:
				$request['status'] = __( 'waiting to delete', 'wp_gdpr' );
				break;
			case 1:
				$request['status'] = __( 'deleted', 'wp_gdpr' );
				break;
		}

		return $request;
	}

	public function add_delete_checkbox( $request ) {
		if ( '0' === $request['status'] ) {
			$request['checkbox'] = $this->create_checkbox_for_single_delete_row( $request['ID'] );
		} else {
			$request['checkbox'] = __( 'deleted', 'wp_gdpr' );
		}

		return $request;
	}

	public function create_checkbox_for_single_delete_row( $id ) {
		return '<input type="checkbox" form="gdpr_admin_del_comments_form"  name="gdpr_requests[]" value="' . $id . '">';
	}

	public function reduce_comments_to_string( $item ) {
		$item['comments'] = array_reduce( unserialize( $item['comments'] ), function ( $carry, $item ) {
			return $carry . $item . ",";
		} );
		$item['comments'] = substr( $item['comments'], 0, - 1 );

		return $item;

	}

	/**
	 * search for plugins
	 */
	public function build_table_with_plugins() {

		$plugins = $this->get_plugins_array();

		$table = new Gdpr_Table_Builder(
			array( __( 'plugin name', 'wp_gdpr' ) ),
			$plugins
			, array() );

		$table->print_table();
	}

	/**
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function filter_plugins( $plugins ) {
		return array_map( function ( $data ) {
			if ( isset( $data['name'] ) ) {
				return array( $data['name'] );
			}else{
				return array('empty');
			}
		}, $plugins );
	}

	/**
	 * @return array|bool|mixed|object|string
	 */
	public function get_plugins_array() {
		if ( is_file( GDPR_DIR . 'assets/json/plugins.json' ) ) {
			$plugins = file_get_contents( GDPR_DIR . 'assets/json/plugins.json' );
			$plugins = json_decode( $plugins, true );
		} else {
			$plugins = array();
		}

		$plugins = $this->filter_plugins( $plugins );

		return $plugins;
	}
}
