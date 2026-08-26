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
	echo '<nav class="watphou-lang-switcher" aria-label="' . esc_attr__( 'Language', 'watphou-travels' ) . '">';
	pll_the_languages( array( 'show_flags' => 0, 'show_names' => 1, 'display_names_as' => 'slug' ) );
	echo '</nav>';
}
