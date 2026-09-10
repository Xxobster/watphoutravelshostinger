<?php
/**
 * Admin one-click importer for content/packages JSON (Hostinger has no WP-CLI / exec).
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'watphou_core_import_admin_menu' );

function watphou_core_import_admin_menu(): void {
	add_submenu_page(
		'watphou-dashboard',
		__( 'Import packages', 'watphou-core' ),
		__( 'Import packages', 'watphou-core' ),
		'manage_options',
		'watphou-import',
		'watphou_core_render_import_page'
	);
}

function watphou_core_packages_dir(): string {
	$candidates = array(
		WATPHOU_CORE_PATH . 'data/packages',
		WP_CONTENT_DIR . '/../content/packages',
		dirname( ABSPATH ) . '/content/packages',
		ABSPATH . 'content/packages',
	);
	foreach ( $candidates as $dir ) {
		$dir = wp_normalize_path( $dir );
		if ( is_dir( $dir ) ) {
			return $dir;
		}
	}
	return '';
}

function watphou_core_redirects_csv_path(): string {
	$candidates = array(
		WATPHOU_CORE_PATH . 'data/redirects.csv',
		WP_CONTENT_DIR . '/../content/redirects.csv',
		dirname( ABSPATH ) . '/content/redirects.csv',
		ABSPATH . 'content/redirects.csv',
	);
	foreach ( $candidates as $file ) {
		$file = wp_normalize_path( $file );
		if ( is_file( $file ) ) {
			return $file;
		}
	}
	return '';
}

function watphou_core_build_tour_content( array $data ): string {
	$blocks = array();
	if ( ! empty( $data['dream'] ) ) {
		$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $data['dream'] ) . '</p><!-- /wp:paragraph -->';
	}
	if ( ! empty( $data['highlights'] ) && is_array( $data['highlights'] ) ) {
		$blocks[] = '<!-- wp:watphou/highlights -->' . "\n" . esc_html( implode( "\n", $data['highlights'] ) ) . "\n" . '<!-- /wp:watphou/highlights -->';
	}
	if ( ! empty( $data['itinerary'] ) && is_array( $data['itinerary'] ) ) {
		foreach ( $data['itinerary'] as $day ) {
			$day_num = (int) ( $day['day'] ?? 1 );
			$title   = esc_attr( $day['title'] ?? '' );
			$body    = esc_html( $day['body'] ?? '' );
			$blocks[] = sprintf(
				'<!-- wp:watphou/itinerary-day {"day":%1$d,"title":"%2$s"} --><p>%3$s</p><!-- /wp:watphou/itinerary-day -->',
				$day_num,
				$title,
				$body
			);
		}
	}
	foreach ( array( 'included' => 'included', 'excluded' => 'excluded', 'upgrades' => 'upgrades' ) as $key => $type ) {
		if ( empty( $data[ $key ] ) || ! is_array( $data[ $key ] ) ) {
			continue;
		}
		$blocks[] = sprintf(
			'<!-- wp:watphou/inclusions {"type":"%1$s"} -->%2$s<!-- /wp:watphou/inclusions -->',
			esc_attr( $type ),
			esc_html( implode( "\n", $data[ $key ] ) )
		);
	}
	if ( ! empty( $data['cta'] ) ) {
		$blocks[] = '<!-- wp:paragraph --><p><strong>' . esc_html( $data['cta'] ) . '</strong></p><!-- /wp:paragraph -->';
	}
	return implode( "\n\n", $blocks );
}

function watphou_core_import_packages(): array {
	$dir   = watphou_core_packages_dir();
	$stats = array( 'tours' => 0, 'pages' => 0, 'errors' => array(), 'dir' => $dir );
	if ( ! $dir ) {
		$stats['errors'][] = 'content/packages directory not found.';
		return $stats;
	}

	$terms = array(
		'1-day'    => 'Day Tours',
		'2-day'    => '2-Day Tours',
		'3-day'    => '3-Day Tours',
		'4-6-day'  => '4–6 Day Tours',
	);
	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'duration' ) ) {
			wp_insert_term( $name, 'duration', array( 'slug' => $slug ) );
		}
	}
	$dest_labels = array(
		'bolaven-plateau' => 'Bolaven Plateau',
		'4000-islands'    => '4000 Islands (Si Phan Don)',
		'champasak'       => 'Vat Phou & Champasak',
		'pakse'           => 'Pakse & Surroundings',
	);
	foreach ( $dest_labels as $slug => $name ) {
		if ( ! term_exists( $slug, 'destination' ) ) {
			wp_insert_term( $name, 'destination', array( 'slug' => $slug ) );
		}
	}

	$files = glob( trailingslashit( $dir ) . '*.json' ) ?: array();
	foreach ( $files as $file ) {
		if ( 'index.json' === basename( $file ) ) {
			continue;
		}
		$data = json_decode( (string) file_get_contents( $file ), true );
		if ( ! is_array( $data ) || empty( $data['slug'] ) ) {
			$stats['errors'][] = 'Invalid JSON: ' . basename( $file );
			continue;
		}
		$slug = sanitize_title( $data['slug'] );
		$type = $data['page_type'] ?? 'tour';

		if ( 'page' === $type || 'tailor-made-tours' === $slug ) {
			$existing = get_page_by_path( $slug );
			$postarr  = array(
				'post_type'    => 'page',
				'post_title'   => $data['title'] ?? $slug,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_content' => watphou_core_build_tour_content( $data ),
				'post_excerpt' => $data['dream'] ?? '',
			);
			if ( $existing ) {
				$postarr['ID'] = $existing->ID;
				wp_update_post( $postarr );
			} else {
				wp_insert_post( $postarr );
			}
			++$stats['pages'];
			continue;
		}

		$existing = get_posts(
			array(
				'post_type'      => 'tour',
				'name'           => $slug,
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			)
		);
		$postarr = array(
			'post_type'    => 'tour',
			'post_title'   => $data['title'] ?? $slug,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_content' => watphou_core_build_tour_content( $data ),
			'post_excerpt' => $data['dream'] ?? ( $data['headline'] ?? '' ),
		);
		if ( $existing ) {
			$postarr['ID'] = (int) $existing[0];
			$post_id       = wp_update_post( $postarr );
		} else {
			$post_id = wp_insert_post( $postarr );
		}
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			$stats['errors'][] = 'Failed tour: ' . $slug;
			continue;
		}

		update_post_meta( $post_id, 'tour_legacy_slug', $slug );
		update_post_meta( $post_id, 'tour_headline', $data['headline'] ?? '' );
		update_post_meta( $post_id, 'tour_duration', $data['duration'] ?? '' );
		update_post_meta( $post_id, 'tour_price_from', $data['price_from'] ?? 'XX' );
		update_post_meta( $post_id, 'tour_currency', 'USD' );
		update_post_meta( $post_id, 'tour_bestseller', ! empty( $data['bestseller'] ) ? '1' : '0' );
		update_post_meta( $post_id, 'tour_priority', (string) (int) ( $data['priority'] ?? 0 ) );
		update_post_meta( $post_id, 'tour_whatsapp_message', 'Hello Watphou Travels, I would like to enquire about: ' . ( $data['title'] ?? $slug ) );
		update_post_meta( $post_id, 'tour_code', $data['code'] ?? '' );
		update_post_meta( $post_id, 'tour_image_file', $data['image'] ?? '' );

		if ( ! empty( $data['duration_term'] ) ) {
			wp_set_object_terms( $post_id, $data['duration_term'], 'duration', false );
		}
		if ( ! empty( $data['destinations'] ) && is_array( $data['destinations'] ) ) {
			wp_set_object_terms( $post_id, $data['destinations'], 'destination', false );
		}
		++$stats['tours'];
	}

	$pages = array(
		'about-us'       => array( 'About Us', 'Your local expert in Southern Laos — private tours with European standards.' ),
		'contact-us'     => array( 'Contact Us', "Phone/WhatsApp: +85620 9949 5858\nEmail: sales.watphoutravel@gmail.com\nAddress: Street N°5, Ban Vat Luang, Pakse, Laos" ),
		'book-online'    => array( 'Book Online', 'Request a private tour. We reply with a clear quote for your dates.' ),
		'day-tours'      => array( 'Day Tours', 'Private full-day and half-day journeys from Pakse.' ),
		'2-day-tours'    => array( '2-Day Tours', 'Overnight packages across Bolaven, Vat Phou and the 4000 Islands.' ),
		'3-day-tours'    => array( '3-Day Tours', 'Three-day private introductions to Southern Laos.' ),
		'4-6-day-tours'  => array( '4–6 Day Tours', 'Longer private journeys with flexible end points.' ),
		'destinations'   => array( 'Destinations', 'Bolaven Plateau, 4000 Islands, Vat Phou & Champasak, Pakse & Surroundings.' ),
	);
	foreach ( $pages as $slug => $pair ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $pair[0],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $pair[1] ) . '</p><!-- /wp:paragraph -->' .
					( 'book-online' === $slug ? "\n\n<!-- wp:shortcode -->[watphou_booking_form]<!-- /wp:shortcode -->" : '' ),
			)
		);
		++$stats['pages'];
	}

	$front = get_page_by_path( 'home' );
	if ( ! $front ) {
		$front_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Home',
				'post_name'   => 'home',
				'post_status' => 'publish',
				'post_content'=> '',
			)
		);
	} else {
		$front_id = $front->ID;
	}
	if ( $front_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $front_id );
	}

	$csv = watphou_core_redirects_csv_path();
	if ( $csv && ( $fh = fopen( $csv, 'r' ) ) ) {
		$map = array();
		fgetcsv( $fh );
		while ( ( $row = fgetcsv( $fh ) ) ) {
			if ( count( $row ) < 3 ) {
				continue;
			}
			$old  = untrailingslashit( $row[0] );
			$new  = $row[1];
			$code = $row[2];
			if ( '200' === $code || '' === $old || '/' === $old ) {
				continue;
			}
			$map[ $old ] = ( '410' === $code ) ? '410' : $new;
		}
		fclose( $fh );
		watphou_core_set_redirect_map( $map );
		$stats['redirects'] = count( $map );
	}

	flush_rewrite_rules( false );
	return $stats;
}

function watphou_core_render_import_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$result = null;
	if ( isset( $_POST['watphou_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['watphou_import_nonce'] ) ), 'watphou_import' ) ) {
		$result = watphou_core_import_packages();
	}
	$dir = watphou_core_packages_dir();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import packages', 'watphou-core' ); ?></h1>
		<p><?php esc_html_e( 'Loads September 2026 package JSON into tours and pages. Prices stay as From $XX.', 'watphou-core' ); ?></p>
		<p><code><?php echo esc_html( $dir ?: '(packages directory not found — upload content/packages next to wp-content)' ); ?></code></p>
		<?php if ( $result ) : ?>
			<div class="notice notice-success"><p>
				<?php
				printf(
					esc_html__( 'Imported %1$d tours, %2$d pages, %3$d redirects.', 'watphou-core' ),
					(int) ( $result['tours'] ?? 0 ),
					(int) ( $result['pages'] ?? 0 ),
					(int) ( $result['redirects'] ?? 0 )
				);
				?>
			</p></div>
			<?php if ( ! empty( $result['errors'] ) ) : ?>
				<div class="notice notice-warning"><ul>
					<?php foreach ( $result['errors'] as $err ) : ?>
						<li><?php echo esc_html( $err ); ?></li>
					<?php endforeach; ?>
				</ul></div>
			<?php endif; ?>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'watphou_import', 'watphou_import_nonce' ); ?>
			<?php submit_button( __( 'Run import', 'watphou-core' ) ); ?>
		</form>
	</div>
	<?php
}
