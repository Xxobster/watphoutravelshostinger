<?php
/**
 * Manager spreadsheet: change tour photos (top + bottom gallery), price, duration, and bestseller.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'watphou_core_tour_desk_menu', 20 );
add_action( 'admin_enqueue_scripts', 'watphou_core_tour_desk_assets' );

function watphou_core_tour_desk_menu(): void {
	add_submenu_page(
		'watphou-dashboard',
		__( 'Quick edit tours', 'watphou-core' ),
		__( 'Quick edit tours', 'watphou-core' ),
		'edit_tours',
		'watphou-tour-desk',
		'watphou_core_render_tour_desk'
	);
}

function watphou_core_tour_desk_assets( string $hook ): void {
	if ( false === strpos( $hook, 'watphou-tour-desk' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'jquery' );
}

function watphou_core_map_duration_term( string $duration ): string {
	$d = strtolower( $duration );
	if ( str_contains( $d, '6' ) || str_contains( $d, '5' ) || str_contains( $d, '4' ) ) {
		return '4-6-day';
	}
	if ( str_contains( $d, '3' ) ) {
		return '3-day';
	}
	if ( str_contains( $d, '2' ) ) {
		return '2-day';
	}
	return '1-day';
}

function watphou_core_desk_gallery_count_text( int $count ): string {
	if ( $count < 1 ) {
		return __( 'No bottom photos on the tour page', 'watphou-core' );
	}
	return sprintf(
		_n( '%d photo on the tour page', '%d photos on the tour page', $count, 'watphou-core' ),
		$count
	);
}

function watphou_core_render_tour_desk(): void {
	if ( ! current_user_can( 'edit_tours' ) ) {
		wp_die( esc_html__( 'Access denied.', 'watphou-core' ) );
	}

	$seed_left = 0;
	if ( function_exists( 'watphou_core_run_gallery_seed_batch' ) && function_exists( 'watphou_core_gallery_seed_complete' ) && ! watphou_core_gallery_seed_complete() && current_user_can( 'upload_files' ) ) {
		$seed_left = watphou_core_run_gallery_seed_batch( 2 );
	}

	$saved = 0;
	if ( isset( $_POST['watphou_desk_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['watphou_desk_nonce'] ) ), 'watphou_desk' ) ) {
		$rows = isset( $_POST['tour'] ) && is_array( $_POST['tour'] ) ? wp_unslash( $_POST['tour'] ) : array();
		foreach ( $rows as $id => $row ) {
			$id = (int) $id;
			if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
				continue;
			}
			if ( get_post_type( $id ) !== 'tour' ) {
				continue;
			}
			$price    = sanitize_text_field( $row['price'] ?? 'XX' );
			$duration = sanitize_text_field( $row['duration'] ?? '' );
			$thumb    = isset( $row['thumb'] ) ? (int) $row['thumb'] : 0;
			$ids      = function_exists( 'watphou_core_tour_translation_ids' ) ? watphou_core_tour_translation_ids( $id ) : array( $id );
			foreach ( $ids as $tid ) {
				if ( function_exists( 'watphou_core_set_tour_price' ) ) {
					watphou_core_set_tour_price( $tid, $price );
				} else {
					update_post_meta( $tid, 'tour_price_from', $price !== '' ? $price : 'XX' );
				}
				if ( function_exists( 'watphou_core_set_tour_duration' ) ) {
					watphou_core_set_tour_duration( $tid, $duration );
				} else {
					update_post_meta( $tid, 'tour_duration', $duration );
				}
				update_post_meta( $tid, 'tour_bestseller', ! empty( $row['bestseller'] ) ? '1' : '0' );
				if ( $thumb > 0 ) {
					set_post_thumbnail( $tid, $thumb );
				} elseif ( isset( $row['clear_thumb'] ) ) {
					delete_post_thumbnail( $tid );
				}
			}
			if ( ! empty( $row['prices_dirty'] ) && function_exists( 'watphou_core_set_price_table' ) ) {
				$json  = is_string( $row['price_table'] ?? null ) ? $row['price_table'] : '';
				$table = ( '' !== $json && strlen( $json ) <= 20000 ) ? json_decode( $json, true, 6 ) : null;
				if ( is_array( $table ) ) {
					$table = watphou_core_sanitize_price_table( $table );
					// "From $" is its own field; the table never changes it (customer's decision 2026-10-16).
					watphou_core_set_price_table( $id, $table );
				}
			}
			if ( ! empty( $row['gallery_dirty'] ) && function_exists( 'watphou_core_unique_positive_ids' ) ) {
				$gids = watphou_core_unique_positive_ids( (string) ( $row['gallery'] ?? '' ) );
				if ( function_exists( 'watphou_core_apply_tour_gallery' ) ) {
					watphou_core_apply_tour_gallery( $id, $gids );
				}
			}
			++$saved;
		}
	}

	$query_args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft', 'pending' ),
		'orderby'        => 'title',
		'order'          => 'ASC',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$query_args['lang'] = pll_default_language() ?: 'en';
	}
	$tours = get_posts( $query_args );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick edit tours', 'watphou-core' ); ?></h1>
		<p><?php esc_html_e( 'Change the top photo, the bottom photos on the tour page, starting price, duration, and homepage bestseller here. You do not need Appearance → Customize. Use “Edit text” to change titles, itinerary, and French/Thai copy. Price, photos, duration, and homepage flag update the listing pages, the tour page, the request form, and French/Thai copies of the same tour.', 'watphou-core' ); ?></p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-tours' ) ); ?>"><?php esc_html_e( 'Manage tours (add, hide, preview, EN/FR/TH text)', 'watphou-core' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-package-sheet' ) ); ?>"><?php esc_html_e( 'Add or update tour text from Excel', 'watphou-core' ); ?></a>
		</p>
		<p>
			<strong><?php esc_html_e( 'Top photo:', 'watphou-core' ); ?></strong>
			<?php esc_html_e( 'The large picture at the top of the tour page (and the listing card).', 'watphou-core' ); ?>
			<strong><?php esc_html_e( 'Bottom photos:', 'watphou-core' ); ?></strong>
			<?php esc_html_e( 'The picture grid under the itinerary. Click “Change bottom photos”, then click each picture to add or remove it. You do not need Ctrl. The tour page shows exactly the photos still selected: 6 selected means 6 on the page, 3 means 3. “Preview on the page” shows that grid before you save. Then click Save all changes.', 'watphou-core' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Price:', 'watphou-core' ); ?></strong>
			<?php esc_html_e( 'Type a number (for example 95) or leave XX until the real price is confirmed. The website shows “From $XX” until then.', 'watphou-core' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Price table:', 'watphou-core' ); ?></strong>
			<?php esc_html_e( 'Click “Edit price table” under a tour. Each line is a group size (type 2, 4, or 6/8) with the price per person in US dollars. Day tours have one price; tours with nights have a twin/double room price and a single room price. Use “Add line” or copy cells from Excel and paste them into the first cell. “From $” is not changed by the table. Then click Save all changes.', 'watphou-core' ); ?>
		</p>
		<?php if ( $seed_left ) : ?>
			<div class="notice notice-info"><p><?php echo esc_html( sprintf( __( 'WordPress is copying the current bottom photos into the Media Library so you can change them. Refresh this page — about %d tours still need a copy.', 'watphou-core' ), $seed_left ) ); ?></p></div>
		<?php endif; ?>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'Saved %d tours.', 'watphou-core' ), $saved ) ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'watphou_desk', 'watphou_desk_nonce' ); ?>
			<?php submit_button( __( 'Save all changes', 'watphou-core' ), 'primary', 'submit', false ); ?>
			<table class="widefat striped watphou-desk-table" style="margin-top:12px;">
				<thead>
					<tr>
						<th style="width:110px;"><?php esc_html_e( 'Top photo', 'watphou-core' ); ?></th>
						<th style="min-width:220px;"><?php esc_html_e( 'Bottom photos', 'watphou-core' ); ?></th>
						<th><?php esc_html_e( 'Tour', 'watphou-core' ); ?></th>
						<th style="width:140px;"><?php esc_html_e( 'From $ (USD)', 'watphou-core' ); ?></th>
						<th style="width:160px;"><?php esc_html_e( 'Duration', 'watphou-core' ); ?></th>
						<th style="width:110px;"><?php esc_html_e( 'Bestseller', 'watphou-core' ); ?></th>
						<th><?php esc_html_e( 'Full text', 'watphou-core' ); ?></th>
						<th style="width:170px;"><?php esc_html_e( 'Price table', 'watphou-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $tours as $tour ) : ?>
					<?php
					$tid     = $tour->ID;
					$price   = (string) get_post_meta( $tid, 'tour_price_from', true );
					$dur     = (string) get_post_meta( $tid, 'tour_duration', true );
					$best    = (string) get_post_meta( $tid, 'tour_bestseller', true );
					$thumb   = (int) get_post_thumbnail_id( $tid );
					$src     = $thumb ? wp_get_attachment_image_url( $thumb, 'thumbnail' ) : '';
					if ( ! $src && function_exists( 'watphou_core_tour_featured_url' ) ) {
						$src = watphou_core_tour_featured_url( $tid, 'thumbnail' );
					}
					$gids    = function_exists( 'watphou_core_tour_gallery_ids' ) ? watphou_core_tour_gallery_ids( $tid ) : array();
					$previews = function_exists( 'watphou_core_tour_gallery_preview_items' ) ? watphou_core_tour_gallery_preview_items( $tid ) : array();
					?>
					<tr>
						<td>
							<img class="watphou-desk-preview" src="<?php echo esc_url( $src ?: includes_url( 'images/media/default.png' ) ); ?>" alt="" style="width:80px;height:80px;object-fit:cover;display:block;margin-bottom:6px;background:#eee;">
							<input type="hidden" class="watphou-desk-thumb" name="tour[<?php echo (int) $tid; ?>][thumb]" value="<?php echo (int) $thumb; ?>">
							<button type="button" class="button watphou-desk-pick"><?php esc_html_e( 'Change top photo', 'watphou-core' ); ?></button>
						</td>
						<td>
							<div class="watphou-desk-gallery-preview" style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:6px;min-height:40px;">
								<?php if ( $previews ) : ?>
									<?php foreach ( $previews as $index => $shot ) : ?>
										<span class="watphou-desk-gallery-thumb">
											<img src="<?php echo esc_url( $shot['url'] ); ?>" data-large="<?php echo esc_url( $shot['large'] ?? $shot['url'] ); ?>" alt="" width="40" height="40">
											<span class="watphou-desk-gallery-num"><?php echo (int) $index + 1; ?></span>
										</span>
									<?php endforeach; ?>
								<?php else : ?>
									<em><?php esc_html_e( 'No bottom photos', 'watphou-core' ); ?></em>
								<?php endif; ?>
							</div>
							<p class="watphou-desk-gallery-count"><?php echo esc_html( watphou_core_desk_gallery_count_text( count( $previews ) ) ); ?></p>
							<input type="hidden" class="watphou-desk-gallery" name="tour[<?php echo (int) $tid; ?>][gallery]" value="<?php echo esc_attr( implode( ',', $gids ) ); ?>">
							<input type="hidden" class="watphou-desk-gallery-dirty" name="tour[<?php echo (int) $tid; ?>][gallery_dirty]" value="0">
							<button type="button" class="button watphou-desk-gallery-pick"><?php esc_html_e( 'Change bottom photos', 'watphou-core' ); ?></button>
							<button type="button" class="button watphou-desk-gallery-preview-btn"><?php esc_html_e( 'Preview on the page', 'watphou-core' ); ?></button>
							<button type="button" class="button-link watphou-desk-gallery-clear"><?php esc_html_e( 'Remove all', 'watphou-core' ); ?></button>
						</td>
						<td>
							<strong><?php echo esc_html( get_the_title( $tid ) ); ?></strong><br>
							<?php if ( 'publish' !== $tour->post_status ) : ?>
								<em><?php esc_html_e( 'Draft — not on the public website', 'watphou-core' ); ?></em><br>
							<?php endif; ?>
							<a href="<?php echo esc_url( get_permalink( $tid ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View on site', 'watphou-core' ); ?></a>
						</td>
						<td>
							<input type="text" class="regular-text" style="width:8rem;" name="tour[<?php echo (int) $tid; ?>][price]" value="<?php echo esc_attr( $price ?: 'XX' ); ?>">
						</td>
						<td>
							<input type="text" class="regular-text" style="width:10rem;" name="tour[<?php echo (int) $tid; ?>][duration]" value="<?php echo esc_attr( $dur ); ?>" placeholder="1 day">
						</td>
						<td>
							<label>
								<input type="checkbox" name="tour[<?php echo (int) $tid; ?>][bestseller]" value="1" <?php checked( in_array( $best, array( '1', 'true', true, 1 ), true ) ); ?>>
								<?php esc_html_e( 'Homepage', 'watphou-core' ); ?>
							</label>
						</td>
						<td>
							<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-tours&tour=' . (int) $tid ) ); ?>"><?php esc_html_e( 'Edit text', 'watphou-core' ); ?></a>
						</td>
						<td>
							<?php
							$ptable   = function_exists( 'watphou_core_get_price_table' ) ? watphou_core_get_price_table( $tid ) : array( 'layout' => 'auto', 'rows' => array() );
							$auto_day = function_exists( 'watphou_tour_is_day_only' ) ? watphou_tour_is_day_only( $tid ) : true;
							$plines   = count( $ptable['rows'] );
							?>
							<span class="watphou-pt-count"><?php echo esc_html( $plines ? sprintf( _n( '%d line', '%d lines', $plines, 'watphou-core' ), $plines ) : __( 'No prices yet', 'watphou-core' ) ); ?></span><br>
							<button type="button" class="button watphou-pt-toggle" aria-expanded="false" aria-controls="watphou-pt-<?php echo (int) $tid; ?>"><?php esc_html_e( 'Edit price table', 'watphou-core' ); ?></button>
						</td>
					</tr>
					<tr class="watphou-pt-row" id="watphou-pt-<?php echo (int) $tid; ?>" hidden>
						<td colspan="8">
							<div class="watphou-pt" data-auto-day="<?php echo $auto_day ? '1' : '0'; ?>" data-table="<?php echo esc_attr( (string) wp_json_encode( $ptable ) ); ?>">
								<input type="hidden" class="watphou-pt-json" name="tour[<?php echo (int) $tid; ?>][price_table]" value="">
								<input type="hidden" class="watphou-pt-dirty" name="tour[<?php echo (int) $tid; ?>][prices_dirty]" value="0">
								<p class="watphou-pt-head">
									<strong><?php echo esc_html( sprintf( __( 'Price table — %s', 'watphou-core' ), get_the_title( $tid ) ) ); ?></strong>
									<label><?php esc_html_e( 'Layout', 'watphou-core' ); ?>
										<select class="watphou-pt-layout">
											<option value="auto"><?php echo esc_html( $auto_day ? __( 'Automatic (day tour, from duration)', 'watphou-core' ) : __( 'Automatic (tour with nights, from duration)', 'watphou-core' ) ); ?></option>
											<option value="day"><?php esc_html_e( 'Day tour: one price per person', 'watphou-core' ); ?></option>
											<option value="overnight"><?php esc_html_e( 'Tour with nights: twin/double and single room', 'watphou-core' ); ?></option>
										</select>
									</label>
								</p>
								<table class="watphou-pt-grid">
									<thead><tr>
										<th><?php esc_html_e( 'Group size', 'watphou-core' ); ?></th>
										<th class="watphou-pt-col-a"></th>
										<th class="watphou-pt-col-b"><?php esc_html_e( 'Private Single Room (price per person, USD)', 'watphou-core' ); ?></th>
										<th></th>
									</tr></thead>
									<tbody></tbody>
								</table>
								<p>
									<button type="button" class="button watphou-pt-add"><?php esc_html_e( 'Add line', 'watphou-core' ); ?></button>
									<button type="button" class="button-link watphou-pt-clear"><?php esc_html_e( 'Clear table', 'watphou-core' ); ?></button>
									<span class="watphou-pt-msg" aria-live="polite"></span>
								</p>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Save all changes', 'watphou-core' ) ); ?>
		</form>
		<div id="watphou-gallery-screen" class="watphou-gallery-screen" hidden>
			<div class="watphou-gallery-screen__panel" role="dialog" aria-modal="true" aria-labelledby="watphou-gallery-screen-title">
				<button type="button" class="button watphou-gallery-screen__close"><?php esc_html_e( 'Close preview', 'watphou-core' ); ?></button>
				<h2 id="watphou-gallery-screen-title"><?php esc_html_e( 'Preview of the bottom photos', 'watphou-core' ); ?></h2>
				<p class="watphou-gallery-screen__note"><?php esc_html_e( 'This is how the grid under the itinerary will look. It is not on the website until you click Save all changes.', 'watphou-core' ); ?></p>
				<p class="watphou-gallery-screen__count"></p>
				<p class="watphou-gallery-screen__sizes">
					<button type="button" class="button button-primary" data-cols="3"><?php esc_html_e( 'Desktop', 'watphou-core' ); ?></button>
					<button type="button" class="button" data-cols="2"><?php esc_html_e( 'Tablet', 'watphou-core' ); ?></button>
					<button type="button" class="button" data-cols="1"><?php esc_html_e( 'Phone', 'watphou-core' ); ?></button>
				</p>
				<div class="watphou-gallery-screen__grid" data-cols="3"></div>
			</div>
		</div>
	</div>
	<style>
		.watphou-desk-gallery-thumb { position: relative; display: inline-block; }
		.watphou-desk-gallery-thumb img { width: 40px; height: 40px; object-fit: cover; display: block; background: #eee; }
		.watphou-desk-gallery-num { position: absolute; left: 1px; top: 1px; background: #111; color: #fff; font-size: 10px; line-height: 14px; min-width: 14px; text-align: center; border-radius: 2px; }
		.watphou-desk-gallery-count { margin: 0 0 6px; color: #646970; }
		.watphou-desk-gallery-preview-btn { margin-left: 4px; }
		.watphou-desk-gallery-clear { margin-left: 8px; }
		.watphou-gallery-screen[hidden] { display: none !important; }
		.watphou-gallery-screen { position: fixed; inset: 0; z-index: 100000; background: rgba(0,0,0,.55); display: flex; align-items: flex-start; justify-content: center; overflow: auto; padding: 32px 16px; }
		.watphou-gallery-screen__panel { background: #fff; color: #1c1c1c; width: min(1120px, 100%); border-radius: 8px; padding: 20px 20px 28px; box-shadow: 0 12px 40px rgba(0,0,0,.25); }
		.watphou-gallery-screen__note, .watphou-gallery-screen__count { margin: .4rem 0; }
		.watphou-gallery-screen__grid { display: grid; gap: .75rem; margin-top: 12px; }
		.watphou-gallery-screen__grid[data-cols="3"] { grid-template-columns: repeat(3, 1fr); }
		.watphou-gallery-screen__grid[data-cols="2"] { grid-template-columns: repeat(2, 1fr); max-width: 760px; }
		.watphou-gallery-screen__grid[data-cols="1"] { grid-template-columns: 1fr; max-width: 360px; }
		.watphou-gallery-screen__grid img { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; display: block; background: #eee; }
		.watphou-gallery-screen__grid[data-cols="1"] img { height: 220px; }
		.watphou-pt-row > td { background: #fff8f3; border-left: 4px solid #f15a24; }
		.watphou-pt-head { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
		.watphou-pt-grid { border-collapse: collapse; margin: 6px 0; background: #fff; }
		.watphou-pt-grid th { text-align: left; padding: 4px 6px; font-size: 12px; border: 1px solid #dcdcde; background: #f6f7f7; }
		.watphou-pt-grid td { padding: 0; border: 1px solid #dcdcde; }
		.watphou-pt-grid td input { border: 0; border-radius: 0; width: 150px; padding: 4px 8px; margin: 0; box-shadow: none; }
		.watphou-pt-grid td input:focus { outline: 2px solid #2271b1; outline-offset: -2px; }
		.watphou-pt-grid td input[aria-invalid="true"] { background: #fcf0f1; }
		.watphou-pt-grid td.watphou-pt-tools { border: 0; padding-left: 6px; white-space: nowrap; }
		.watphou-pt-grid.is-day .watphou-pt-col-b { display: none; }
		.watphou-pt-msg { margin-left: 10px; color: #646970; }
		.watphou-selected-bar { display: flex; gap: 8px; align-items: flex-start; padding: 8px 12px; background: #f6f7f7; border-bottom: 1px solid #dcdcde; }
		.watphou-selected-bar__label { margin: 6px 0 0; min-width: 150px; font-size: 12px; }
		.watphou-selected-bar__items { display: flex; gap: 6px; flex-wrap: wrap; }
		.watphou-selected-bar__item { position: relative; }
		.watphou-selected-bar__item img { width: 48px; height: 48px; object-fit: cover; display: block; background: #ddd; }
		.watphou-selected-bar__item button { position: absolute; top: -4px; right: -4px; width: 16px; height: 16px; border: 0; border-radius: 50%; background: #1d2327; color: #fff; line-height: 14px; cursor: pointer; padding: 0; }
	</style>
	<script>
	jQuery(function($){
		$('.watphou-desk-pick').on('click', function(e){
			e.preventDefault();
			var $row = $(this).closest('tr');
			var frame = wp.media({
				title: 'Choose top photo',
				button: { text: 'Use this photo' },
				multiple: false,
				library: { type: 'image' }
			});
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$row.find('.watphou-desk-thumb').val(att.id);
				$row.find('.watphou-desk-preview').attr('src', att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url);
			});
			frame.open();
		});
		var countLabels = {
			none: <?php echo wp_json_encode( watphou_core_desk_gallery_count_text( 0 ) ); ?>,
			one: <?php echo wp_json_encode( __( '%d photo on the tour page', 'watphou-core' ) ); ?>,
			many: <?php echo wp_json_encode( __( '%d photos on the tour page', 'watphou-core' ) ); ?>
		};
		function escAttr(value){
			return String(value == null ? '' : value).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
		}
		function countText(n){
			if (!n) { return countLabels.none; }
			return (n === 1 ? countLabels.one : countLabels.many).replace('%d', String(n));
		}
		function pictureUrl(att, large){
			var sizes = att.sizes || {};
			if (large) {
				return (sizes.medium_large && sizes.medium_large.url) || (sizes.large && sizes.large.url) || (sizes.medium && sizes.medium.url) || att.url || '';
			}
			return (sizes.thumbnail && sizes.thumbnail.url) || att.url || '';
		}
		function renderGallery($row, items){
			var html = '';
			if (!items.length) {
				html = '<em><?php echo esc_js( __( 'No bottom photos', 'watphou-core' ) ); ?></em>';
			} else {
				items.forEach(function(item, index){
					html += '<span class="watphou-desk-gallery-thumb"><img src="' + escAttr(item.url) + '" data-large="' + escAttr(item.large || item.url) + '" alt="" width="40" height="40"><span class="watphou-desk-gallery-num">' + (index + 1) + '</span></span>';
				});
			}
			$row.find('.watphou-desk-gallery-preview').html(html);
			$row.find('.watphou-desk-gallery-count').text(countText(items.length));
		}
		function rowGalleryItems($row){
			var items = [];
			$row.find('.watphou-desk-gallery-preview img').each(function(){
				items.push({
					url: this.getAttribute('src') || '',
					large: this.getAttribute('data-large') || this.getAttribute('src') || ''
				});
			});
			return items;
		}
		function openPagePreview($row){
			var items = rowGalleryItems($row);
			var $screen = $('#watphou-gallery-screen');
			var $grid = $screen.find('.watphou-gallery-screen__grid');
			var html = '';
			items.forEach(function(item){
				html += '<img src="' + escAttr(item.large) + '" alt="">';
			});
			if (!html) {
				html = '<p><?php echo esc_js( __( 'No bottom photos. The tour page will not show a picture grid.', 'watphou-core' ) ); ?></p>';
			}
			$grid.html(html).attr('data-cols', '3');
			$screen.find('.watphou-gallery-screen__count').text(countText(items.length));
			$screen.find('.watphou-gallery-screen__sizes [data-cols]').removeClass('button-primary');
			$screen.find('.watphou-gallery-screen__sizes [data-cols="3"]').addClass('button-primary');
			$screen.removeAttr('hidden');
		}
		function selectionHas(selection, id){
			var found = false;
			selection.each(function(model){
				if (parseInt(model.get('id'), 10) === id) { found = true; }
			});
			return found;
		}
		$('.watphou-desk-gallery-pick').on('click', function(e){
			e.preventDefault();
			var $row = $(this).closest('tr');
			var currentIds = String($row.find('.watphou-desk-gallery').val() || '').split(',').map(function(part){
				return parseInt(part, 10);
			}).filter(function(id){ return id > 0; });
			var frame = wp.media({
				title: 'Choose bottom photos',
				button: { text: 'Use these photos' },
				multiple: 'add',
				library: { type: 'image' }
			});
			function paintButton(){
				var selection = frame.state().get('selection');
				var n = selection.length;
				var label = n ? ('Use these ' + n + (n === 1 ? ' photo' : ' photos')) : 'Use these photos';
				frame.$('.media-toolbar-primary .media-button-select').text(label);
				renderSelectedBar(selection);
			}
			function renderSelectedBar(selection){
				var modal = frame.modal && frame.modal.$el;
				if (!modal || !modal.length) { return; }
				var bar = modal.find('.watphou-selected-bar');
				if (!bar.length) {
					modal.find('.media-frame-content').prepend('<div class="watphou-selected-bar"><p class="watphou-selected-bar__label"></p><div class="watphou-selected-bar__items"></div></div>');
					bar = modal.find('.watphou-selected-bar');
					bar.on('click', '[data-remove]', function(event){
						event.preventDefault();
						event.stopPropagation();
						userTouched = true;
						var id = parseInt(jQuery(this).attr('data-remove'), 10);
						var current = frame.state().get('selection');
						var existing = null;
						current.each(function(model){
							if (parseInt(model.get('id'), 10) === id) { existing = model; }
						});
						if (existing) { current.remove(existing); }
					});
				}
				var html = '';
				selection.each(function(model, index){
					var data = model.toJSON();
					html += '<span class="watphou-selected-bar__item"><img src="' + escAttr(pictureUrl(data, false)) + '" alt=""><button type="button" data-remove="' + parseInt(data.id, 10) + '" aria-label="Remove">&times;</button></span>';
				});
				bar.find('.watphou-selected-bar__items').html(html || '');
				bar.find('.watphou-selected-bar__label').text(selection.length ? ('Selected: ' + selection.length + '. Click a photo again, or \u00d7, to remove it.') : 'None selected. Click photos below to add them.');
			}
			function syncVisibleSelection(){
				var selection = frame.state().get('selection');
				var library = frame.state().get('library');
				if (!library) { return; }
				library.each(function(model){
					var id = parseInt(model.get('id'), 10);
					var existing = null;
					selection.each(function(item){
						if (parseInt(item.get('id'), 10) === id) { existing = item; }
					});
					if (existing && existing.cid !== model.cid) {
						var at = selection.indexOf(existing);
						selection.remove(existing, { silent: true });
						selection.add(model, { at: at });
					}
				});
			}
			var userTouched = false;
			function seedSelection(){
				var selection = frame.state().get('selection');
				currentIds.forEach(function(id){
					if (selectionHas(selection, id)) { return; }
					var model = wp.media.attachment(id);
					selection.add(model);
					if (!model.get('url')) { model.fetch(); }
				});
				paintButton();
			}
			frame.on('open', function(){
				var modalEl = frame.modal && frame.modal.el;
				if (modalEl && !modalEl._watphouGalleryToggle) {
					modalEl._watphouGalleryToggle = true;
					modalEl.addEventListener('click', function(event){
						var tile = event.target.closest('.media-frame-content .attachments .attachment');
						if (tile) { userTouched = true; }
						if (!tile) { return; }
						var id = parseInt(tile.getAttribute('data-id'), 10);
						if (!id) { return; }
						event.preventDefault();
						event.stopPropagation();
						event.stopImmediatePropagation();
						var selection = frame.state().get('selection');
						var existing = null;
						selection.each(function(model){
							if (parseInt(model.get('id'), 10) === id) { existing = model; }
						});
						if (existing) {
							selection.remove(existing);
						} else {
							var model = wp.media.attachment(id);
							if (!model.get('url')) { model.fetch(); }
							selection.add(model);
						}
						paintButton();
					}, true);
				}
				var selection = frame.state().get('selection');
				selection.off('add remove reset', paintButton);
				selection.on('add remove reset', paintButton);
				var library = frame.state().get('library');
				if (library) {
					library.off('add reset', syncVisibleSelection);
					library.on('add reset', syncVisibleSelection);
				}
				seedSelection();
				syncVisibleSelection();
				window.setTimeout(function(){
					if (userTouched) { return; }
					var selection = frame.state().get('selection');
					var idsNow = [];
					selection.each(function(model){ idsNow.push(parseInt(model.get('id'), 10)); });
					var same = idsNow.length === currentIds.length && currentIds.every(function(id, index){ return idsNow[index] === id; });
					if (same) { return; }
					selection.reset();
					seedSelection();
				}, 200);
			});
			frame.on('select', function(){
				var ids = [];
				var items = [];
				frame.state().get('selection').each(function(att){
					var data = att.toJSON();
					if (!data.id) { return; }
					ids.push(data.id);
					items.push({
						url: pictureUrl(data, false),
						large: pictureUrl(data, true)
					});
				});
				$row.find('.watphou-desk-gallery').val(ids.join(','));
				$row.find('.watphou-desk-gallery-dirty').val('1');
				renderGallery($row, items);
			});
			frame.open();
		});
		$('.watphou-desk-gallery-preview-btn').on('click', function(e){
			e.preventDefault();
			openPagePreview($(this).closest('tr'));
		});
		$('#watphou-gallery-screen').on('click', '.watphou-gallery-screen__sizes [data-cols]', function(e){
			e.preventDefault();
			var cols = String($(this).data('cols'));
			var $screen = $('#watphou-gallery-screen');
			$screen.find('.watphou-gallery-screen__grid').attr('data-cols', cols);
			$screen.find('.watphou-gallery-screen__sizes [data-cols]').removeClass('button-primary');
			$(this).addClass('button-primary');
		});
		$('#watphou-gallery-screen').on('click', function(e){
			if (e.target === this) {
				$(this).attr('hidden', 'hidden');
			}
		});
		$('.watphou-gallery-screen__close').on('click', function(e){
			e.preventDefault();
			$('#watphou-gallery-screen').attr('hidden', 'hidden');
		});
		$(document).on('keydown', function(e){
			if (e.key === 'Escape' && !$('#watphou-gallery-screen').is('[hidden]')) {
				$('#watphou-gallery-screen').attr('hidden', 'hidden');
			}
		});
		$('.watphou-desk-gallery-clear').on('click', function(e){
			e.preventDefault();
			if (!window.confirm('Remove all bottom photos from this tour page?')) {
				return;
			}
			var $row = $(this).closest('tr');
			$row.find('.watphou-desk-gallery').val('');
			$row.find('.watphou-desk-gallery-dirty').val('1');
			renderGallery($row, []);
		});
	});
	</script>
	<script>
	(function(){
		var L = <?php echo wp_json_encode(
			array(
				'priceDay'   => __( 'Price (per person, USD)', 'watphou-core' ),
				'priceTwin'  => __( 'Sharing Twin/Double Room (price per person, USD)', 'watphou-core' ),
				'remove'     => __( 'Remove line', 'watphou-core' ),
				'up'         => __( 'Move up', 'watphou-core' ),
				'down'       => __( 'Move down', 'watphou-core' ),
				'badGroup'   => __( 'Group size: type a number such as 2 or a range such as 6/8.', 'watphou-core' ),
				'badPrice'   => __( 'Price: numbers only, for example 605. Leave empty for “On request”.', 'watphou-core' ),
				'clearAsk'   => __( 'Remove every line of this price table?', 'watphou-core' ),
				'changed'    => __( 'Changed — click Save all changes.', 'watphou-core' ),
				'lines'      => __( '%d lines', 'watphou-core' ),
				'line'       => __( '%d line', 'watphou-core' ),
				'none'       => __( 'No prices yet', 'watphou-core' ),
			)
		); ?>;
		var GROUP_RE = /^\s*(\d{1,2})\s*(?:[\/\-–—]|to)?\s*(\d{1,2})?\s*(?:pax|people|persons?)?\s*\+?\s*$/i;
		var PRICE_RE = /^\d{1,6}(\.\d{1,2})?$/;
		function cleanPrice(v){
			v = String(v || '').replace(/\$|USD|\/\s*pax|pax|\s| /gi, '');
			if (/^\d{1,3}(,\d{3})+(\.\d{1,2})?$/.test(v)) { v = v.replace(/,/g, ''); }
			return v;
		}
		function rowLabel(r){ return r.min === r.max ? String(r.min) : r.min + '-' + r.max; }
		document.querySelectorAll('.watphou-pt').forEach(function(box){
			var tr = box.closest('tr');
			var mainRow = tr.previousElementSibling;
			var toggle = mainRow ? mainRow.querySelector('.watphou-pt-toggle') : null;
			var countEl = mainRow ? mainRow.querySelector('.watphou-pt-count') : null;
			var grid = box.querySelector('.watphou-pt-grid');
			var body = grid.querySelector('tbody');
			var layout = box.querySelector('.watphou-pt-layout');
			var jsonIn = box.querySelector('.watphou-pt-json');
			var dirtyIn = box.querySelector('.watphou-pt-dirty');
			var msg = box.querySelector('.watphou-pt-msg');
			var autoDay = box.getAttribute('data-auto-day') === '1';
			var data = {};
			try { data = JSON.parse(box.getAttribute('data-table') || '{}'); } catch (e) { data = {}; }
			layout.value = ['auto','day','overnight'].indexOf(data.layout) >= 0 ? data.layout : 'auto';
			function isDay(){ return layout.value === 'day' || (layout.value === 'auto' && autoDay); }
			function makeInput(cls, value){
				var td = document.createElement('td');
				var input = document.createElement('input');
				input.type = 'text';
				input.className = cls;
				input.value = value || '';
				input.setAttribute('autocomplete', 'off');
				td.appendChild(input);
				return td;
			}
			function addRow(values, after){
				values = values || ['', '', ''];
				var row = document.createElement('tr');
				row.appendChild(makeInput('watphou-pt-g', values[0]));
				row.appendChild(makeInput('watphou-pt-a', values[1]));
				var b = makeInput('watphou-pt-b', values[2]);
				b.className = 'watphou-pt-col-b';
				row.appendChild(b);
				var tools = document.createElement('td');
				tools.className = 'watphou-pt-tools';
				[['↑', L.up, 'up'], ['↓', L.down, 'down'], ['✕', L.remove, 'del']].forEach(function(spec){
					var btn = document.createElement('button');
					btn.type = 'button';
					btn.className = 'button button-small';
					btn.textContent = spec[0];
					btn.title = spec[1];
					btn.setAttribute('aria-label', spec[1]);
					btn.setAttribute('data-act', spec[2]);
					tools.appendChild(btn);
				});
				row.appendChild(tools);
				if (after && after.parentNode === body) { body.insertBefore(row, after.nextSibling); } else { body.appendChild(row); }
				return row;
			}
			function paint(){
				grid.classList.toggle('is-day', isDay());
				grid.querySelector('.watphou-pt-col-a').textContent = isDay() ? L.priceDay : L.priceTwin;
			}
			function collect(){
				var rows = [];
				var ok = true;
				body.querySelectorAll('tr').forEach(function(row){
					var g = row.querySelector('.watphou-pt-g');
					var a = row.querySelector('.watphou-pt-a');
					var b = row.querySelector('.watphou-pt-b');
					var gv = g.value.trim(), av = cleanPrice(a.value), bv = cleanPrice(b.value);
					if (!gv && !av && !bv) { [g,a,b].forEach(function(i){ i.removeAttribute('aria-invalid'); }); return; }
					var gOk = GROUP_RE.test(gv);
					var aOk = !av || PRICE_RE.test(av);
					var bOk = isDay() || !bv || PRICE_RE.test(bv);
					g.toggleAttribute('aria-invalid', !gOk);
					a.toggleAttribute('aria-invalid', !aOk);
					b.toggleAttribute('aria-invalid', !bOk);
					if (!gOk || !aOk || !bOk) { ok = false; return; }
					rows.push([gv, av, isDay() ? '' : bv]);
				});
				return { ok: ok, rows: rows };
			}
			function changed(){
				var res = collect();
				jsonIn.value = JSON.stringify({ layout: layout.value, rows: res.rows });
				dirtyIn.value = '1';
				msg.textContent = res.ok ? L.changed : (L.badGroup + ' ' + L.badPrice);
				msg.style.color = res.ok ? '' : '#b32d2e';
				if (countEl) {
					var n = res.rows.length;
					countEl.textContent = n ? (n === 1 ? L.line : L.lines).replace('%d', n) : L.none;
				}
			}
			(Array.isArray(data.rows) ? data.rows : []).forEach(function(r){
				addRow([rowLabel(r), r.a || '', r.b || '']);
			});
			if (!body.children.length) { addRow(); }
			paint();
			if (toggle) {
				toggle.addEventListener('click', function(){
					var open = tr.hidden;
					tr.hidden = !open;
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
					if (open) { var first = body.querySelector('input'); if (first) { first.focus(); } }
				});
			}
			layout.addEventListener('change', function(){ paint(); changed(); });
			box.querySelector('.watphou-pt-add').addEventListener('click', function(){
				var row = addRow();
				row.querySelector('input').focus();
				changed();
			});
			box.querySelector('.watphou-pt-clear').addEventListener('click', function(){
				if (!window.confirm(L.clearAsk)) { return; }
				body.innerHTML = '';
				addRow();
				changed();
			});
			body.addEventListener('input', changed);
			body.addEventListener('click', function(e){
				var btn = e.target.closest('button[data-act]');
				if (!btn) { return; }
				var row = btn.closest('tr');
				var act = btn.getAttribute('data-act');
				if (act === 'del') { row.remove(); if (!body.children.length) { addRow(); } }
				if (act === 'up' && row.previousElementSibling) { body.insertBefore(row, row.previousElementSibling); }
				if (act === 'down' && row.nextElementSibling) { body.insertBefore(row.nextElementSibling, row); }
				changed();
			});
			// Enter moves down a line like Excel; a new line is added at the bottom.
			body.addEventListener('keydown', function(e){
				if (e.key !== 'Enter' || !e.target.matches('input')) { return; }
				e.preventDefault();
				var row = e.target.closest('tr');
				var col = Array.prototype.indexOf.call(row.children, e.target.parentNode);
				var next = row.nextElementSibling || addRow(null, row);
				var target = next.children[col] && next.children[col].querySelector('input');
				if (target) { target.focus(); }
			});
			// Paste a block of cells copied from Excel (tabs between columns, one line per row).
			body.addEventListener('paste', function(e){
				if (!e.target.matches('input')) { return; }
				var text = (e.clipboardData || window.clipboardData).getData('text');
				if (!/[\t\n]/.test(text)) { return; }
				e.preventDefault();
				var lines = text.replace(/\r/g, '').replace(/\n+$/, '').split('\n').slice(0, 20);
				var row = e.target.closest('tr');
				var startCol = Array.prototype.indexOf.call(row.children, e.target.parentNode);
				lines.forEach(function(line, i){
					var cells = line.split('\t');
					if (i > 0) { row = row.nextElementSibling || addRow(null, row); }
					cells.forEach(function(cell, j){
						var td = row.children[startCol + j];
						var input = td && td.querySelector('input');
						if (input && startCol + j < 3) { input.value = cell.trim(); }
					});
				});
				changed();
			});
		});
		var deskForm = document.querySelector('.watphou-desk-table') && document.querySelector('.watphou-desk-table').closest('form');
		if (deskForm) {
			deskForm.addEventListener('submit', function(e){
				var bad = deskForm.querySelector('.watphou-pt [aria-invalid]');
				if (!bad) { return; }
				e.preventDefault();
				var tr = bad.closest('.watphou-pt-row');
				if (tr) { tr.hidden = false; }
				bad.focus();
				window.alert(L.badGroup + '\n' + L.badPrice);
			});
		}
	})();
	</script>
	<?php
}
