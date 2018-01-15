<?php


namespace wp_gdpr\controller;

use wp_gdpr\lib\Appsaloon_Table_Builder;

class Controller_Menu_Page {
	public function build_table_with_requests() {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}gdpr_requests";

		$requesting_users = $wpdb->get_results( $query, ARRAY_N );

		$table = new Appsaloon_Table_Builder(
			array( 'id', 'username', 'email', 'requested at' ),
			$requesting_users
			, array() );

		$table->print_table();
	}

	public function build_table_with_plugins() {

		$plugins = get_plugins();
		$plugins = array_map( function ( $k ) {
			return array( $k['Name'] );
		}, $plugins );


		$plugins = $this->filter_plugins( $plugins );

		$table = new Appsaloon_Table_Builder(
			array( 'plugin name' ),
			$plugins
			, array() );

		$table->print_table();

	}

	/**
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function filter_plugins( array $plugins ) {

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
