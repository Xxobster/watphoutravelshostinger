<?php
/**
 * Full tour manager: add, edit EN/FR/TH, preview, publish, hide, delete — no Gutenberg required.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'watphou_core_tour_manager_menu', 19 );
add_action( 'admin_init', 'watphou_core_redirect_gutenberg_tour_editor' );
add_action( 'admin_enqueue_scripts', 'watphou_core_tour_manager_assets' );

function watphou_core_tour_manager_menu(): void {
	add_submenu_page(
		'watphou-dashboard',
		__( 'Manage tours', 'watphou-core' ),
		__( 'Manage tours', 'watphou-core' ),
		'edit_tours',
		'watphou-tours',
		'watphou_core_render_tour_manager'
	);
}

function watphou_core_tour_manager_assets( string $hook ): void {
	if ( false === strpos( $hook, 'watphou-tours' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'jquery' );
}

function watphou_core_redirect_gutenberg_tour_editor(): void {
	if ( ! is_admin() || 'GET' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_tours' ) ) {
		return;
	}
	global $pagenow;
	if ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && 'tour' === $_GET['post_type'] ) {
		wp_safe_redirect( admin_url( 'admin.php?page=watphou-tours&tour=new' ) );
		exit;
	}
	if ( 'post.php' === $pagenow && isset( $_GET['post'] ) && ( ! isset( $_GET['action'] ) || 'edit' === $_GET['action'] ) ) {
		$id = (int) $_GET['post'];
		if ( $id && 'tour' === get_post_type( $id ) ) {
			$en = $id;
			if ( function_exists( 'pll_get_post' ) ) {
				$maybe = pll_get_post( $id, 'en' );
				if ( $maybe ) {
					$en = (int) $maybe;
				}
			}
			wp_safe_redirect( admin_url( 'admin.php?page=watphou-tours&tour=' . $en ) );
			exit;
		}
	}
}

/**
 * @return array<string, mixed>
 */
function watphou_core_pack_from_request( string $lang ): array {
	$raw  = isset( $_POST['pack'][ $lang ] ) && is_array( $_POST['pack'][ $lang ] ) ? wp_unslash( $_POST['pack'][ $lang ] ) : array();
	$days = array();
	if ( ! empty( $raw['days'] ) && is_array( $raw['days'] ) ) {
		$n = 1;
		foreach ( $raw['days'] as $day ) {
			if ( ! is_array( $day ) ) {
				continue;
			}
			$title = sanitize_text_field( $day['title'] ?? '' );
			$body  = sanitize_textarea_field( $day['body'] ?? '' );
			if ( '' === $title && '' === $body ) {
				continue;
			}
			$days[] = array(
				'day'   => $n,
				'title' => $title,
				'body'  => $body,
			);
			++$n;
		}
	}
	$dest = array();
	if ( ! empty( $raw['destinations'] ) && is_array( $raw['destinations'] ) ) {
		$dest = array_values( array_filter( array_map( 'sanitize_title', $raw['destinations'] ) ) );
	}
	return array(
		'title'         => sanitize_text_field( $raw['title'] ?? '' ),
		'slug'          => sanitize_title( $raw['slug'] ?? '' ),
		'headline'      => sanitize_text_field( $raw['headline'] ?? '' ),
		'duration'      => sanitize_text_field( $raw['duration'] ?? '' ),
		'duration_term' => sanitize_title( $raw['duration_term'] ?? '1-day' ) ?: '1-day',
		'price_from'    => sanitize_text_field( $raw['price_from'] ?? 'XX' ) ?: 'XX',
		'dream'         => sanitize_textarea_field( $raw['dream'] ?? '' ),
		'cta'           => sanitize_text_field( $raw['cta'] ?? '' ),
		'highlights'    => watphou_core_excel_split_list( sanitize_textarea_field( $raw['highlights'] ?? '' ) ),
		'included'      => watphou_core_excel_split_list( sanitize_textarea_field( $raw['included'] ?? '' ) ),
		'excluded'      => watphou_core_excel_split_list( sanitize_textarea_field( $raw['excluded'] ?? '' ) ),
		'upgrades'      => watphou_core_excel_split_list( sanitize_textarea_field( $raw['upgrades'] ?? '' ) ),
		'itinerary'     => $days,
		'destinations'  => $dest,
		'bestseller'    => ! empty( $raw['bestseller'] ),
		'page_type'     => 'tour',
	);
}

function watphou_core_pack_has_text( array $pack ): bool {
	return '' !== trim( (string) ( $pack['title'] ?? '' ) )
		|| '' !== trim( (string) ( $pack['dream'] ?? '' ) )
		|| '' !== trim( (string) ( $pack['headline'] ?? '' ) );
}

function watphou_core_render_tour_manager(): void {
	if ( ! current_user_can( 'edit_tours' ) ) {
		wp_die( esc_html__( 'Access denied.', 'watphou-core' ) );
	}

	$notice = '';
	$tour_q = isset( $_GET['tour'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['tour'] ) ) : '';

	if ( isset( $_GET['trashed'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'watphou_trash_done' ) ) {
		$notice = __( 'Tour moved to trash.', 'watphou-core' );
	}

	if ( isset( $_GET['action'], $_GET['id'], $_GET['_wpnonce'] ) && 'trash' === $_GET['action'] && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'watphou_trash_tour' ) ) {
		$id = (int) $_GET['id'];
		if ( $id && 'tour' === get_post_type( $id ) && current_user_can( 'delete_post', $id ) ) {
			wp_trash_post( $id );
			if ( function_exists( 'pll_get_post_translations' ) ) {
				foreach ( pll_get_post_translations( $id ) as $tid ) {
					if ( (int) $tid !== $id ) {
						wp_trash_post( (int) $tid );
					}
				}
			}
			wp_safe_redirect( admin_url( 'admin.php?page=watphou-tours&trashed=1&_wpnonce=' . wp_create_nonce( 'watphou_trash_done' ) ) );
			exit;
		}
	}

	if ( isset( $_GET['action'], $_GET['id'], $_GET['_wpnonce'] ) && 'visibility' === $_GET['action'] && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'watphou_visibility_tour' ) ) {
		$id     = (int) $_GET['id'];
		$status = ( isset( $_GET['status'] ) && 'publish' === $_GET['status'] ) ? 'publish' : 'draft';
		if ( $id && 'tour' === get_post_type( $id ) && current_user_can( 'edit_post', $id ) ) {
			$ids = array( $id );
			if ( function_exists( 'pll_get_post_translations' ) ) {
				$ids = array_map( 'intval', pll_get_post_translations( $id ) ) ?: $ids;
			}
			foreach ( $ids as $tid ) {
				if ( $tid && 'tour' === get_post_type( $tid ) ) {
					wp_update_post(
						array(
							'ID'          => $tid,
							'post_status' => $status,
						)
					);
				}
			}
			$notice = 'publish' === $status
				? __( 'Tour is now visible on the website.', 'watphou-core' )
				: __( 'Tour hidden (draft). Preview still works while you are logged in.', 'watphou-core' );
		}
	}

	if ( isset( $_POST['watphou_manager_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['watphou_manager_nonce'] ) ), 'watphou_manager' ) ) {
		$en      = watphou_core_pack_from_request( 'en' );
		$edit_id = isset( $_POST['tour_id'] ) ? (int) $_POST['tour_id'] : 0;
		$save    = sanitize_key( (string) ( $_POST['watphou_save'] ?? 'save' ) );
		if ( 'publish' === $save ) {
			$status = 'publish';
		} elseif ( 'draft' === $save ) {
			$status = 'draft';
		} elseif ( $edit_id && 'tour' === get_post_type( $edit_id ) ) {
			$status = get_post_status( $edit_id ) ?: 'draft';
			if ( 'publish' !== $status ) {
				$status = 'draft';
			}
		} else {
			$status = 'draft';
		}
		if ( '' === $en['slug'] && '' !== $en['title'] ) {
			$en['slug'] = sanitize_title( $en['title'] );
		}
		if ( $edit_id && 'tour' === get_post_type( $edit_id ) ) {
			$en['slug'] = $en['slug'] ?: (string) get_post_field( 'post_name', $edit_id );
			if ( ! current_user_can( 'edit_post', $edit_id ) ) {
				wp_die( esc_html__( 'Access denied.', 'watphou-core' ) );
			}
		}
		$en['id']                = $edit_id;
		$en['post_status']       = $status;
		$en['allow_slug_change'] = true;
		if ( '' === $en['slug'] || '' === $en['title'] ) {
			$notice = __( 'Please enter a title (and a slug for a new tour).', 'watphou-core' );
		} else {
			try {
				$result = watphou_core_upsert_tour_from_data( $en );
				$id     = (int) $result['id'];
				wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => $status,
						'post_name'   => $en['slug'] ?: get_post_field( 'post_name', $id ),
					)
				);
				$thumb = isset( $_POST['tour_thumb'] ) ? (int) $_POST['tour_thumb'] : 0;
				if ( $thumb > 0 ) {
					set_post_thumbnail( $id, $thumb );
				}
				watphou_core_store_tour_lists( $id, $en );
				if ( function_exists( 'watphou_core_tour_translation_ids' ) ) {
					foreach ( watphou_core_tour_translation_ids( $id ) as $tid ) {
						if ( function_exists( 'watphou_core_set_tour_price' ) ) {
							watphou_core_set_tour_price( $tid, (string) $en['price_from'] );
						}
						update_post_meta( $tid, 'tour_bestseller', ! empty( $en['bestseller'] ) ? '1' : '0' );
						if ( $thumb > 0 ) {
							set_post_thumbnail( $tid, $thumb );
						}
					}
				}
				foreach ( array( 'fr', 'th' ) as $lang ) {
					$pack = watphou_core_pack_from_request( $lang );
					if ( ! watphou_core_pack_has_text( $pack ) ) {
						continue;
					}
					if ( '' === $pack['title'] ) {
						$pack['title'] = $en['title'];
					}
					$pack['duration_term'] = $en['duration_term'];
					$pack['destinations']  = $en['destinations'];
					$pack['post_status']   = $status;
					watphou_core_update_translated_tour( $id, $lang, $pack );
					if ( function_exists( 'pll_get_post' ) ) {
						$tid = (int) pll_get_post( $id, $lang );
						if ( $tid ) {
							wp_update_post(
								array(
									'ID'          => $tid,
									'post_status' => $status,
								)
							);
							watphou_core_store_tour_lists( $tid, $pack );
						}
					}
				}
				$flag = 'publish' === $status ? 'published' : 'draft';
				wp_safe_redirect( admin_url( 'admin.php?page=watphou-tours&tour=' . $id . '&saved=' . $flag ) );
				exit;
			} catch ( Exception $e ) {
				$notice = $e->getMessage();
			}
		}
	}

	if ( isset( $_GET['saved'] ) ) {
		$notice = 'published' === $_GET['saved']
			? __( 'Tour saved and published. Visitors can see it now.', 'watphou-core' )
			: __( 'Tour saved as draft. Use Preview (logged in) before publishing.', 'watphou-core' );
	}

	if ( 'new' === $tour_q || ctype_digit( $tour_q ) ) {
		watphou_core_render_tour_editor( 'new' === $tour_q ? 0 : (int) $tour_q, $notice );
		return;
	}
	watphou_core_render_tour_index( $notice );
}

function watphou_core_render_tour_index( string $notice ): void {
	$query_args = array(
		'post_type'      => 'tour',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'orderby'        => 'title',
		'order'          => 'ASC',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$query_args['lang'] = pll_default_language() ?: 'en';
	}
	$tours = get_posts( $query_args );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Manage tours', 'watphou-core' ); ?>
			<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-tours&tour=new' ) ); ?>"><?php esc_html_e( 'Add new tour', 'watphou-core' ); ?></a>
		</h1>
		<p><?php esc_html_e( 'Edit English, French, and Thai text here (same fields as the Excel package sheets). Save as draft to preview while logged in, then publish when it should appear on the website. Photos can also be changed on Quick edit tours.', 'watphou-core' ); ?></p>
		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Photo', 'watphou-core' ); ?></th>
					<th><?php esc_html_e( 'Tour', 'watphou-core' ); ?></th>
					<th><?php esc_html_e( 'Duration', 'watphou-core' ); ?></th>
					<th><?php esc_html_e( 'On website', 'watphou-core' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'watphou-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $tours as $tour ) : ?>
				<?php
				$tid   = (int) $tour->ID;
				$thumb = get_the_post_thumbnail_url( $tid, 'thumbnail' );
				$edit  = admin_url( 'admin.php?page=watphou-tours&tour=' . $tid );
				$live  = get_permalink( $tid );
				$prev  = get_preview_post_link( $tid ) ?: $live;
				$trash = wp_nonce_url( admin_url( 'admin.php?page=watphou-tours&action=trash&id=' . $tid ), 'watphou_trash_tour' );
				$hide  = wp_nonce_url( admin_url( 'admin.php?page=watphou-tours&action=visibility&status=draft&id=' . $tid ), 'watphou_visibility_tour' );
				$show  = wp_nonce_url( admin_url( 'admin.php?page=watphou-tours&action=visibility&status=publish&id=' . $tid ), 'watphou_visibility_tour' );
				?>
				<tr>
					<td><?php echo $thumb ? '<img src="' . esc_url( $thumb ) . '" alt="" width="60" height="60" style="object-fit:cover;">' : '&mdash;'; ?></td>
					<td><strong><a href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( get_the_title( $tid ) ); ?></a></strong><br>
						<code><?php echo esc_html( $tour->post_name ); ?></code></td>
					<td><?php echo esc_html( (string) get_post_meta( $tid, 'tour_duration', true ) ); ?></td>
					<td><?php echo 'publish' === $tour->post_status ? esc_html__( 'Yes (published)', 'watphou-core' ) : esc_html__( 'No (draft — preview only)', 'watphou-core' ); ?></td>
					<td>
						<a class="button button-small" href="<?php echo esc_url( $edit ); ?>"><?php esc_html_e( 'Edit texts', 'watphou-core' ); ?></a>
						<a class="button button-small" href="<?php echo esc_url( $prev ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'watphou-core' ); ?></a>
						<?php if ( 'publish' === $tour->post_status ) : ?>
							<a class="button button-small" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View live', 'watphou-core' ); ?></a>
							<a class="button button-small" href="<?php echo esc_url( $hide ); ?>"><?php esc_html_e( 'Hide from website', 'watphou-core' ); ?></a>
						<?php else : ?>
							<a class="button button-small" href="<?php echo esc_url( $show ); ?>"><?php esc_html_e( 'Show on website', 'watphou-core' ); ?></a>
						<?php endif; ?>
						<a class="button button-small button-link-delete" href="<?php echo esc_url( $trash ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Move this tour to trash?', 'watphou-core' ) ); ?>');"><?php esc_html_e( 'Delete', 'watphou-core' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

function watphou_core_render_lang_fields( string $lang, array $fields, bool $show_slug ): void {
	$prefix = 'pack[' . $lang . ']';
	$days   = $fields['itinerary'] ?: array( array( 'title' => '', 'body' => '' ) );
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th><label><?php esc_html_e( 'Title', 'watphou-core' ); ?></label></th>
			<td><input class="large-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[title]" value="<?php echo esc_attr( $fields['title'] ); ?>"></td>
		</tr>
		<?php if ( $show_slug ) : ?>
		<tr>
			<th><label><?php esc_html_e( 'Slug (web address name)', 'watphou-core' ); ?></label></th>
			<td><input class="regular-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[slug]" value="<?php echo esc_attr( $fields['slug'] ); ?>">
				<p class="description"><?php esc_html_e( 'Lowercase with hyphens, for example bolaven-plateau-classic-full-day-tour. Keep it stable after publish.', 'watphou-core' ); ?></p></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th><label><?php esc_html_e( 'Headline', 'watphou-core' ); ?></label></th>
			<td><input class="large-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[headline]" value="<?php echo esc_attr( $fields['headline'] ); ?>"></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Duration label', 'watphou-core' ); ?></label></th>
			<td><input class="regular-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[duration]" value="<?php echo esc_attr( $fields['duration'] ); ?>" placeholder="1 day"></td>
		</tr>
		<?php if ( $show_slug ) : ?>
		<tr>
			<th><label><?php esc_html_e( 'Menu category', 'watphou-core' ); ?></label></th>
			<td>
				<select name="<?php echo esc_attr( $prefix ); ?>[duration_term]">
					<?php
					$opts = array(
						'1-day'   => __( 'Day Tours', 'watphou-core' ),
						'2-day'   => __( '2-Day Tours', 'watphou-core' ),
						'3-day'   => __( '3-Day Tours', 'watphou-core' ),
						'4-6-day' => __( '4-6 Day Tours', 'watphou-core' ),
					);
					foreach ( $opts as $val => $label ) {
						printf( '<option value="%s"%s>%s</option>', esc_attr( $val ), selected( $fields['duration_term'], $val, false ), esc_html( $label ) );
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Destinations', 'watphou-core' ); ?></th>
			<td>
				<?php
				$dests = array(
					'bolaven-plateau' => 'Bolaven Plateau',
					'4000-islands'    => '4000 Islands (Si Phan Don)',
					'champasak'       => 'Vat Phou & Champasak',
					'pakse'           => 'Pakse & Surroundings',
				);
				foreach ( $dests as $slug => $label ) {
					printf(
						'<label style="margin-right:1rem;"><input type="checkbox" name="%s[destinations][]" value="%s"%s> %s</label>',
						esc_attr( $prefix ),
						esc_attr( $slug ),
						checked( in_array( $slug, $fields['destinations'], true ), true, false ),
						esc_html( $label )
					);
				}
				?>
			</td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'From $ (USD)', 'watphou-core' ); ?></label></th>
			<td><input class="small-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[price_from]" value="<?php echo esc_attr( $fields['price_from'] ); ?>">
				<span class="description"><?php esc_html_e( 'Leave XX until the real price is confirmed.', 'watphou-core' ); ?></span></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Homepage bestseller', 'watphou-core' ); ?></th>
			<td><label><input type="checkbox" name="<?php echo esc_attr( $prefix ); ?>[bestseller]" value="1" <?php checked( $fields['bestseller'] ); ?>> <?php esc_html_e( 'Show in Popular Private Tours', 'watphou-core' ); ?></label></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th><label><?php esc_html_e( 'Introduction (dream paragraph)', 'watphou-core' ); ?></label></th>
			<td><textarea class="large-text" rows="5" name="<?php echo esc_attr( $prefix ); ?>[dream]"><?php echo esc_textarea( $fields['dream'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Highlights (one per line)', 'watphou-core' ); ?></label></th>
			<td><textarea class="large-text" rows="5" name="<?php echo esc_attr( $prefix ); ?>[highlights]"><?php echo esc_textarea( implode( "\n", $fields['highlights'] ) ); ?></textarea></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Itinerary days', 'watphou-core' ); ?></th>
			<td class="watphou-days" data-lang="<?php echo esc_attr( $lang ); ?>">
				<?php foreach ( $days as $i => $day ) : ?>
					<div class="watphou-day" style="margin-bottom:12px;padding:12px;background:#fff;border:1px solid #ccd0d4;">
						<p><label><?php esc_html_e( 'Day title', 'watphou-core' ); ?><br>
							<input class="large-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[days][<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $day['title'] ?? '' ); ?>"></label></p>
						<p><label><?php esc_html_e( 'Day body', 'watphou-core' ); ?><br>
							<textarea class="large-text" rows="3" name="<?php echo esc_attr( $prefix ); ?>[days][<?php echo (int) $i; ?>][body]"><?php echo esc_textarea( $day['body'] ?? '' ); ?></textarea></label></p>
					</div>
				<?php endforeach; ?>
				<button type="button" class="button watphou-add-day"><?php esc_html_e( 'Add a day', 'watphou-core' ); ?></button>
			</td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'What is included (one per line)', 'watphou-core' ); ?></label></th>
			<td><textarea class="large-text" rows="4" name="<?php echo esc_attr( $prefix ); ?>[included]"><?php echo esc_textarea( implode( "\n", $fields['included'] ) ); ?></textarea></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'What is not included (one per line)', 'watphou-core' ); ?></label></th>
			<td><textarea class="large-text" rows="3" name="<?php echo esc_attr( $prefix ); ?>[excluded]"><?php echo esc_textarea( implode( "\n", $fields['excluded'] ) ); ?></textarea></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Optional upgrades (one per line)', 'watphou-core' ); ?></label></th>
			<td><textarea class="large-text" rows="3" name="<?php echo esc_attr( $prefix ); ?>[upgrades]"><?php echo esc_textarea( implode( "\n", $fields['upgrades'] ) ); ?></textarea></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Call to action', 'watphou-core' ); ?></label></th>
			<td><input class="large-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[cta]" value="<?php echo esc_attr( $fields['cta'] ); ?>"></td>
		</tr>
	</table>
	<?php
}

function watphou_core_render_tour_editor( int $tour_id, string $notice ): void {
	$en = watphou_core_get_tour_editor_fields( $tour_id );
	$fr = $en;
	$th = $en;
	if ( $tour_id && function_exists( 'pll_get_post' ) ) {
		$fr_id = (int) pll_get_post( $tour_id, 'fr' );
		$th_id = (int) pll_get_post( $tour_id, 'th' );
		if ( $fr_id ) {
			$fr = watphou_core_get_tour_editor_fields( $fr_id );
		}
		if ( $th_id ) {
			$th = watphou_core_get_tour_editor_fields( $th_id );
		}
	}
	$thumb = (int) $en['thumb'];
	$src   = $thumb ? wp_get_attachment_image_url( $thumb, 'medium' ) : '';
	$prev   = $tour_id ? ( get_preview_post_link( $tour_id ) ?: get_permalink( $tour_id ) ) : '';
	$status = (string) ( $en['status'] ?? 'draft' );
	?>
	<div class="wrap">
		<h1><?php echo $tour_id ? esc_html__( 'Edit tour', 'watphou-core' ) : esc_html__( 'Add new tour', 'watphou-core' ); ?></h1>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=watphou-tours' ) ); ?>">&larr; <?php esc_html_e( 'All tours', 'watphou-core' ); ?></a>
			<?php if ( $prev ) : ?>
				| <a href="<?php echo esc_url( $prev ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview this tour', 'watphou-core' ); ?></a>
			<?php endif; ?>
			<?php if ( $tour_id ) : ?>
				| <strong><?php echo 'publish' === $status ? esc_html__( 'On website: yes', 'watphou-core' ) : esc_html__( 'On website: no (draft)', 'watphou-core' ); ?></strong>
			<?php endif; ?>
		</p>
		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'watphou_manager', 'watphou_manager_nonce' ); ?>
			<input type="hidden" name="tour_id" value="<?php echo (int) $tour_id; ?>">
			<p>
				<button type="submit" name="watphou_save" value="save" class="button"><?php esc_html_e( 'Save changes (keep draft/live as it is)', 'watphou-core' ); ?></button>
				<button type="submit" name="watphou_save" value="draft" class="button"><?php esc_html_e( 'Save as draft (hide + preview first)', 'watphou-core' ); ?></button>
				<button type="submit" name="watphou_save" value="publish" class="button button-primary"><?php esc_html_e( 'Save and publish on the website', 'watphou-core' ); ?></button>
			</p>
			<h2><?php esc_html_e( 'Photo', 'watphou-core' ); ?></h2>
			<p>
				<img class="watphou-mgr-preview" src="<?php echo esc_url( $src ?: includes_url( 'images/media/default.png' ) ); ?>" alt="" style="width:180px;height:120px;object-fit:cover;background:#eee;display:block;margin-bottom:8px;">
				<input type="hidden" class="watphou-mgr-thumb" name="tour_thumb" value="<?php echo (int) $thumb; ?>">
				<button type="button" class="button watphou-mgr-pick"><?php esc_html_e( 'Change photo', 'watphou-core' ); ?></button>
			</p>
			<h2 class="nav-tab-wrapper watphou-lang-tabs">
				<a href="#watphou-tab-en" class="nav-tab nav-tab-active"><?php esc_html_e( 'English', 'watphou-core' ); ?></a>
				<a href="#watphou-tab-fr" class="nav-tab"><?php esc_html_e( 'French', 'watphou-core' ); ?></a>
				<a href="#watphou-tab-th" class="nav-tab"><?php esc_html_e( 'Thai', 'watphou-core' ); ?></a>
			</h2>
			<div id="watphou-tab-en" class="watphou-tab"><?php watphou_core_render_lang_fields( 'en', $en, true ); ?></div>
			<div id="watphou-tab-fr" class="watphou-tab" style="display:none;"><?php watphou_core_render_lang_fields( 'fr', $fr, false ); ?></div>
			<div id="watphou-tab-th" class="watphou-tab" style="display:none;"><?php watphou_core_render_lang_fields( 'th', $th, false ); ?></div>
			<p>
				<button type="submit" name="watphou_save" value="save" class="button"><?php esc_html_e( 'Save changes (keep draft/live as it is)', 'watphou-core' ); ?></button>
				<button type="submit" name="watphou_save" value="draft" class="button"><?php esc_html_e( 'Save as draft (hide + preview first)', 'watphou-core' ); ?></button>
				<button type="submit" name="watphou_save" value="publish" class="button button-primary"><?php esc_html_e( 'Save and publish on the website', 'watphou-core' ); ?></button>
			</p>
		</form>
	</div>
	<script>
	jQuery(function($){
		$('.watphou-lang-tabs a').on('click', function(e){
			e.preventDefault();
			var id = $(this).attr('href');
			$('.watphou-lang-tabs a').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			$('.watphou-tab').hide();
			$(id).show();
		});
		$('.watphou-add-day').on('click', function(){
			var $box = $(this).closest('.watphou-days');
			var lang = $box.data('lang');
			var i = $box.find('.watphou-day').length;
			var html = '<div class="watphou-day" style="margin-bottom:12px;padding:12px;background:#fff;border:1px solid #ccd0d4;">' +
				'<p><label>Day title<br><input class="large-text" type="text" name="pack['+lang+'][days]['+i+'][title]" value=""></label></p>' +
				'<p><label>Day body<br><textarea class="large-text" rows="3" name="pack['+lang+'][days]['+i+'][body]"></textarea></label></p></div>';
			$(this).before(html);
		});
		var frame;
		$('.watphou-mgr-pick').on('click', function(e){
			e.preventDefault();
			frame = wp.media({ title: 'Choose tour photo', button: { text: 'Use this photo' }, multiple: false });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$('.watphou-mgr-thumb').val(att.id);
				$('.watphou-mgr-preview').attr('src', att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url);
			});
			frame.open();
		});
	});
	</script>
	<?php
}
