<?php


namespace wp_gdpr\controller;

use wp_gdpr\lib\Appsaloon_Customtables;
use wp_gdpr\lib\Appsaloon_Table_Builder;
use wp_gdpr\lib\Gdpr_Container;

class Controller_Menu_Page {

	/**
	 * Controller_Menu_Page constructor.
	 */
	public function __construct() {
		if ( ! has_action( 'init', array( $this, 'send_email' ) ) ) {
			add_action( 'init', array( $this, 'send_email' ) );
		}
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
		$table = new Appsaloon_Table_Builder(
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

		$table_name = $wpdb->prefix . Appsaloon_Customtables::REQUESTS_TABLE_NAME;

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

		$query = "SELECT * FROM {$wpdb->prefix}gdpr_requests";

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
				$subject        = __( 'Data request', 'wp_gdpr' );
				//TODO prevent duplicates
				$request = $this->get_request_gdpr_by_email( $single_address );

				if ( ! $request ) {
					return;
				}

				$content = $this->get_email_content( $request[0] );

				$this->set_notice();


				wp_mail( $to, $subject, $content, array() );

				$this->update_gdpr_request_status( $single_address );
			}
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


		$query = "SELECT * FROM {$wpdb->prefix}gdpr_requests WHERE email='$email' AND status='0'";

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * @param $single_adress
	 *
	 * @return string content of e-mail
	 *
	 */
	public function get_email_content( $single_request ) {
		ob_start();
		$url = $this->create_unique_url( $single_request );
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
	public function create_unique_url( $request ) {
		return home_url() . '/' . base64_encode( 'gdpr#' . $request['email'] . '#' . base64_encode( $request['timestamp'] ) );
	}

	public function set_notice() {
		/**
		 * set notice
		 */
		$notice = Gdpr_Container::make( 'wp_gdpr\lib\Appsaloon_Notice' );
		$notice->set_message( __( 'E-mail send', 'wp_gdpr' ) );
		$notice->register_notice();
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
		$table_name = $wpdb->prefix . Appsaloon_Customtables::DELETE_REQUESTS_TABLE_NAME;

		$query = "SELECT * FROM $table_name";

		$requests = $wpdb->get_results( $query, ARRAY_A );
		$requests = array_map( array( $this, 'add_delete_checkbox' ), $requests );
		$requests = array_map( array( $this, 'reduce_comments_to_string' ), $requests );

		$table = new Appsaloon_Table_Builder(
			array(
				__( 'id', 'wp_gdrp' ),
				__( 'e-mail', 'wp_gdrp' ),
				__( 'comments(ID)', 'wp_gdrp' ),
				__( 'requested at', 'wp_gdrp' ),
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

	public function add_delete_checkbox( $request ) {
		$request['checkbox'] = $this->create_checkbox_for_single_delete_row( $request['ID'] );

		return $request;
	}

	public function create_checkbox_for_single_delete_row( $id ) {
		return '<input type="checkbox" form="gdpr_form"  name="gdpr_emails[]" value="' . $id . '">';
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

		$plugins = get_plugins();
		$plugins = array_map( function ( $k ) {
			return array( $k['Name'] );
		}, $plugins );


		$plugins = $this->filter_plugins( $plugins );

		$table = new Appsaloon_Table_Builder(
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

		return array_filter( $plugins, function ( $data ) {
			$plugin_name = strtolower( $data[0] );
			foreach ( array( 'woocommerce', 'gdpr', 'gravity' ) as $pl ) {
				if ( strpos( $plugin_name, $pl ) !== false ) {
					return true;
				}
			}
		} );
	}
}
