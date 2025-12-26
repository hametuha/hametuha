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

