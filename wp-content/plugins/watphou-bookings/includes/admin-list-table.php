<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Watphou_Bookings_List_Table extends WP_List_Table {
	public function get_columns(): array {
		return array(
			'reference'      => __( 'Reference', 'watphou-bookings' ),
			'customer_name'  => __( 'Customer', 'watphou-bookings' ),
			'tour_name'      => __( 'Tour', 'watphou-bookings' ),
			'preferred_date' => __( 'Date', 'watphou-bookings' ),
			'status'         => __( 'Status', 'watphou-bookings' ),
			'created_at'     => __( 'Created', 'watphou-bookings' ),
		);
	}

	public function prepare_items(): void {
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$this->items = Watphou_Booking_Repository::list( $status ? array( 'status' => $status ) : array() );
		$this->_column_headers = array( $this->get_columns(), array(), array() );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'reference':
				return '<a href="' . esc_url( admin_url( 'admin.php?page=watphou-bookings&booking_id=' . (int) $item->id ) ) . '">' . esc_html( $item->reference ) . '</a>';
			default:
				return esc_html( $item->$column_name ?? '' );
		}
	}
}
