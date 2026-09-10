<?php
/**
 * Manager spreadsheet: change tour photo, price, duration, and bestseller without the page Customizer.
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

function watphou_core_render_tour_desk(): void {
	if ( ! current_user_can( 'edit_tours' ) ) {
		wp_die( esc_html__( 'Access denied.', 'watphou-core' ) );
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
			update_post_meta( $id, 'tour_price_from', $price !== '' ? $price : 'XX' );
			update_post_meta( $id, 'tour_duration', $duration );
			update_post_meta( $id, 'tour_bestseller', ! empty( $row['bestseller'] ) ? '1' : '0' );
			if ( $duration && taxonomy_exists( 'duration' ) ) {
				wp_set_object_terms( $id, watphou_core_map_duration_term( $duration ), 'duration', false );
			}
			if ( $thumb > 0 ) {
				set_post_thumbnail( $id, $thumb );
			} elseif ( isset( $row['clear_thumb'] ) ) {
				delete_post_thumbnail( $id );
			}
			++$saved;
		}
	}

	$query_args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
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
		<p><?php esc_html_e( 'Change the photo, starting price, duration, and homepage bestseller here. You do not need Appearance → Customize. Use “Edit text” only when you want to change the itinerary or long description.', 'watphou-core' ); ?></p>
		<p>
			<strong><?php esc_html_e( 'Price:', 'watphou-core' ); ?></strong>
			<?php esc_html_e( 'Type a number (for example 95) or leave XX until the real price is confirmed. The website shows “From $XX” until then.', 'watphou-core' ); ?>
		</p>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'Saved %d tours.', 'watphou-core' ), $saved ) ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'watphou_desk', 'watphou_desk_nonce' ); ?>
			<?php submit_button( __( 'Save all changes', 'watphou-core' ), 'primary', 'submit', false ); ?>
			<table class="widefat striped" style="margin-top:12px;">
				<thead>
					<tr>
						<th style="width:110px;"><?php esc_html_e( 'Photo', 'watphou-core' ); ?></th>
						<th><?php esc_html_e( 'Tour', 'watphou-core' ); ?></th>
						<th style="width:140px;"><?php esc_html_e( 'From $ (USD)', 'watphou-core' ); ?></th>
						<th style="width:160px;"><?php esc_html_e( 'Duration', 'watphou-core' ); ?></th>
						<th style="width:110px;"><?php esc_html_e( 'Bestseller', 'watphou-core' ); ?></th>
						<th><?php esc_html_e( 'Full text', 'watphou-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $tours as $tour ) : ?>
					<?php
					$tid   = $tour->ID;
					$price = (string) get_post_meta( $tid, 'tour_price_from', true );
					$dur   = (string) get_post_meta( $tid, 'tour_duration', true );
					$best  = (string) get_post_meta( $tid, 'tour_bestseller', true );
					$thumb = (int) get_post_thumbnail_id( $tid );
					$src   = $thumb ? wp_get_attachment_image_url( $thumb, 'thumbnail' ) : '';
					?>
					<tr>
						<td>
							<img class="watphou-desk-preview" src="<?php echo esc_url( $src ?: includes_url( 'images/media/default.png' ) ); ?>" alt="" style="width:80px;height:80px;object-fit:cover;display:block;margin-bottom:6px;background:#eee;">
							<input type="hidden" class="watphou-desk-thumb" name="tour[<?php echo (int) $tid; ?>][thumb]" value="<?php echo (int) $thumb; ?>">
							<button type="button" class="button watphou-desk-pick"><?php esc_html_e( 'Change photo', 'watphou-core' ); ?></button>
						</td>
						<td>
							<strong><?php echo esc_html( get_the_title( $tid ) ); ?></strong><br>
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
							<a class="button" href="<?php echo esc_url( get_edit_post_link( $tid, 'raw' ) ); ?>"><?php esc_html_e( 'Edit text', 'watphou-core' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Save all changes', 'watphou-core' ) ); ?>
		</form>
	</div>
	<script>
	jQuery(function($){
		var frame;
		$('.watphou-desk-pick').on('click', function(e){
			e.preventDefault();
			var $row = $(this).closest('tr');
			frame = wp.media({ title: 'Choose tour photo', button: { text: 'Use this photo' }, multiple: false });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$row.find('.watphou-desk-thumb').val(att.id);
				$row.find('.watphou-desk-preview').attr('src', att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url);
			});
			frame.open();
		});
	});
	</script>
	<?php
}
