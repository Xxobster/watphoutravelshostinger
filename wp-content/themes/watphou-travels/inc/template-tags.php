<?php
defined( 'ABSPATH' ) || exit;

function watphou_render_tour_card( int $post_id ): void {
	$duration = get_post_meta( $post_id, 'tour_duration', true );
	$price    = function_exists( 'watphou_get_tour_price_display' ) ? watphou_get_tour_price_display( $post_id ) : '';
	?>
	<article class="watphou-tour-card">
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="watphou-tour-card__link">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<div class="watphou-tour-card__image"><?php echo get_the_post_thumbnail( $post_id, 'tour-card' ); ?></div>
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

function watphou_language_switcher(): void {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}
	$langs = pll_the_languages(
		array(
			'show_flags'             => 0,
			'show_names'             => 1,
			'display_names_as'       => 'slug',
			'hide_if_no_translation' => 0,
			'raw'                    => 1,
		)
	);
	if ( ! is_array( $langs ) ) {
		return;
	}
	echo '<ul>';
	foreach ( $langs as $lang ) {
		$slug = $lang['slug'] ?? '';
		$url  = $lang['url'] ?? '';
		if ( '' === $slug || '' === $url ) {
			continue;
		}
		// Skip unpublished French/Thai drafts so visitors are not sent to 404s.
		if ( empty( $lang['current_lang'] ) && function_exists( 'pll_get_post' ) && get_queried_object_id() ) {
			$translated = pll_get_post( get_queried_object_id(), $slug );
			if ( $translated && 'publish' !== get_post_status( $translated ) ) {
				continue;
			}
			if ( ! $translated && function_exists( 'pll_default_language' ) && $slug !== pll_default_language() ) {
				continue;
			}
		}
		$current = ! empty( $lang['current_lang'] );
		printf(
			'<li class="%1$s"><a lang="%2$s" hreflang="%2$s" href="%3$s">%4$s</a></li>',
			$current ? 'current-lang' : 'lang-item',
			esc_attr( $slug ),
			esc_url( $url ),
			esc_html( strtoupper( $slug ) )
		);
	}
	echo '</ul>';
}
