<?php
/**
 * CLI check: both office Gmail addresses stay unique and ordered.
 */
define( 'ABSPATH', __DIR__ . '/' );
require dirname( __DIR__ ) . '/wp-content/plugins/watphou-core/includes/mail.php';

$got = watphou_unique_office_emails(
	'sales.watphoutravel@gmail.com',
	'watphoutravel.of@gmail.com'
);
$want = array( 'sales.watphoutravel@gmail.com', 'watphoutravel.of@gmail.com' );
if ( $got !== $want ) {
	fwrite( STDERR, 'expected both inboxes, got ' . implode( ',', $got ) . PHP_EOL );
	exit( 1 );
}

$dedupe = watphou_unique_office_emails(
	'sales.watphoutravel@gmail.com',
	'sales.watphoutravel@gmail.com'
);
if ( array( 'sales.watphoutravel@gmail.com' ) !== $dedupe ) {
	fwrite( STDERR, 'duplicate emails were not collapsed' . PHP_EOL );
	exit( 1 );
}

$fallback = watphou_unique_office_emails( '', 'not-an-email' );
if ( array( 'sales.watphoutravel@gmail.com' ) !== $fallback ) {
	fwrite( STDERR, 'empty list should fall back to sales inbox' . PHP_EOL );
	exit( 1 );
}

echo "office email recipients: OK\n";
