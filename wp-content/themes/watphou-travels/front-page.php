<?php
defined( 'ABSPATH' ) || exit;

get_header();

$img    = WATPHOU_THEME_URI . '/assets/images';
$slides = function_exists( 'watphou_home_hero_slides' ) ? watphou_home_hero_slides() : array();
$first  = $slides[0]['url'] ?? ( $img . '/tad-fane.jpg' );
$first_alt = $slides[0]['alt'] ?? '';
?>

<section class="wpt-hero wpt-hero--home" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Southern Laos photos', 'watphou-travels' ); ?>">
	<div class="wpt-hero__slides">
		<?php if ( $slides ) : ?>
			<?php foreach ( $slides as $i => $slide ) : ?>
				<?php
				$srcset = function_exists( 'watphou_hero_srcset' ) ? watphou_hero_srcset( $slide['url'] ) : '';
				?>
				<img
					class="wpt-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>"
					width="1920"
					height="822"
					sizes="100vw"
					<?php if ( 0 === $i ) : ?>
						src="<?php echo esc_url( $slide['url'] ); ?>"
						<?php if ( $srcset ) : ?>
							srcset="<?php echo esc_attr( $srcset ); ?>"
						<?php endif; ?>
						data-src="<?php echo esc_url( $slide['url'] ); ?>"
						<?php if ( $srcset ) : ?>
							data-srcset="<?php echo esc_attr( $srcset ); ?>"
						<?php endif; ?>
						fetchpriority="high"
						decoding="async"
					<?php else : ?>
						src="data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs="
						data-src="<?php echo esc_url( $slide['url'] ); ?>"
						<?php if ( $srcset ) : ?>
							data-srcset="<?php echo esc_attr( $srcset ); ?>"
						<?php endif; ?>
						decoding="async"
						aria-hidden="true"
					<?php endif; ?>
					alt="<?php echo esc_attr( $slide['alt'] ); ?>"
				>
			<?php endforeach; ?>
		<?php else : ?>
			<img class="wpt-hero__slide is-active" src="<?php echo esc_url( $first ); ?>" alt="<?php echo esc_attr( $first_alt ); ?>" fetchpriority="high" decoding="async">
		<?php endif; ?>
	</div>
	<div class="wpt-hero__overlay"></div>
	<div class="wpt-container wpt-hero__content">
		<h1><?php esc_html_e( 'Discover Southern Laos Your Way', 'watphou-travels' ); ?></h1>
		<p class="wpt-hero__sub">“<?php esc_html_e( 'Your private journeys with a local Pakse team and European standards', 'watphou-travels' ); ?>”</p>
		<div class="wpt-hero__actions">
			<a class="wpt-btn-primary" href="#best-sellers"><?php esc_html_e( 'Get Started Today', 'watphou-travels' ); ?></a>
			<a class="wpt-btn-whatsapp" href="<?php echo esc_url( function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858' ); ?>"><?php esc_html_e( 'WhatsApp', 'watphou-travels' ); ?></a>
		</div>
	</div>
</section>

<section class="wpt-explore">
	<div class="wpt-container wpt-explore__inner">
		<h2><?php esc_html_e( 'Explore Southern Laos like Never Before', 'watphou-travels' ); ?></h2>
		<p><?php esc_html_e( 'Welcome to Southern Laos, a captivating region where the mighty Mekong River carves its path through lush landscapes, ancient histories whisper from forgotten temples, and life unfolds at an incredibly gentle pace.', 'watphou-travels' ); ?></p>
		<p><?php esc_html_e( 'Far from the bustling crowds, this enchanting part of Laos offers a truly authentic Southeast Asian experience. Discover Pakse, the Bolaven Plateau with its waterfalls and coffee farms, the UNESCO-listed Vat Phou temple, and the peaceful 4000 Islands.', 'watphou-travels' ); ?></p>
		<p><?php esc_html_e( 'As a local agency based in Pakse, Watphou Travels is uniquely positioned to help you uncover hidden gems. Whether you seek a one-day waterfall escape or a week-long private journey, every itinerary is 100% private and crafted around you.', 'watphou-travels' ); ?></p>
	</div>
</section>

<section class="wpt-adventures" id="best-sellers">
	<div class="wpt-container">
		<div class="wpt-adventures__heading">
			<h2><?php esc_html_e( 'Popular Private Tours', 'watphou-travels' ); ?></h2>
			<p><?php esc_html_e( 'A selection of our most popular journeys in Southern Laos.', 'watphou-travels' ); ?></p>
		</div>
		<div class="wpt-adventures__list">
			<?php
			$best_ids = function_exists( 'watphou_core_homepage_bestseller_ids' )
				? watphou_core_homepage_bestseller_ids( 4 )
				: array();
			if ( ! $best_ids && function_exists( 'watphou_core_catalog_bestseller_slugs' ) && function_exists( 'watphou_core_find_tour_id' ) ) {
				foreach ( watphou_core_catalog_bestseller_slugs() as $best_slug ) {
					$bid = watphou_core_find_tour_id( $best_slug );
					if ( $bid ) {
						if ( function_exists( 'pll_get_post' ) ) {
							$lang = function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : '';
							$tid  = $lang ? (int) pll_get_post( $bid, $lang ) : 0;
							$best_ids[] = $tid ?: $bid;
						} else {
							$best_ids[] = $bid;
						}
					}
				}
			}
			if ( $best_ids ) {
				$query = new WP_Query(
					array(
						'post_type'      => 'tour',
						'post__in'       => $best_ids,
						'orderby'        => 'post__in',
						'posts_per_page' => 4,
						'post_status'    => 'publish',
						'lang'           => function_exists( 'watphou_core_current_lang_slug' ) ? watphou_core_current_lang_slug() : '',
					)
				);
			} else {
				$query = new WP_Query(
					array(
						'post_type'      => 'tour',
						'posts_per_page' => 4,
						'meta_key'       => 'tour_priority',
						'orderby'        => 'meta_value_num',
						'order'          => 'DESC',
						'meta_query'     => array(
							array(
								'key'   => 'tour_bestseller',
								'value' => '1',
							),
						),
					)
				);
			}
			if ( ! $query->have_posts() ) {
				$query = new WP_Query(
					array(
						'post_type'      => 'tour',
						'posts_per_page' => 4,
						'meta_key'       => 'tour_priority',
						'orderby'        => 'meta_value_num',
						'order'          => 'DESC',
					)
				);
			}
			$fallbacks = array( 'tad-fane.jpg', 'liphi.jpg', 'vatphou.jpg', 'bolaven.jpg', 'coffee.jpg', 'waterfall.jpg' );
			$i         = 0;
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) :
					$query->the_post();
					$pid      = get_the_ID();
					$duration = get_post_meta( $pid, 'tour_duration', true ) ?: __( 'Flexible', 'watphou-travels' );
					if ( function_exists( 'watphou_core_translate_public_string' ) ) {
						$duration = watphou_core_translate_public_string( (string) $duration );
					}
					$thumb    = function_exists( 'watphou_core_tour_featured_url' )
						? watphou_core_tour_featured_url( (int) $pid, 'tour-card' )
						: get_the_post_thumbnail_url( $pid, 'tour-card' );
					$price    = function_exists( 'watphou_tour_price_number' ) ? watphou_tour_price_number( $pid ) : 'XX';
					if ( ! $thumb ) {
						$thumb = $img . '/' . $fallbacks[ $i % count( $fallbacks ) ];
					}
					?>
					<article class="wpt-tour-row">
						<div class="wpt-tour-row__image">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" decoding="async">
							<span class="wpt-day-badge"><?php echo esc_html( $duration ); ?></span>
						</div>
						<div class="wpt-tour-row__content">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<div class="wpt-tour-row__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 42 ) ); ?></div>
							<h4><?php esc_html_e( 'Travel Routes', 'watphou-travels' ); ?></h4>
							<p><?php esc_html_e( 'Private departure from Pakse — flexible itinerary', 'watphou-travels' ); ?></p>
						</div>
						<div class="wpt-tour-row__price">
							<div class="wpt-price">
								<span><?php esc_html_e( 'from', 'watphou-travels' ); ?></span>
								<span class="wpt-price__num">$<?php echo esc_html( $price ); ?></span>
								<span><?php esc_html_e( '/Person', 'watphou-travels' ); ?></span>
							</div>
							<a class="wpt-view-tour" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View tour', 'watphou-travels' ); ?></a>
						</div>
						<a class="wpt-tour-row__hit" href="<?php the_permalink(); ?>"><span class="screen-reader-text"><?php the_title(); ?></span></a>
					</article>
					<?php
					++$i;
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</div>
</section>

<section class="wpt-steps">
	<div class="wpt-container">
		<h2><?php esc_html_e( 'Plan Your Private Tour in 5 Simple Steps', 'watphou-travels' ); ?></h2>
		<p class="wpt-section-sub"><?php esc_html_e( 'We receive your request, prepare a quotation, confirm what is included, then finalise the tour with you.', 'watphou-travels' ); ?></p>
		<div class="wpt-steps__grid">
			<div>
				<span class="wpt-step-num">1</span>
				<h3><?php esc_html_e( 'Choose Your Tour', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Browse our day tours and multi-day packages across Southern Laos.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">2</span>
				<h3><?php esc_html_e( 'Send Your Request', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Tell us your dates, pace, and interests — every tour is 100% private.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">3</span>
				<h3><?php esc_html_e( 'Receive a Quotation', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Our Pakse team replies with a clear quotation for your group and dates.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">4</span>
				<h3><?php esc_html_e( 'Confirm Inclusions', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'We confirm what is included with you before you accept the quotation.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">5</span>
				<h3><?php esc_html_e( 'Finalise Your Tour', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Once you accept, we finalise the itinerary and you travel with our local team.', 'watphou-travels' ); ?></p>
			</div>
		</div>
	</div>
</section>

<?php
if ( function_exists( 'watphou_core_render_google_reviews' ) ) {
	watphou_core_render_google_reviews();
}
?>

<section class="wpt-why" id="why-us">
	<div class="wpt-container">
		<h2><?php esc_html_e( 'Why Us!', 'watphou-travels' ); ?></h2>
		<div class="wpt-why__grid">
			<div>
				<h3><?php esc_html_e( 'Local expertise + European standards', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Lao team based in Pakse with professional service standards.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<h3><?php esc_html_e( '100% private tours', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Your own vehicle and driver — no shared groups.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<h3><?php esc_html_e( 'Authentic experiences', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Real villages, coffee plantations, waterfalls, and hidden corners.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<h3><?php esc_html_e( 'Transparent & flexible', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Clear starting prices with easy comfort upgrades.', 'watphou-travels' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="wpt-contact-cta">
	<div class="wpt-container">
		<h2><?php esc_html_e( 'Ready to plan your Southern Laos journey?', 'watphou-travels' ); ?></h2>
		<a class="wpt-btn-primary" href="<?php echo esc_url( function_exists( 'watphou_get_whatsapp_url' ) ? watphou_get_whatsapp_url() : 'https://wa.me/8562099495858' ); ?>"><?php esc_html_e( 'WhatsApp us now', 'watphou-travels' ); ?></a>
		<div class="wpt-contact-form">
			<?php echo do_shortcode( '[watphou_booking_form]' ); ?>
		</div>
	</div>
</section>

<?php
get_footer();
