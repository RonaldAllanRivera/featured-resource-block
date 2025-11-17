<?php
/**
 * Resources custom post type registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FRB_Post_Type_Resources {
	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'mist_resource';

	/**
	 * Register hooks.
	 */
	public static function register() {
		self::register_post_type();
	}

	/**
	 * Register the Resources post type.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'               => _x( 'Resources', 'post type general name', 'featured-resource-block' ),
			'singular_name'      => _x( 'Resource', 'post type singular name', 'featured-resource-block' ),
			'menu_name'          => _x( 'Resources', 'admin menu', 'featured-resource-block' ),
			'name_admin_bar'     => _x( 'Resource', 'add new on admin bar', 'featured-resource-block' ),
			'add_new'            => _x( 'Add New', 'resource', 'featured-resource-block' ),
			'add_new_item'       => __( 'Add New Resource', 'featured-resource-block' ),
			'new_item'           => __( 'New Resource', 'featured-resource-block' ),
			'edit_item'          => __( 'Edit Resource', 'featured-resource-block' ),
			'view_item'          => __( 'View Resource', 'featured-resource-block' ),
			'all_items'          => __( 'All Resources', 'featured-resource-block' ),
			'search_items'       => __( 'Search Resources', 'featured-resource-block' ),
			'parent_item_colon'  => __( 'Parent Resources:', 'featured-resource-block' ),
			'not_found'          => __( 'No resources found.', 'featured-resource-block' ),
			'not_found_in_trash' => __( 'No resources found in Trash.', 'featured-resource-block' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => true,
			'rewrite'            => array( 'slug' => 'resources' ),
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-portfolio',
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
