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
		add_action( 'admin_post_frb_run_sync_now', array( __CLASS__, 'handle_run_sync_now' ) );
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
			'api_endpoint',
			__( 'API Endpoint', 'featured-resource-block' ),
			array( __CLASS__, 'render_field_api_endpoint' ),
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

		if ( isset( $input['api_endpoint'] ) ) {
			$endpoint = trim( $input['api_endpoint'] );

			if ( '' === $endpoint ) {
				$options['api_endpoint'] = FRB_Sync_Service::API_ENDPOINT;
			} else {
				$options['api_endpoint'] = esc_url_raw( $endpoint );
			}
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
			'api_key'      => '',
			'api_endpoint' => FRB_Sync_Service::API_ENDPOINT,
			'enable_sync'  => 0,
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
			esc_html__( 'Configure the Resource Sync integration. The API endpoint should point to a JSON feed (for example, a jsonbin.io bin). When sync is enabled, a WP-Cron event named frb_resource_sync_cron runs approximately every 15 minutes.', 'featured-resource-block' )
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
		<p class="description"><?php esc_html_e( 'Optional API key sent as an X-Master-Key header for private endpoints (for example, jsonbin.io). Leave blank for public or local endpoints that do not require authentication.', 'featured-resource-block' ); ?></p>
		<?php
	}

	/**
	 * Render API Endpoint field.
	 */
	public static function render_field_api_endpoint() {
		$options      = self::get_options();
		$api_endpoint = isset( $options['api_endpoint'] ) ? $options['api_endpoint'] : FRB_Sync_Service::API_ENDPOINT;
		?>
		<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[api_endpoint]" value="<?php echo esc_attr( $api_endpoint ); ?>" class="regular-text code" />
		<p class="description"><?php esc_html_e( 'Full URL to your JSON endpoint (for example, a jsonbin.io bin or a local REST URL).', 'featured-resource-block' ); ?></p>
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

	public static function handle_run_sync_now() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do this.', 'featured-resource-block' ) );
		}

		check_admin_referer( 'frb_run_sync_now' );

		if ( class_exists( 'FRB_Sync_Service' ) ) {
			FRB_Sync_Service::run();
		}

		$redirect = wp_get_referer();

		if ( ! $redirect ) {
			$redirect = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
		}

		$redirect = add_query_arg( 'frb_sync_ran', '1', $redirect );

		wp_safe_redirect( $redirect );
		exit;
	}
}
