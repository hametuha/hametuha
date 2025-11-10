<?php
/**
 * リストの作成・更新フォーム
 *
 * @var array{list_id: string} $args
 */

$list_id = $args['list_id'];
$post_args = [
	'title'   => '',
	'excerpt' => '',
	'status'  => 'publish',
	'option'  => '',
];
$button_label = __( '更新', 'hametuha' );
if ( 'new' !== $list_id ) {
	// 更新
	$post = get_post( $list_id );
	$args = [
		'title' => $post->post_title,
		'excerpt' => $post->post_excerpt,
		'status' => $post->post_status,
		'option' => get_post_meta( $list_id, \Hametuha\Model\Lists::META_KEY_RECOMMEND, true ) ? 'recommended' : '',
	];
}
?>
<form action="<?php echo esc_url( rest_url( 'hametuha/v1/lists/' . $list_id ) ) ?>" method="post" class="list-create-form">
	<?php wp_nonce_field( 'wp_rest' ); ?>
	<div class="mb-3">
		<label for="list-name" class="form-label">リスト名 <span class="badge text-bg-danger">必須</span></label>
		<input type="text" class="form-control" id="list-name" name="list_name" placeholder="ex. マイベスト短編" value="<?php echo esc_attr( $args['title'] ); ?>" />
	</div>
	<div class="mb-3">
		<label for="list-excerpt" class="form-label">説明文 <span class="badge text-bg-danger">必須</span></label>
		<textarea class="form-control" id="list-excerpt" name="list_excerpt" placeholder="ex. 私が一番いいと思う短編集だけ集めました。"><?php echo esc_textarea( $args['excerpt'] ) ?></textarea>
	</div>
	<div class="mb-3">
		<label for="list-status" class="form-label">公開状態</label>
		<select class="form-select" id="list-status" name="list_status">
			<?php
			foreach ( [
				'publish' => _x( '公開', 'list-form', 'hametuha' ),
				'private' => __('非公開（自分専用）', 'hametuha' ),
			] as $value => $label ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $value ),
					selected( $value, $args['status'], false ),
					esc_html( $label )
				);
			}
			?>
		</select>
	</div>
	<?php if ( current_user_can( 'edit_others_posts') ) : ?>
		<div class="mb-3">
			<label for="list-option" class="form-label">オプション <span class="badge text-bg-primary">編集者専用</span></label>
			<select class="form-select" id="list-option" name="list_option">
				<?php
				foreach ( [
					''            => __( '個人用のリストとして利用', 'hametuha' ),
					'recommended' => __( 'おすすめリスト（トップページに公開）', 'hametuha' ),
				] as $value => $label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $value ),
						selected( $value, $args['option'], false ),
						esc_html( $label )
					);
				}
				?>
			</select>
		</div>
	<?php endif; ?>
	<div class="d-grid">
		<input type="submit" class="btn btn-primary btn-lg" value="<?php echo esc_html( $button_label ); ?>" />
	</div>
</form>
