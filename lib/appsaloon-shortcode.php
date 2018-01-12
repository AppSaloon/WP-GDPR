<?php

namespace stany_nodigt_uit\services;

class Appsaloon_Shortcode {
	/**
	 * shortcode arguments
	 */
	protected $shortcode_arguments;
	/**
	 * shortcode name
	 */
	protected $shortcode_name;

	/**
	 * allows to register shortcode name and arguments
	 */
	public function __construct( $shortcode_name, $shortcode_arguments ) {
		$this->shortcode_arguments = $shortcode_arguments;
		$this->shortcode_name      = $shortcode_name;
	}

	/**
	 * content of shortcode
	 */
	protected $content;

	/**
	 * add content that should be showd in shortcode
	 */
	public function add_content( string $content ) {
		$this->content = $content;
	}

	/**
	 * register shortcode
	 */
	public function register_shortcode() {

		add_shortcode( $this->shortcode_name, array( $this, 'get_content' ) );
	}

	/**
	 * get content for shortcode
	 */
	public function get_content() {
		return $this->content;
	}

}
