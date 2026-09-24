<?php
defined( 'ABSPATH' ) || exit;

function watphou_core_register_settings(): void {
	register_setting( 'watphou_settings', 'watphou_phone', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'watphou_settings', 'watphou_whatsapp', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'watphou_settings', 'watphou_email', array( 'sanitize_callback' => 'sanitize_email' ) );
	register_setting( 'watphou_settings', 'watphou_email_alt', array( 'sanitize_callback' => 'sanitize_email' ) );
	register_setting( 'watphou_settings', 'watphou_address', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
	register_setting( 'watphou_settings', 'watphou_facebook', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'watphou_settings', 'watphou_instagram', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'watphou_settings', 'watphou_tripadvisor', array( 'sanitize_callback' => 'esc_url_raw' ) );
	register_setting( 'watphou_settings', 'watphou_ga4_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'watphou_settings', 'watphou_gsc_verification', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting(
		'watphou_settings',
		'watphou_google_places_key',
		array(
			'sanitize_callback' => 'watphou_core_sanitize_google_places_key',
			'show_in_rest'      => false,
		)
	);
}

function watphou_core_sanitize_google_places_key( $value ): string {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return (string) get_option( 'watphou_google_places_key', '' );
	}
	return sanitize_text_field( $value );
}

function watphou_core_admin_menu(): void {
	add_options_page(
		__( 'Watphou Settings', 'watphou-core' ),
		__( 'Watphou Travels', 'watphou-core' ),
		'manage_options',
		'watphou-settings',
		'watphou_core_settings_page'
	);
	add_menu_page(
		__( 'Watphou Dashboard', 'watphou-core' ),
		__( 'Watphou', 'watphou-core' ),
		'edit_tours',
		'watphou-dashboard',
		'watphou_core_dashboard_page',
		'dashicons-palmtree',
		3
	);
}

function watphou_core_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Watphou Travels — Global Settings', 'watphou-core' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'watphou_settings' ); ?>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'Phone', 'watphou-core' ); ?></th>
					<td><input name="watphou_phone" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_phone', '+85620 9949 5858' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'WhatsApp', 'watphou-core' ); ?></th>
					<td><input name="watphou_whatsapp" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_whatsapp', '+8562099495858' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'Email', 'watphou-core' ); ?></th>
					<td>
						<input name="watphou_email" type="email" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ) ); ?>"/>
						<p class="description"><?php esc_html_e( 'Primary office inbox. Tour requests are emailed here.', 'watphou-core' ); ?></p>
					</td></tr>
				<tr><th><?php esc_html_e( 'Alt email', 'watphou-core' ); ?></th>
					<td>
						<input name="watphou_email_alt" type="email" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_email_alt', 'watphoutravel.of@gmail.com' ) ); ?>"/>
						<p class="description"><?php esc_html_e( 'Second office inbox. The same tour request is also emailed here (shown on About / Contact as well).', 'watphou-core' ); ?></p>
					</td></tr>
				<tr><th><?php esc_html_e( 'Address', 'watphou-core' ); ?></th>
					<td><textarea name="watphou_address" class="large-text" rows="3"><?php echo esc_textarea( get_option( 'watphou_address', 'Street N°5, Ban Vat Luang, Pakse, Laos' ) ); ?></textarea></td></tr>
				<tr><th><?php esc_html_e( 'Facebook URL', 'watphou-core' ); ?></th>
					<td><input name="watphou_facebook" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_facebook', '' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'Instagram URL', 'watphou-core' ); ?></th>
					<td><input name="watphou_instagram" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_instagram', '' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'TripAdvisor URL', 'watphou-core' ); ?></th>
					<td><input name="watphou_tripadvisor" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_tripadvisor', '' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'Google Analytics 4 measurement ID', 'watphou-core' ); ?></th>
					<td>
						<input name="watphou_ga4_id" class="regular-text" placeholder="G-XXXXXXXX" value="<?php echo esc_attr( get_option( 'watphou_ga4_id', '' ) ); ?>"/>
						<p class="description"><?php esc_html_e( 'Leave empty until you have a real Google Analytics 4 (GA4) ID. Not used on staging.', 'watphou-core' ); ?></p>
					</td>
				</tr>
				<tr><th><?php esc_html_e( 'Google Search Console verification', 'watphou-core' ); ?></th>
					<td>
						<input name="watphou_gsc_verification" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_gsc_verification', '' ) ); ?>"/>
						<p class="description"><?php esc_html_e( 'HTML-tag content only. Use this on the real domain, not the Hostinger temporary domain.', 'watphou-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Google Places Application Programming Interface key', 'watphou-core' ); ?></th>
					<td>
						<?php
						$places_from_const = defined( 'WATPHOU_GOOGLE_PLACES_KEY' ) && is_string( WATPHOU_GOOGLE_PLACES_KEY ) && '' !== WATPHOU_GOOGLE_PLACES_KEY;
						$places_saved      = (string) get_option( 'watphou_google_places_key', '' );
						if ( $places_from_const ) :
							?>
							<p class="description"><?php esc_html_e( 'A key is already set in wp-config.php as WATPHOU_GOOGLE_PLACES_KEY. The homepage will refresh the newest 4–5 star Google reviews about once a day.', 'watphou-core' ); ?></p>
						<?php else : ?>
							<input name="watphou_google_places_key" type="password" autocomplete="off" class="regular-text" value="" placeholder="<?php echo $places_saved ? esc_attr__( 'Key saved — paste a new one to replace it', 'watphou-core' ) : ''; ?>"/>
							<p class="description"><?php esc_html_e( 'Optional. Restrict this key to Places Application Programming Interface (Place Details) for this website. Google returns at most five reviews per request; we keep 4–5 star quotes, newest first. Leave blank to keep a saved key. Never put the key in git.', 'watphou-core' ); ?></p>
						<?php endif; ?>
						<?php if ( function_exists( 'watphou_core_google_places_key' ) && watphou_core_google_places_key() ) : ?>
							<p>
								<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=watphou_refresh_google_reviews' ), 'watphou_refresh_google_reviews' ) ); ?>">
									<?php esc_html_e( 'Refresh Google reviews now', 'watphou-core' ); ?>
								</a>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
		$reviews_ok  = isset( $_GET['watphou_reviews_refreshed'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['watphou_reviews_refreshed'] ) );
		$reviews_err = isset( $_GET['watphou_reviews_error'] ) ? sanitize_key( wp_unslash( $_GET['watphou_reviews_error'] ) ) : '';
		if ( $reviews_ok ) :
			?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Google reviews cache was refreshed.', 'watphou-core' ); ?></p></div>
		<?php elseif ( $reviews_err ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Google reviews refresh failed. Check the key and that Places Application Programming Interface Place Details is enabled.', 'watphou-core' ); ?></p></div>
		<?php endif; ?>
	</div>
	<?php
}
