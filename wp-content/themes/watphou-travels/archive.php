<?php
defined( 'ABSPATH' ) || exit;
get_header();
$img       = WATPHOU_THEME_URI . '/assets/images';
$fallbacks = array( 'tad-fane.jpg', 'liphi.jpg', 'vatphou.jpg', 'bolaven.jpg', 'coffee.jpg', 'waterfall.jpg' );
?>
<main class="wpt-main">
	<section class="wpt-archive-hero">
		<div class="wpt-container">
			<h1><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<p>', '</p>' ); ?>
		</div>
	</section>
	<div class="wpt-container wpt-adventures__list">
		<?php
		$i = 0;
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$pid   = get_the_ID();
				$thumb = get_the_post_thumbnail_url( $pid, 'large' );
				if ( ! $thumb ) {
					$thumb = $img . '/' . $fallbacks[ $i % count( $fallbacks ) ];
				}
				$duration = get_post_meta( $pid, 'tour_duration', true ) ?: __( 'Flexible', 'watphou-travels' );
				?>
				<article class="wpt-tour-row">
					<div class="wpt-tour-row__image">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
						<span class="wpt-day-badge"><?php echo esc_html( $duration ); ?></span>
					</div>
					<div class="wpt-tour-row__content">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<div class="wpt-tour-row__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 36 ) ); ?></div>
					</div>
					<div class="wpt-tour-row__price">
						<div class="wpt-price">
							<span><?php esc_html_e( 'from', 'watphou-travels' ); ?></span>
							<span class="wpt-price__num">$XX</span>
							<span><?php esc_html_e( '/Person', 'watphou-travels' ); ?></span>
						</div>
						<a class="wpt-view-tour" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View tour', 'watphou-travels' ); ?></a>
					</div>
				</article>
				<?php
				++$i;
			}
		} else {
			echo '<p>' . esc_html__( 'No tours found.', 'watphou-travels' ) . '</p>';
		}
		?>
	</div>
</main>
<?php
get_footer();
