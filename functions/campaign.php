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
 * Detect if term has limit.
 *
 * @param int $term_id
 *
 * @return bool
 */
function hametuha_campaign_has_limit( $term_id ) {
	$date = get_term_meta( $term_id, '_campaign_limit', true );
	return (bool) preg_match( '#^\d{4}-\d{2}-\d{2}$#', $date );
}

/**
 * 文字数の制限を出力する
 *
 * @param int|string|WP_Term $term
 * @param string $format
 *
 * @return bool|string
 */
function hametuha_campaign_length( $term, $format = 'paper' ) {
	$term = get_term( $term, 'campaign' );
	if ( ! $term || is_wp_error( $term ) ) {
		return false;
	}
	$formatter = function( $number, $min = true ) use ( $format ) {
		switch ( $format ) {
			case 'paper':
				$return = sprintf( '%s枚', number_format( $number / 400 ) );
				break;
			default:
				$return = sprintf( '%s文字', number_format( $number ) );
				break;
		}
		return $return . ( $min ? '以上' : '以下' );
	};
	$return = '';
	if ( $min = get_term_meta( $term->term_id, '_campaign_min_length', true ) ) {
		$return .= $formatter( $min );
	}
	if ( $max = get_term_meta( $term->term_id, '_campaign_max_length', true ) ) {
		$return .= $formatter( $max, false );
	}
	if ( 'paper' == $format && $return ) {
		$return = '400字詰原稿用紙'.$return;
	}
	return $return;
}

/**
 * キャンペーンとして有効か否かを返す
 *
 * @param int $campaign_id
 * @param null|int|WP_Post $post
 *
 * @return WP_Error|true
 */
function hametuha_valid_for_campaign( $campaign_id, $post = null ) {
	$post = get_post( $post );
	$campaign = get_term_by( 'id', $campaign_id, 'campaign' );
	$error = new WP_Error();
	if ( ! $campaign ) {
		$error->add( 404, '該当するキャンペーンが存在しません。' );
		return $error;
	}
	if ( hametuha_campaign_has_limit( $campaign_id ) ) {
		if ( ( false !== array_search( $post->post_status, [ 'future', 'publish', 'private' ] ) )
		     && ! hametuha_is_available_campaign( $campaign, $post->post_date )
		) {
			$error->add( '500', '応募期限を過ぎています。' );
		}
	}
	$min = get_term_meta( $campaign_id, '_campaign_min_length' );
	if ( $min && ( mb_strlen( strip_tags( $post->post_content ) ) < $min ) ) {
		$error->add( 500, '最低応募文字数に達していません。' );
	}
	$max = get_term_meta( $campaign_id, '_campaign_max_length' );
	if ( $max && ( mb_strlen( strip_tags( $post->post_content ) ) > $max ) ) {
		$error->add( 500, '文字数が長すぎます。' );
	}
	return $error->get_error_messages() ? $error : true;
}

/**
 * タクソノミーを登録
 */
add_action( 'init', function () {
	// 応募
	register_taxonomy( 'campaign', 'post', [
		'label'             => '応募',
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
 * カラムに募集期限を追加
 */
add_filter( 'manage_edit-campaign_columns', function( $columns ) {
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
add_filter( 'manage_campaign_custom_column', function( $return, $column, $term_id ) {
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
				<input id="campaign_limit" name="campaign_limit" type="text" class="regular-text" placeholder="YYYY-MM-DD"
				       value="<?= esc_attr( get_term_meta( $tag->term_id, '_campaign_limit', true ) ) ?>"/>
			</td>
		</tr>
		<tr>
			<th>
				<label for="campaign_limit">最低文字数</label>
			</th>
			<td>
				<input id="campaign_limit" name="campaign_min_length" type="number" class="regular-text"
				       value="<?= esc_attr( get_term_meta( $tag->term_id, '_campaign_min_length', true ) ) ?>"/>
			</td>
		</tr>
		<tr>
			<th>
				<label for="campaign_limit">最大文字数</label>
			</th>
			<td>
				<input id="campaign_limit" name="campaign_max_length" type="number" class="regular-text"
				       value="<?= esc_attr( get_term_meta( $tag->term_id, '_campaign_max_length', true ) ) ?>"/>
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
		foreach ( [ 'campaign_limit', 'campaign_min_length', 'campaign_max_length' ] as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_term_meta( $term_id, '_'.$key, $_POST[ $key ] );
			}
		}
	}
}, 10, 2 );
