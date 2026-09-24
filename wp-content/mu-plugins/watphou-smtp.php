<?php
/**
 * Plugin Name: Watphou SMTP
 * Description: Send WordPress mail through Hostinger or authenticated Simple Mail Transfer Protocol (SMTP).
 */

defined( 'ABSPATH' ) || exit;

$watphou_smtp_secret = WP_CONTENT_DIR . '/watphou-smtp-secrets.php';
if ( is_readable( $watphou_smtp_secret ) ) {
	require_once $watphou_smtp_secret;
}

/**
 * Envelope / From address on the current public host (not a Gmail address).
 */
function watphou_smtp_from_address(): string {
	if ( defined( 'WATPHOU_SMTP_FROM' ) && is_email( (string) WATPHOU_SMTP_FROM ) ) {
		return (string) WATPHOU_SMTP_FROM;
	}
	$host = function_exists( 'wp_parse_url' ) ? (string) wp_parse_url( home_url(), PHP_URL_HOST ) : '';
	if ( '' === $host || str_contains( $host, 'hostingersite.com' ) ) {
		$host = 'watphoutravels.site';
	}
	if ( str_starts_with( strtolower( $host ), 'www.' ) ) {
		$host = substr( $host, 4 );
	}
	return 'bookings@' . $host;
}

function watphou_smtp_from_name(): string {
	if ( defined( 'WATPHOU_SMTP_FROM_NAME' ) && '' !== (string) WATPHOU_SMTP_FROM_NAME ) {
		return (string) WATPHOU_SMTP_FROM_NAME;
	}
	return 'Watphou Travels';
}

/**
 * Authenticated SMTP when WATPHOU_SMTP_USER and WATPHOU_SMTP_PASS are set.
 * Otherwise PHP mail() with a From address on this domain (Hostinger shared mail).
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer.
 */
function watphou_smtp_configure_phpmailer( $phpmailer ): void {
	$from = watphou_smtp_from_address();
	$phpmailer->setFrom( $from, watphou_smtp_from_name(), false );
	$phpmailer->Sender = $from;

	$user = defined( 'WATPHOU_SMTP_USER' ) ? (string) WATPHOU_SMTP_USER : '';
	$pass = defined( 'WATPHOU_SMTP_PASS' ) ? (string) WATPHOU_SMTP_PASS : '';
	$host = defined( 'WATPHOU_SMTP_HOST' ) ? (string) WATPHOU_SMTP_HOST : '';
	if ( '' === $host && '' !== $user && '' !== $pass ) {
		$host = ( false !== strpos( $user, '@gmail.com' ) ) ? 'smtp.gmail.com' : 'smtp.hostinger.com';
	}
	if ( '' === $host ) {
		return;
	}

	$port   = defined( 'WATPHOU_SMTP_PORT' ) ? (int) WATPHOU_SMTP_PORT : 0;
	$secure = defined( 'WATPHOU_SMTP_SECURE' ) ? strtolower( (string) WATPHOU_SMTP_SECURE ) : '';
	if ( $port < 1 ) {
		$port = ( 'tls' === $secure || 'smtp.gmail.com' === $host ) ? 587 : 465;
	}
	if ( '' === $secure ) {
		$secure = ( 587 === $port ) ? 'tls' : 'ssl';
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = $host;
	$phpmailer->Port       = $port;
	$phpmailer->SMTPSecure = $secure;
	if ( '' !== $user && '' !== $pass ) {
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $user;
		$phpmailer->Password = $pass;
	} else {
		$phpmailer->SMTPAuth = false;
	}
}

add_action( 'phpmailer_init', 'watphou_smtp_configure_phpmailer' );
add_filter( 'wp_mail_from', 'watphou_smtp_from_address', 20 );
add_filter( 'wp_mail_from_name', 'watphou_smtp_from_name', 20 );
