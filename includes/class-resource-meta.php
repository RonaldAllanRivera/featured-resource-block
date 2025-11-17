<?php
/**
 * Resources meta registration and meta box.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Resource_Meta {
	/**
	 * Meta keys.
	 */
	const META_RESOURCE_URL = 'mist_resource_url';
	const META_REMOTE_ID    = 'mist_remote_id';

	/**
	 * Register hooks.
	 */
	public static function register() {
		self::register_meta();
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . FRB_Post_Type_Resources::POST_TYPE, array( __CLASS__, 'save_meta_box' ) );
	}

	/**
	 * Register post meta fields.
	 */
	public static function register_meta() {
		register_post_meta(
			FRB_Post_Type_Resources::POST_TYPE,
			self::META_RESOURCE_URL,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'auth_callback'     => '__return_true',
			)
		);

		register_post_meta(
			FRB_Post_Type_Resources::POST_TYPE,
			self::META_REMOTE_ID,
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
	}

	/**
	 * Add Resource URL meta box.
	 */
	public static function add_meta_box() {
		add_meta_box(
			'mist_resource_url',
			__( 'Resource URL', 'featured-resource-block' ),
			array( __CLASS__, 'render_meta_box' ),
			FRB_Post_Type_Resources::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Render the Resource URL meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'mist_resource_meta', 'mist_resource_meta_nonce' );

		$value = get_post_meta( $post->ID, self::META_RESOURCE_URL, true );
		?>
		<p>
			<label for="mist-resource-url"><?php esc_html_e( 'Resource URL', 'featured-resource-block' ); ?></label><br />
			<input type="url" id="mist-resource-url" name="mist_resource_url" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
		</p>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['mist_resource_meta_nonce'] ) || ! wp_verify_nonce( $_POST['mist_resource_meta_nonce'], 'mist_resource_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['mist_resource_url'] ) ) {
			$raw = wp_unslash( $_POST['mist_resource_url'] );
			$url = esc_url_raw( $raw );

			if ( ! empty( $url ) ) {
				update_post_meta( $post_id, self::META_RESOURCE_URL, $url );
			} else {
				delete_post_meta( $post_id, self::META_RESOURCE_URL );
			}
		}
	}

	/**
	 * Simple auth callback to ensure only users who can edit the post can manage meta.
	 *
	 * @param bool   $allowed Current allowed value.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Post ID.
	 *
	 * @return bool
	 */
	public static function auth_meta( $allowed, $meta_key, $post_id ) {
		unset( $meta_key );

		if ( current_user_can( 'edit_post', $post_id ) ) {
			return true;
		}

		return false;
	}
}
