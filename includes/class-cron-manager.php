<?php
/**
 * Cron manager for the Featured Resource Block plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Cron_Manager {
	/**
	 * Cron hook name.
	 */
	const CRON_HOOK = 'frb_resource_sync_cron';

	/**
	 * Custom schedule key.
	 */
	const SCHEDULE_KEY = 'frb_every_15_minutes';

	/**
	 * Register cron-related hooks.
	 */
	public static function register() {
		add_filter( 'cron_schedules', array( __CLASS__, 'register_schedule' ) );
		add_action( self::CRON_HOOK, array( 'FRB_Sync_Service', 'run' ) );

		add_action( 'update_option_' . FRB_Settings_Page::OPTION_NAME, array( __CLASS__, 'handle_option_update' ), 10, 3 );
		add_action( 'add_option_' . FRB_Settings_Page::OPTION_NAME, array( __CLASS__, 'handle_option_add' ), 10, 2 );
	}

	/**
	 * Activation hook handler.
	 */
	public static function activate() {
		self::sync_schedule_with_settings();
	}

	/**
	 * Deactivation hook handler.
	 */
	public static function deactivate() {
		self::clear_scheduled_event();
	}

	/**
	 * Register a 15-minute interval.
	 *
	 * @param array $schedules Existing schedules.
	 *
	 * @return array
	 */
	public static function register_schedule( $schedules ) {
		if ( ! isset( $schedules[ self::SCHEDULE_KEY ] ) ) {
			$schedules[ self::SCHEDULE_KEY ] = array(
				'interval' => 15 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 15 minutes (FRB)', 'featured-resource-block' ),
			);
		}

		return $schedules;
	}

	/**
	 * Handle settings updates.
	 *
	 * @param mixed  $old_value Old value.
	 * @param mixed  $value     New value.
	 * @param string $option    Option name.
	 */
	public static function handle_option_update( $old_value, $value, $option ) {
		unset( $old_value, $option );
		self::sync_schedule_with_settings( $value );
	}

	/**
	 * Handle settings being added for the first time.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  New value.
	 */
	public static function handle_option_add( $option, $value ) {
		if ( FRB_Settings_Page::OPTION_NAME !== $option ) {
			return;
		}

		self::sync_schedule_with_settings( $value );
	}

	/**
	 * Ensure schedule matches current settings.
	 *
	 * @param array|null $settings Settings (if already available).
	 */
	protected static function sync_schedule_with_settings( $settings = null ) {
		if ( null === $settings ) {
			if ( class_exists( 'FRB_Settings_Page' ) ) {
				$settings = FRB_Settings_Page::get_options();
			} else {
				$settings = array();
			}
		}

		$enabled = ! empty( $settings['enable_sync'] );

		if ( $enabled ) {
			self::schedule_event();
		} else {
			self::clear_scheduled_event();
		}
	}

	/**
	 * Schedule the cron event if not already scheduled.
	 */
	protected static function schedule_event() {
		if ( wp_next_scheduled( self::CRON_HOOK ) ) {
			return;
		}

		wp_schedule_event( time(), self::SCHEDULE_KEY, self::CRON_HOOK );
	}

	/**
	 * Unschedule the cron event.
	 */
	protected static function clear_scheduled_event() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );

		while ( false !== $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
			$timestamp = wp_next_scheduled( self::CRON_HOOK );
		}
	}
}
