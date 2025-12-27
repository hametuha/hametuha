<?php


/**
 * タグのメタボックスをカスタマイズする
 *
 * @param array $args
 * @param string $taxonomy
 *
 * @return array
 */
add_filter( 'register_taxonomy_args', function ( $args, $taxonomy ) {
	if ( 'post_tag' == $taxonomy ) {
		$args['meta_box_cb'] = function ( $post ) {
			$tags = wp_cache_get( 'sub_genre', 'tags' );
			if ( false === $tags ) {
				$tags  = [];
				$terms = get_tags( [
					'hide_empty' => 0,
				] );
				foreach ( $terms as $term ) {
					if ( ! ( $meta = get_term_meta( $term->term_id, 'genre', true ) ) ) {
						continue;
					}
					if ( ! isset( $tags[ $meta ] ) ) {
						$tags[ $meta ] = [];
					}
					$tags[ $meta ][] = $term;
				}
				wp_cache_set( 'sub_genre', $tags, 'tags', 60 * 60 );
			}

			$posts_tags = get_the_tags( $post->ID );
			if ( $posts_tags ) {
				$value = implode( ', ', array_map( function ( $tag ) {
					return $tag->name;
				}, $posts_tags ) );
			} else {
				$value = '';
			}
			?>
			<input id="hametuha-tag-input" type="hidden" name="tax_input[post_tag]" value="<?php echo esc_attr( $value ); ?>"/>
			<?php foreach ( $tags as $genre => $terms ) : ?>
				<h4><?php echo esc_html( $genre ?: 'その他' ); ?></h4>
				<?php foreach ( $terms as $tag ) : ?>
					<label class="hametuha-tag-label">
						<input type="checkbox" class="hametuha-tag-cb"
								value="<?php echo esc_attr( $tag->name ); ?>" <?php checked( has_tag( $tag->term_id, $post ) ); ?>/> <?php echo esc_attr( $tag->name ); ?>
					</label>
				<?php endforeach; ?>
				<?php
			endforeach;
			?>
			<p class="description">
				欲しいジャンルがない場合は<a href="<?php echo home_url( '/topic/feature-request/' ); ?>">掲示板</a>で要望を出してください。
			</p>
			<hr />
			<label>
				<textarea class="hametuha-tag-extra" rows="3" placeholder="タグ1, タグ2"></textarea>
				<span>その他のタグはカンマ(,)区切りで入力してください</span>
			</label>
			<?php
		};
	}

	return $args;
}, 10, 2 );


/**
 * コンテンツを入力するフィールドを追加
 */
foreach ( [ 'campaign', 'nouns' ] as $taxonomy ) {
	add_action( $taxonomy . '_edit_form', function ( $tag, $taxonomy ) {
		?>
		<div>
			<?php wp_nonce_field( 'update_term_content', '_term_content_nonce', false ); ?>
			<?php wp_editor( get_term_meta( $tag->term_id, '_term_content', true ), 'term_content' ); ?>
		</div>
		<?php
	}, 11, 2 );
}

/**
 * コンテンツを入力するフィールドを保存
 */
add_action( 'edit_terms', function ( $term_id, $taxonomy ) {
	if ( isset( $_POST['_term_content_nonce'] ) && wp_verify_nonce( $_POST['_term_content_nonce'], 'update_term_content' ) ) {
		update_term_meta( $term_id, '_term_content', $_POST['term_content'] );
	}
}, 10, 2 );


/**
 * タグのタームメタを設定する
 *
 * @param stdClass $term
 * @param string $taxonomy
 */
add_action( 'post_tag_edit_form_fields', function ( $term ) {
	$genre = get_term_meta( $term->term_id, 'genre', true );
	?>
	<tr>
		<th><label for="tag-genre">タグの種別</label></th>
		<td>
			<select name="tag_genre" id="tag-genre">
				<option value="" <?php selected( ! $genre ); ?>>指定なし</option>
				<?php
				foreach ( hametuha_tag_types() as $val ) :
					?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val == $genre ); ?>>
						<?php echo esc_html( $val ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="tag-type">オプション</label></th>
		<td>
			<?php
			// todo: これがなんのためにあるのかわからない
			?>
			<script>
				jQuery(document).ready(function ($) {
					$('#my-color').wpColorPicker();
				});
			</script>
			<?php wp_nonce_field( 'edit_tag_meta', '_tagmetanonce', false ); ?>
		</td>
	</tr>
	<?php
}, 10, 2 );

/**
 * Save term meta
 *
 * @param int $term_id
 * @param string $taxonomy
 */
add_action( 'edited_terms', function ( $term_id, $taxonomy ) {
	// Check and verify nonce.
	if ( 'post_tag' == $taxonomy && isset( $_POST['_tagmetanonce'] ) && wp_verify_nonce( $_POST['_tagmetanonce'], 'edit_tag_meta' ) ) {
		// Save term meta
		update_term_meta( $term_id, 'tag_type', $_POST['tag_type'] );
		update_term_meta( $term_id, 'genre', $_POST['tag_genre'] );
		wp_cache_delete( 'tag_genre', 'tags' );
	}
}, 10, 2 );

/**
 * タグ一覧にジャンルカラムを追加
 *
 * @param array $columns カラム配列
 * @return array
 */
add_filter( 'manage_edit-post_tag_columns', function ( $columns ) {
	$new_columns = [];
	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;
		// nameの後にジャンルカラムを挿入
		if ( 'name' === $key ) {
			$new_columns['genre'] = 'ジャンル';
		}
	}
	return $new_columns;
} );

/**
 * タグ一覧のジャンルカラムに値を表示
 *
 * @param string $content     カラムの内容
 * @param string $column_name カラム名
 * @param int    $term_id     タームID
 * @return string
 */
add_filter( 'manage_post_tag_custom_column', function ( $content, $column_name, $term_id ) {
	if ( 'genre' === $column_name ) {
		$genre = get_term_meta( $term_id, 'genre', true );
		if ( $genre ) {
			$content = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( 'genre', rawurlencode( $genre ), admin_url( 'edit-tags.php?taxonomy=post_tag' ) ) ),
				esc_html( $genre )
			);
		} else {
			$content = '<span style="color: #999;">—</span>';
		}
	}
	return $content;
}, 10, 3 );

/**
 * タグ一覧のジャンル絞り込みクエリを実行
 *
 * @param array $args get_termsの引数
 * @param array $taxonomies タクソノミー配列
 * @return array
 */
add_filter( 'get_terms_args', function ( $args, $taxonomies ) {
	// 管理画面のタグ一覧でのみ動作
	if ( ! is_admin() ) {
		return $args;
	}
	global $pagenow;
	if ( 'edit-tags.php' !== $pagenow ) {
		return $args;
	}
	if ( ! in_array( 'post_tag', (array) $taxonomies, true ) ) {
		return $args;
	}
	if ( ! isset( $_GET['genre'] ) || '' === $_GET['genre'] ) {
		return $args;
	}

	$genre = sanitize_text_field( $_GET['genre'] );

	if ( '_none' === $genre ) {
		// ジャンル未設定のタグを取得
		$args['meta_query'] = [
			'relation' => 'OR',
			[
				'key'     => 'genre',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'   => 'genre',
				'value' => '',
			],
		];
	} else {
		// 指定ジャンルのタグを取得
		$args['meta_query'] = [
			[
				'key'   => 'genre',
				'value' => $genre,
			],
		];
	}

	return $args;
}, 10, 2 );

/**
 * タグ一覧にカウント絞り込みUIを追加
 */
add_action( 'admin_footer-edit-tags.php', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'post_tag' !== $screen->taxonomy ) {
		return;
	}

	$comparisons = [
		''     => '件数で絞り込み',
		'ceq'  => '件数 =',
		'clt'  => '件数 <',
		'clte' => '件数 ≤',
		'cgt'  => '件数 >',
		'cgte' => '件数 ≥',
	];

	// 現在の選択状態を取得
	$current_comparison = '';
	$current_value      = '';
	foreach ( array_keys( $comparisons ) as $key ) {
		if ( $key && isset( $_GET[ $key ] ) && is_numeric( $_GET[ $key ] ) ) {
			$current_comparison = $key;
			$current_value      = intval( $_GET[ $key ] );
			break;
		}
	}

	$options_html = '';
	foreach ( $comparisons as $key => $label ) {
		$selected      = ( $key === $current_comparison ) ? ' selected' : '';
		$options_html .= sprintf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $key ),
			$selected,
			esc_html( $label )
		);
	}
	// 絞り込み用のパラメータを除いたベースURL
	$count_params = [ 'clt', 'clte', 'cgt', 'cgte', 'ceq' ];
	$base_params  = $_GET;
	foreach ( $count_params as $param ) {
		unset( $base_params[ $param ] );
	}
	$base_url = add_query_arg( $base_params, admin_url( 'edit-tags.php' ) );
	?>
	<script>
	(function() {
		const bulkActions = document.querySelector('.tablenav.top .bulkactions');
		if (!bulkActions) return;

		// UIを作成
		const wrapper = document.createElement('div');
		wrapper.className = 'alignleft actions';
		wrapper.innerHTML = `
			<select name="count_comparison" id="count-comparison-select">
				<?php echo $options_html; ?>
			</select>
			<input type="number" id="count-value-input"
				   value="<?php echo esc_attr( $current_value ); ?>"
				   min="0" step="1" style="width: 80px;"
				   placeholder="件数" />
			<button type="button" id="count-filter-button" class="button">絞り込み</button>
		`;

		// bulkactionsの後に挿入
		bulkActions.parentNode.insertBefore(wrapper, bulkActions.nextSibling);

		// 絞り込みボタンのクリックイベント
		const filterButton = document.getElementById('count-filter-button');
		filterButton.addEventListener('click', function() {
			const comparison = document.getElementById('count-comparison-select');
			const value = document.getElementById('count-value-input');

			if (!comparison || !value) return;

			// ベースURL
			let url = <?php echo wp_json_encode( $base_url ); ?>;

			// 比較演算子が選択されていて値がある場合、パラメータを追加
			if (comparison.value && value.value !== '') {
				const separator = url.includes('?') ? '&' : '?';
				url += separator + encodeURIComponent(comparison.value) + '=' + encodeURIComponent(value.value);
			}

			// ページ遷移
			window.location.href = url;
		});
	})();
	</script>
	<?php
} );

/**
 * 管理画面のターム一覧で$_GETパラメータをクエリ引数に変換
 *
 * @param array $args       クエリ引数
 * @param array $taxonomies タクソノミー配列
 * @return array
 */
add_filter( 'get_terms_args', function ( $args, $taxonomies ) {
	// 管理画面のターム一覧でのみ動作
	if ( ! is_admin() ) {
		return $args;
	}
	global $pagenow;
	if ( 'edit-tags.php' !== $pagenow ) {
		return $args;
	}

	// カウント絞り込みパラメータを$argsに変換
	$count_params = [ 'clt', 'clte', 'cgt', 'cgte', 'ceq' ];
	foreach ( $count_params as $param ) {
		if ( isset( $_GET[ $param ] ) && is_numeric( $_GET[ $param ] ) ) {
			$args[ $param ] = intval( $_GET[ $param ] );
		}
	}

	return $args;
}, 10, 2 );

/**
 * タームのカウント絞り込みクエリを実行
 *
 * $argsにclt, clte, cgt, cgte, ceqが設定されている場合にWHERE句を追加。
 * new WP_Term_Query(['ceq' => 0]) のような使い方も可能。
 *
 * - clt:  count less than
 * - clte: count less than or equal
 * - cgt:  count greater than
 * - cgte: count greater than or equal
 * - ceq:  count equal
 *
 * @param array $clauses    SQL句の配列
 * @param array $taxonomies タクソノミー配列
 * @param array $args       クエリ引数
 * @return array
 */
add_filter( 'terms_clauses', function ( $clauses, $taxonomies, $args ) {
	$conditions = [];

	// clt: count less than
	if ( isset( $args['clt'] ) && is_numeric( $args['clt'] ) ) {
		$conditions[] = sprintf( 'tt.count < %d', intval( $args['clt'] ) );
	}

	// clte: count less than or equal
	if ( isset( $args['clte'] ) && is_numeric( $args['clte'] ) ) {
		$conditions[] = sprintf( 'tt.count <= %d', intval( $args['clte'] ) );
	}

	// cgt: count greater than
	if ( isset( $args['cgt'] ) && is_numeric( $args['cgt'] ) ) {
		$conditions[] = sprintf( 'tt.count > %d', intval( $args['cgt'] ) );
	}

	// cgte: count greater than or equal
	if ( isset( $args['cgte'] ) && is_numeric( $args['cgte'] ) ) {
		$conditions[] = sprintf( 'tt.count >= %d', intval( $args['cgte'] ) );
	}

	// ceq: count equal
	if ( isset( $args['ceq'] ) && is_numeric( $args['ceq'] ) ) {
		$conditions[] = sprintf( 'tt.count = %d', intval( $args['ceq'] ) );
	}

	// 条件があれば WHERE 句に追加
	if ( ! empty( $conditions ) ) {
		$clauses['where'] .= ' AND ' . implode( ' AND ', $conditions );
	}

	return $clauses;
}, 10, 3 );

