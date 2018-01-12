<?php

/**
 * This class does include classes with namespaces as a file
 */
class Appsaloon_Autoloader {

	const NAMESPACE_NAME = 'wp_gdpr\\';

	/**
	 * Register autoloader
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'autoloader_callback' ) );
	}

	/**
	 * Include class if the file is found
	 *
	 * @param $class    string  Full name of a class: namespaces\classname
	 */
	public function autoloader_callback( $class ) {
		if ( strpos( $class, self::NAMESPACE_NAME ) === 0 ) {
			$path = substr( $class, strlen( self::NAMESPACE_NAME ) );
			$path = strtolower( $path );
			$path = str_replace( '_', '-', $path );
			$path = str_replace( '\\', DIRECTORY_SEPARATOR, $path ) . '.php';
			$path = GDPR_DIR . DIRECTORY_SEPARATOR . $path;

			if ( file_exists( $path ) ) {
				include $path;
			}
		}
	}
}

new Appsaloon_Autoloader();
