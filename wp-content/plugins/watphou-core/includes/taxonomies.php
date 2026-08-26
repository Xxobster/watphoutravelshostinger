<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_register_taxonomies(): void {
	register_taxonomy(
		'destination',
		array( 'tour' ),
		array(
			'labels'            => array(
				'name'          => __( 'Destinations', 'watphou-core' ),
				'singular_name' => __( 'Destination', 'watphou-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'destinations' ),
			'show_in_rest'      => true,
			'show_admin_column' => true,
		)
	);

	register_taxonomy(
		'tour_type',
		array( 'tour' ),
		array(
			'labels'            => array(
				'name'          => __( 'Tour Types', 'watphou-core' ),
				'singular_name' => __( 'Tour Type', 'watphou-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'tour-type' ),
			'show_in_rest'      => true,
			'show_admin_column' => true,
		)
	);

	register_taxonomy(
		'duration_cat',
		array( 'tour' ),
		array(
			'labels'            => array(
				'name'          => __( 'Duration', 'watphou-core' ),
				'singular_name' => __( 'Duration Category', 'watphou-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'duration' ),
			'show_in_rest'      => true,
			'show_admin_column' => true,
		)
	);
}
