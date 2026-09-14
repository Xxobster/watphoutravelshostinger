<?php
/**
 * Minimal .xlsx reader (shared strings + cell values). No Composer.
 */
if ( ! defined( 'ABSPATH' ) && ! defined( 'WATPHOU_XLSX_STANDALONE' ) ) {
	exit;
}

/**
 * Read an .xlsx file into named sheets of header-keyed rows.
 *
 * @return array<string, array<int, array<string, string>>>
 */
function watphou_xlsx_read( string $path ): array {
	if ( ! class_exists( 'ZipArchive' ) ) {
		throw new RuntimeException( 'PHP ZipArchive is required to read Excel files.' );
	}
	if ( ! is_readable( $path ) ) {
		throw new RuntimeException( 'Excel file is not readable.' );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $path ) ) {
		throw new RuntimeException( 'Could not open the Excel file. Save it as .xlsx (Excel workbook).' );
	}

	$strings = watphou_xlsx_shared_strings( (string) $zip->getFromName( 'xl/sharedStrings.xml' ) );
	$sheets  = watphou_xlsx_sheet_targets( $zip );
	$out     = array();

	foreach ( $sheets as $name => $inner ) {
		$xml = (string) $zip->getFromName( $inner );
		if ( '' === $xml ) {
			continue;
		}
		$grid = watphou_xlsx_sheet_grid( $xml, $strings );
		if ( empty( $grid ) ) {
			continue;
		}
		$out[ $name ] = watphou_xlsx_grid_to_assoc( $grid );
	}

	$zip->close();
	return $out;
}

/**
 * @return string[]
 */
function watphou_xlsx_shared_strings( string $xml ): array {
	if ( '' === $xml ) {
		return array();
	}
	$simple = watphou_xlsx_simplexml( $xml );
	if ( ! $simple ) {
		return array();
	}
	$out = array();
	foreach ( $simple->si as $si ) {
		$out[] = watphou_xlsx_si_text( $si );
	}
	return $out;
}

function watphou_xlsx_si_text( SimpleXMLElement $si ): string {
	$parts = array();
	if ( isset( $si->t ) ) {
		$parts[] = (string) $si->t;
	}
	if ( isset( $si->r ) ) {
		foreach ( $si->r as $run ) {
			if ( isset( $run->t ) ) {
				$parts[] = (string) $run->t;
			}
		}
	}
	return html_entity_decode( implode( '', $parts ), ENT_QUOTES | ENT_XML1, 'UTF-8' );
}

/**
 * @return array<string, string> sheet name => zip path
 */
function watphou_xlsx_sheet_targets( ZipArchive $zip ): array {
	$workbook_xml = (string) $zip->getFromName( 'xl/workbook.xml' );
	$rels_xml     = (string) $zip->getFromName( 'xl/_rels/workbook.xml.rels' );
	$rid_to_target = array();
	if ( preg_match_all( '/Id="([^"]+)"[^>]*Target="([^"]+)"/', $rels_xml, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $row ) {
			$rid_to_target[ $row[1] ] = ltrim( str_replace( '\\', '/', $row[2] ), '/' );
		}
	}
	if ( preg_match_all( '/Target="([^"]+)"[^>]*Id="([^"]+)"/', $rels_xml, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $row ) {
			$rid_to_target[ $row[2] ] = ltrim( str_replace( '\\', '/', $row[1] ), '/' );
		}
	}

	$out = array();
	if ( ! preg_match_all( '/<sheet\b[^>]*>/i', $workbook_xml, $tags ) ) {
		return $out;
	}
	foreach ( $tags[0] as $tag ) {
		$name = '';
		$rid  = '';
		if ( preg_match( '/name="([^"]+)"/', $tag, $nm ) ) {
			$name = html_entity_decode( $nm[1], ENT_QUOTES | ENT_XML1, 'UTF-8' );
		}
		if ( preg_match( '/r:id="([^"]+)"/i', $tag, $idm ) ) {
			$rid = $idm[1];
		}
		if ( '' === $name || '' === $rid || empty( $rid_to_target[ $rid ] ) ) {
			continue;
		}
		$target = $rid_to_target[ $rid ];
		if ( ! str_starts_with( $target, 'xl/' ) ) {
			$target = 'xl/' . $target;
		}
		$out[ $name ] = $target;
	}
	return $out;
}

/**
 * @param string[] $strings
 * @return array<int, array<int, string>>
 */
function watphou_xlsx_sheet_grid( string $xml, array $strings ): array {
	$simple = watphou_xlsx_simplexml( $xml );
	if ( ! $simple || ! isset( $simple->sheetData ) ) {
		return array();
	}
	$grid = array();
	foreach ( $simple->sheetData->row as $row ) {
		$ridx = (int) $row['r'];
		if ( $ridx < 1 ) {
			$ridx = count( $grid ) + 1;
		}
		foreach ( $row->c as $cell ) {
			$ref = (string) $cell['r'];
			$col = watphou_xlsx_col_index( $ref );
			$grid[ $ridx ][ $col ] = watphou_xlsx_cell_value( $cell, $strings );
		}
	}
	ksort( $grid );
	return $grid;
}

/**
 * @param array<int, array<int, string>> $grid
 * @return array<int, array<string, string>>
 */
function watphou_xlsx_grid_to_assoc( array $grid ): array {
	if ( empty( $grid ) ) {
		return array();
	}
	$header_row = array_shift( $grid );
	if ( ! is_array( $header_row ) ) {
		return array();
	}
	ksort( $header_row );
	$headers = array();
	foreach ( $header_row as $col => $raw ) {
		$key = watphou_xlsx_header_key( (string) $raw );
		if ( '' !== $key ) {
			$headers[ $col ] = $key;
		}
	}
	$rows = array();
	foreach ( $grid as $cells ) {
		$row    = array();
		$filled = false;
		foreach ( $headers as $col => $key ) {
			$val         = isset( $cells[ $col ] ) ? trim( (string) $cells[ $col ] ) : '';
			$row[ $key ] = $val;
			if ( '' !== $val ) {
				$filled = true;
			}
		}
		if ( $filled ) {
			$rows[] = $row;
		}
	}
	return $rows;
}

function watphou_xlsx_header_key( string $raw ): string {
	$n = strtolower( trim( $raw ) );
	$n = preg_replace( '/[^a-z0-9]+/', '_', $n );
	return trim( (string) $n, '_' );
}

function watphou_xlsx_col_index( string $ref ): int {
	$letters = strtoupper( (string) preg_replace( '/[^A-Z]/', '', $ref ) );
	$n       = 0;
	$len     = strlen( $letters );
	for ( $i = 0; $i < $len; $i++ ) {
		$n = ( $n * 26 ) + ( ord( $letters[ $i ] ) - 64 );
	}
	return max( 1, $n );
}

/**
 * @param string[] $strings
 */
function watphou_xlsx_cell_value( SimpleXMLElement $cell, array $strings ): string {
	$type = (string) $cell['t'];
	if ( 's' === $type ) {
		$idx = (int) (string) $cell->v;
		return $strings[ $idx ] ?? '';
	}
	if ( 'inlineStr' === $type ) {
		if ( isset( $cell->is ) ) {
			return watphou_xlsx_si_text( $cell->is );
		}
		return '';
	}
	if ( 'b' === $type ) {
		return ( (string) $cell->v === '1' ) ? '1' : '0';
	}
	if ( isset( $cell->v ) ) {
		return (string) $cell->v;
	}
	return '';
}

function watphou_xlsx_simplexml( string $xml ): ?SimpleXMLElement {
	if ( '' === $xml ) {
		return null;
	}
	$stripped = preg_replace( '/xmlns(:[a-z0-9]+)?="[^"]*"/i', '', $xml );
	$stripped = preg_replace( '/\sxml:space="preserve"/i', '', (string) $stripped );
	$stripped = preg_replace( '/\s[a-z0-9]+:[a-z0-9]+="[^"]*"/i', '', (string) $stripped );
	$prev     = libxml_use_internal_errors( true );
	$simple   = simplexml_load_string( (string) $stripped );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );
	return $simple instanceof SimpleXMLElement ? $simple : null;
}
