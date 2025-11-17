<?php
/**
 * Resource Sync settings page view.
 *
 * Variables available from FRB_Settings_Page::render_page():
 * - $options          array  Current options.
 * - $last_sync_time   string Last sync time (optional).
 * - $last_sync_error  string Last sync error message (optional).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Resource Sync', 'featured-resource-block' ); ?></h1>

	<p><?php esc_html_e( 'Configure how the Featured Resource Block plugin synchronises mock resources from the remote API.', 'featured-resource-block' ); ?></p>

	<form method="post" action="options.php">
		<?php
		settings_fields( FRB_Settings_Page::OPTION_GROUP );
		do_settings_sections( FRB_Settings_Page::PAGE_SLUG );
		submit_button();
		?>
	</form>

	<hr />

	<h2><?php esc_html_e( 'Sync Status', 'featured-resource-block' ); ?></h2>

	<p>
		<strong><?php esc_html_e( 'Last sync time:', 'featured-resource-block' ); ?></strong>
		<?php
		if ( ! empty( $last_sync_time ) ) {
			echo ' ' . esc_html( $last_sync_time );
		} else {
			esc_html_e( 'No sync has run yet.', 'featured-resource-block' );
		}
		?>
	</p>

	<?php if ( ! empty( $last_sync_error ) ) : ?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Last sync error:', 'featured-resource-block' ); ?></strong>
				<?php echo ' ' . esc_html( $last_sync_error ); ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( defined( 'FRB_DEBUG' ) && FRB_DEBUG && current_user_can( 'manage_options' ) ) : ?>
		<hr />
		<h2><?php esc_html_e( 'Debug: Stored Options', 'featured-resource-block' ); ?></h2>
		<p><?php esc_html_e( 'Current frb_resource_sync option as stored in the database.', 'featured-resource-block' ); ?></p>
		<pre class="frb-debug-options"><code><?php echo esc_html( wp_json_encode( $options, JSON_PRETTY_PRINT ) ); ?></code></pre>
	<?php endif; ?>
</div>
