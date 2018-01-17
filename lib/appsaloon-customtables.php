<?php
namespace wp_gdpr\lib;

class Appsaloon_Customtables {

	/**
	 * name of table without prefix
	 */
	const REQUESTS_TABLE_NAME = 'gdpr_requests';
	/**
	 * create custom table
	 * use dbDelta()
	 */
	public static function create_request_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::REQUESTS_TABLE_NAME;

		$query      = "CREATE TABLE " . $table_name . " (
			ID INT(10) NOT NULL AUTO_INCREMENT,
			email VARCHAR(60) DEFAULT NULL,
			status INT(2) DEFAULT NULL,
			timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (ID)
		)";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $query );
	}
}
