<?php
defined( 'ABSPATH' ) || exit;

function watphou_get_payment_provider(): Watphou_Payment_Provider {
	if ( defined( 'WATPHOU_DEMO' ) && WATPHOU_DEMO ) {
		return new Watphou_Mock_Payment_Provider();
	}
	$mode = defined( 'BCEL_MODE' ) ? BCEL_MODE : 'sandbox';
	if ( 'production' === $mode && defined( 'BCEL_MERCHANT_ID' ) && BCEL_MERCHANT_ID ) {
		return new Watphou_Bcel_Payment_Provider();
	}
	return new Watphou_Mock_Payment_Provider();
}

function watphou_bookings_handle_pay_route(): void {
	$token = get_query_var( 'watphou_pay_token' );
	if ( ! $token ) {
		return;
	}
	$booking = Watphou_Booking_Repository::get_by_token( sanitize_text_field( $token ) );
	if ( ! $booking ) {
		status_header( 404 );
		wp_die( esc_html__( 'Invalid payment link.', 'watphou-bookings' ) );
	}
	if ( $booking->payment_expires_at && strtotime( $booking->payment_expires_at ) < time() ) {
		Watphou_Booking_Repository::transition( (int) $booking->id, 'expired', 0, 'Payment link expired' );
		wp_die( esc_html__( 'This payment link has expired.', 'watphou-bookings' ) );
	}

	if ( isset( $_GET['simulate'] ) && defined( 'WATPHOU_DEMO' ) && WATPHOU_DEMO ) {
		Watphou_Booking_Repository::transition( (int) $booking->id, 'paid', 0, 'Mock payment completed' );
		Watphou_Booking_Repository::transition( (int) $booking->id, 'confirmed', 0, 'Booking confirmed (demo)' );
		wp_die( esc_html__( 'Demo payment successful. Booking confirmed.', 'watphou-bookings' ), '', array( 'response' => 200 ) );
	}

	$provider = watphou_get_payment_provider();
	$session  = $provider->create_session( $booking, (float) $booking->quoted_amount, $booking->currency, $booking->reference );
	wp_safe_redirect( $session['redirect_url'] );
	exit;
}

function watphou_bookings_register_callback_route(): void {
	register_rest_route(
		'watphou/v1',
		'/payment/callback',
		array(
			'methods'             => 'POST',
			'callback'            => 'watphou_bookings_payment_callback',
			'permission_callback' => '__return_true',
		)
	);
}

function watphou_bookings_payment_callback( WP_REST_Request $request ): WP_REST_Response {
	$payload   = $request->get_json_params();
	$signature = $request->get_header( 'X-Watphou-Signature' ) ?: '';
	$provider  = watphou_get_payment_provider();

	if ( ! $provider->verify_callback( (array) $payload, (string) $signature ) ) {
		return new WP_REST_Response( array( 'error' => 'invalid_signature' ), 403 );
	}

	$reference = sanitize_text_field( $payload['reference'] ?? '' );
	global $wpdb;
	$booking = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Watphou_Booking_Repository::table() . ' WHERE reference = %s', $reference ) );
	if ( ! $booking ) {
		return new WP_REST_Response( array( 'error' => 'not_found' ), 404 );
	}

	$amount = floatval( $payload['amount'] ?? 0 );
	if ( abs( $amount - (float) $booking->quoted_amount ) > 0.01 ) {
		return new WP_REST_Response( array( 'error' => 'amount_mismatch' ), 400 );
	}

	Watphou_Booking_Repository::transition( (int) $booking->id, 'paid', 0, 'Payment callback verified' );
	Watphou_Booking_Repository::transition( (int) $booking->id, 'confirmed', 0, 'Auto-confirmed after payment' );

	return new WP_REST_Response( array( 'ok' => true ) );
}
