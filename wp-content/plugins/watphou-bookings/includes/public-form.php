<?php
defined( 'ABSPATH' ) || exit;

add_shortcode( 'watphou_booking_form', 'watphou_booking_form_shortcode' );
add_action( 'wp_ajax_watphou_submit_booking', 'watphou_handle_booking_submit' );
add_action( 'wp_ajax_nopriv_watphou_submit_booking', 'watphou_handle_booking_submit' );

function watphou_booking_form_shortcode( $atts ): string {
	$atts = shortcode_atts( array( 'tour_id' => 0 ), $atts, 'watphou_booking_form' );
	$tour_id = (int) $atts['tour_id'];
	ob_start();
	?>
	<form class="watphou-booking-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
		<input type="hidden" name="action" value="watphou_submit_booking"/>
		<?php wp_nonce_field( 'watphou_booking_submit', 'watphou_booking_nonce' ); ?>
		<input type="hidden" name="tour_id" value="<?php echo esc_attr( $tour_id ); ?>"/>
		<p><label><?php esc_html_e( 'Name', 'watphou-bookings' ); ?> <input required name="customer_name" type="text"/></label></p>
		<p><label><?php esc_html_e( 'Email', 'watphou-bookings' ); ?> <input required name="email" type="email"/></label></p>
		<p><label><?php esc_html_e( 'Phone', 'watphou-bookings' ); ?> <input name="phone" type="tel"/></label></p>
		<p><label><?php esc_html_e( 'WhatsApp', 'watphou-bookings' ); ?> <input name="whatsapp" type="tel"/></label></p>
		<p><label><?php esc_html_e( 'Preferred date', 'watphou-bookings' ); ?> <input required name="preferred_date" type="date"/></label></p>
		<p><label><?php esc_html_e( 'Alternative date', 'watphou-bookings' ); ?> <input name="alt_date" type="date"/></label></p>
		<p><label><?php esc_html_e( 'Adults', 'watphou-bookings' ); ?> <input name="adults" type="number" min="1" value="2"/></label></p>
		<p><label><?php esc_html_e( 'Children', 'watphou-bookings' ); ?> <input name="children" type="number" min="0" value="0"/></label></p>
		<p><label><?php esc_html_e( 'Country', 'watphou-bookings' ); ?> <input name="country" type="text"/></label></p>
		<p><label><?php esc_html_e( 'Pickup / hotel', 'watphou-bookings' ); ?> <input name="pickup_location" type="text"/></label></p>
		<p><label><?php esc_html_e( 'Message', 'watphou-bookings' ); ?> <textarea name="customer_message" rows="4"></textarea></label></p>
		<p><label><input required name="privacy_consent" type="checkbox" value="1"/> <?php esc_html_e( 'I agree to the privacy policy.', 'watphou-bookings' ); ?></label></p>
		<p><button type="submit" class="wp-block-button__link"><?php esc_html_e( 'Request Booking', 'watphou-bookings' ); ?></button></p>
	</form>
	<?php
	return ob_get_clean();
}

function watphou_handle_booking_submit(): void {
	check_ajax_referer( 'watphou_booking_submit', 'watphou_booking_nonce' );
	$tour_id = (int) ( $_POST['tour_id'] ?? 0 );
	$tour    = $tour_id ? get_post( $tour_id ) : null;
	$id      = Watphou_Booking_Repository::create(
		array(
			'tour_id'          => $tour_id,
			'tour_name'        => $tour ? $tour->post_title : sanitize_text_field( wp_unslash( $_POST['tour_name'] ?? '' ) ),
			'customer_name'    => sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) ),
			'email'            => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'phone'            => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'whatsapp'         => sanitize_text_field( wp_unslash( $_POST['whatsapp'] ?? '' ) ),
			'preferred_date'   => sanitize_text_field( wp_unslash( $_POST['preferred_date'] ?? '' ) ),
			'alt_date'         => sanitize_text_field( wp_unslash( $_POST['alt_date'] ?? '' ) ),
			'adults'           => (int) ( $_POST['adults'] ?? 1 ),
			'children'         => (int) ( $_POST['children'] ?? 0 ),
			'country'          => sanitize_text_field( wp_unslash( $_POST['country'] ?? '' ) ),
			'pickup_location'  => sanitize_textarea_field( wp_unslash( $_POST['pickup_location'] ?? '' ) ),
			'customer_message' => sanitize_textarea_field( wp_unslash( $_POST['customer_message'] ?? '' ) ),
			'privacy_consent'  => ! empty( $_POST['privacy_consent'] ) ? 1 : 0,
			'language'         => function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en',
		)
	);
	wp_mail(
		get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ),
		'New booking request',
		"New booking #{$id} from {$_POST['customer_name']}"
	);
	wp_send_json_success( array( 'booking_id' => $id, 'message' => __( 'Thank you! We will confirm availability shortly.', 'watphou-bookings' ) ) );
}
