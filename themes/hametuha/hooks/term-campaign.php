<?php


/**
 * タクソノミーを登録
 */
add_action( 'init', function () {
	// 応募
	register_taxonomy( 'campaign', 'post', [
		'label'             => '公募',
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'capabilities'      => [
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_posts',
		],
		'rewrite'           => [
			'slug'       => 'campaign',
			'with_front' => false,
		],
		'meta_box_cb'       => function ( WP_Post $post ) {
			$terms = get_terms( [
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
				$post_terms     = get_the_terms( $post, 'campaign' );
				$can_select     = true;
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
									   value="0" <?php checked( empty( $post_terms ) ); ?>/>
								応募しない
							</label>
						</li>
						<?php foreach ( $term_available as $term ) : ?>
							<li>
								<label>
									<input type="radio" name="tax_input[campaign][]"
										   value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( has_term( $term->name, $term->taxonomy, $post ) ); ?>/>
									<?php echo esc_html( $term->name ); ?>
									<?php if ( $limit = get_term_meta( $term->term_id, '_campaign_limit', true ) ) : ?>
										<small><?php echo mysql2date( 'Y年n月j日（D）まで', $limit ); ?></small>
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
										   value="<?php echo esc_attr( $term->term_id ); ?>"/>
									<strong>応募済み: </strong><?php echo esc_html( $term->name ); ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="description">応募できるものはありません。</p>
				<?php endif; ?>
				<?php
			endif;
		},
	] );
} );

/**
 * カラムに募集期限を追加
 */
add_filter( 'manage_edit-campaign_columns', function ( $columns ) {
	$new_columns = [];
	foreach ( $columns as $col => $label ) {
		if ( 'posts' == $col ) {
			$new_columns['limit'] = '募集期限';
		}
		if ( 'description' !== $col ) {
			$new_columns[ $col ] = $label;
		}
	}

	return $new_columns;
} );

// 募集期限を出力
add_filter( 'manage_campaign_custom_column', function ( $return, $column, $term_id ) {
	switch ( $column ) {
		case 'limit':
			if ( ! hametuha_campaign_has_limit( $term_id ) ) {
				$return = '<span style="color:lightgrey">---</span>';
			} else {
				if ( hametuha_is_available_campaign( get_term_by( 'id', $term_id, 'campaign' ) ) ) {
					$label = '%s - <strong style="color:red;">募集中</strong>';
				} else {
					$label = '<span style="color:lightgrey">%s - 募集終了</span>';
				}
				$return = sprintf( $label, mysql2date( get_option( 'date_format' ), get_term_meta( $term_id, '_campaign_limit', true ) ) );
			}
			break;
		default:
			// Do nothing.
			break;
	}

	return $return;
}, 10, 3 );

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
				<input id="campaign_limit" name="campaign_limit" type="date" class="regular-text"
					   placeholder="YYYY-MM-DD"
					   value="<?php echo esc_attr( get_term_meta( $tag->term_id, '_campaign_limit', true ) ); ?>"/>
			</td>
		</tr>
		<tr>
			<th>
				<label for="campaign_range_end">評価〆切</label>
			</th>
			<td>
				<input id="campaign_range_end" name="campaign_range_end" type="date" class="regular-text"
					   placeholder="YYYY-MM-DD"
					   value="<?php echo esc_attr( get_term_meta( $tag->term_id, '_campaign_range_end', true ) ); ?>"/>
			</td>
		</tr>
		<tr>
			<th>
				<label for="campaign_min_length">最低文字数</label>
			</th>
			<td>
				<input id="campaign_min_length" name="campaign_min_length" type="number" class="regular-text"
					   value="<?php echo esc_attr( get_term_meta( $tag->term_id, '_campaign_min_length', true ) ); ?>"/>
			</td>
		</tr>
		<tr>
			<th>
				<label for="campaign_max_length">最大文字数</label>
			</th>
			<td>
				<input id="campaign_max_length" name="campaign_max_length" type="number" class="regular-text"
					   value="<?php echo esc_attr( get_term_meta( $tag->term_id, '_campaign_max_length', true ) ); ?>"/>
			</td>
		</tr>
		<tr>
			<th>
				<label for="campaign_detail">補足</label>
			</th>
			<td>
				<textarea rows="3" style="width: 90%;" id="campaign_detail"
						  name="campaign_detail"><?php echo esc_textarea( get_term_meta( $tag->term_id, '_campaign_detail', true ) ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th>
				<label for="campaign_url">URL</label>
			</th>
			<td>
				<input id="campaign_url" name="campaign_url" type="url" class="regular-text"
					   value="<?php echo esc_attr( get_term_meta( $tag->term_id, '_campaign_url', true ) ); ?>"/>
			</td>
		</tr>
		<tr>
			<th>
				コラボレーション
			</th>
			<td>
				<?php
				$current = get_term_meta( $tag->term_id, '_is_collaboration', true );
				foreach ( [
					'' => 'なし',
					'1' => '共同作業型キャンペーン',
				] as $value => $label ) {
					?>
					<label style="display: block; margin: 0 1em 1em 0;">
						<input type="radio" name="is_collaboration"
							   value="<?php echo esc_attr( $value ); ?>" <?php checked( $current, $value ); ?>/>
						<?php echo esc_html( $label ); ?>
					</label>
					<?php
				}
				?>
				<p class="description">
					<?php esc_html_e( '共同型プロジェクトの場合は投稿していない人もサポーターとして非公開の作品を閲覧できます。', 'hametuha' ); ?>
				</p>
			</td>
		</tr>
		<?php
	}
} );

/**
 * 応募要項を保存
 */
add_action( 'edit_terms', function ( $term_id, $taxonomy ) {
	if ( 'campaign' == $taxonomy && isset( $_POST['campaign_limit'] ) ) {
		foreach (
			[
				'campaign_limit',
				'campaign_range_end',
				'campaign_min_length',
				'campaign_max_length',
				'campaign_detail',
				'campaign_url',
				'is_collaboration'
			] as $key
		) {
			if ( isset( $_POST[ $key ] ) ) {
				update_term_meta( $term_id, '_' . $key, $_POST[ $key ] );
			}
		}
		// Clear cache
		wp_cache_delete( $term_id, 'campaign_record' );
	}
}, 10, 2 );
/**
 * レビューが更新されたらキャッシュ削除
 *
 * @param WP_Post $post
 */
add_action( 'hametuha_post_reviewed', function ( $post ) {
	if ( ( $campaigns = get_the_terms( $post, 'campaign' ) ) && ! is_wp_error( $campaigns ) ) {
		foreach ( $campaigns as $campaign ) {
			wp_cache_delete( $campaign->term_id, 'campaign_record' );
		}
	}
} );

/**
 * コメントが交信されたらキャッシュ削除
 *
 * @param int $comment_id
 * @param WP_Comment $comment
 */
add_action( 'wp_insert_comment', function ( $comment_id, $comment ) {
	if ( ( $campaigns = get_the_terms( $comment->comment_post_ID, 'campaign' ) ) && ! is_wp_error( $campaigns ) ) {
		foreach ( $campaigns as $campaign ) {
			wp_cache_delete( $campaign->term_id, 'campaign_record' );
		}
	}
}, 10, 2 );


/**
 * 合評会一覧を出力するショートコード
 */
add_shortcode( 'campaign_list', function ( $atts ) {
	$atts     = shortcode_atts( [
		'year' => hametuha_financial_year(),
	], $atts, 'campaign_list' );
	$campaign = hametuha_review_terms( $atts['year'], false );
	if ( ! $campaign ) {
		return '';
	}
	$content = '<ol class="campaign-review">';
	foreach ( $campaign as $term ) {
		$link     = get_term_link( $term );
		$label    = esc_html( $term->name );
		$desc     = nl2br( esc_html( $term->description ) );
		$count    = number_format_i18n( $term->count );
		$limit    = hametuha_is_available_campaign( $term )
			? sprintf( '<span class="label label-danger campaign-review__label">%s〆切</span>', mysql2date( get_option( 'date_format' ), get_term_meta( $term->term_id, '_campaign_limit', true ) ) )
			: '<span class="label label-default campaign-review__label">募集終了</span>';
		$content .= <<<HTML
		<li class="campaign-review__item">
			<a href="{$link}" class="campaign-review__link block-link">
				<strong class="campaign-review__title">
					{$label}
					{$limit}
				</strong>
				<p class="campaign-review__desc">
					{$desc}
				</p>
				<i class="icon-arrow-right2"></i>
			</a>
		</li>
HTML;

	}
	$content .= '</ol>';

	return $content;
} );
