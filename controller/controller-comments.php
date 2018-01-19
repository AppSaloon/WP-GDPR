<?php

namespace wp_gdpr\controller;

use wp_gdpr\lib\Gdpr_Customtables;
use wp_gdpr\lib\Gdpr_Container;
use wp_gdpr\lib\Gdpr_Table_Builder;

class Controller_Comments {

	/**
	 * @var $email_request string
	 * this e-mail is used to decode and encode unique url
	 */
	public $email_request;
	public $message;

	public function __construct() {
		$this->redirect_template();
        $page_slug = trim( $_SERVER["REQUEST_URI"] , '/' );
		if (strpos($page_slug, 'gdpr') !== false) {
            add_action('wp_enqueue_scripts', array($this, 'loadStyle'), 10);
        }
		add_action( 'init', array( $this, 'save_delete_request' ) );
	}

	/**
	 * redirect template when GET request for unique url
	 */
	public function redirect_template() {
		if ( $this->decode_url_request() ) {
			add_action( 'template_redirect', array( $this, 'get_template' ) );

			/**
			 * update status to 'url visited'
			 */
			$this->update_gdpr_status( $this->email_request );
		}
	}

	/**
	 * @return bool
	 * example url home.be/gdpr#example@mail.com
	 */
	public function decode_url_request() {
		//remove slash
		$substring = substr( $_SERVER['REQUEST_URI'], 1 );
		//decode base64 result is gdpr#example@mail.com
		$decoded = base64_decode( $substring );
		if ( strpos( $decoded, 'gdpr#' ) !== false ) {
			//explode into array( 'gdpr', 'example@email.com' )
			//get second element from array
			$email               = explode( '#', $decoded )[1];
			$this->email_request = $email;
			global $wpdb;

			$table_name = $wpdb->prefix . 'gdpr_requests';
			$time_stamp = base64_decode( explode( '#', $decoded )[2] );

			$query = "SELECT * FROM $table_name WHERE email='$email' AND timestamp='$time_stamp'";

			return ! empty( $wpdb->get_results( $query ) );
		}

		return false;
	}

	/**
	 * @param $email
	 * update status in custom gdpr_requests table
	 * status 2 is: e-mail send
	 */
	public function update_gdpr_status( $email ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'gdpr_requests';

		$wpdb->update( $table_name, array( 'status' => 2 ), array( 'email' => $email ) );
	}

	/**
	 * get template to show comments and other data
	 * about user with requested e-mail address
	 * set variable $controller to use in template
	 */
	public function get_template() {
		$controller = $this;
		include_once GDPR_DIR . 'view/front/gdpr-template.php';
		die;
	}

	/**
	 * build table with all comments
	 * selected by e-mail address
	 */
	public function create_table_with_comments() {
		$comments = $this->get_all_comments_by_author( $this->email_request );
		$comments = $this->map_comments( $comments );
		$comments = array_map( array( $this, 'add_checkbox' ), $comments );

		$table = new Gdpr_Table_Builder(
			array( __('comment date', 'wp_gdpr'), __('comment content', 'wp_gdpr'), __('post ID', 'wp_gdpr'), __('delete', 'wp_gdpr') ),
			$comments
			, array( $this->get_form_content() ), 'gdpr_comments_table' );

		$table->print_table();
	}

	/**
	 * @param $author_email
	 *
	 * @return array|int
	 * get all comments from default comments table
	 */
	public function get_all_comments_by_author( $author_email ) {
		return get_comments( array( 'author_email' => $author_email ) );
	}

	/**
	 * @param $comments
	 *
	 * @return array
	 */
	public function map_comments( $comments ) {
		$comments = array_map( function ( $data ) {
			return array(
				'comment_date'    => $data->comment_date,
				'comment_content' => $data->comment_content,
				'comment_post_ID' => $data->comment_post_ID,
				'comment_ID'      => $data->comment_ID
			);
		}, $comments );

		return $comments;
	}

	/**
	 *
	 * @return string
	 */
	public function get_form_content() {
		ob_start();
		$email = $this->email_request;
		include_once GDPR_DIR . 'view/admin/small-form-delete-request.php';

		return ob_get_clean();
	}

	public function add_checkbox( $comment ) {
		$comment['checkbox'] = $this->create_single_input_with_comment_id( $comment['comment_ID'] );
		unset( $comment['comment_ID'] );

		return $comment;
	}

	public function create_single_input_with_comment_id( $comment_id ) {
		return '<input type="checkbox" form="wgdpr_delete_comments_form"  name="gdpr_delete_comments[]" value="' . $comment_id . '">';
	}

	public function loadStyle() {
		wp_enqueue_style( 'gdpr-main-css', GDPR_URL . 'assets/css/main.css' );
	}

	public function save_delete_request() {

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_REQUEST["send_gdp_del_request"] ) && isset($_REQUEST['gdpr_delete_comments']) && is_array( $_REQUEST['gdpr_delete_comments'] ) ) {
			//save in database
			global $wpdb;

			$comments_ids = array_filter( $_REQUEST['gdpr_delete_comments'], array(
				$this,
				'sanitize_comments_input'
			) );

			$table_name = $wpdb->prefix . Gdpr_Customtables::DELETE_REQUESTS_TABLE_NAME;

			$wpdb->insert(
				$table_name,
				array(
					'email'     => sanitize_email( $_REQUEST["gdpr_email"] ),
					'comments'    => serialize( $comments_ids),
					'timestamp' => current_time( 'mysql' )
				)
			);
			$this->message = '<h3>Administrator received yor request. Thank You.</h3>';
			//TODO email to admin
		}

	}

	/**
	 * @param $comment
	 * @return bool
	 *
	 * check if input value is numeric
	 */
	public function sanitize_comments_input( $comment ) {
		return is_numeric( $comment );
	}
}
