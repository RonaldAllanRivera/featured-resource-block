<?php
/**
 * Elementor integration for Featured Resource Block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Elementor_Integration {
	/**
	 * Minimum Elementor version.
	 */
	const MIN_ELEMENTOR_VERSION = '3.0.0';

	/**
	 * Hook Elementor integration.
	 */
	public static function register() {
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widget' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_styles' ) );
	}

	public static function register_styles() {
		wp_register_style(
			'frb-frontend',
			FRB_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			FRB_PLUGIN_VERSION
		);
	}

	/**
	 * Register the Featured Resource widget with Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager instance.
	 */
	public static function register_widget( $widgets_manager ) {
		if ( ! self::is_elementor_active_and_compatible() ) {
			return;
		}

		$widgets_manager->register( new \FRB_Widget_Featured_Resource() );
	}

	/**
	 * Check if Elementor is active and meets the minimum version.
	 *
	 * @return bool
	 */
	protected static function is_elementor_active_and_compatible() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return false;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '<' ) ) {
			return false;
		}

		return true;
	}
}
