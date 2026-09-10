<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_tour_manager_caps(): array {
	return array(
		'read'                    => true,
		'upload_files'            => true,
		'edit_posts'              => true,
		'edit_published_posts'    => true,
		'publish_posts'           => true,
		'delete_posts'            => true,
		'delete_published_posts'  => true,
		'edit_pages'              => true,
		'edit_published_pages'    => true,
		'publish_pages'           => true,
		'edit_tour'               => true,
		'read_tour'               => true,
		'delete_tour'             => true,
		'edit_tours'              => true,
		'edit_others_tours'       => true,
		'publish_tours'           => true,
		'read_private_tours'      => true,
		'delete_tours'            => true,
		'delete_private_tours'    => true,
		'delete_published_tours'  => true,
		'delete_others_tours'     => true,
		'edit_private_tours'      => true,
		'edit_published_tours'    => true,
		'manage_watphou_bookings' => true,
	);
}

function watphou_core_register_roles(): void {
	$caps = watphou_core_tour_manager_caps();
	$role = get_role( 'tour_manager' );
	if ( ! $role ) {
		add_role( 'tour_manager', __( 'Tour Manager', 'watphou-core' ), $caps );
	} else {
		foreach ( $caps as $cap => $grant ) {
			if ( $grant ) {
				$role->add_cap( $cap );
			}
		}
	}

	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( array( 'edit_tour', 'read_tour', 'delete_tour', 'edit_tours', 'edit_others_tours', 'publish_tours', 'delete_tours', 'manage_watphou_bookings' ) as $cap ) {
			$admin->add_cap( $cap );
		}
	}
}

add_action( 'init', 'watphou_core_ensure_manager_user', 20 );

function watphou_core_ensure_manager_user(): void {
	if ( ! defined( 'WATPHOU_DEMO' ) || ! WATPHOU_DEMO ) {
		return;
	}
	$user = get_user_by( 'login', 'manager' );
	if ( $user ) {
		if ( ! in_array( 'tour_manager', (array) $user->roles, true ) ) {
			$user->set_role( 'tour_manager' );
		}
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

add_filter( 'login_redirect', 'watphou_core_login_redirect', 10, 3 );

function watphou_core_login_redirect( $redirect_to, $requested, $user ) {
	if ( $user instanceof WP_User && in_array( 'tour_manager', (array) $user->roles, true ) ) {
		return admin_url( 'admin.php?page=watphou-tour-desk' );
	}
	return $redirect_to;
}
