<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_register_post_types(): void {
	register_post_type(
		'tour',
		array(
			'labels'              => array(
				'name'          => __( 'Tours', 'watphou-core' ),
				'singular_name' => __( 'Tour', 'watphou-core' ),
				'add_new_item'  => __( 'Add New Tour', 'watphou-core' ),
				'edit_item'     => __( 'Edit Tour', 'watphou-core' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'tours' ),
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-palmtree',
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'template'            => array(
				array( 'core/paragraph', array( 'placeholder' => 'Dream paragraph (2-3 sentences)...' ) ),
				array( 'watphou/highlights' ),
				array( 'watphou/itinerary-day' ),
				array( 'watphou/inclusions', array( 'type' => 'included' ) ),
				array( 'watphou/inclusions', array( 'type' => 'excluded' ) ),
				array( 'watphou/inclusions', array( 'type' => 'upgrades' ) ),
			),
			'capability_type'     => 'tour',
			'map_meta_cap'        => true,
		)
	);

	register_post_type(
		'testimonial',
		array(
			'labels'       => array(
				'name'          => __( 'Reviews', 'watphou-core' ),
				'singular_name' => __( 'Review', 'watphou-core' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-star-filled',
			'supports'     => array( 'title', 'editor' ),
		)
	);
}
