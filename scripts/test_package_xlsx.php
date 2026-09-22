<?php
/**
 * CLI check: parse the example workbook without WordPress.
 */
define( 'WATPHOU_XLSX_STANDALONE', true );

$path = dirname( __DIR__ ) . '/wp-content/plugins/watphou-core/data/watphou-package-import-example.xlsx';
require dirname( __DIR__ ) . '/wp-content/plugins/watphou-core/includes/xlsx-reader.php';

if ( ! is_readable( $path ) ) {
	fwrite( STDERR, "missing $path\n" );
	exit( 1 );
}

$sheets = watphou_xlsx_read( $path );
$names  = array_keys( $sheets );
echo 'sheets: ' . implode( ', ', $names ) . PHP_EOL;

if ( empty( $sheets['Tours'] ) ) {
	fwrite( STDERR, "Tours sheet missing or empty\n" );
	exit( 1 );
}
if ( empty( $sheets['Itinerary'] ) ) {
	fwrite( STDERR, "Itinerary sheet missing or empty\n" );
	exit( 1 );
}

$tour = $sheets['Tours'][0];
$need = array( 'slug', 'title', 'dream', 'highlights', 'included' );
foreach ( $need as $key ) {
	if ( empty( $tour[ $key ] ) ) {
		fwrite( STDERR, "missing Tours column $key\n" );
		print_r( array_keys( $tour ) );
		exit( 1 );
	}
}

if ( '3-day-classic-experience-in-southern-laos' !== $tour['slug'] ) {
	fwrite( STDERR, 'unexpected slug: ' . $tour['slug'] . PHP_EOL );
	exit( 1 );
}
if ( count( $sheets['Itinerary'] ) < 3 ) {
	fwrite( STDERR, 'expected 3 itinerary days, got ' . count( $sheets['Itinerary'] ) . PHP_EOL );
	exit( 1 );
}

echo 'tour: ' . $tour['title'] . PHP_EOL;
echo 'days: ' . count( $sheets['Itinerary'] ) . PHP_EOL;
echo 'highlights lines: ' . ( substr_count( $tour['highlights'], "\n" ) + 1 ) . PHP_EOL;
echo "OK\n";
exit( 0 );
