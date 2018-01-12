<?php
namespace wp_gdpr\lib;

if ( ! class_exists( 'Log' ) ) {
	class Appsaloon_Log {

		/**
		 * Session KEY for log
		 */
		CONST SESSION_LOG = 'appsaloon_log';
		CONST TABLE_NAME = 'appsaloon_log';

		/**
		 * Creating of logging table
		 */
		public static function create_log_table() {
			global $wpdb;

			$table_name = $wpdb->prefix . self::TABLE_NAME;

			$query = "CREATE TABLE " . $table_name . " (
				  id INT(11) NOT NULL AUTO_INCREMENT,
				  message_type VARCHAR(20) DEFAULT NULL,
				  message TEXT NOT NULL,
				  file VARCHAR(255) DEFAULT NULL,
				  function VARCHAR(40) DEFAULT NULL,
				  line VARCHAR(40) DEFAULT NULL,
				  timestamp DATETIME DEFAULT NULL,
				  PRIMARY KEY (id)
				)";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $query );

			static::info( 'Log table updated' );
		}

		/**
		 * Save message with type debug
		 *
		 * @param       $msg      string  Message to save
		 * @param bool $file string  In which file did the call came from
		 * @param bool $function string  In which function
		 * @param bool $line string  In which line
		 */
		public static function debug( $msg, $file = false, $function = false, $line = false ) {
			static::add( 'debug', $msg, $file, $function, $line );
		}

		/**
		 * Save message with type info
		 *
		 * @param       $msg      string  Message to save
		 * @param bool $file string  In which file did the call came from
		 * @param bool $function string  In which function
		 * @param bool $line string  In which line
		 */
		public static function info( $msg, $file = false, $function = false, $line = false ) {
			static::add( 'info', $msg, $file, $function, $line );
		}

		/**
		 * Save message with type warn
		 *
		 * @param       $msg      string  Message to save
		 * @param bool $file string  In which file did the call came from
		 * @param bool $function string  In which function
		 * @param bool $line string  In which line
		 */
		public static function warn( $msg, $file = false, $function = false, $line = false ) {
			static::add( 'warn', $msg, $file, $function, $line );
		}

		/**
		 * Save message with type error
		 *
		 * @param       $msg      string  Message to save
		 * @param bool $file string  In which file did the call came from
		 * @param bool $function string  In which function
		 * @param bool $line string  In which line
		 */
		public static function error( $msg, $file = false, $function = false, $line = false ) {
			static::add( 'error', $msg, $file, $function, $line );
		}

		/**
		 * Save message with type fatal
		 *
		 * @param       $msg      string  Message to save
		 * @param bool $file string  In which file did the call came from
		 * @param bool $function string  In which function
		 * @param bool $line string  In which line
		 */
		public static function fatal( $msg, $file = false, $function = false, $line = false ) {
			static::add( 'fatal', $msg, $file, $function, $line );
		}

		/**
		 * Save message
		 *
		 * @param       $msg_type string  The message type (debug, info, warn, error or fatal)
		 * @param       $msg      string  Message to save
		 * @param       $file     string  In which file did the call came from
		 * @param bool $function string  In which function
		 * @param       $line     string  In which line
		 */
		public static function add( $msg_type, $msg, $file, $function, $line ) {
			$backtrace = debug_backtrace();

			$file      = ( $file === false ) ? $backtrace[1]['file'] : $file;
			$line      = ( $line === false ) ? $backtrace[1]['line'] : $line;
			$function  = ( $function === false ) ? $backtrace[2]['function'] : $function;
			$timestamp = current_time( 'mysql' );

			static::log_to_session( $msg_type, $msg, $file, $function, $line, $timestamp );
		}

		/**
		 * Save message to session
		 *
		 * @param       $msg_type   string  The message type (debug, info, warn, error or fatal)
		 * @param       $msg        string  Message to save
		 * @param       $file       string  In which file did the call came from
		 * @param bool $function string  In which function
		 * @param       $line       string  In which line
		 * @param       $timestamp  string  Timestamp of the log
		 */
		public static function log_to_session( $msg_type, $msg, $file, $function, $line, $timestamp ) {
			if ( ! isset( $_SESSION[ static::SESSION_LOG ] ) ) {
				$_SESSION[ static::SESSION_LOG ] = array();
			}

			$_SESSION[ static::SESSION_LOG ][] = array(
				'message_type' => $msg_type,
				'message'      => $msg,
				'file'         => $file,
				'function'     => $function,
				'line'         => $line,
				'timestamp'    => $timestamp
			);
		}

		/**
		 * Saving log records to database
		 * This function will be executed as the last PHP function.
		 */
		public static function log_to_database() {
			if ( isset( $_SESSION[ static::SESSION_LOG ] ) && is_array( $_SESSION[ static::SESSION_LOG ] ) ) {
				static::create_log_table();

				global $wpdb;
				$values = array();

				foreach ( $_SESSION[ static::SESSION_LOG ] as $log ) {
					$values[] = $wpdb->prepare( "(%s, %s, %s, %s, %s, %s)",
						$log['message_type'],
						( is_array( $log['message'] ) ) ? serialize( $log['message'] ) : $log['message'],
						$log['file'],
						$log['function'],
						$log['line'],
						$log['timestamp'] );
				}

				$table_name = $wpdb->prefix . self::TABLE_NAME;

				$query = "INSERT INTO " . $table_name . " (message_type, message, file, function, line, timestamp) VALUES ";
				$query .= implode( ", ", $values );

				$wpdb->query( $query );
			}

			// clear logs
			if ( isset( $_SESSION[ static::SESSION_LOG ] ) ) {
				unset( $_SESSION[ static::SESSION_LOG ] );
			}

		}
	}
}
