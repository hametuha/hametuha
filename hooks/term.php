<?php


/**
 * タグのメタボックスをカスタマイズする
 *
 * @param array $args
 * @param string $taxonomy
 *
 * @return array
 */
add_filter(
	'register_taxonomy_args',
	function ( $args, $taxonomy ) {
		if ( 'post_tag' == $taxonomy ) {
			$args['meta_box_cb'] = function ( $post ) {
				$tags = wp_cache_get( 'sub_genre', 'tags' );
				if ( false === $tags ) {
					$tags  = [];
					$terms = get_tags(
						[
							'hide_empty' => 0,
						]
					);
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
					$value = implode(
						', ',
						array_map(
							function ( $tag ) {
								return $tag->name;
							},
							$posts_tags
						)
					);
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
	},
	10,
	2
);


/**
 * コンテンツを入力するフィールドを追加
 */
foreach ( [ 'campaign', 'nouns' ] as $taxonomy ) {
	add_action(
		$taxonomy . '_edit_form',
		function ( $tag, $taxonomy ) {
			?>
		<div>
			<?php wp_nonce_field( 'update_term_content', '_term_content_nonce', false ); ?>
			<?php wp_editor( get_term_meta( $tag->term_id, '_term_content', true ), 'term_content' ); ?>
		</div>
			<?php
		},
		11,
		2
	);
}


/**
 * コンテンツを入力するフィールドを保存
 */
add_action(
	'edit_terms',
	function ( $term_id, $taxonomy ) {
		if ( isset( $_POST['_term_content_nonce'] ) && wp_verify_nonce( $_POST['_term_content_nonce'], 'update_term_content' ) ) {
			update_term_meta( $term_id, '_term_content', $_POST['term_content'] );
		}
	},
	10,
	2
);
