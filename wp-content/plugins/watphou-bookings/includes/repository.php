<?php
defined( 'ABSPATH' ) || exit;

class Watphou_Booking_Repository {
	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'watphou_bookings';
	}

	public static function events_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'watphou_booking_events';
	}

	public static function generate_reference(): string {
		return 'WPT-' . strtoupper( wp_generate_password( 8, false, false ) );
	}

	public static function create( array $data ): int {
		global $wpdb;
		$now = current_time( 'mysql' );
		$wpdb->insert(
			self::table(),
			array_merge(
				$data,
				array(
					'reference'  => self::generate_reference(),
					'status'     => 'requested',
					'created_at' => $now,
					'updated_at' => $now,
				)
			),
			self::formats( $data )
		);
		$id = (int) $wpdb->insert_id;
		self::log_event( $id, null, 'requested', 0, 'Booking created' );
		return $id;
	}

	public static function get( int $id ): ?object {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
	}

	public static function get_by_token( string $token ): ?object {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE payment_token = %s', $token ) );
	}

	public static function count_by_status( string $status ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE status = %s', $status ) );
	}

	public static function transition( int $id, string $to, int $user_id = 0, string $note = '' ): bool {
		global $wpdb;
		$row = self::get( $id );
		if ( ! $row || ! Watphou_Booking_State_Machine::can_transition( $row->status, $to ) ) {
			return false;
		}
		$wpdb->update(
			self::table(),
			array( 'status' => $to, 'updated_at' => current_time( 'mysql' ) ),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		self::log_event( $id, $row->status, $to, $user_id, $note );
		return true;
	}

	public static function log_event( int $booking_id, ?string $from, string $to, int $user_id, string $note ): void {
		global $wpdb;
		$wpdb->insert(
			self::events_table(),
			array(
				'booking_id'  => $booking_id,
				'from_status' => $from,
				'to_status'   => $to,
				'user_id'     => $user_id,
				'note'        => $note,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s' )
		);
	}

	public static function list( array $args = array() ): array {
		global $wpdb;
		$where = '1=1';
		$params = array();
		if ( ! empty( $args['status'] ) ) {
			$where   .= ' AND status = %s';
			$params[] = $args['status'];
		}
		$sql = 'SELECT * FROM ' . self::table() . " WHERE {$where} ORDER BY created_at DESC LIMIT 100";
		if ( $params ) {
			$sql = $wpdb->prepare( $sql, ...$params );
		}
		return $wpdb->get_results( $sql );
	}

	private static function formats( array $data ): array {
		$formats = array();
		foreach ( array_keys( $data ) as $key ) {
			$formats[] = in_array( $key, array( 'tour_id', 'adults', 'children', 'privacy_consent' ), true ) ? '%d' : '%s';
		}
		return $formats;
	}
}
