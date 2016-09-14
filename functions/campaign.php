<?php
/**
 * キャンペーン用ファイル
 */

/**
 * キャンペーンが応募中か
 *
 * @param WP_Term $term
 * @param string $when
 *
 * @return bool
 */
function hametuha_is_available_campaign( $term, $when = 'now' ) {
	$tz   = new DateTimeZone( 'Asia/Tokyo' );
	$time = new DateTime( $when, $tz );
	if ( ! $limit = get_term_meta( $term->term_id, '_campaign_limit', true ) ) {
		return true;
	}
	$limit = new DateTime( $limit . ' 23:59:59', $tz );

	return ( $limit >= $time );
}

/**
 * タクソノミーを登録
 */
add_action( 'init', function () {
	// 応募
	register_taxonomy( 'campaign', 'post', [
		'label'             => '応募',
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'capabilities'      => [
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_posts',
		],
		'rewrite'           => [ 'slug' => 'campaign' ],
		'meta_box_cb'       => function ( WP_Post $post ) {
			$terms          = get_terms( [
				'taxonomy'   => 'campaign',
				'hide_empty' => false,
			] );
			if ( ! $terms || is_wp_error( $terms ) ) :
				?>
				<p class="description">応募できるものはありません。</p>
				<?php
			else :
				$term_available = array_filter( $terms, function ( $term ) {
					return hametuha_is_available_campaign( $term );
				} );
				$post_terms = get_the_terms( $post, 'campaign' );
				$can_select = true;
				if ( $post_terms && ! is_wp_error( $post_terms ) ) {
					foreach ( $post_terms as $term ) {
						if ( ! hametuha_is_available_campaign( $term ) ) {
							$can_select = false;
						}
					}
				}
				// 選択できるのでラジオボタン
				if ( $can_select ) :
					?>
					<ul>
						<li>
							<label>
								<input type="radio" name="tax_input[campaign][]"
								       value="0" <?php checked( empty( $post_terms ) ) ?>/>
								応募しない
							</label>
						</li>
						<?php foreach ( $term_available as $term ) : ?>
							<li>
								<label>
									<input type="radio" name="tax_input[campaign][]"
									       value="<?= esc_attr( $term->term_id ) ?>" <?php checked( has_term( $term->name, $term->taxonomy, $post ) ) ?>/>
									<?= esc_html( $term->name ) ?>
									<?php if ( $limit = get_term_meta( $term->term_id, '_campaign_limit', true ) ) : ?>
										<small><?= mysql2date( 'Y年n月j日（D）まで', $limit ) ?></small>
									<?php else : ?>
										<small>期限なし</small>
									<?php endif; ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( $post_terms ) : /* 応募済みで変更不可 */ ?>
					<ul>
						<?php foreach ( $post_terms as $term ) : ?>
							<li>
								<label>
									<input type="hidden" name="tax_input[campaign][]"
									       value="<?= esc_attr( $term->term_id ) ?>"/>
									<strong>応募済み: </strong><?= esc_html( $term->name ) ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="description">応募できるものはありません。</p>
				<?php endif; ?>
			<?php endif;
		},
	] );
} );

/**
 * 日付を入力するフィールドを追加
 */
add_action( 'edit_tag_form_fields', function ( $tag ) {
	if ( 'campaign' == $tag->taxonomy ) {
		?>
		<tr>
			<th>
				<label for="campaign_limit">応募期限</label>
			</th>
			<td>
				<input id="campaign_limit" name="campaign_limit" type="text" class="regular-text"
				       value="<?= esc_attr( get_term_meta( $tag->term_id, '_campaign_limit', true ) ) ?>"/>
			</td>
		</tr>
		<?php
	}
} );

/**
 * 期限を保存
 */
add_action( 'edit_terms', function ( $term_id, $taxonomy ) {
	if ( 'campaign' == $taxonomy && isset( $_POST['campaign_limit'] ) ) {
		update_term_meta( $term_id, '_campaign_limit', $_POST['campaign_limit'] );
	}
}, 10, 2 );
