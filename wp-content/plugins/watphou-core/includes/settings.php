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
					<td><input name="watphou_email" type="email" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'Alt email', 'watphou-core' ); ?></th>
					<td><input name="watphou_email_alt" type="email" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_email_alt', 'watphoutravel.of@gmail.com' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'Address', 'watphou-core' ); ?></th>
					<td><textarea name="watphou_address" class="large-text" rows="3"><?php echo esc_textarea( get_option( 'watphou_address', 'Street N°5, Ban Vat Luang, Pakse, Laos' ) ); ?></textarea></td></tr>
				<tr><th><?php esc_html_e( 'Facebook URL', 'watphou-core' ); ?></th>
					<td><input name="watphou_facebook" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_facebook', '' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'Instagram URL', 'watphou-core' ); ?></th>
					<td><input name="watphou_instagram" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_instagram', '' ) ); ?>"/></td></tr>
				<tr><th><?php esc_html_e( 'TripAdvisor URL', 'watphou-core' ); ?></th>
					<td><input name="watphou_tripadvisor" class="regular-text" value="<?php echo esc_attr( get_option( 'watphou_tripadvisor', '' ) ); ?>"/></td></tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
