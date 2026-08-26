<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_admin_notices(): void {
	if ( ! current_user_can( 'edit_tours' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'tour', 'page', 'post' ), true ) ) {
		return;
	}
	if ( function_exists( 'pll_get_post_language' ) && function_exists( 'pll_languages_list' ) ) {
		$langs = pll_languages_list();
		$post_id = get_the_ID();
		if ( $post_id ) {
			foreach ( $langs as $lang ) {
				if ( 'en' === $lang ) {
					continue;
				}
				$trans_id = pll_get_post( $post_id, $lang );
				if ( ! $trans_id ) {
					echo '<div class="notice notice-info"><p>' . esc_html(
						sprintf(
							/* translators: %s language code */
							__( 'Missing %s translation for this content.', 'watphou-core' ),
							strtoupper( $lang )
						)
					) . '</p></div>';
					break;
				}
			}
		}
	}
	$price = get_post_meta( get_the_ID(), 'tour_price_from', true );
	if ( 'tour' === $screen->post_type && ( ! $price || 'XX' === $price ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Price not set — displaying "From $XX" placeholder on the website.', 'watphou-core' ) . '</p></div>';
	}
}
