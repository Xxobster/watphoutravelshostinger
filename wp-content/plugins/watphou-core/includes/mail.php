<?php
defined( 'ABSPATH' ) || exit;

/**
 * Unique office inboxes. Primary first, then alt. Empty or duplicate values are dropped.
 *
 * @return string[]
 */
function watphou_unique_office_emails( string $primary, string $alt ): array {
	$out = array();
	foreach ( array( $primary, $alt ) as $raw ) {
		$email = function_exists( 'sanitize_email' ) ? sanitize_email( $raw ) : strtolower( trim( $raw ) );
		$ok    = function_exists( 'is_email' )
			? (bool) is_email( $email )
			: (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
		if ( $email && $ok && ! in_array( $email, $out, true ) ) {
			$out[] = $email;
		}
	}
	return $out ? $out : array( 'sales.watphoutravel@gmail.com' );
}

/**
 * Inboxes that receive tour-request mail.
 *
 * @return string[]
 */
function watphou_office_email_recipients(): array {
	return watphou_unique_office_emails(
		(string) get_option( 'watphou_email', 'sales.watphoutravel@gmail.com' ),
		(string) get_option( 'watphou_email_alt', 'watphoutravel.of@gmail.com' )
	);
}
