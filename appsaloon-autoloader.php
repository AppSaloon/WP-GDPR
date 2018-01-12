<?php

/**
 * This class does include classes with namespaces as a file
 */
class Appsaloon_Autoloader {

	/**
	 * Register autoloader
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'social_video_plugin' ) );
	}

	/**
	 * Include class if the file is found
	 *
	 * @param $class    string  Full name of a class: namespaces\classname
	 */
	public function social_video_plugin( $class ) {
		if ( strpos( $class, 'stany_nodigt_uit\\' ) === 0 ) {
			$path = substr( $class, strlen( 'stany_nodigt_uit\\' ) );
			$path = strtolower( $path );
			$path = str_replace( '_', '-', $path );
			$path = str_replace( '\\', DIRECTORY_SEPARATOR, $path ) . '.php';
			$path = SOCIAL_VIDEO_DIR . DIRECTORY_SEPARATOR . $path;

			if ( file_exists( $path ) ) {
				include $path;
			}
		}
	}
}

new Appsaloon_Autoloader();
