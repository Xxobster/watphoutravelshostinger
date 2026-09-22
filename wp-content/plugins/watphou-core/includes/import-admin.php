<?php
/**
 * Manager Excel importer for tours. Photos stay on Quick edit tours.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'watphou_core_import_admin_menu', 21 );
add_action( 'admin_init', 'watphou_core_redirect_old_import_page' );
add_action( 'admin_init', 'watphou_core_maybe_send_example_xlsx', 0 );

function watphou_core_import_admin_menu(): void {
	add_submenu_page(
		'watphou-dashboard',
		__( 'Add tours (Excel)', 'watphou-core' ),
		__( 'Add tours (Excel)', 'watphou-core' ),
		'edit_tours',
		'watphou-package-sheet',
		'watphou_core_render_import_page'
	);
	add_submenu_page(
		'options.php',
		__( 'Tour Excel example', 'watphou-core' ),
		__( 'Tour Excel example', 'watphou-core' ),
		'edit_tours',
		'watphou-package-example',
		'watphou_core_download_package_example'
	);
}

function watphou_core_redirect_old_import_page(): void {
	if ( ! is_admin() || ! isset( $_GET['page'] ) ) {
		return;
	}
	$page = sanitize_key( wp_unslash( (string) $_GET['page'] ) );
	if ( 'watphou-import' === $page ) {
		wp_safe_redirect( admin_url( 'admin.php?page=watphou-package-sheet' ) );
		exit;
	}
}

function watphou_core_example_xlsx_path(): string {
	return WATPHOU_CORE_PATH . 'data/watphou-package-import-example.xlsx';
}

function watphou_core_maybe_send_example_xlsx(): void {
	if ( ! is_admin() || ! isset( $_GET['page'] ) ) {
		return;
	}
	if ( 'watphou-package-example' !== sanitize_key( wp_unslash( (string) $_GET['page'] ) ) ) {
		return;
	}
	watphou_core_download_package_example();
}

function watphou_core_download_package_example(): void {
	if ( ! current_user_can( 'edit_tours' ) ) {
		wp_die( esc_html__( 'Access denied.', 'watphou-core' ) );
	}
	$path = watphou_core_example_xlsx_path();
	if ( ! is_readable( $path ) ) {
		wp_die( esc_html__( 'Example Excel file is missing.', 'watphou-core' ) );
	}
	nocache_headers();
	header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	header( 'Content-Disposition: attachment; filename="Watphou-tour-package-example.xlsx"' );
	header( 'Content-Length: ' . (string) filesize( $path ) );
	readfile( $path );
	exit;
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

function watphou_core_import_packages(): array {
	$dir   = watphou_core_packages_dir();
	$stats = array(
		'tours'   => 0,
		'pages'   => 0,
		'errors'  => array(),
		'dir'     => $dir,
		'created' => array(),
		'updated' => array(),
	);
	if ( ! $dir ) {
		$stats['errors'][] = 'content/packages directory not found.';
		return $stats;
	}

	watphou_core_ensure_package_terms();

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

		try {
			$result = watphou_core_upsert_tour_from_data( $data );
		} catch ( Exception $e ) {
			$stats['errors'][] = $e->getMessage();
			continue;
		}
		++$stats['tours'];
		if ( $result['created'] ) {
			$stats['created'][] = $result['title'];
		} else {
			$stats['updated'][] = $result['title'];
		}
	}

	$pages = array(
		'about-us'      => array( 'About Us', 'About Watphou Travels — established in 2008 in Pakse. Full page copy matches the live Wix About Us page.' ),
		'book-online'   => array( 'Request a quote', 'You are requesting a private tour. We reply with a clear quote for your dates.' ),
		'contact-us'    => array( 'Contact Us', "Planning a trip in Southern Laos? Contact our Pakse team by WhatsApp, phone or email and tell us your dates and travel plans.\nPhone/WhatsApp: +85620 9949 5858\nEmail: sales.watphoutravel@gmail.com\nAddress: Street N°5, Ban Vat Luang, Pakse, Laos" ),
		'day-tours'     => array( 'Day Tours', 'Private full-day and half-day journeys from Pakse.' ),
		'2-day-tours'   => array( '2-Day Tours', 'Overnight packages across Bolaven, Vat Phou and the 4000 Islands.' ),
		'3-day-tours'   => array( '3-Day Tours', 'Three-day private introductions to Southern Laos.' ),
		'4-6-day-tours' => array( '4-6 Day Tours', 'Longer private journeys with flexible end points.' ),
		'destinations'  => array( 'Destinations', 'Bolaven Plateau, 4000 Islands, Vat Phou & Champasak, Pakse & Surroundings.' ),
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
				'post_type'    => 'page',
				'post_title'   => 'Home',
				'post_name'    => 'home',
				'post_status'  => 'publish',
				'post_content' => '',
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

/**
 * @return array{tours:int,created:array,updated:array,errors:array}
 */
function watphou_core_import_excel_upload(): array {
	$stats = array(
		'tours'         => 0,
		'created'       => array(),
		'updated'       => array(),
		'translations'  => array(),
		'errors'        => array(),
	);
	if ( empty( $_FILES['watphou_xlsx'] ) || ! is_array( $_FILES['watphou_xlsx'] ) ) {
		$stats['errors'][] = 'Choose an Excel file first.';
		return $stats;
	}
	$file = $_FILES['watphou_xlsx'];
	if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
		$stats['errors'][] = 'Upload failed. Try a smaller .xlsx file.';
		return $stats;
	}
	$tmp  = (string) ( $file['tmp_name'] ?? '' );
	$name = strtolower( (string) ( $file['name'] ?? '' ) );
	if ( '' === $tmp || ! is_uploaded_file( $tmp ) ) {
		$stats['errors'][] = 'Choose an Excel file first.';
		return $stats;
	}
	if ( ! str_ends_with( $name, '.xlsx' ) ) {
		$stats['errors'][] = 'Please upload an .xlsx Excel workbook (not .xls or .csv).';
		return $stats;
	}

	try {
		$sheets = watphou_xlsx_read( $tmp );
	} catch ( Exception $e ) {
		$stats['errors'][] = $e->getMessage();
		return $stats;
	}

	$tour_rows = watphou_core_excel_sheet_rows( $sheets, array( 'tours', 'tour', 'packages' ) );
	$day_rows  = watphou_core_excel_sheet_rows( $sheets, array( 'itinerary', 'days', 'day_by_day' ) );
	if ( ! $tour_rows ) {
		$stats['errors'][] = 'No rows found on the Tours sheet. Keep the first row as column titles.';
		return $stats;
	}

	$packages = watphou_core_excel_to_packages( $tour_rows, $day_rows );
	if ( ! $packages ) {
		$stats['errors'][] = 'No tours found. Each row needs a slug or a title.';
		return $stats;
	}

	foreach ( $packages as $data ) {
		try {
			$result = watphou_core_upsert_tour_from_data( $data );
		} catch ( Exception $e ) {
			$stats['errors'][] = $e->getMessage();
			continue;
		}
		++$stats['tours'];
		if ( $result['created'] ) {
			$stats['created'][] = $result['title'];
		} else {
			$stats['updated'][] = $result['title'];
		}
	}

	foreach ( array( 'fr', 'th' ) as $lang ) {
		$lang_tours = watphou_core_excel_sheet_rows( $sheets, watphou_core_excel_lang_aliases( $lang, 'tours' ) );
		if ( ! $lang_tours ) {
			continue;
		}
		$lang_days  = watphou_core_excel_sheet_rows( $sheets, watphou_core_excel_lang_aliases( $lang, 'itinerary' ) );
		$lang_packs = watphou_core_excel_to_packages( $lang_tours, $lang_days );
		foreach ( $lang_packs as $data ) {
			if ( empty( $data['title'] ) ) {
				continue;
			}
			$en_id = watphou_core_find_tour_id( (string) $data['slug'], (string) ( $data['code'] ?? '' ) );
			if ( ! $en_id ) {
				$stats['errors'][] = strtoupper( $lang ) . ': no English tour matches slug ' . $data['slug'];
				continue;
			}
			watphou_core_update_translated_tour( $en_id, $lang, $data );
			$stats['translations'][] = strtoupper( $lang ) . ' ' . $data['title'];
		}
	}
	return $stats;
}

/**
 * @param array<string, array<int, array<string, string>>> $sheets
 * @param string[]                                         $aliases
 * @return array<int, array<string, string>>
 */
function watphou_core_excel_sheet_rows( array $sheets, array $aliases ): array {
	foreach ( $sheets as $name => $rows ) {
		$key = watphou_xlsx_header_key( (string) $name );
		if ( in_array( $key, $aliases, true ) ) {
			return $rows;
		}
	}
	return array();
}

/**
 * @return string[]
 */
function watphou_core_excel_lang_aliases( string $lang, string $kind ): array {
	$lang = strtolower( $lang );
	$name = ( 'th' === $lang ) ? 'thai' : 'french';
	if ( 'itinerary' === $kind ) {
		return array( 'itinerary_' . $lang, 'days_' . $lang, 'itinerary_' . $name );
	}
	return array( $lang, $name, 'tours_' . $lang, 'tour_' . $lang, 'tours_' . $name );
}

function watphou_core_render_import_page(): void {
	if ( ! current_user_can( 'edit_tours' ) ) {
		wp_die( esc_html__( 'Access denied.', 'watphou-core' ) );
	}

	$excel_result = null;
	$json_result  = null;
	if ( isset( $_POST['watphou_excel_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['watphou_excel_nonce'] ) ), 'watphou_excel' ) ) {
		$excel_result = watphou_core_import_excel_upload();
	}
	if ( current_user_can( 'manage_options' ) && isset( $_POST['watphou_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['watphou_import_nonce'] ) ), 'watphou_import' ) ) {
		$json_result = watphou_core_import_packages();
	}

	$example_url = admin_url( 'admin.php?page=watphou-package-example' );
	$desk_url    = admin_url( 'admin.php?page=watphou-tour-desk' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Add or update tours with Excel', 'watphou-core' ); ?></h1>
		<p><?php esc_html_e( 'Fill the Excel sheet with the tour text, upload it here, then add the photo in Quick edit tours. The sheet does not change pictures.', 'watphou-core' ); ?></p>

		<ol>
			<li>
				<a class="button button-secondary" href="<?php echo esc_url( $example_url ); ?>">
					<?php esc_html_e( 'Download example Excel (3-Day Classic)', 'watphou-core' ); ?>
				</a>
			</li>
			<li><?php esc_html_e( 'Keep the example row as a model. To add a new tour, copy that row and change the slug (web address name) and the title.', 'watphou-core' ); ?></li>
			<li><?php esc_html_e( 'Put each day of the itinerary on the Itinerary sheet, using the same slug.', 'watphou-core' ); ?></li>
			<li><?php esc_html_e( 'French text goes on the FR sheet. Thai text goes on the TH sheet. Keep the same slug as on Tours. Day titles and bodies are extra columns (day1_title, day1_body, and so on).', 'watphou-core' ); ?></li>
			<li><?php esc_html_e( 'Leave price as XX until the real price is confirmed. Do not invent a number.', 'watphou-core' ); ?></li>
			<li><?php esc_html_e( 'Upload the .xlsx file below. Then open Quick edit tours and click Change photo.', 'watphou-core' ); ?></li>
		</ol>

		<?php if ( $excel_result ) : ?>
			<?php watphou_core_render_import_result( $excel_result, $desk_url ); ?>
		<?php endif; ?>

		<form method="post" enctype="multipart/form-data" style="margin:1.5rem 0;padding:1rem;background:#fff;border:1px solid #c3c4c7;max-width:40rem;">
			<?php wp_nonce_field( 'watphou_excel', 'watphou_excel_nonce' ); ?>
			<p>
				<label for="watphou_xlsx"><strong><?php esc_html_e( 'Excel file (.xlsx)', 'watphou-core' ); ?></strong></label><br>
				<input type="file" id="watphou_xlsx" name="watphou_xlsx" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
			</p>
			<?php submit_button( __( 'Upload and save tours', 'watphou-core' ), 'primary', 'submit', false ); ?>
			<a class="button" href="<?php echo esc_url( $desk_url ); ?>"><?php esc_html_e( 'Open Quick edit tours', 'watphou-core' ); ?></a>
		</form>

		<h2><?php esc_html_e( 'Allowed destination names', 'watphou-core' ); ?></h2>
		<p><?php esc_html_e( 'In the Destinations column, use one or more of these, separated by commas:', 'watphou-core' ); ?></p>
		<ul>
			<li><code>bolaven-plateau</code></li>
			<li><code>4000-islands</code></li>
			<li><code>champasak</code></li>
			<li><code>pakse</code></li>
		</ul>
		<p><?php esc_html_e( 'Duration menu: 1-day, 2-day, 3-day, or 4-6-day.', 'watphou-core' ); ?></p>

		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<details>
				<summary><?php esc_html_e( 'Webmaster: JSON package folder (not for daily use)', 'watphou-core' ); ?></summary>
				<p><?php esc_html_e( 'Loads September 2026 package JSON into tours and pages. Prices stay as From $XX.', 'watphou-core' ); ?></p>
				<p><code><?php echo esc_html( watphou_core_packages_dir() ?: '(packages directory not found on this server)' ); ?></code></p>
				<?php if ( $json_result ) : ?>
					<?php watphou_core_render_import_result( $json_result, $desk_url ); ?>
				<?php endif; ?>
				<form method="post">
					<?php wp_nonce_field( 'watphou_import', 'watphou_import_nonce' ); ?>
					<?php submit_button( __( 'Run JSON import', 'watphou-core' ), 'secondary' ); ?>
				</form>
			</details>
		<?php endif; ?>
	</div>
	<?php
}

function watphou_core_render_import_result( array $result, string $desk_url ): void {
	$created = $result['created'] ?? array();
	$updated = $result['updated'] ?? array();
	$errors  = $result['errors'] ?? array();
	if ( $created || $updated ) {
		echo '<div class="notice notice-success"><p>';
		printf(
			esc_html__( 'Saved %1$d tour(s): %2$d new, %3$d updated.', 'watphou-core' ),
			(int) ( $result['tours'] ?? ( count( $created ) + count( $updated ) ) ),
			count( $created ),
			count( $updated )
		);
		echo '</p>';
		if ( $created ) {
			echo '<p>' . esc_html__( 'New:', 'watphou-core' ) . ' ' . esc_html( implode( ', ', $created ) ) . '</p>';
		}
		if ( $updated ) {
			echo '<p>' . esc_html__( 'Updated:', 'watphou-core' ) . ' ' . esc_html( implode( ', ', $updated ) ) . '</p>';
		}
		$translations = $result['translations'] ?? array();
		if ( $translations ) {
			echo '<p>' . esc_html__( 'French / Thai:', 'watphou-core' ) . ' ' . esc_html( implode( ', ', $translations ) ) . '</p>';
		}
		echo '<p><a href="' . esc_url( $desk_url ) . '">' . esc_html__( 'Add photos in Quick edit tours', 'watphou-core' ) . '</a></p>';
		echo '</div>';
	}
	if ( $errors ) {
		echo '<div class="notice notice-warning"><ul>';
		foreach ( $errors as $err ) {
			echo '<li>' . esc_html( (string) $err ) . '</li>';
		}
		echo '</ul></div>';
	}
}
