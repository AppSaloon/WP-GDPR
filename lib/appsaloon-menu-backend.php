<?php

namespace wp_gdpr\lib;

/**
 * Class Menu_Controller
 * @package ot_hotel\controller
 * simple controller to make menu page
 * with option to save api key
 */
class Appsaloon_Menu_Backend {
	const MENU_PAGE_TITLE = 'ot_flights_menu';

	const PAGE_SLUG = 'page_slug';

	const MENU_TITLE = 'WP GDPR';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_callback' ) );
	}

	/**
	 * add menu page
	 */
	public function add_menu_callback() {
		add_menu_page( self::MENU_PAGE_TITLE, self::MENU_TITLE, 'manage_options', self::PAGE_SLUG, array(
			$this,
			'menu_page_output'
		) );
	}

	/**
	 * generate output for menu page from template
	 */
	public function menu_page_output() {
		require_once GDPR_DIR . 'view/admin/menu-page.php';
	}
}
