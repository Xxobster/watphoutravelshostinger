<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_register_roles(): void {
	remove_role( 'tour_manager' );
	add_role(
		'tour_manager',
		__( 'Tour Manager', 'watphou-core' ),
		array(
			'read'                   => true,
			'upload_files'           => true,
			'edit_posts'             => true,
			'edit_published_posts'   => true,
			'publish_posts'          => true,
			'delete_posts'           => true,
			'delete_published_posts' => true,
			'edit_pages'             => true,
			'edit_published_pages'   => true,
			'publish_pages'          => true,
			'edit_tours'             => true,
			'edit_published_tours'   => true,
			'publish_tours'          => true,
			'delete_tours'           => true,
			'delete_published_tours' => true,
			'edit_others_tours'      => false,
			'manage_watphou_bookings'=> true,
		)
	);

	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( array( 'edit_tours', 'publish_tours', 'delete_tours', 'manage_watphou_bookings' ) as $cap ) {
			$admin->add_cap( $cap );
		}
	}
}

add_action( 'init', 'watphou_core_ensure_manager_user', 20 );

function watphou_core_ensure_manager_user(): void {
	if ( ! defined( 'WATPHOU_DEMO' ) || ! WATPHOU_DEMO ) {
		return;
	}
	if ( get_user_by( 'login', 'manager' ) ) {
		return;
	}
	$id = wp_insert_user(
		array(
			'user_login'   => 'manager',
			'user_pass'    => '000000',
			'user_email'   => 'manager-demo@watphou-travels.local',
			'display_name' => 'Tour Manager',
			'role'         => 'tour_manager',
		)
	);
	if ( ! is_wp_error( $id ) ) {
		update_user_meta( $id, 'watphou_must_change_password', 1 );
	}
}
