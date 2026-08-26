<?php
defined( 'ABSPATH' ) || exit;

interface Watphou_Payment_Provider {
	public function create_session( object $booking, float $amount, string $currency, string $reference ): array;
	public function verify_callback( array $payload, string $signature ): bool;
	public function get_status( string $reference ): string;
	public function cancel( string $reference ): bool;
}
