<?php
/**
 * Simple logger for the Featured Resource Block plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Logger {
	/**
	 * Whether debug messages should be logged.
	 *
	 * @return bool
	 */
	protected static function should_log_debug() {
		if ( defined( 'FRB_DEBUG' ) && FRB_DEBUG ) {
			return true;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		return false;
	}

	/**
	 * Log a debug message (only when debug is enabled).
	 *
	 * @param string $message Message.
	 * @param array  $context Optional context data.
	 */
	public static function debug( $message, array $context = array() ) {
		if ( ! self::should_log_debug() ) {
			return;
		}

		self::write_log( 'DEBUG', $message, $context );
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message Message.
	 * @param array  $context Optional context data.
	 */
	public static function error( $message, array $context = array() ) {
		self::write_log( 'ERROR', $message, $context );
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message Message.
	 * @param array  $context Optional context data.
	 */
	public static function info( $message, array $context = array() ) {
		self::write_log( 'INFO', $message, $context );
	}

	/**
	 * Internal writer.
	 *
	 * @param string $level   Log level.
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	protected static function write_log( $level, $message, array $context ) {
		if ( ! function_exists( 'error_log' ) ) {
			return;
		}

		$prefix = sprintf( 'FRB [%s]', $level );

		if ( ! empty( $context ) && function_exists( 'wp_json_encode' ) ) {
			$context_json = wp_json_encode( $context );
			$message     = sprintf( '%s | context: %s', $message, $context_json );
		}

		error_log( sprintf( '%s: %s', $prefix, $message ) );
	}
}
