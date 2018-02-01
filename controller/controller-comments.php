<?php

namespace wp_gdpr\controller;

use wp_gdpr\lib\Gdpr_Customtables;
use wp_gdpr\lib\Gdpr_Container;
use wp_gdpr\lib\Gdpr_Table_Builder;

class Controller_Comments {
	const CSV_NAME = 'comments_csv';

	/**
	 * @var $email_request string
	 * this e-mail is used to decode and encode unique url
	 */
	public $email_request;
	public $message;
	public $register_here;

	public function __construct() {
		$this->redirect_template();
		$page_slug = trim( $_SERVER["REQUEST_URI"], '/' );
		if ( strpos( $page_slug, 'gdpr' ) !== false ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_style' ), 10 );
		}
		add_action( 'init', array( $this, 'save_delete_request' ) );
		add_action( 'init', array( $this, 'download_csv' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_ajax_wp_gdpr', array( $this, 'wp_gdpr' ) );
		add_action( 'wp_ajax_nopriv_wp_gdpr', array( $this, 'wp_gdpr' ) );
	}

	/**
	 * redirect template when GET request for unique url
	 */
	public function redirect_template() {
		if ( $this->decode_url_request() ) {
			add_action( 'template_redirect', array( $this, 'get_template' ) );
			$this->register_here = true;
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

	public function wp_gdpr() {
		switch ( $_REQUEST['action_switch'] ) {
			case 'edit_comment':

				$field      = sanitize_text_field( $_REQUEST['input_name'] );
				$new_value  = $_REQUEST['new_value'];
				$comment_id = sanitize_text_field( $_REQUEST['comment_id'] );

				//when id is not a number
				if ( ! is_numeric( $comment_id ) ) {
					wp_send_json( __( 'Something went wrong.', 'wp_gdpr' ) );
				}

				//when email update
				if ( 'comment_author_email' === $field ) {
					$new_value = sanitize_email( $new_value );
					if ( empty ( $new_value ) ) {
						wp_send_json( '<h3>' . __( 'Email is not valid', 'wp_gdpr' ) . '</h3>' );
					}
					//when other inputs edit
				} else {
					$new_value = sanitize_text_field( $new_value );
				}


				//create args of comment to update
				$comment = $this->build_comment( $comment_id, $field, $new_value );

				wp_update_comment(
					$comment
				);

				//send feedback
				wp_send_json( '<h3>' . __( 'Comment is changed', 'wp_gdpr' ) . '</h3>' );
				break;
		}
	}

	/**
	 * @param $comment_id
	 * @param $field
	 * @param $new_value
	 *
	 * @return array
	 */
	public function build_comment( $comment_id, $field, $new_value ) {
		$comment = array(
			'comment_ID' => $comment_id,
			$field       => $new_value,
		);

		//if setting require approve of comment set as unapproved
		if ( 1 == get_option( 'comment_moderation', true ) ) {
			$comment['comment_approved'] = 0;
		}

		return $comment;
	}

	public function load_scripts() {
		if ( $this->register_here == true ) {
			wp_enqueue_script( 'gdpr-main-js', GDPR_URL . 'assets/js/update_comments.js', array( 'jquery' ), '', false );
			wp_localize_script( 'gdpr-main-js', 'localized_object', array(
				'url'    => admin_url( 'admin-ajax.php' ),
				'action' => 'wp_gdpr'
			) );
		}
	}

	public function download_csv() {
		//DOWNLOAD CSV
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_REQUEST['gdpr_download_csv'] ) ) {
			//save in database
			if ( isset( $_REQUEST['gdpr_email'] ) ) {
				$user_email = sanitize_email( $_REQUEST['gdpr_email'] );
			}

			global $wpdb;

			//DOWNLOAD all
			if ( ! empty( $user_email ) ) {
				$all_comments = $this->get_all_comments_by_author( $user_email );
			}

			if ( ! empty( $all_comments ) ) {
				//create csv object and download comments
				$csv = Gdpr_Container::make( 'wp_gdpr\model\Csv_Downloader' );
				$csv->add_headers(
					array(
						__( 'name', 'wp_gdpr' ),
						__( 'email', 'wp_gdpr' ),
						__( 'comment', 'wp_gdpr' ),
						__( 'website', 'wp_gdpr' ),
					)
				);
				$csv->set_filename( self::CSV_NAME );
				$csv->map_comments_into_csv_data( $all_comments );
				$csv->download_csv();
			}
		}
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
	 * get template to show comments and other data
	 * about user with requested e-mail address
	 * set variable $controller to use in template
	 */
	public function get_template() {
		$controller = $this;
		include_once GDPR_DIR . 'view/front/gdpr-template.php';
		wp_die();
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
			array(
				__( 'comment date', 'wp_gdpr' ),
				__( 'author email', 'wp_gdpr' ),
				__( 'author name', 'wp_gdpr' ),
				__( 'comment content', 'wp_gdpr' ),
				__( 'post ID', 'wp_gdpr' ),
				__( 'delete', 'wp_gdpr' )
			),
			$comments
			, array( $this->get_form_content() ), 'gdpr_comments_table' );

		$table->print_table();
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
				'email'           => $this->change_into_input( $data->comment_author_email, 'comment_author_email', $data->comment_ID ),
				'name'            => $this->change_into_input( $data->comment_author, 'comment_author', $data->comment_ID ),
				'comment_content' => $this->change_into_textarea( $data->comment_content, 'comment_content', $data->comment_ID ),
				'comment_post_ID' => $data->comment_post_ID,
				'comment_ID'      => $data->comment_ID
			);
		}, $comments );

		return $comments;
	}

	public function change_into_input( $val, $name, $id ) {
		return '<input type="text" data-id="' . $id . '" data-name="' . $name . '" class="js-comment-edit" value="' . $val . '">';
	}

	public function change_into_textarea( $val, $name, $id ) {
		return '<textarea data-id="' . $id . '" data-name="' . $name . '" class="js-comment-edit"> ' . $val . '</textarea>';
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

	public function load_style() {
		wp_enqueue_style( 'gdpr-main-css', GDPR_URL . 'assets/css/main.css' );
	}

	public function save_delete_request() {

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_REQUEST["send_gdp_del_request"] ) && isset( $_REQUEST['gdpr_delete_comments'] ) && is_array( $_REQUEST['gdpr_delete_comments'] ) ) {
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
					'comments'  => serialize( $comments_ids ),
					'status'    => 0,
					'timestamp' => current_time( 'mysql' )
				)
			);
			$this->message = '<h3>' . __( "The site administrator received your request. Thank You.", "wp_gdpr" ) . '</h3>';
			//TODO email to admin
		}

	}

	/**
	 * @param $comment
	 *
	 * @return bool
	 *
	 * check if input value is numeric
	 */
	public function sanitize_comments_input( $comment ) {
		return is_numeric( $comment );
	}
}
