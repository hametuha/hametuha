<?php
/**
 * キャンペーン用ファイル
 */

/**
 * タクソノミーを登録
 */
add_action( 'init', function () {
	// 応募
	register_taxonomy( 'campaign', 'post', array(
		'label'        => '応募',
		'hierarchical' => true,
		'public'       => false,
		'show_ui'      => true,
		'capabilities' => array(
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_posts',
		),
		'rewrite'      => array( 'slug' => 'campaign' ),
		'meta_box_cb'  => function ( WP_Post $post ) {
			$terms = get_terms( 'campaign', array( 'hide_empty' => false ) );
			if ( ! $terms ) {
				echo <<<HTML
					<p class="description">応募できるものはありません。</p>
HTML;
			} else {
				$post_terms = get_the_terms($post, 'campaign');
				?>
				<ul>
					<li>
						<label>
							<input type="radio" name="tax_input[campaign][]"
								   value="0" <?php checked( empty($post_terms) ) ?>/>
							応募しない
						</label>
					</li>
					<?php foreach ( $terms as $term ) : ?>
						<li>
							<label>
								<input type="radio" name="tax_input[campaign][]"
									   value="<?= esc_attr( $term->term_id ) ?>" <?php checked( has_term( $term->name, $term->taxonomy, $post ) ) ?>/>
								<?= esc_html( $term->name ) ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php
			}
		},
	) );
} );


