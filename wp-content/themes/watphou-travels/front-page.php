<?php
defined( 'ABSPATH' ) || exit;

get_header();

$img = WATPHOU_THEME_URI . '/assets/images';
?>

<section class="wpt-hero" style="background-image:url('<?php echo esc_url( $img . '/tad-fane.jpg' ); ?>')">
	<div class="wpt-hero__overlay"></div>
	<div class="wpt-container wpt-hero__content">
		<h1><?php esc_html_e( 'Discover Southern Laos Your Way', 'watphou-travels' ); ?></h1>
		<p class="wpt-hero__sub">“<?php esc_html_e( 'Your private journeys with a local Pakse team and European standards', 'watphou-travels' ); ?>”</p>
		<a class="wpt-btn-primary" href="#best-sellers"><?php esc_html_e( 'Get Started Today', 'watphou-travels' ); ?></a>
	</div>
</section>

<section class="wpt-explore">
	<div class="wpt-container wpt-explore__inner">
		<h2><?php esc_html_e( 'Explore Southern Laos like Never Before', 'watphou-travels' ); ?></h2>
		<div class="wpt-quote-mark" aria-hidden="true">“</div>
		<p><?php esc_html_e( 'Welcome to Southern Laos, a captivating region where the mighty Mekong River carves its path through lush landscapes, ancient histories whisper from forgotten temples, and life unfolds at an incredibly gentle pace.', 'watphou-travels' ); ?></p>
		<p><?php esc_html_e( 'Far from the bustling crowds, this enchanting part of Laos offers a truly authentic Southeast Asian experience. Discover Pakse, the Bolaven Plateau with its waterfalls and coffee farms, the UNESCO-listed Vat Phou temple, and the peaceful 4000 Islands.', 'watphou-travels' ); ?></p>
		<p><?php esc_html_e( 'As a local agency based in Pakse, Watphou Travels is uniquely positioned to help you uncover hidden gems. Whether you seek a one-day waterfall escape or a week-long private journey, every itinerary is 100% private and crafted around you.', 'watphou-travels' ); ?></p>
	</div>
</section>

<section class="wpt-interest">
	<div class="wpt-container">
		<h2><?php esc_html_e( 'Tailored Tours for Your Interest', 'watphou-travels' ); ?></h2>
	</div>
	<div class="wpt-interest__track">
		<?php
		$interests = array(
			array( 'Bolaven Plateau', '1 Day', $img . '/bolaven.jpg', home_url( '/destinations/bolaven-plateau/' ) ),
			array( '4000 Islands', '2 Days', $img . '/liphi.jpg', home_url( '/destinations/4000-islands/' ) ),
			array( 'Vat Phou Temple', '1 Day', $img . '/vatphou.jpg', home_url( '/destinations/champasak/' ) ),
			array( 'Coffee Culture', '1 Day', $img . '/coffee.jpg', home_url( '/tours/bolaven-plateau-classic-full-day-tour/' ) ),
			array( 'Waterfalls', '1 Day', $img . '/waterfall.jpg', home_url( '/tours/bolaven-plateau-classic-full-day-tour/' ) ),
			array( 'Pakse & Mekong', '1 Day', $img . '/donkhone.jpg', home_url( '/destinations/pakse/' ) ),
		);
		foreach ( $interests as $item ) :
			?>
			<article class="wpt-interest-card">
				<div class="wpt-interest-card__image">
					<img src="<?php echo esc_url( $item[2] ); ?>" alt="<?php echo esc_attr( $item[0] ); ?>">
					<div class="wpt-day-badge"><?php echo esc_html( $item[1] ); ?></div>
				</div>
				<div class="wpt-interest-card__body">
					<h3><?php echo esc_html( $item[0] ); ?></h3>
					<div class="wpt-interest-card__meta">
						<div class="wpt-from-price">
							<span><?php esc_html_e( 'from', 'watphou-travels' ); ?></span>
							<strong>$XX</strong>
							<span><?php esc_html_e( '/Person', 'watphou-travels' ); ?></span>
						</div>
						<span class="wpt-explore-link"><?php esc_html_e( 'Explore', 'watphou-travels' ); ?></span>
					</div>
				</div>
				<a class="wpt-interest-card__hit" href="<?php echo esc_url( $item[3] ); ?>"><span class="screen-reader-text"><?php echo esc_html( $item[0] ); ?></span></a>
			</article>
		<?php endforeach; ?>
	</div>
</section>

<section class="wpt-adventures" id="best-sellers">
	<div class="wpt-container">
		<div class="wpt-adventures__heading">
			<h2><?php esc_html_e( 'Top Adventures Selected for You', 'watphou-travels' ); ?></h2>
			<p><?php esc_html_e( 'These handpicked tours promise you unforgettable memories.', 'watphou-travels' ); ?></p>
		</div>
		<div class="wpt-adventures__list">
			<?php
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
					$thumb    = get_the_post_thumbnail_url( $pid, 'large' );
					if ( ! $thumb ) {
						$thumb = $img . '/' . $fallbacks[ $i % count( $fallbacks ) ];
					}
					?>
					<article class="wpt-tour-row">
						<div class="wpt-tour-row__image">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
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
								<span class="wpt-price__num">$XX</span>
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

<section class="wpt-destinations">
	<div class="wpt-container">
		<h2><?php esc_html_e( 'The Most Popular Destinations in Southern Laos', 'watphou-travels' ); ?></h2>
		<p class="wpt-section-sub"><?php esc_html_e( 'Here are just a few trip ideas to get you started!', 'watphou-travels' ); ?></p>
		<div class="wpt-dest-grid">
			<?php
			$dests = array(
				array( 'Pakse', $img . '/donkhone.jpg', home_url( '/destinations/pakse/' ) ),
				array( 'Bolaven Plateau', $img . '/bolaven.jpg', home_url( '/destinations/bolaven-plateau/' ) ),
				array( 'Vat Phou', $img . '/vatphou.jpg', home_url( '/destinations/champasak/' ) ),
				array( '4000 Islands', $img . '/liphi.jpg', home_url( '/destinations/4000-islands/' ) ),
				array( 'Tad Fane', $img . '/tad-fane.jpg', home_url( '/tours/bolaven-plateau-classic-full-day-tour/' ) ),
				array( 'Coffee Highlands', $img . '/coffee.jpg', home_url( '/destinations/bolaven-plateau/' ) ),
			);
			foreach ( $dests as $d ) :
				?>
				<a class="wpt-dest-tile" href="<?php echo esc_url( $d[2] ); ?>">
					<img src="<?php echo esc_url( $d[1] ); ?>" alt="<?php echo esc_attr( $d[0] ); ?>">
					<h3><?php echo esc_html( $d[0] ); ?></h3>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="wpt-steps">
	<div class="wpt-container">
		<h2><?php esc_html_e( 'Enjoy Amazing Holidays in 5 Easy Steps', 'watphou-travels' ); ?></h2>
		<p class="wpt-section-sub"><?php esc_html_e( 'A seamless journey from planning to adventure.', 'watphou-travels' ); ?></p>
		<div class="wpt-steps__grid">
			<div>
				<span class="wpt-step-num">1</span>
				<h3><?php esc_html_e( 'Choose Your Tour', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Browse our day tours and multi-day packages across Southern Laos.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">2</span>
				<h3><?php esc_html_e( 'Customize Your Itinerary', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Tell us your dates, pace, and interests — every tour is 100% private.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">3</span>
				<h3><?php esc_html_e( 'Confirm Your Booking', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'We send a clear quote. Prices stay as From $XX until confirmed.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">4</span>
				<h3><?php esc_html_e( 'Prepare for Your Adventure', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Receive practical tips, meeting points, and what to pack.', 'watphou-travels' ); ?></p>
			</div>
			<div>
				<span class="wpt-step-num">5</span>
				<h3><?php esc_html_e( 'Enjoy Your Tour', 'watphou-travels' ); ?></h3>
				<p><?php esc_html_e( 'Travel with a local driver-guide from Pakse and create lasting memories.', 'watphou-travels' ); ?></p>
			</div>
		</div>
	</div>
</section>

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

<section class="wpt-reviews" id="reviews">
	<div class="wpt-container">
		<h2><?php esc_html_e( 'Hear from Our Happy Travelers', 'watphou-travels' ); ?></h2>
		<p class="wpt-reviews__note"><?php esc_html_e( 'Genuine Google reviews will appear here once imported. We do not display invented reviews.', 'watphou-travels' ); ?></p>
		<a class="wpt-cta-outline" href="https://www.google.com/maps/search/Watphou+Travels+Pakse" target="_blank" rel="noopener"><?php esc_html_e( 'Read more reviews on Google', 'watphou-travels' ); ?></a>
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
