<?php
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Import scraped JSON content into WordPress.
 *
 * ## EXAMPLES
 *
 *     wp watphou import-content
 */
class Watphou_Import_CLI {
	public function import_content( $args, $assoc_args ): void {
		$json_dir = '/var/www/watphou-content/extracted';
		if ( ! is_dir( $json_dir ) ) {
			$json_dir = ABSPATH . '../content/extracted';
		}
		if ( ! is_dir( $json_dir ) ) {
			$json_dir = dirname( ABSPATH ) . '/content/extracted';
		}
		if ( ! is_dir( $json_dir ) ) {
			WP_CLI::error( 'content/extracted not found on server. Copy content/ directory first.' );
		}

		$featured = array(
			'bolaven-plateau-classic-full-day-tour' => 100,
			'bolaven-plateau-classic-2-day'         => 90,
			'4000islands-vatphu-temple-2-day-1-night' => 85,
			'3-day-classic-experience-in-southern-laos' => 80,
		);

		$files = glob( $json_dir . '/*.json' );
		$count = 0;
		foreach ( $files as $file ) {
			$data = json_decode( file_get_contents( $file ), true );
			if ( ! $data || empty( $data['slug'] ) ) {
				continue;
			}
			$slug = $data['slug'];
			$type = $data['page_type'] ?? 'page';

			if ( 'tour' === $type ) {
				$existing = get_posts(
					array(
						'post_type'      => 'tour',
						'meta_key'       => 'tour_legacy_slug',
						'meta_value'     => $slug,
						'posts_per_page' => 1,
						'fields'         => 'ids',
					)
				);
				$postarr = array(
					'post_type'    => 'tour',
					'post_title'   => $data['title'] ?: $slug,
					'post_content' => '<!-- wp:paragraph --><p>' . esc_html( wp_trim_words( $data['body_text'] ?? '', 80 ) ) . '</p><!-- /wp:paragraph -->',
					'post_status'  => 'publish',
					'post_name'    => $slug,
				);
				if ( $existing ) {
					$postarr['ID'] = $existing[0];
					wp_update_post( $postarr );
					$post_id = $existing[0];
				} else {
					$post_id = wp_insert_post( $postarr );
				}
				update_post_meta( $post_id, 'tour_legacy_slug', $slug );
				update_post_meta( $post_id, 'tour_price_from', 'XX' );
				update_post_meta( $post_id, 'tour_currency', 'USD' );
				if ( isset( $featured[ $slug ] ) ) {
					update_post_meta( $post_id, 'tour_bestseller', 1 );
					update_post_meta( $post_id, 'tour_priority', $featured[ $slug ] );
				}
				++$count;
			} elseif ( in_array( $type, array( 'page', 'home', 'listing', 'destination' ), true ) ) {
				$page_slug = ( 'home' === $slug ) ? 'home' : $slug;
				$existing  = get_page_by_path( $page_slug );
				$postarr   = array(
					'post_type'    => 'page',
					'post_title'   => $data['title'] ?: $page_slug,
					'post_content' => '<!-- wp:paragraph --><p>' . esc_html( wp_trim_words( $data['body_text'] ?? '', 120 ) ) . '</p><!-- /wp:paragraph -->',
					'post_status'  => 'publish',
					'post_name'    => $page_slug,
				);
				if ( $existing ) {
					$postarr['ID'] = $existing->ID;
					wp_update_post( $postarr );
				} else {
					wp_insert_post( $postarr );
				}
				++$count;
			}
		}

		// Tailor-made page
		if ( ! get_page_by_path( 'tailor-made-tours' ) ) {
			wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_title'   => 'Tailor-made Tours',
					'post_name'    => 'tailor-made-tours',
					'post_status'  => 'publish',
					'post_content' => '<!-- wp:paragraph --><p>Contact us to design your private journey through Southern Laos.</p><!-- /wp:paragraph -->',
				)
			);
		}

		// Redirect map from CSV if present
		$csv = dirname( $json_dir ) . '/redirects.csv';
		if ( file_exists( $csv ) ) {
			$map = array();
			if ( ( $fh = fopen( $csv, 'r' ) ) ) {
				$header = fgetcsv( $fh );
				while ( ( $row = fgetcsv( $fh ) ) ) {
					if ( count( $row ) < 3 ) {
						continue;
					}
					$old  = $row[0];
					$new  = $row[1];
					$code = $row[2];
					$map[ $old ] = ( '410' === $code ) ? '410' : $new;
				}
				fclose( $fh );
				watphou_core_set_redirect_map( $map );
			}
		}

		flush_rewrite_rules();
		WP_CLI::success( "Imported/updated {$count} items." );
	}
}

WP_CLI::add_command( 'watphou', 'Watphou_Import_CLI' );
