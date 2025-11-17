<?php
/**
 * Resource Sync settings page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Settings_Page {
	/**
	 * Option name.
	 */
	const OPTION_NAME = 'frb_resource_sync';

	/**
	 * Option group.
	 */
	const OPTION_GROUP = 'frb_resource_sync';

	/**
	 * Settings page slug.
	 */
	const PAGE_SLUG = 'frb-resource-sync';

	/**
	 * Register settings page hooks.
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add the Resource Sync settings page under Settings.
	 */
	public static function add_menu_page() {
		add_options_page(
			__( 'Resource Sync', 'featured-resource-block' ),
			__( 'Resource Sync', 'featured-resource-block' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			)
		);

		add_settings_section(
			'frb_resource_sync_main',
			__( 'Resource Sync Settings', 'featured-resource-block' ),
			array( __CLASS__, 'render_section_intro' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'api_key',
			__( 'API Key', 'featured-resource-block' ),
			array( __CLASS__, 'render_field_api_key' ),
			self::PAGE_SLUG,
			'frb_resource_sync_main'
		);

		add_settings_field(
			'enable_sync',
			__( 'Enable Sync', 'featured-resource-block' ),
			array( __CLASS__, 'render_field_enable_sync' ),
			self::PAGE_SLUG,
			'frb_resource_sync_main'
		);
	}

	/**
	 * Sanitize settings values.
	 *
	 * @param array $input Raw input.
	 *
	 * @return array
	 */
	public static function sanitize( $input ) {
		$options = self::get_options();

		if ( isset( $input['api_key'] ) ) {
			$options['api_key'] = sanitize_text_field( $input['api_key'] );
		}

		$options['enable_sync'] = ! empty( $input['enable_sync'] ) ? 1 : 0;

		return $options;
	}

	/**
	 * Get current options with defaults.
	 *
	 * @return array
	 */
	public static function get_options() {
		$defaults = array(
			'api_key'     => '',
			'enable_sync' => 0,
		);

		$options = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return array_merge( $defaults, $options );
	}

	/**
	 * Section intro callback.
	 */
	public static function render_section_intro() {
		printf(
			'<p>%s</p>',
			esc_html__( 'Configure the mock Resource Sync integration. The API key is treated as real configuration even though the endpoint is mocked.', 'featured-resource-block' )
		);
	}

	/**
	 * Render API Key field.
	 */
	public static function render_field_api_key() {
		$options = self::get_options();
		$api_key = isset( $options['api_key'] ) ? $options['api_key'] : '';
		?>
		<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'API key used when calling the mock Resource Sync endpoint.', 'featured-resource-block' ); ?></p>
		<?php
	}

	/**
	 * Render Enable Sync field.
	 */
	public static function render_field_enable_sync() {
		$options     = self::get_options();
		$enable_sync = ! empty( $options['enable_sync'] );
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_sync]" value="1" <?php checked( $enable_sync ); ?> />
			<?php esc_html_e( 'Enable scheduled sync via WP-Cron.', 'featured-resource-block' ); ?>
		</label>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options         = self::get_options();
		$last_sync_time  = get_option( 'frb_last_sync_time', '' );
		$last_sync_error = get_option( 'frb_last_sync_error', '' );

		// Make variables available to the view.
		require FRB_PLUGIN_DIR . 'admin/views/settings-page.php';
	}
}
