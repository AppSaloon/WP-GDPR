<?php

namespace wp_gdpr\controller;

use wp_gdpr\lib\Gdpr_Container;
use wp_gdpr\lib\Appsaloon_Table_Builder;

class Controller_Comments {

	/**
	 * @var email from unique url
	 */
	public $email_request;

	public function __construct() {
		$this->redirect_template();
	}

	public function redirect_template() {
		if ( $this->decode_url_request() ) {
			add_action( 'template_redirect', array( $this, 'get_template' ) );
		}
	}

	public function decode_url_request() {
		$substring = substr( $_SERVER['REQUEST_URI'], 1 );
		$decoded   = base64_decode( $substring );
		if ( strpos( $decoded, 'gdpr#' ) !== false ) {
			$email               = explode( '#', $decoded )[1];
			$this->email_request = $email;

			return true;
		}

		return false;
	}

	public function count_comments( $comments ) {
		return count( $comments );
	}

	public function get_template() {
		$controller = $this;
		include_once GDPR_DIR . 'view/front/gdpr-template.php';
		die;
	}

	public function send_email() {

	}

	public function create_table_with_comments() {
		$comments = $this->get_all_comments_by_author( $this->email_request );
		$comments = $this->filter_comments( $comments );

		$table = new Appsaloon_Table_Builder(
			array( 'comment date', 'comment content', 'post ID' ),
			$comments
			, array(), 'gdpr_comments_table' );

		$table->print_table();
	}

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
