<?php
/**
 * Plugin Name: Featured Resource Block
 * Description: Adds a Resources custom post type, an Elementor widget, and mock API sync.
 * Version: 0.1.0
 * Author: Ronald Allan Rivera
 * Text Domain: featured-resource-block
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
if ( ! defined( 'FRB_PLUGIN_FILE' ) ) {
	define( 'FRB_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'FRB_PLUGIN_DIR' ) ) {
	define( 'FRB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'FRB_PLUGIN_URL' ) ) {
	define( 'FRB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'FRB_PLUGIN_VERSION' ) ) {
	define( 'FRB_PLUGIN_VERSION', '0.1.0' );
}

function frb_activate_plugin() {
	if ( class_exists( 'FRB_Plugin' ) && method_exists( 'FRB_Plugin', 'activate' ) ) {
		FRB_Plugin::activate();
	}
}

function frb_deactivate_plugin() {
	if ( class_exists( 'FRB_Plugin' ) && method_exists( 'FRB_Plugin', 'deactivate' ) ) {
		FRB_Plugin::deactivate();
	}
}

register_activation_hook( FRB_PLUGIN_FILE, 'frb_activate_plugin' );
register_deactivation_hook( FRB_PLUGIN_FILE, 'frb_deactivate_plugin' );

// Simple autoloader for plugin classes.
if ( ! function_exists( 'frb_autoload' ) ) {
	function frb_autoload( $class ) {
		if ( 0 !== strpos( $class, 'FRB_' ) ) {
			return;
		}

		$filename = 'class-' . strtolower( str_replace( array( 'FRB_', '_' ), array( '', '-' ), $class ) ) . '.php';
		$file     = FRB_PLUGIN_DIR . 'includes/' . $filename;

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}

	spl_autoload_register( 'frb_autoload' );
}

/**
 * Main plugin bootstrap function.
 */
function frb_load_plugin() {
	// Avoid loading in CLI or during certain AJAX calls if not needed.
	if ( ! class_exists( 'FRB_Plugin' ) ) {
		return;
	}

	FRB_Plugin::instance();
}

// Load the core plugin class file manually before autoloaded references.
require_once FRB_PLUGIN_DIR . 'includes/class-plugin.php';

// Hook plugin bootstrap.
add_action( 'plugins_loaded', 'frb_load_plugin' );
