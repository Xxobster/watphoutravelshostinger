<?php
/**
 * Plugin Name: Watphou Bookings
 * Description: Booking requests, state machine, and payment workflow for Watphou Travels.
 * Version: 1.0.0
 * Author: Watphou Travels
 * Text Domain: watphou-bookings
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WATPHOU_BOOKINGS_VERSION', '1.0.0' );
define( 'WATPHOU_BOOKINGS_PATH', plugin_dir_path( __FILE__ ) );

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
