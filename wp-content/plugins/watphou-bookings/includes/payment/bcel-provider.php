<?php
defined( 'ABSPATH' ) || exit;

class Watphou_Bcel_Payment_Provider implements Watphou_Payment_Provider {
	public function create_session( object $booking, float $amount, string $currency, string $reference ): array {
		throw new RuntimeException( 'BCEL integration not configured. Obtain merchant credentials and API documentation.' );
	}

	public function verify_callback( array $payload, string $signature ): bool {
		throw new RuntimeException( 'BCEL integration not configured.' );
	}

	public function get_status( string $reference ): string {
		throw new RuntimeException( 'BCEL integration not configured.' );
	}

	public function cancel( string $reference ): bool {
		throw new RuntimeException( 'BCEL integration not configured.' );
	}
}
