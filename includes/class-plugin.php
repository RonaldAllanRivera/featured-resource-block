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

		if ( class_exists( 'FRB_Cron_Manager' ) ) {
			FRB_Cron_Manager::activate();
		}
	}

	public static function deactivate() {
		if ( class_exists( 'FRB_Cron_Manager' ) ) {
			FRB_Cron_Manager::deactivate();
		}
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

		if ( class_exists( 'FRB_Settings_Page' ) ) {
			FRB_Settings_Page::register();
		}

		// Elementor integration.
		if ( class_exists( 'FRB_Elementor_Integration' ) ) {
			FRB_Elementor_Integration::register();
		}
		// Cron manager for scheduled sync.
		if ( class_exists( 'FRB_Cron_Manager' ) ) {
			FRB_Cron_Manager::register();
		}
		// Local mock API endpoint for development/testing.
		if ( class_exists( 'FRB_Mock_Api' ) ) {
			FRB_Mock_Api::register();
		}
	}

	/**
	 * General initialization.
	 */
	public function init() {
		if ( defined( 'FRB_DEBUG' ) && FRB_DEBUG ) {
			error_log( sprintf( 'FRB: plugin loaded (version %s)', FRB_PLUGIN_VERSION ) );
		}

		// CPT and meta registration.
		if ( class_exists( 'FRB_Post_Type_Resources' ) ) {
			FRB_Post_Type_Resources::register();
		}

		if ( class_exists( 'FRB_Resource_Meta' ) ) {
			FRB_Resource_Meta::register();
		}

		// Placeholder for textdomain loading and other init-time setup.
	}

	/**
	 * Admin initialization.
	 */
	public function admin_init() {
		// Placeholder for admin-only setup.
	}
}
