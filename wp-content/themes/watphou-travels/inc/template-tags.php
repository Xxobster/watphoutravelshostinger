<?php
defined( 'ABSPATH' ) || exit;

function watphou_render_tour_card( int $post_id ): void {
	$duration = get_post_meta( $post_id, 'tour_duration', true );
	if ( $duration && function_exists( 'watphou_core_translate_public_string' ) ) {
		$duration = watphou_core_translate_public_string( (string) $duration );
	}
	$price    = function_exists( 'watphou_get_tour_price_display' ) ? watphou_get_tour_price_display( $post_id ) : '';
	$thumb    = function_exists( 'watphou_core_tour_featured_url' ) ? watphou_core_tour_featured_url( $post_id, 'tour-card' ) : get_the_post_thumbnail_url( $post_id, 'tour-card' );
	?>
	<article class="watphou-tour-card">
		<a href="<?php echo esc_url( function_exists( 'watphou_localized_permalink' ) ? watphou_localized_permalink( $post_id ) : get_permalink( $post_id ) ); ?>" class="watphou-tour-card__link">
			<?php if ( $thumb ) : ?>
				<div class="watphou-tour-card__image"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async"></div>
			<?php endif; ?>
			<div class="watphou-tour-card__body">
				<h3 class="watphou-tour-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $duration ) : ?><p class="watphou-tour-card__duration"><?php echo esc_html( $duration ); ?></p><?php endif; ?>
				<p class="watphou-tour-card__price"><?php echo esc_html( $price ); ?></p>
				<p class="watphou-tour-card__excerpt"><?php echo esc_html( get_the_excerpt( $post_id ) ); ?></p>
				<span class="watphou-tour-card__cta"><?php esc_html_e( 'View Details →', 'watphou-travels' ); ?></span>
			</div>
		</a>
	</article>
	<?php
}

/**
 * Starting price number for listing cards. Public prices stay XX until confirmed.
 */
function watphou_tour_price_number( int $post_id ): string {
	if ( function_exists( 'watphou_get_tour_price_amount' ) ) {
		return watphou_get_tour_price_amount( $post_id );
	}
	$from = trim( (string) get_post_meta( $post_id, 'tour_price_from', true ) );
	if ( '' === $from || 'XX' === strtoupper( $from ) ) {
		return 'XX';
	}
	return ltrim( $from, '$' );
}

/**
 * @return WP_Post[]
 */
function watphou_get_tours_for_duration( string $term_slug ): array {
	$term_id = 0;
	$term    = get_term_by( 'slug', $term_slug, 'duration' );
	$lang    = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : '';
	if ( $term && ! is_wp_error( $term ) ) {
		$term_id = (int) $term->term_id;
		if ( function_exists( 'pll_get_term' ) ) {
			$translated = $lang ? pll_get_term( $term_id, $lang ) : pll_get_term( $term_id );
			if ( $translated ) {
				$term_id = (int) $translated;
			}
		}
	}

	$args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);
	if ( $lang ) {
		$args['lang'] = $lang;
	}
	if ( $term_id ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'duration',
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		);
	}
	$query = new WP_Query( $args );
	$posts = $query->have_posts() ? $query->posts : array();
	if ( ! $posts ) {
	$all = get_posts(
			array(
				'post_type'      => 'tour',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'lang'           => $lang ?: '',
			)
		);
		$posts = array();
		foreach ( $all as $post ) {
			$dur    = (string) get_post_meta( $post->ID, 'tour_duration', true );
			$mapped = function_exists( 'watphou_core_map_duration_term' )
				? watphou_core_map_duration_term( $dur )
				: '1-day';
			if ( $mapped === $term_slug ) {
				$posts[] = $post;
			}
		}
	}
	if ( function_exists( 'watphou_core_sort_tours_by_catalog' ) ) {
		return watphou_core_sort_tours_by_catalog( $posts );
	}
	return $posts;
}

function watphou_render_tour_row( WP_Post $post, int $index = 0 ): void {
	$img       = WATPHOU_THEME_URI . '/assets/images';
	$fallbacks = array( 'tad-fane.jpg', 'liphi.jpg', 'vatphou.jpg', 'bolaven.jpg', 'coffee.jpg', 'waterfall.jpg' );
	$pid       = (int) $post->ID;
	$thumb     = function_exists( 'watphou_core_tour_featured_url' )
		? watphou_core_tour_featured_url( $pid, 'large' )
		: get_the_post_thumbnail_url( $pid, 'large' );
	if ( ! $thumb ) {
		$thumb = $img . '/' . $fallbacks[ $index % count( $fallbacks ) ];
	}
	$duration = get_post_meta( $pid, 'tour_duration', true ) ?: __( 'Flexible', 'watphou-travels' );
	if ( function_exists( 'watphou_core_translate_public_string' ) ) {
		$duration = watphou_core_translate_public_string( (string) $duration );
	}
	$price    = watphou_tour_price_number( $pid );
	$excerpt  = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 36 );
	$view     = function_exists( 'watphou_localized_permalink' ) ? watphou_localized_permalink( $pid ) : get_permalink( $pid );
	?>
	<article class="wpt-tour-row">
		<div class="wpt-tour-row__image">
			<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $pid ) ); ?>" loading="lazy" decoding="async">
			<span class="wpt-day-badge"><?php echo esc_html( $duration ); ?></span>
		</div>
		<div class="wpt-tour-row__content">
			<h3><a href="<?php echo esc_url( $view ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a></h3>
			<div class="wpt-tour-row__desc"><?php echo esc_html( $excerpt ); ?></div>
		</div>
		<div class="wpt-tour-row__price">
			<div class="wpt-price">
				<span><?php esc_html_e( 'from', 'watphou-travels' ); ?></span>
				<span class="wpt-price__num">$<?php echo esc_html( $price ); ?></span>
				<span><?php esc_html_e( '/Person', 'watphou-travels' ); ?></span>
			</div>
			<a class="wpt-view-tour" href="<?php echo esc_url( $view ); ?>"><?php esc_html_e( 'View tour', 'watphou-travels' ); ?></a>
		</div>
		<a class="wpt-tour-row__hit" href="<?php echo esc_url( $view ); ?>"><span class="screen-reader-text"><?php echo esc_html( get_the_title( $pid ) ); ?></span></a>
	</article>
	<?php
}

/**
 * @return array<int, array{url:string,alt:string}>
 */
function watphou_home_hero_slides(): array {
	return function_exists( 'watphou_core_home_hero_slides' ) ? watphou_core_home_hero_slides() : array();
}
