<?php
/**
 * Genuine Google Maps reviews for the homepage carousel.
 * Texts come from the public listing or the Places Application Programming Interface;
 * never invented. Newest 4–5 star reviews first (leftmost).
 */
defined( 'ABSPATH' ) || exit;

const WATPHOU_CORE_REVIEWS_MIN_RATING = 4;
const WATPHOU_CORE_REVIEWS_MAX        = 5;
const WATPHOU_CORE_REVIEWS_CACHE_KEY  = 'watphou_google_reviews_cache';
const WATPHOU_CORE_REVIEWS_CRON       = 'watphou_core_refresh_google_reviews';

function watphou_core_google_reviews_json_path(): string {
	return WATPHOU_CORE_PATH . 'data/google-reviews.json';
}

function watphou_core_google_places_key(): string {
	if ( defined( 'WATPHOU_GOOGLE_PLACES_KEY' ) && is_string( WATPHOU_GOOGLE_PLACES_KEY ) && '' !== WATPHOU_GOOGLE_PLACES_KEY ) {
		return WATPHOU_GOOGLE_PLACES_KEY;
	}
	return trim( (string) get_option( 'watphou_google_places_key', '' ) );
}

function watphou_core_google_reviews_empty(): array {
	return array(
		'place_id'       => '',
		'maps_url'       => 'https://maps.app.goo.gl/QUTghqefjS9LCZSA8',
		'maps_place_url' => '',
		'rating'         => 0,
		'review_count'   => 0,
		'geo'            => array(),
		'opening_hours'  => '',
		'reviews'        => array(),
		'source'         => 'none',
	);
}

function watphou_core_review_photo_url( string $url ): string {
	$url = esc_url_raw( $url );
	if ( '' === $url ) {
		return '';
	}
	$resized = preg_replace( '/=w\d+-h\d+/', '=s120-c', $url );
	return is_string( $resized ) ? $resized : $url;
}

function watphou_core_review_author_name( string $author ): string {
	$author = trim( $author );
	$author = preg_replace( '/^រូបថត\s+/u', '', $author );
	return is_string( $author ) ? trim( $author ) : '';
}

function watphou_core_review_contrib_id( string $value, string $url = '' ): string {
	$digits = preg_replace( '/\D+/', '', $value );
	if ( is_string( $digits ) && strlen( $digits ) >= 10 ) {
		return $digits;
	}
	if ( preg_match( '#/contrib/(\d+)#', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

function watphou_core_review_sort_ts( array $row ): int {
	$date = trim( (string) ( $row['date'] ?? '' ) );
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}/', $date ) ) {
		$t = strtotime( $date );
		if ( $t ) {
			return (int) $t;
		}
	}
	$label = strtolower( trim( (string) ( $row['date_label'] ?? '' ) ) );
	$now   = time();
	if ( preg_match( '/\ba year ago\b/', $label ) ) {
		return (int) strtotime( '-1 year', $now );
	}
	if ( preg_match( '/\ba month ago\b/', $label ) ) {
		return (int) strtotime( '-1 month', $now );
	}
	if ( preg_match( '/\ba week ago\b/', $label ) ) {
		return (int) strtotime( '-1 week', $now );
	}
	if ( preg_match( '/(\d+)\s*(year|month|week|day|hour)s?\s+ago/', $label, $m ) ) {
		$n    = max( 1, (int) $m[1] );
		$unit = $m[2] . 's';
		$t    = strtotime( '-' . $n . ' ' . $unit, $now );
		return $t ? (int) $t : 0;
	}
	if ( preg_match( '/(\d+)\s*ឆ្នាំ/u', (string) ( $row['date_label'] ?? '' ), $m ) ) {
		return (int) strtotime( '-' . max( 1, (int) $m[1] ) . ' years', $now );
	}
	if ( preg_match( '/(\d+)\s*ខែ/u', (string) ( $row['date_label'] ?? '' ), $m ) ) {
		return (int) strtotime( '-' . max( 1, (int) $m[1] ) . ' months', $now );
	}
	return 0;
}

function watphou_core_normalize_review_row( array $row, array $meta, int $index ): ?array {
	$author = watphou_core_review_author_name( (string) ( $row['author'] ?? '' ) );
	$text   = trim( (string) ( $row['text'] ?? '' ) );
	$rating = (int) ( $row['rating'] ?? 0 );
	if ( '' === $author || '' === $text || $rating < WATPHOU_CORE_REVIEWS_MIN_RATING ) {
		return null;
	}
	$place   = (string) ( $meta['place_id'] ?? '' );
	$listing = (string) ( $meta['maps_url'] ?? 'https://maps.app.goo.gl/QUTghqefjS9LCZSA8' );
	$contrib = watphou_core_review_contrib_id( (string) ( $row['contrib_id'] ?? '' ), (string) ( $row['maps_url'] ?? '' ) );
	$url     = (string) ( $row['maps_url'] ?? '' );
	if ( '' === $url ) {
		$url = $listing;
		if ( $contrib && $place ) {
			$url = 'https://www.google.com/maps/contrib/' . $contrib . '/place/' . rawurlencode( $place );
		}
	}
	return array(
		'author'     => $author,
		'rating'     => max( 1, min( 5, $rating ) ),
		'date'       => (string) ( $row['date'] ?? '' ),
		'date_label' => (string) ( $row['date_label'] ?? '' ),
		'language'   => (string) ( $row['language'] ?? '' ),
		'photo'      => watphou_core_review_photo_url( (string) ( $row['photo'] ?? '' ) ),
		'text'       => $text,
		'maps_url'   => $url,
		'contrib_id' => $contrib,
		'_ts'        => watphou_core_review_sort_ts( $row ),
		'_i'         => $index,
	);
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array<string, mixed>>
 */
function watphou_core_sort_reviews_newest_first( array $rows ): array {
	usort(
		$rows,
		static function ( array $a, array $b ): int {
			$ta = (int) ( $a['_ts'] ?? 0 );
			$tb = (int) ( $b['_ts'] ?? 0 );
			if ( $ta === $tb ) {
				return (int) ( $a['_i'] ?? 0 ) <=> (int) ( $b['_i'] ?? 0 );
			}
			return $tb <=> $ta;
		}
	);
	foreach ( $rows as &$row ) {
		unset( $row['_ts'], $row['_i'] );
	}
	unset( $row );
	return array_values( $rows );
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array<string, mixed>>
 */
function watphou_core_collect_reviews( array $rows, array $meta ): array {
	$out = array();
	$i   = 0;
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$norm = watphou_core_normalize_review_row( $row, $meta, $i );
		if ( null === $norm ) {
			continue;
		}
		$out[] = $norm;
		++$i;
	}
	return $out;
}

function watphou_core_google_reviews_file_payload(): array {
	$empty = watphou_core_google_reviews_empty();
	$path  = watphou_core_google_reviews_json_path();
	if ( ! is_readable( $path ) ) {
		return $empty;
	}
	$raw = json_decode( (string) file_get_contents( $path ), true );
	if ( ! is_array( $raw ) ) {
		return $empty;
	}
	$meta = array(
		'place_id'       => (string) ( $raw['place_id'] ?? '' ),
		'maps_url'       => (string) ( $raw['maps_url'] ?? $empty['maps_url'] ),
		'maps_place_url' => (string) ( $raw['maps_place_url'] ?? '' ),
		'rating'         => (float) ( $raw['rating'] ?? 0 ),
		'review_count'   => (int) ( $raw['review_count'] ?? 0 ),
		'geo'            => is_array( $raw['geo'] ?? null ) ? $raw['geo'] : array(),
		'opening_hours'  => (string) ( $raw['opening_hours'] ?? '' ),
		'source'         => 'file',
	);
	$meta['reviews'] = watphou_core_collect_reviews( (array) ( $raw['reviews'] ?? array() ), $meta );
	return $meta;
}

function watphou_core_merge_review_lists( array $preferred, array $fallback ): array {
	$seen = array();
	$out  = array();
	$i    = 0;
	foreach ( array( $preferred, $fallback ) as $list ) {
		foreach ( $list as $row ) {
			$key = strtolower( (string) ( $row['author'] ?? '' ) );
			if ( '' === $key || isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$row['_i']    = $i;
			$row['_ts']   = (int) ( $row['_ts'] ?? watphou_core_review_sort_ts( $row ) );
			$out[]        = $row;
			++$i;
		}
	}
	$out = watphou_core_sort_reviews_newest_first( $out );
	return array_slice( $out, 0, WATPHOU_CORE_REVIEWS_MAX );
}

function watphou_core_google_reviews_data(): array {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$file  = watphou_core_google_reviews_file_payload();
	$cache = get_option( WATPHOU_CORE_REVIEWS_CACHE_KEY );
	$from_api = array();
	if ( is_array( $cache ) && ! empty( $cache['reviews'] ) && is_array( $cache['reviews'] ) ) {
		$from_api = watphou_core_collect_reviews( $cache['reviews'], $file );
		if ( ! empty( $cache['rating'] ) ) {
			$file['rating'] = (float) $cache['rating'];
		}
		if ( ! empty( $cache['review_count'] ) ) {
			$file['review_count'] = (int) $cache['review_count'];
		}
		$file['source'] = 'places_api';
	}
	$file['reviews'] = watphou_core_merge_review_lists( $from_api, $file['reviews'] );
	watphou_core_maybe_schedule_google_reviews_refresh();
	return $data = $file;
}

function watphou_core_reviewer_maps_url( array $review ): string {
	if ( ! empty( $review['maps_url'] ) ) {
		return (string) $review['maps_url'];
	}
	$data = watphou_core_google_reviews_data();
	return (string) ( $data['maps_url'] ?? 'https://maps.app.goo.gl/QUTghqefjS9LCZSA8' );
}

function watphou_core_maybe_schedule_google_reviews_refresh(): void {
	if ( ! watphou_core_google_places_key() ) {
		return;
	}
	$cache   = get_option( WATPHOU_CORE_REVIEWS_CACHE_KEY );
	$fetched = is_array( $cache ) ? (int) ( $cache['fetched_at'] ?? 0 ) : 0;
	if ( $fetched > ( time() - DAY_IN_SECONDS ) ) {
		return;
	}
	if ( ! wp_next_scheduled( WATPHOU_CORE_REVIEWS_CRON ) ) {
		wp_schedule_single_event( time() + 15, WATPHOU_CORE_REVIEWS_CRON );
	}
}

function watphou_core_ensure_google_reviews_cron(): void {
	if ( ! watphou_core_google_places_key() ) {
		return;
	}
	if ( ! wp_next_scheduled( WATPHOU_CORE_REVIEWS_CRON ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', WATPHOU_CORE_REVIEWS_CRON );
	}
}

/**
 * @return array<string, mixed>|WP_Error
 */
function watphou_core_places_http_get( string $url, array $headers = array() ) {
	return wp_remote_get(
		$url,
		array(
			'timeout' => 12,
			'headers' => $headers,
		)
	);
}

function watphou_core_review_from_places_new( array $item ): array {
	$original = is_array( $item['originalText'] ?? null ) ? $item['originalText'] : array();
	$localized = is_array( $item['text'] ?? null ) ? $item['text'] : array();
	$text      = trim( (string) ( $original['text'] ?? '' ) );
	$language  = (string) ( $original['languageCode'] ?? '' );
	if ( '' === $text ) {
		$text     = trim( (string) ( $localized['text'] ?? '' ) );
		$language = (string) ( $localized['languageCode'] ?? $language );
	}
	$attr  = is_array( $item['authorAttribution'] ?? null ) ? $item['authorAttribution'] : array();
	$uri   = (string) ( $attr['uri'] ?? '' );
	$when  = (string) ( $item['publishTime'] ?? '' );
	$date  = '';
	if ( preg_match( '/^(\d{4}-\d{2}-\d{2})/', $when, $m ) ) {
		$date = $m[1];
	}
	return array(
		'author'      => (string) ( $attr['displayName'] ?? '' ),
		'rating'      => (int) ( $item['rating'] ?? 0 ),
		'date'        => $date,
		'date_label'  => (string) ( $item['relativePublishTimeDescription'] ?? '' ),
		'language'    => $language,
		'photo'       => (string) ( $attr['photoUri'] ?? '' ),
		'text'        => $text,
		'maps_url'    => $uri,
		'contrib_id'  => watphou_core_review_contrib_id( '', $uri ),
	);
}

function watphou_core_review_from_places_legacy( array $item ): array {
	$uri  = (string) ( $item['author_url'] ?? '' );
	$when = (int) ( $item['time'] ?? 0 );
	$date = $when > 0 ? gmdate( 'Y-m-d', $when ) : '';
	return array(
		'author'     => (string) ( $item['author_name'] ?? '' ),
		'rating'     => (int) ( $item['rating'] ?? 0 ),
		'date'       => $date,
		'date_label' => (string) ( $item['relative_time_description'] ?? '' ),
		'language'   => (string) ( $item['original_language'] ?? $item['language'] ?? '' ),
		'photo'      => (string) ( $item['profile_photo_url'] ?? '' ),
		'text'       => trim( (string) ( $item['text'] ?? $item['original_text'] ?? '' ) ),
		'maps_url'   => $uri,
		'contrib_id' => watphou_core_review_contrib_id( '', $uri ),
	);
}

/**
 * @return array<string, mixed>|WP_Error
 */
function watphou_core_fetch_google_reviews_via_places() {
	$key = watphou_core_google_places_key();
	if ( '' === $key ) {
		return new WP_Error( 'no_key', 'Places Application Programming Interface key is not set.' );
	}
	$file     = watphou_core_google_reviews_file_payload();
	$place_id = $file['place_id'] ?: 'ChIJzWB4m7P4FDER1RsT6DS65oE';

	$new_url = add_query_arg(
		array(
			'languageCode' => 'en',
			'reviewsSort'  => 'NEWEST',
		),
		'https://places.googleapis.com/v1/places/' . rawurlencode( $place_id )
	);
	$new_res = watphou_core_places_http_get(
		$new_url,
		array(
			'X-Goog-Api-Key'   => $key,
			'X-Goog-FieldMask' => 'rating,userRatingCount,reviews,googleMapsUri',
		)
	);
	if ( ! is_wp_error( $new_res ) && 200 === (int) wp_remote_retrieve_response_code( $new_res ) ) {
		$body = json_decode( (string) wp_remote_retrieve_body( $new_res ), true );
		if ( is_array( $body ) && empty( $body['error'] ) ) {
			$mapped = array();
			foreach ( (array) ( $body['reviews'] ?? array() ) as $item ) {
				if ( is_array( $item ) ) {
					$mapped[] = watphou_core_review_from_places_new( $item );
				}
			}
			return array(
				'fetched_at'   => time(),
				'source'       => 'places_api_new',
				'place_id'     => $place_id,
				'rating'       => (float) ( $body['rating'] ?? $file['rating'] ),
				'review_count' => (int) ( $body['userRatingCount'] ?? $file['review_count'] ),
				'reviews'      => $mapped,
			);
		}
	}

	$legacy_url = add_query_arg(
		array(
			'place_id'     => $place_id,
			'fields'       => 'name,rating,user_ratings_total,reviews,url',
			'reviews_sort' => 'newest',
			'key'          => $key,
		),
		'https://maps.googleapis.com/maps/api/place/details/json'
	);
	$legacy_res = watphou_core_places_http_get( $legacy_url );
	if ( is_wp_error( $legacy_res ) ) {
		return $legacy_res;
	}
	$legacy = json_decode( (string) wp_remote_retrieve_body( $legacy_res ), true );
	if ( ! is_array( $legacy ) || 'OK' !== ( $legacy['status'] ?? '' ) ) {
		$code = is_array( $legacy ) ? (string) ( $legacy['status'] ?? 'unknown' ) : 'bad_json';
		if ( ! is_wp_error( $new_res ) ) {
			$code .= '; new_http=' . (int) wp_remote_retrieve_response_code( $new_res );
		}
		return new WP_Error( 'places_denied', 'Places Application Programming Interface request failed (' . $code . ').' );
	}
	$result = is_array( $legacy['result'] ?? null ) ? $legacy['result'] : array();
	$mapped = array();
	foreach ( (array) ( $result['reviews'] ?? array() ) as $item ) {
		if ( is_array( $item ) ) {
			$mapped[] = watphou_core_review_from_places_legacy( $item );
		}
	}
	return array(
		'fetched_at'   => time(),
		'source'       => 'places_api_legacy',
		'place_id'     => $place_id,
		'rating'       => (float) ( $result['rating'] ?? $file['rating'] ),
		'review_count' => (int) ( $result['user_ratings_total'] ?? $file['review_count'] ),
		'reviews'      => $mapped,
	);
}

/**
 * @return true|WP_Error
 */
function watphou_core_refresh_google_reviews_now() {
	$payload = watphou_core_fetch_google_reviews_via_places();
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}
	update_option( WATPHOU_CORE_REVIEWS_CACHE_KEY, $payload, false );
	return true;
}

function watphou_core_cron_refresh_google_reviews(): void {
	if ( get_transient( 'watphou_google_reviews_fetching' ) ) {
		return;
	}
	set_transient( 'watphou_google_reviews_fetching', 1, 90 );
	$result = watphou_core_refresh_google_reviews_now();
	delete_transient( 'watphou_google_reviews_fetching' );
	if ( is_wp_error( $result ) ) {
		error_log( 'Watphou Google reviews refresh failed: ' . $result->get_error_code() );
	}
}

function watphou_core_handle_refresh_google_reviews(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You cannot refresh Google reviews.', 'watphou-core' ) );
	}
	check_admin_referer( 'watphou_refresh_google_reviews' );
	$result = watphou_core_refresh_google_reviews_now();
	$url    = admin_url( 'options-general.php?page=watphou-settings' );
	if ( is_wp_error( $result ) ) {
		$url = add_query_arg( 'watphou_reviews_error', rawurlencode( $result->get_error_code() ), $url );
	} else {
		$url = add_query_arg( 'watphou_reviews_refreshed', '1', $url );
	}
	wp_safe_redirect( $url );
	exit;
}

function watphou_core_reviewer_initial( string $author ): string {
	$clean = wp_strip_all_tags( $author );
	if ( function_exists( 'mb_substr' ) ) {
		$ch = mb_substr( $clean, 0, 1 );
		return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $ch ) : strtoupper( $ch );
	}
	return strtoupper( substr( $clean, 0, 1 ) );
}

function watphou_core_render_google_reviews(): void {
	$data  = watphou_core_google_reviews_data();
	$items = $data['reviews'];
	if ( ! $items ) {
		return;
	}
	$listing = (string) ( $data['maps_url'] ?: 'https://maps.app.goo.gl/QUTghqefjS9LCZSA8' );
	$quote   = get_theme_file_uri( 'assets/images/icon-review.svg' );
	$count   = (int) $data['review_count'];
	$rating  = (float) $data['rating'];
	?>
	<section class="wpt-reviews" id="what-clients-say" aria-labelledby="wpt-reviews-title">
		<div class="wpt-container">
			<h2 class="wpt-reviews__title" id="wpt-reviews-title">
				<a href="<?php echo esc_url( $listing ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'What Our Clients Say', 'watphou-travels' ); ?></a>
			</h2>
			<?php if ( $rating > 0 && $count > 0 ) : ?>
				<p class="wpt-section-sub wpt-reviews__summary">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: average rating, 2: number of Google reviews */
							__( '%1$s out of 5 from %2$d Google reviews', 'watphou-travels' ),
							number_format_i18n( $rating, 1 ),
							$count
						)
					);
					?>
				</p>
			<?php endif; ?>
			<div class="wpt-reviews__slider" data-wpt-reviews-slider>
				<button type="button" class="wpt-reviews__nav wpt-reviews__nav--prev" aria-label="<?php esc_attr_e( 'Previous reviews', 'watphou-travels' ); ?>">‹</button>
				<div class="wpt-reviews__viewport">
					<ul class="wpt-reviews__track">
						<?php foreach ( $items as $review ) : ?>
							<?php
							$url     = watphou_core_reviewer_maps_url( $review );
							$stars   = (int) $review['rating'];
							$label   = $review['date_label'] ?: $review['date'];
							$initial = watphou_core_reviewer_initial( $review['author'] );
							$lang    = preg_replace( '/[^a-z-]/', '', strtolower( (string) ( $review['language'] ?? '' ) ) );
							?>
							<li class="wpt-reviews__slide">
								<a class="wpt-review-card" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
									<img class="wpt-review-quote" src="<?php echo esc_url( $quote ); ?>" width="36" height="28" alt="" aria-hidden="true">
									<span class="wpt-review-stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star rating */ __( '%d stars', 'watphou-travels' ), $stars ) ); ?>">
										<?php echo esc_html( str_repeat( '★', $stars ) ); ?>
									</span>
									<p class="wpt-review-text"<?php echo $lang ? ' lang="' . esc_attr( $lang ) . '"' : ''; ?> dir="auto"><?php echo esc_html( $review['text'] ); ?></p>
									<div class="wpt-review-who">
										<span class="wpt-review-avatar" aria-hidden="true">
											<?php if ( $review['photo'] ) : ?>
												<img src="<?php echo esc_url( $review['photo'] ); ?>" alt="" width="70" height="70" loading="lazy" decoding="async" referrerpolicy="no-referrer">
											<?php endif; ?>
											<span class="wpt-review-initial"><?php echo esc_html( $initial ); ?></span>
										</span>
										<span class="wpt-review-meta">
											<strong><?php echo esc_html( $review['author'] ); ?></strong>
											<?php if ( $label ) : ?>
												<br><time<?php echo $review['date'] ? ' datetime="' . esc_attr( $review['date'] ) . '"' : ''; ?>><?php echo esc_html( $label ); ?></time>
											<?php endif; ?>
										</span>
									</div>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<button type="button" class="wpt-reviews__nav wpt-reviews__nav--next" aria-label="<?php esc_attr_e( 'Next reviews', 'watphou-travels' ); ?>">›</button>
			</div>
			<p class="wpt-reviews__google">
				<a href="<?php echo esc_url( $listing ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read more on Google', 'watphou-travels' ); ?></a>
			</p>
		</div>
	</section>
	<?php
}

add_action( WATPHOU_CORE_REVIEWS_CRON, 'watphou_core_cron_refresh_google_reviews' );
add_action( 'init', 'watphou_core_ensure_google_reviews_cron' );
add_action( 'admin_post_watphou_refresh_google_reviews', 'watphou_core_handle_refresh_google_reviews' );
