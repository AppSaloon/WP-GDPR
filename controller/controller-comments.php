<?php

namespace wp_gdpr\controller;

use wp_gdpr\lib\Gdpr_Container;
use wp_gdpr\lib\Appsaloon_Table_Builder;

class Controller_Comments {

	/**
	 * @var $email_request string
	 * this email is used to decode and encode unique url
	 */
	public $email_request;

	public function __construct() {
		$this->redirect_template();
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
		$decoded   = base64_decode( $substring );
		if ( strpos( $decoded, 'gdpr#' ) !== false ) {
			//explode into array( 'gdpr', 'example@email.com' )
			//get second element from array
			$email               = explode( '#', $decoded )[1];
			$this->email_request = $email;

			return true;
		}

		return false;
	}

	/**
	 * @param $email
	 * update status in custom gdpr_requests table
	 * status 2 is: email sent
	 */
	public function update_gdpr_status( $email ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'gdpr_requests';

		$wpdb->update( $table_name, array( 'status' => 2 ), array( 'email' => $email ) );
	}

	/**
	 * get template to show comments and other data
	 * about user with requested email address
	 * set variable $controller to use in template
	 */
	public function get_template() {
		$controller = $this;
		include_once GDPR_DIR . 'view/front/gdpr-template.php';
		die;
	}

	/**
	 * build table with all comments
	 * selected by email address
	 */
	public function create_table_with_comments() {
		$comments = $this->get_all_comments_by_author( $this->email_request );
		$comments = $this->filter_comments( $comments );

		$table = new Appsaloon_Table_Builder(
			array( 'comment date', 'comment content', 'post ID' ),
			$comments
			, array(), 'gdpr_comments_table' );

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
	public function filter_comments( $comments ) {
		$comments = array_map( function ( $data ) {
			return array( $data->comment_date, $data->comment_content, $data->comment_post_ID );
		}, $comments );

		return $comments;
	}
}
