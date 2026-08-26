<?php
defined( 'ABSPATH' ) || exit;

class Watphou_Mock_Payment_Provider implements Watphou_Payment_Provider {
	public function create_session( object $booking, float $amount, string $currency, string $reference ): array {
		return array(
			'provider'    => 'mock',
			'session_id'  => 'MOCK-' . $reference,
			'redirect_url'=> home_url( '/pay/' . $booking->payment_token . '?simulate=1' ),
			'message'     => __( 'SIMULATION — no real payment will be processed.', 'watphou-bookings' ),
		);
	}

	public function verify_callback( array $payload, string $signature ): bool {
		$secret = defined( 'WATPHOU_MOCK_CALLBACK_SECRET' ) ? WATPHOU_MOCK_CALLBACK_SECRET : 'demo-mock-secret';
		$expected = hash_hmac( 'sha256', wp_json_encode( $payload ), $secret );
		return hash_equals( $expected, $signature );
	}

	public function get_status( string $reference ): string {
		return 'pending';
	}

	public function cancel( string $reference ): bool {
		return true;
	}
}
