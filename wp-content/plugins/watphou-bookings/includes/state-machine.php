<?php
defined( 'ABSPATH' ) || exit;

class Watphou_Booking_State_Machine {
	private static array $transitions = array(
		'requested'              => array( 'under_review', 'rejected' ),
		'under_review'           => array( 'availability_confirmed', 'rejected' ),
		'availability_confirmed' => array( 'quote_sent' ),
		'quote_sent'             => array( 'payment_pending', 'cancelled' ),
		'payment_pending'        => array( 'paid', 'payment_failed', 'expired' ),
		'payment_failed'         => array( 'payment_pending' ),
		'paid'                   => array( 'confirmed' ),
		'confirmed'              => array( 'refunded', 'cancelled' ),
		'expired'                => array( 'quote_sent' ),
	);

	public static function can_transition( string $from, string $to ): bool {
		return isset( self::$transitions[ $from ] ) && in_array( $to, self::$transitions[ $from ], true );
	}

	public static function allowed_transitions( string $from ): array {
		return self::$transitions[ $from ] ?? array();
	}

	public static function all_statuses(): array {
		return array_keys( self::$transitions );
	}
}
