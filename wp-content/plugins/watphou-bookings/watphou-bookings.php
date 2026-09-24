<?php
/**
 * Plugin Name: Watphou Bookings
 * Description: Booking requests, state machine, and payment workflow for Watphou Travels.
 * Version: 1.2.1
 * Author: Watphou Travels
 * Text Domain: watphou-bookings
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WATPHOU_BOOKINGS_VERSION', '1.2.1' );
define( 'WATPHOU_BOOKINGS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WATPHOU_BOOKINGS_URL', plugin_dir_url( __FILE__ ) );

require_once WATPHOU_BOOKINGS_PATH . 'includes/database.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/state-machine.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/repository.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/admin-list-table.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/admin-booking-detail.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/public-form.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/payment/interface.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/payment/mock-provider.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/payment/bcel-provider.php';
require_once WATPHOU_BOOKINGS_PATH . 'includes/payment-router.php';

add_action( 'wp_enqueue_scripts', 'watphou_bookings_enqueue_public' );

function watphou_bookings_enqueue_public(): void {
	if ( ! watphou_bookings_page_needs_form_script() ) {
		return;
	}
	wp_enqueue_script(
		'watphou-bookings-form',
		WATPHOU_BOOKINGS_URL . 'assets/form.js',
		array(),
		WATPHOU_BOOKINGS_VERSION,
		true
	);
	wp_localize_script(
		'watphou-bookings-form',
		'watphouBookings',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'i18n'    => array(
				'missingTitle' => __( 'Please complete these required fields:', 'watphou-bookings' ),
				'sending'      => __( 'Sending…', 'watphou-bookings' ),
				'network'      => __( 'The request could not be sent. Check your connection and try again.', 'watphou-bookings' ),
			),
		)
	);
}

/**
 * The request form is on the homepage, tour pages, and pages that contain the shortcode.
 */
function watphou_bookings_page_needs_form_script(): bool {
	if ( is_admin() ) {
		return false;
	}
	if ( is_front_page() || is_singular( 'tour' ) ) {
		return true;
	}
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( has_shortcode( $post->post_content, 'watphou_booking_form' ) || str_contains( $post->post_content, 'watphou_booking_form' ) ) {
		return true;
	}
	$slug = (string) $post->post_name;
	$slug = preg_replace( '/-(?:2|3|fr|th)$/', '', $slug ) ?: $slug;
	return in_array( $slug, array( 'book-online', 'contact-us', 'tailor-made-tours' ), true );
}

register_activation_hook( __FILE__, 'watphou_bookings_activate' );

function watphou_bookings_activate(): void {
	watphou_bookings_create_tables();
}

add_action( 'admin_menu', 'watphou_bookings_admin_menu' );
add_action( 'init', 'watphou_bookings_register_rewrite' );
add_action( 'template_redirect', 'watphou_bookings_handle_pay_route' );
add_action( 'rest_api_init', 'watphou_bookings_register_callback_route' );

function watphou_bookings_admin_menu(): void {
	add_menu_page(
		__( 'Bookings', 'watphou-bookings' ),
		__( 'Bookings', 'watphou-bookings' ),
		'manage_watphou_bookings',
		'watphou-bookings',
		'watphou_bookings_list_page',
		'dashicons-calendar-alt',
		4
	);
}

function watphou_bookings_register_rewrite(): void {
	add_rewrite_rule( '^pay/([a-zA-Z0-9]+)/?$', 'index.php?watphou_pay_token=$matches[1]', 'top' );
	add_rewrite_tag( '%watphou_pay_token%', '([a-zA-Z0-9]+)' );
}

function watphou_bookings_list_page(): void {
	if ( ! current_user_can( 'manage_watphou_bookings' ) ) {
		wp_die( esc_html__( 'Access denied.', 'watphou-bookings' ) );
	}
	if ( isset( $_GET['booking_id'] ) ) {
		watphou_bookings_detail_page( (int) $_GET['booking_id'] );
		return;
	}
	$table = new Watphou_Bookings_List_Table();
	$table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Booking Requests', 'watphou-bookings' ); ?></h1>
		<form method="get">
			<input type="hidden" name="page" value="watphou-bookings"/>
			<?php $table->display(); ?>
		</form>
	</div>
	<?php
}
