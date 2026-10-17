<?php
/**
 * Per-tour group price table (izitour-style): one row per group size, price per person.
 *
 * Day tours show one price column. Tours with nights show "Sharing Twin/Double Room"
 * and "Private Single Room". The table is stored as JSON in post meta `tour_price_table`
 * on the English tour and copied to the French/Thai translations on save.
 *
 * Prices are only ever read from this meta on the server. The inquiry form never
 * accepts a price from the browser.
 */
defined( 'ABSPATH' ) || exit;

const WATPHOU_PRICE_TABLE_META   = 'tour_price_table';
const WATPHOU_PRICE_TABLE_MAXROW = 20;
const WATPHOU_PRICE_MAX_PEOPLE   = 99;

add_action( 'init', 'watphou_core_register_price_table_meta' );
add_filter( 'wp_robots', 'watphou_core_inquiry_noindex' );

function watphou_core_register_price_table_meta(): void {
	register_post_meta(
		'tour',
		WATPHOU_PRICE_TABLE_META,
		array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => static function ( $allowed, $meta_key, $post_id ) {
				unset( $allowed, $meta_key );
				return current_user_can( 'edit_post', (int) $post_id );
			},
		)
	);
}

/**
 * True on the "Send Inquiry" form page of a tour (`?inquiry=1`).
 */
function watphou_core_is_inquiry_page(): bool {
	return is_singular( 'tour' ) && isset( $_GET['inquiry'] ) && '1' === (string) $_GET['inquiry']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * The form page is a variant of the tour page: keep it out of search results.
 *
 * @param array<string, bool|string> $robots
 * @return array<string, bool|string>
 */
function watphou_core_inquiry_noindex( array $robots ): array {
	if ( watphou_core_is_inquiry_page() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}

/**
 * "6/8", "6-8 pax", "6 – 8", "4", "4 pax" → [min, max]. Null when it is not a group size.
 *
 * @return array{0:int,1:int}|null
 */
function watphou_core_parse_group_size( string $text ): ?array {
	$text = trim( wp_strip_all_tags( $text ) );
	if ( ! preg_match( '/^(\d{1,2})\s*(?:[\/\-–—]|to|à|ถึง)?\s*(\d{1,2})?\s*(?:pax|people|persons?|pers\.?|personnes?|ท่าน|คน)?\s*\+?$/iu', $text, $m ) ) {
		return null;
	}
	$min = (int) $m[1];
	$max = isset( $m[2] ) && '' !== $m[2] ? (int) $m[2] : $min;
	if ( $max < $min ) {
		list( $min, $max ) = array( $max, $min );
	}
	if ( $min < 1 || $max > WATPHOU_PRICE_MAX_PEOPLE ) {
		return null;
	}
	return array( $min, $max );
}

/**
 * A stored price: digits with optional decimals, no currency sign. '' means "on request".
 */
function watphou_core_clean_price( $value ): string {
	$value = is_scalar( $value ) ? (string) $value : '';
	$value = str_replace( array( '$', 'USD', 'usd', ' ', "\u{00A0}", '/pax', 'pax' ), '', $value );
	// Accept "1,327" (thousands) and "1327.50"; reject anything else.
	if ( preg_match( '/^\d{1,3}(,\d{3})+(\.\d{1,2})?$/', $value ) ) {
		$value = str_replace( ',', '', $value );
	}
	if ( ! preg_match( '/^\d{1,6}(\.\d{1,2})?$/', $value ) ) {
		return '';
	}
	$num = (float) $value;
	if ( $num <= 0 ) {
		return '';
	}
	return ( floor( $num ) == $num ) ? (string) (int) $num : number_format( $num, 2, '.', '' );
}

/**
 * Validate a table coming from Quick Edit (or anywhere else).
 *
 * @param mixed $raw ['layout' => 'auto'|'day'|'overnight', 'rows' => [[group, a, b], …]]
 * @return array{layout:string, rows:array<int, array{min:int,max:int,a:string,b:string}>}
 */
function watphou_core_sanitize_price_table( $raw ): array {
	$out = array(
		'layout' => 'auto',
		'rows'   => array(),
	);
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	$layout = isset( $raw['layout'] ) && is_string( $raw['layout'] ) ? $raw['layout'] : 'auto';
	$out['layout'] = in_array( $layout, array( 'auto', 'day', 'overnight' ), true ) ? $layout : 'auto';
	$rows = isset( $raw['rows'] ) && is_array( $raw['rows'] ) ? array_slice( $raw['rows'], 0, WATPHOU_PRICE_TABLE_MAXROW * 2 ) : array();
	$seen = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$row   = array_values( $row );
		$group = isset( $row[0] ) && is_scalar( $row[0] ) ? (string) $row[0] : '';
		if ( isset( $row[0] ) && is_array( $row[0] ) ) {
			continue;
		}
		$size = watphou_core_parse_group_size( $group );
		if ( ! $size ) {
			continue;
		}
		$key = $size[0] . '-' . $size[1];
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$out['rows'][] = array(
			'min' => $size[0],
			'max' => $size[1],
			'a'   => watphou_core_clean_price( $row[1] ?? '' ),
			'b'   => watphou_core_clean_price( $row[2] ?? '' ),
		);
		if ( count( $out['rows'] ) >= WATPHOU_PRICE_TABLE_MAXROW ) {
			break;
		}
	}
	usort(
		$out['rows'],
		static function ( $x, $y ) {
			return array( $x['min'], $x['max'] ) <=> array( $y['min'], $y['max'] );
		}
	);
	return $out;
}

/**
 * @return array{layout:string, rows:array<int, array{min:int,max:int,a:string,b:string}>}
 */
function watphou_core_get_price_table( int $post_id ): array {
	$raw = (string) get_post_meta( $post_id, WATPHOU_PRICE_TABLE_META, true );
	if ( '' === $raw && function_exists( 'pll_get_post' ) ) {
		$en = (int) pll_get_post( $post_id, 'en' );
		if ( $en && $en !== $post_id ) {
			$raw = (string) get_post_meta( $en, WATPHOU_PRICE_TABLE_META, true );
		}
	}
	$data = '' !== $raw ? json_decode( $raw, true, 8 ) : null;
	if ( ! is_array( $data ) ) {
		return array(
			'layout' => 'auto',
			'rows'   => array(),
		);
	}
	// Stored rows use keys; feed them back through the sanitizer as a list.
	$list = array();
	foreach ( (array) ( $data['rows'] ?? array() ) as $row ) {
		if ( is_array( $row ) ) {
			$list[] = array( ( $row['min'] ?? '' ) . '/' . ( $row['max'] ?? '' ), $row['a'] ?? '', $row['b'] ?? '' );
		}
	}
	return watphou_core_sanitize_price_table(
		array(
			'layout' => $data['layout'] ?? 'auto',
			'rows'   => $list,
		)
	);
}

/**
 * Day layout (one price column) or overnight layout (twin/double + single).
 */
function watphou_core_price_table_is_day( int $post_id, ?array $table = null ): bool {
	$table = $table ?? watphou_core_get_price_table( $post_id );
	if ( 'day' === $table['layout'] ) {
		return true;
	}
	if ( 'overnight' === $table['layout'] ) {
		return false;
	}
	return function_exists( 'watphou_tour_is_day_only' ) ? watphou_tour_is_day_only( $post_id ) : false;
}

/**
 * Save the table on the tour and its French/Thai copies.
 */
function watphou_core_set_price_table( int $post_id, array $table ): void {
	$table = watphou_core_sanitize_price_table(
		array(
			'layout' => $table['layout'] ?? 'auto',
			'rows'   => array_map(
				static function ( $r ) {
					return is_array( $r ) && isset( $r['min'] )
						? array( $r['min'] . '/' . $r['max'], $r['a'] ?? '', $r['b'] ?? '' )
						: $r;
				},
				(array) ( $table['rows'] ?? array() )
			),
		)
	);
	$json = wp_json_encode( $table );
	$ids  = function_exists( 'watphou_core_tour_translation_ids' ) ? watphou_core_tour_translation_ids( $post_id ) : array( $post_id );
	foreach ( $ids as $tid ) {
		if ( $table['rows'] || 'auto' !== $table['layout'] ) {
			update_post_meta( $tid, WATPHOU_PRICE_TABLE_META, wp_slash( $json ) );
		} else {
			delete_post_meta( $tid, WATPHOU_PRICE_TABLE_META );
		}
	}
}

/**
 * Lowest per-person price in the table ('' when none), used for the "From $" line.
 */
function watphou_core_price_table_lowest( array $table ): string {
	$low = null;
	foreach ( $table['rows'] as $row ) {
		foreach ( array( $row['a'], $row['b'] ) as $p ) {
			if ( '' !== $p && ( null === $low || (float) $p < $low ) ) {
				$low = (float) $p;
			}
		}
	}
	if ( null === $low ) {
		return '';
	}
	return ( floor( $low ) == $low ) ? (string) (int) $low : number_format( $low, 2, '.', '' );
}

/**
 * Largest group size listed (8 when the table is empty, as on the customer sheet).
 */
function watphou_core_price_table_max_people( array $table ): int {
	$max = 0;
	foreach ( $table['rows'] as $row ) {
		$max = max( $max, (int) $row['max'] );
	}
	return $max > 0 ? $max : 8;
}

/**
 * Index of the row whose group size contains $people, or -1.
 */
function watphou_core_price_table_row_for( array $table, int $people ): int {
	foreach ( $table['rows'] as $i => $row ) {
		if ( $people >= $row['min'] && $people <= $row['max'] ) {
			return (int) $i;
		}
	}
	return -1;
}

function watphou_core_group_label( array $row ): string {
	if ( $row['min'] === $row['max'] ) {
		/* translators: %d: number of people */
		return sprintf( __( '%d people', 'watphou-core' ), $row['min'] );
	}
	/* translators: 1: smallest group, 2: largest group, e.g. 3-5 people */
	return sprintf( __( '%1$d-%2$d people', 'watphou-core' ), $row['min'], $row['max'] );
}

function watphou_core_format_usd( string $amount ): string {
	if ( '' === $amount ) {
		return '';
	}
	$num = (float) $amount;
	return '$' . number_format( $num, ( floor( $num ) == $num ) ? 0 : 2, '.', ',' );
}

/**
 * Total for a group: people x stored price per person, formatted. '' when the price is on request.
 */
function watphou_core_price_total( string $amount, int $people ): string {
	if ( '' === $amount || $people < 1 ) {
		return '';
	}
	return watphou_core_format_usd( (string) ( (float) $amount * $people ) );
}

/**
 * Price for a group split between room types, from the stored table only.
 * Day tours: pass everyone as $twin and 0 as $single. The row is the one matching the
 * total number of people, so 2 people use the 1-2 row even if the 3-5 row was clicked.
 *
 * @return array{people:int, row:int, label:string, lines:array<int, array{key:string,people:int,unit:string,amount:string}>, total:string}
 *   unit/amount/total are '' when the price is on request (no matching row or empty cell).
 */
function watphou_core_price_quote( array $table, bool $day, int $twin, int $single ): array {
	$twin   = max( 0, $twin );
	$single = $day ? 0 : max( 0, $single );
	$people = $twin + $single;
	$idx    = $people > 0 ? watphou_core_price_table_row_for( $table, $people ) : -1;
	$row    = $idx >= 0 ? $table['rows'][ $idx ] : null;
	$out    = array(
		'people' => $people,
		'row'    => $idx,
		'label'  => $row ? watphou_core_group_label( $row ) : '',
		'lines'  => array(),
		'total'  => '',
	);
	$sum     = 0.0;
	$priced  = (bool) $row;
	$parts   = $day ? array( array( 'a', $twin ) ) : array( array( 'a', $twin ), array( 'b', $single ) );
	foreach ( $parts as $part ) {
		list( $key, $n ) = $part;
		if ( $n < 1 ) {
			continue;
		}
		$unit = $row ? (string) $row[ $key ] : '';
		if ( '' === $unit ) {
			$priced = false;
		} else {
			$sum += (float) $unit * $n;
		}
		$out['lines'][] = array(
			'key'    => $key,
			'people' => $n,
			'unit'   => $unit,
			'amount' => '' === $unit ? '' : (string) ( (float) $unit * $n ),
		);
	}
	if ( $priced && $out['lines'] ) {
		$out['total'] = (string) $sum;
	}
	return $out;
}

/**
 * Link to the inquiry form page for one row ('more' = the group-size 9+ button).
 *
 * @param int|string $group Row index, or 'more'.
 */
function watphou_core_tour_inquiry_url( int $post_id, $group = '' ): string {
	$base = function_exists( 'watphou_localized_permalink' ) ? watphou_localized_permalink( $post_id ) : (string) get_permalink( $post_id );
	$args = array( 'inquiry' => '1' );
	if ( 'more' === $group || ( is_int( $group ) && $group >= 0 ) ) {
		$args['group'] = (string) $group;
	}
	return add_query_arg( $args, $base );
}

/**
 * Data the inquiry page needs for the summary box (all from the database).
 *
 * @return array{day:bool, max:int, rows:array<int, array{label:string,min:int,max:int,a:string,b:string}>}
 */
function watphou_core_price_table_public_data( int $post_id ): array {
	$table = watphou_core_get_price_table( $post_id );
	$rows  = array();
	foreach ( $table['rows'] as $row ) {
		$rows[] = array(
			'label' => watphou_core_group_label( $row ),
			'min'   => $row['min'],
			'max'   => $row['max'],
			'a'     => watphou_core_format_usd( $row['a'] ),
			'b'     => watphou_core_format_usd( $row['b'] ),
			// Plain numbers so the summary can show the total (people x price per person).
			'an'    => '' === $row['a'] ? 0 : (float) $row['a'],
			'bn'    => '' === $row['b'] ? 0 : (float) $row['b'],
		);
	}
	return array(
		'day'  => watphou_core_price_table_is_day( $post_id, $table ),
		'max'  => watphou_core_price_table_max_people( $table ),
		'rows' => $rows,
		'i18n' => array(
			'people'    => __( '%d people', 'watphou-core' ),
			'person'    => __( '1 person', 'watphou-core' ),
			'onRequest' => __( 'On request', 'watphou-core' ),
			'quote'     => __( 'On request – we will send you a quotation', 'watphou-core' ),
		),
	);
}

/**
 * Public price table with one "Send Inquiry" button per row. Replaces "Request this tour".
 */
function watphou_core_render_price_table( int $post_id ): string {
	$table = watphou_core_get_price_table( $post_id );
	$day   = watphou_core_price_table_is_day( $post_id, $table );
	$max   = watphou_core_price_table_max_people( $table );
	$per   = __( 'per person', 'watphou-core' );
	$send  = __( 'Send Inquiry', 'watphou-core' );
	ob_start();
	?>
	<section class="wpt-prices" id="prices" aria-labelledby="wpt-prices-title">
		<h2 id="wpt-prices-title"><?php esc_html_e( 'Prices', 'watphou-core' ); ?></h2>
		<?php if ( $table['rows'] ) : ?>
			<p class="wpt-prices__note"><?php esc_html_e( 'Private tour. Prices in US dollars (USD) per person, depending on the size of your group.', 'watphou-core' ); ?></p>
			<table class="wpt-prices__table<?php echo $day ? ' wpt-prices__table--day' : ''; ?>">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Group size', 'watphou-core' ); ?></th>
						<?php if ( $day ) : ?>
							<th scope="col"><?php esc_html_e( 'Price', 'watphou-core' ); ?> <span class="wpt-prices__per">(<?php echo esc_html( $per ); ?>)</span></th>
						<?php else : ?>
							<th scope="col"><?php esc_html_e( 'Sharing Twin/Double Room', 'watphou-core' ); ?> <span class="wpt-prices__per">(<?php esc_html_e( 'price per person', 'watphou-core' ); ?>)</span></th>
							<th scope="col"><?php esc_html_e( 'Private Single Room', 'watphou-core' ); ?> <span class="wpt-prices__per">(<?php esc_html_e( 'price per person', 'watphou-core' ); ?>)</span></th>
						<?php endif; ?>
						<th scope="col"><span class="screen-reader-text"><?php echo esc_html( $send ); ?></span></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $table['rows'] as $i => $row ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( watphou_core_group_label( $row ) ); ?></th>
						<?php
						$cells = $day
							? array( array( __( 'Price', 'watphou-core' ) . ' (' . $per . ')', $row['a'] ) )
							: array(
								array( __( 'Sharing Twin/Double Room', 'watphou-core' ) . ' (' . __( 'price per person', 'watphou-core' ) . ')', $row['a'] ),
								array( __( 'Private Single Room', 'watphou-core' ) . ' (' . __( 'price per person', 'watphou-core' ) . ')', $row['b'] ),
							);
						foreach ( $cells as $cell ) :
							?>
							<td data-label="<?php echo esc_attr( $cell[0] ); ?>">
								<?php if ( '' !== $cell[1] ) : ?>
									<span class="wpt-prices__amount"><?php echo esc_html( watphou_core_format_usd( $cell[1] ) ); ?></span>
								<?php else : ?>
									<span class="wpt-prices__on-request"><?php esc_html_e( 'On request', 'watphou-core' ); ?></span>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
						<td class="wpt-prices__action"><a class="wpt-btn-inquiry" rel="nofollow" href="<?php echo esc_url( watphou_core_tour_inquiry_url( $post_id, (int) $i ) ); ?>"><?php echo esc_html( $send ); ?></a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p class="wpt-prices__note"><?php esc_html_e( 'Prices for this private tour are on request. Send us an inquiry and we will reply with a quote for your group.', 'watphou-core' ); ?></p>
			<p><a class="wpt-btn-inquiry" rel="nofollow" href="<?php echo esc_url( watphou_core_tour_inquiry_url( $post_id ) ); ?>"><?php echo esc_html( $send ); ?></a></p>
		<?php endif; ?>
		<div class="wpt-prices__more">
			<p>
				<svg aria-hidden="true" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.6"/><path d="M15.5 14.2c3 .2 5.5 2.6 5.5 5.8"/></svg>
				<?php
				/* translators: %d: largest group size in the table, e.g. 8 */
				echo esc_html( sprintf( __( 'Traveling with more than %d people? You can select a different group size in the next step.', 'watphou-core' ), $max ) );
				?>
			</p>
			<a class="wpt-btn-inquiry wpt-btn-inquiry--outline" rel="nofollow" href="<?php echo esc_url( watphou_core_tour_inquiry_url( $post_id, 'more' ) ); ?>">
				<?php echo esc_html( $send ); // Customer's request: the 9+ button reads "Send Inquiry". ?>
			</a>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

add_action( 'init', 'watphou_core_maybe_seed_sample_price_tables', 60 );

/**
 * Group sizes from the customer's sheet "Sample for website" (2026-10-16); placeholder price $66 everywhere,
 * the customer enters real prices in Quick edit tours.
 *
 * @return array<int, array{0:string,1:string,2:string}>
 */
function watphou_core_sample_price_rows( bool $day ): array {
	return $day
		? array( array( '1-2', '66', '' ), array( '3-5', '66', '' ), array( '6-8', '66', '' ) )
		: array( array( '1-2', '66', '66' ), array( '3-5', '66', '66' ), array( '6-8', '66', '66' ) );
}

/**
 * Once: give every tour that has no price table yet the sample table for its layout,
 * so the Prices table shows. Managers change or clear it in Quick edit tours.
 * Tables already entered are never overwritten.
 */
function watphou_core_maybe_seed_sample_price_tables(): void {
	if ( '1' === get_option( 'watphou_price_table_seed' ) ) {
		return;
	}
	update_option( 'watphou_price_table_seed', '1', false );
	$args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft', 'pending' ),
		'fields'         => 'ids',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = pll_default_language() ?: 'en';
	}
	foreach ( get_posts( $args ) as $tour_id ) {
		$tour_id = (int) $tour_id;
		if ( watphou_core_get_price_table( $tour_id )['rows'] ) {
			continue;
		}
		$day = function_exists( 'watphou_tour_is_day_only' ) ? watphou_tour_is_day_only( $tour_id ) : true;
		watphou_core_set_price_table(
			$tour_id,
			array(
				'layout' => 'auto',
				'rows'   => watphou_core_sample_price_rows( $day ),
			)
		);
	}
}

add_action( 'init', 'watphou_core_maybe_set_placeholder_prices', 61 );

/**
 * Once (customer's request 2026-10-16): put $66 in every price cell of every existing table,
 * keeping the group-size lines. Single room cells only on tours with nights. "From $" is untouched.
 */
function watphou_core_maybe_set_placeholder_prices(): void {
	if ( '1' === get_option( 'watphou_price_table_66' ) ) {
		return;
	}
	update_option( 'watphou_price_table_66', '1', false );
	$args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'fields'         => 'ids',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = pll_default_language() ?: 'en';
	}
	foreach ( get_posts( $args ) as $tour_id ) {
		$tour_id = (int) $tour_id;
		$table   = watphou_core_get_price_table( $tour_id );
		if ( ! $table['rows'] ) {
			continue;
		}
		$day = watphou_core_price_table_is_day( $tour_id, $table );
		foreach ( $table['rows'] as $i => $row ) {
			$table['rows'][ $i ]['a'] = '66';
			$table['rows'][ $i ]['b'] = $day ? '' : '66';
		}
		watphou_core_set_price_table( $tour_id, $table );
	}
}

add_action( 'init', 'watphou_core_maybe_widen_group_rows', 62 );

/**
 * Once (customer's request 2026-10-17): the sample lines "2 / 4 / 6-8 people" become
 * "1-2 / 3-5 / 6-8 people", keeping their prices. Tables with other lines are left alone.
 */
function watphou_core_maybe_widen_group_rows(): void {
	if ( '1' === get_option( 'watphou_price_table_ranges' ) ) {
		return;
	}
	update_option( 'watphou_price_table_ranges', '1', false );
	$args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'fields'         => 'ids',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = pll_default_language() ?: 'en';
	}
	$map = array(
		'2-2' => array( 1, 2 ),
		'4-4' => array( 3, 5 ),
		'6-8' => array( 6, 8 ),
	);
	foreach ( get_posts( $args ) as $tour_id ) {
		$tour_id = (int) $tour_id;
		$table   = watphou_core_get_price_table( $tour_id );
		$keys    = array_map(
			static function ( $r ) {
				return $r['min'] . '-' . $r['max'];
			},
			$table['rows']
		);
		if ( array( '2-2', '4-4', '6-8' ) !== $keys ) {
			continue;
		}
		foreach ( $table['rows'] as $i => $row ) {
			list( $table['rows'][ $i ]['min'], $table['rows'][ $i ]['max'] ) = $map[ $keys[ $i ] ];
		}
		watphou_core_set_price_table( $tour_id, $table );
	}
}

add_action( 'init', 'watphou_core_maybe_set_test_prices', 63 );

/**
 * Once (customer's request 2026-10-17, test prices): 1-2 people $66, 3-5 people $77, 6-8 people $88
 * in every price cell (single room only on tours with nights). Only tables with exactly these
 * three lines are changed. "From $" is untouched.
 */
function watphou_core_maybe_set_test_prices(): void {
	if ( '1' === get_option( 'watphou_price_table_test_667788' ) ) {
		return;
	}
	update_option( 'watphou_price_table_test_667788', '1', false );
	$args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'fields'         => 'ids',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = pll_default_language() ?: 'en';
	}
	$prices = array(
		'1-2' => '66',
		'3-5' => '77',
		'6-8' => '88',
	);
	foreach ( get_posts( $args ) as $tour_id ) {
		$tour_id = (int) $tour_id;
		$table   = watphou_core_get_price_table( $tour_id );
		$keys    = array_map(
			static function ( $r ) {
				return $r['min'] . '-' . $r['max'];
			},
			$table['rows']
		);
		if ( array_keys( $prices ) !== $keys ) {
			continue;
		}
		$day = watphou_core_price_table_is_day( $tour_id, $table );
		foreach ( $table['rows'] as $i => $row ) {
			$table['rows'][ $i ]['a'] = $prices[ $keys[ $i ] ];
			$table['rows'][ $i ]['b'] = $day ? '' : $prices[ $keys[ $i ] ];
		}
		watphou_core_set_price_table( $tour_id, $table );
	}
}
