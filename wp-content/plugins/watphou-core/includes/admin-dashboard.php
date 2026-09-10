<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_dashboard_page(): void {
	if ( ! current_user_can( 'edit_tours' ) ) {
		wp_die( esc_html__( 'Access denied.', 'watphou-core' ) );
	}

	$new_bookings = 0;
	if ( class_exists( 'Watphou_Booking_Repository' ) ) {
		$new_bookings = Watphou_Booking_Repository::count_by_status( 'requested' );
	}

	$missing_prices = get_posts(
		array(
			'post_type'      => 'tour',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				array( 'key' => 'tour_price_from', 'compare' => 'NOT EXISTS' ),
				array( 'key' => 'tour_price_from', 'value' => array( '', 'XX' ), 'compare' => 'IN' ),
			),
			'fields'         => 'ids',
		)
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Watphou Travels Dashboard', 'watphou-core' ); ?></h1>
		<?php if ( defined( 'WATPHOU_DEMO' ) && WATPHOU_DEMO ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'Demonstration website — not for public use.', 'watphou-core' ); ?></p></div>
		<?php endif; ?>
		<div class="watphou-dashboard-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin:1.5rem 0;">
			<div class="postbox" style="padding:1rem;"><h2><?php echo (int) $new_bookings; ?></h2><p><?php esc_html_e( 'New booking requests', 'watphou-core' ); ?></p></div>
			<div class="postbox" style="padding:1rem;"><h2><?php echo count( $missing_prices ); ?></h2><p><?php esc_html_e( 'Tours missing prices', 'watphou-core' ); ?></p></div>
		</div>
		<h2><?php esc_html_e( 'Quick links', 'watphou-core' ); ?></h2>
		<ul>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-tour-desk' ) ); ?>"><?php esc_html_e( 'Quick edit prices & photos', 'watphou-core' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=tour' ) ); ?>"><?php esc_html_e( 'Add a tour', 'watphou-core' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=tour' ) ); ?>"><?php esc_html_e( 'All tours (full list)', 'watphou-core' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-bookings' ) ); ?>"><?php esc_html_e( 'Booking requests', 'watphou-core' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'options-general.php?page=watphou-settings' ) ); ?>"><?php esc_html_e( 'Contact settings', 'watphou-core' ); ?></a></li>
		</ul>
		<h2><?php esc_html_e( 'Editing tips', 'watphou-core' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Prices and photos: Watphou → Quick edit tours (do not use Appearance → Customize).', 'watphou-core' ); ?></li>
			<li><?php esc_html_e( 'Long text and itinerary: Tours → click the tour → Edit text.', 'watphou-core' ); ?></li>
			<li><?php esc_html_e( 'Use Itinerary Day blocks for day-by-day schedules.', 'watphou-core' ); ?></li>
			<li><?php esc_html_e( 'Thai pages need a professional translator — do not machine-translate.', 'watphou-core' ); ?></li>
		</ol>
	</div>
	<?php
}
