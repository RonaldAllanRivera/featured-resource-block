<?php
/**
 * Elementor widget: Featured Resource Block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Widget_Featured_Resource extends \Elementor\Widget_Base {
	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'frb_featured_resource';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Featured Resource Block', 'featured-resource-block' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * Widget categories.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Styles dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'frb-frontend' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'featured-resource-block' ),
			)
		);

		$this->add_control(
			'resource_id',
			array(
				'label'       => __( 'Selected Resource', 'featured-resource-block' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_resource_options(),
				'label_block' => true,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout Style', 'featured-resource-block' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'card'    => __( 'Card', 'featured-resource-block' ),
					'minimal' => __( 'Minimal', 'featured-resource-block' ),
				),
				'default' => 'card',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Button Text', 'featured-resource-block' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'View resource', 'featured-resource-block' ),
				'placeholder' => __( 'View resource', 'featured-resource-block' ),
			)
		);

		$this->add_control(
			'gradient_background',
			array(
				'label'        => __( 'Gradient Background', 'featured-resource-block' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'featured-resource-block' ),
				'label_off'    => __( 'Off', 'featured-resource-block' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => __( 'Image Size', 'featured-resource-block' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'thumbnail' => __( 'Thumbnail', 'featured-resource-block' ),
					'medium'    => __( 'Medium', 'featured-resource-block' ),
					'large'     => __( 'Large', 'featured-resource-block' ),
					'full'      => __( 'Full', 'featured-resource-block' ),
				),
				'default' => 'large',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$resource_id = isset( $settings['resource_id'] ) ? (int) $settings['resource_id'] : 0;

		if ( ! $resource_id ) {
			return;
		}

		$post = get_post( $resource_id );

		if ( ! $post || FRB_Post_Type_Resources::POST_TYPE !== $post->post_type ) {
			return;
		}

		$title         = get_the_title( $post );
		$excerpt       = get_the_excerpt( $post );
		$resource_url  = get_post_meta( $post->ID, FRB_Resource_Meta::META_RESOURCE_URL, true );
		$thumbnail_id  = get_post_thumbnail_id( $post );
		$image_size    = ! empty( $settings['image_size'] ) ? $settings['image_size'] : 'large';
		$button_text   = ! empty( $settings['button_text'] ) ? $settings['button_text'] : __( 'View resource', 'featured-resource-block' );
		$layout        = ! empty( $settings['layout'] ) ? $settings['layout'] : 'card';
		$gradient_on   = ! empty( $settings['gradient_background'] ) && 'yes' === $settings['gradient_background'];

		$wrapper_classes = array( 'frb-featured-resource', 'frb-layout-' . $layout );

		if ( $gradient_on ) {
			$wrapper_classes[] = 'frb-has-gradient';
		}

		$wrapper_class_attr = implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) );

		$link_url = $resource_url ? $resource_url : get_permalink( $post );

		$image_html = '';

		if ( $thumbnail_id ) {
			$image_html = wp_get_attachment_image( $thumbnail_id, $image_size, false, array( 'class' => 'frb-resource-image' ) );
		}

		?>
		<article class="<?php echo esc_attr( $wrapper_class_attr ); ?>">
			<div class="frb-resource-inner">
				<?php if ( $image_html ) : ?>
					<div class="frb-resource-image-wrap">
						<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>

				<div class="frb-resource-body">
					<h3 class="frb-resource-title"><?php echo esc_html( $title ); ?></h3>
					<?php if ( $excerpt ) : ?>
						<div class="frb-resource-excerpt"><?php echo wp_kses_post( wpautop( $excerpt ) ); ?></div>
					<?php endif; ?>

					<div class="frb-resource-footer">
						<a class="frb-resource-button" href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $button_text ); ?>
						</a>
					</div>
				</div>
			</div>
		</article>
		<?php
	}

	/**
	 * Get options for the resource select control.
	 *
	 * @return array
	 */
	protected function get_resource_options() {
		$options = array();

		$query = new \WP_Query(
			array(
				'post_type'      => FRB_Post_Type_Resources::POST_TYPE,
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$options[ $post->ID ] = sprintf( '%s (ID: %d)', $post->post_title, $post->ID );
			}
		}

		wp_reset_postdata();

		return $options;
	}
}
