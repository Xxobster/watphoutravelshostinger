<?php
defined( 'ABSPATH' ) || exit;

function watphou_bookings_detail_page( int $id ): void {
	$booking = Watphou_Booking_Repository::get( $id );
	if ( ! $booking ) {
		echo '<div class="wrap"><p>' . esc_html__( 'Booking not found.', 'watphou-bookings' ) . '</p></div>';
		return;
	}

	if ( isset( $_POST['watphou_booking_action'] ) && check_admin_referer( 'watphou_booking_' . $id ) ) {
		$action = sanitize_text_field( wp_unslash( $_POST['watphou_booking_action'] ) );
		$note   = sanitize_textarea_field( wp_unslash( $_POST['staff_note'] ?? '' ) );
		if ( 'send_payment' === $action ) {
			global $wpdb;
			if ( 'availability_confirmed' === $booking->status ) {
				Watphou_Booking_Repository::transition( $id, 'quote_sent', get_current_user_id(), 'Quote prepared' );
				$booking = Watphou_Booking_Repository::get( $id );
			}
			$token   = bin2hex( random_bytes( 16 ) );
			$expires = gmdate( 'Y-m-d H:i:s', time() + 7 * DAY_IN_SECONDS );
			$wpdb->update(
				Watphou_Booking_Repository::table(),
				array(
					'payment_token'      => $token,
					'payment_expires_at' => $expires,
					'quoted_amount'      => floatval( $_POST['quoted_amount'] ?? 0 ),
					'updated_at'         => current_time( 'mysql' ),
				),
				array( 'id' => $id )
			);
			Watphou_Booking_Repository::transition( $id, 'payment_pending', get_current_user_id(), 'Payment link created' );
			$booking = Watphou_Booking_Repository::get( $id );
		} elseif ( Watphou_Booking_State_Machine::can_transition( $booking->status, $action ) ) {
			Watphou_Booking_Repository::transition( $id, $action, get_current_user_id(), $note );
			$booking = Watphou_Booking_Repository::get( $id );
		}
	}
	$pay_url = $booking->payment_token ? home_url( '/pay/' . $booking->payment_token ) : '';
	?>
	<div class="wrap">
		<h1><?php echo esc_html( $booking->reference ); ?> — <?php echo esc_html( $booking->status ); ?></h1>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-bookings' ) ); ?>">&larr; <?php esc_html_e( 'Back', 'watphou-bookings' ); ?></a></p>
		<table class="widefat striped"><tbody>
			<?php foreach ( array( 'customer_name', 'email', 'phone', 'whatsapp', 'tour_name', 'preferred_date', 'adults', 'customer_message' ) as $f ) : ?>
				<tr><th><?php echo esc_html( ucwords( str_replace( '_', ' ', $f ) ) ); ?></th><td><?php echo esc_html( $booking->$f ?? '' ); ?></td></tr>
			<?php endforeach; ?>
		</tbody></table>
		<h2><?php esc_html_e( 'Actions', 'watphou-bookings' ); ?></h2>
		<form method="post">
			<?php wp_nonce_field( 'watphou_booking_' . $id ); ?>
			<p><textarea name="staff_note" class="large-text" rows="3" placeholder="<? esc_attr_e( 'Internal note', 'watphou-bookings' ); ?>"></textarea></p>
			<p>
				<?php foreach ( Watphou_Booking_State_Machine::allowed_transitions( $booking->status ) as $next ) : ?>
					<button type="submit" name="watphou_booking_action" value="<?php echo esc_attr( $next ); ?>" class="button"><?php echo esc_html( $next ); ?></button>
				<?php endforeach; ?>
			</p>
			<?php if ( in_array( $booking->status, array( 'quote_sent', 'availability_confirmed' ), true ) ) : ?>
				<p>
					<label><?php esc_html_e( 'Quoted amount (USD)', 'watphou-bookings' ); ?>
						<input type="number" step="0.01" name="quoted_amount" value="<?php echo esc_attr( $booking->quoted_amount ); ?>"/>
					</label>
					<button type="submit" name="watphou_booking_action" value="send_payment" class="button button-primary"><?php esc_html_e( 'Create payment link', 'watphou-bookings' ); ?></button>
				</p>
			<?php endif; ?>
		</form>
		<?php if ( $pay_url ) : ?>
			<h2><?php esc_html_e( 'Payment link', 'watphou-bookings' ); ?></h2>
			<p><code><?php echo esc_html( $pay_url ); ?></code></p>
			<p><a class="button" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', get_option( 'watphou_whatsapp', '+8562099495858' ) ) . '?text=' . rawurlencode( 'Your payment link: ' . $pay_url ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Copy to WhatsApp', 'watphou-bookings' ); ?></a></p>
		<?php endif; ?>
	</div>
	<?php
}
