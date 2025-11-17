<?php
/**
 * Core plugin orchestrator.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var FRB_Plugin
	 */
	protected static $instance;

	public static function activate() {
		self::instance();
	}

	public static function deactivate() {
	}

	/**
	 * Get singleton instance.
	 *
	 * @return FRB_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * FRB_Plugin constructor.
	 */
	protected function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register core hooks.
	 */
	protected function register_hooks() {
		// Init hooks (CPT, meta, textdomain, etc.).
		add_action( 'init', array( $this, 'init' ) );

		// Admin-specific hooks.
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Elementor integration and sync will be wired in later phases.
	}

	/**
	 * General initialization.
	 */
	public function init() {
		if ( defined( 'FRB_DEBUG' ) && FRB_DEBUG ) {
			error_log( sprintf( 'FRB: plugin loaded (version %s)', FRB_PLUGIN_VERSION ) );
		}

		// Placeholder for CPT registration, textdomain loading, etc.
	}

	/**
	 * Admin initialization.
	 */
	public function admin_init() {
		// Placeholder for admin-only setup.
	}
}
