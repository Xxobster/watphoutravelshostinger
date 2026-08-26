<?php
defined( 'ABSPATH' ) || exit;

function watphou_bookings_create_tables(): void {
	global $wpdb;
	$charset = $wpdb->get_charset_collate();
	$bookings = $wpdb->prefix . 'watphou_bookings';
	$events   = $wpdb->prefix . 'watphou_booking_events';

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	dbDelta(
		"CREATE TABLE {$bookings} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			reference varchar(32) NOT NULL,
			tour_id bigint(20) unsigned DEFAULT 0,
			tour_name varchar(255) DEFAULT '',
			status varchar(32) NOT NULL DEFAULT 'requested',
			preferred_date date DEFAULT NULL,
			alt_date date DEFAULT NULL,
			adults smallint unsigned DEFAULT 1,
			children smallint unsigned DEFAULT 0,
			children_ages text,
			customer_name varchar(191) NOT NULL,
			email varchar(191) NOT NULL,
			phone varchar(64) DEFAULT '',
			whatsapp varchar(64) DEFAULT '',
			country varchar(64) DEFAULT '',
			language varchar(8) DEFAULT 'en',
			pickup_location text,
			accommodation_pref varchar(191) DEFAULT '',
			guide_pref varchar(191) DEFAULT '',
			customer_message text,
			staff_notes text,
			quoted_amount decimal(12,2) DEFAULT NULL,
			currency varchar(8) DEFAULT 'USD',
			payment_token varchar(64) DEFAULT NULL,
			payment_expires_at datetime DEFAULT NULL,
			privacy_consent tinyint(1) DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY reference (reference),
			KEY status (status),
			KEY payment_token (payment_token)
		) {$charset};"
	);

	dbDelta(
		"CREATE TABLE {$events} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			booking_id bigint(20) unsigned NOT NULL,
			from_status varchar(32) DEFAULT NULL,
			to_status varchar(32) NOT NULL,
			user_id bigint(20) unsigned DEFAULT 0,
			note text,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY booking_id (booking_id)
		) {$charset};"
	);
}
