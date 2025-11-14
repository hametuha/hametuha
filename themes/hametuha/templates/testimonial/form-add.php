<?php
/**
 * レビューを追加・編集するフォーム
 *
 * @feature-group series
 * @var WP_Post $post
 * @var array $args
 */
$args = wp_parse_args( $args, [
	'comment'      => null,
	'method'       => 'post',
	'layout'       => 'simple', // 'simple' or 'horizontal'
	'show_buttons' => true,
	'source'       => '',
	'text'         => '',
	'display'      => 1,
	'priority'     => 0,
	'twitter'      => false,
	'rank'         => 0,
	'url'          => '',
	'excerpt'      => '',
] );

$is_edit = false;
if ( $args['comment'] ) {
	/** @var WP_Comment $comment */
	$comment = $args['comment'];
	$id      = $comment->comment_ID;
	$is_edit = true;
	$args    = array_merge( $args, [
		'method'   => 'put',
		'source'   => $comment->comment_author,
		'text'     => $comment->comment_content,
		'display'  => $comment->comment_approved ? 1 : 0,
		'priority' => get_comment_meta( $comment->comment_ID, 'priority', true ) ?: 0,
		'twitter'  => (bool) get_comment_meta( $comment->comment_ID, 'twitter', true ),
		'rank'     => get_comment_meta( $comment->comment_ID, 'rank', true ) ?: 0,
		'url'      => $comment->comment_author_url,
		'excerpt'  => get_comment_meta( $comment->comment_ID, 'comment_excerpt', true ),
	] );
} elseif ( isset( $post ) ) {
	$id = $post->ID;
	// 新規作成時のデフォルト値
	if ( is_user_logged_in() ) {
		$args['source'] = get_userdata( get_current_user_id() )->display_name;
	}
} else {
	return;
}

// レイアウトクラス
$layout_class = $args['layout'] === 'horizontal' ? 'row mb-3' : 'mb-3';
$label_class  = $args['layout'] === 'horizontal' ? 'col-sm-4 col-form-label' : 'form-label';
$input_wrap   = $args['layout'] === 'horizontal' ? 'col-sm-8' : '';

?>
<form id="testimonial-form" class="testimonial-edit-form" data-id="<?php echo esc_attr( $id ); ?>" data-mode="<?php echo $is_edit ? 'edit' : 'add'; ?>"
		action="<?php echo rest_url( '/hametuha/v1/testimonials/' . $id . '/' ); ?>" method="<?php echo esc_attr( $args['method'] ); ?>">
	<?php wp_nonce_field( 'wp_rest' ); ?>

	<?php
	// 編集モードでかつ、自分のseriesに紐づくコメントでない場合は基本フィールドを表示しない
	$show_basic_fields = ! $is_edit || ( isset( $post ) && $post->ID == $comment->comment_post_ID );
	?>

	<?php if ( $show_basic_fields && ! $args['twitter'] ) : ?>
		<div class="<?php echo esc_attr( $layout_class ); ?>">
			<label for="testimonial-source" class="<?php echo esc_attr( $label_class ); ?>">
				名前または引用元
				<small class="badge bg-danger">X（旧twitter）以外必須</small>
			</label>
			<?php
			if ( $input_wrap ) :
				?>
				<div class="<?php echo esc_attr( $input_wrap ); ?>"><?php endif; ?>
				<input type="text" class="form-control" id="testimonial-source" name="testimonial-source"
						value="<?php echo esc_attr( $args['source'] ); ?>" placeholder="ex. 破滅太郎"/>
			<?php
			if ( $input_wrap ) :
				?>
				</div><?php endif; ?>
		</div>

		<div class="<?php echo esc_attr( $layout_class ); ?>">
			<label for="testimonial-rank" class="<?php echo esc_attr( $label_class ); ?>">
				五段階評価
				<small class="text-muted">オプション</small>
			</label>
			<?php
			if ( $input_wrap ) :
				?>
				<div class="<?php echo esc_attr( $input_wrap ); ?>"><?php endif; ?>
				<select class="form-select" id="testimonial-rank" name="testimonial-rank">
					<?php
					foreach (
						[
							'0' => '評価なし',
							'5' => 'とても良い',
							'4' => '良い',
							'3' => '普通',
							'2' => '悪い',
							'1' => 'とても悪い',
						] as $index => $label
					) {
						printf( '<option value="%1$s"%2$s>%1$s %3$s</option>', $index, selected( $args['rank'], $index, false ), $label );
					}
					?>
				</select>
			<?php
			if ( $input_wrap ) :
				?>
				</div><?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $show_basic_fields ) : ?>
		<div class="<?php echo esc_attr( $layout_class ); ?>">
			<label for="testimonial-url" class="<?php echo esc_attr( $label_class ); ?>">
				URL
				<small class="text-muted">オプション</small>
			</label>
			<?php
			if ( $input_wrap ) :
				?>
				<div class="<?php echo esc_attr( $input_wrap ); ?>"><?php endif; ?>
				<input type="text" class="form-control" id="testimonial-url" name="testimonial-url"
						value="<?php echo esc_attr( $args['url'] ); ?>"
						placeholder="ex. http://example.jp/review/1234"/>
				<?php if ( ! $is_edit ) : ?>
					<div class="form-text">twitterの場合はURLを入れるだけで大丈夫です。
						<a href="<?php echo home_url( '/faq/how-to-get-twitter-url/' ); ?>">詳しく&raquo;</a>
					</div>
				<?php endif; ?>
			<?php
			if ( $input_wrap ) :
				?>
				</div><?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $is_edit ) : ?>
		<div class="<?php echo esc_attr( $layout_class ); ?>">
			<label class="<?php echo esc_attr( $label_class ); ?>">公開状態</label>
			<?php
			if ( $input_wrap ) :
				?>
				<div class="<?php echo esc_attr( $input_wrap ); ?>"><?php endif; ?>
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="testimonial-display"
							id="testimonial-display-0-<?php echo $id; ?>"
							value="0" <?php checked( ! $args['display'] ); ?>>
					<label class="form-check-label" for="testimonial-display-0-<?php echo $id; ?>">
						公開しない
					</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="testimonial-display"
							id="testimonial-display-1-<?php echo $id; ?>"
							value="1" <?php checked( $args['display'] ); ?>>
					<label class="form-check-label" for="testimonial-display-1-<?php echo $id; ?>">
						公開する
					</label>
				</div>
			<?php
			if ( $input_wrap ) :
				?>
				</div><?php endif; ?>
		</div>

		<div class="<?php echo esc_attr( $layout_class ); ?>">
			<label class="<?php echo esc_attr( $label_class ); ?>" for="testimonial-priority">
				優先順位
			</label>
			<?php
			if ( $input_wrap ) :
				?>
				<div class="<?php echo esc_attr( $input_wrap ); ?>"><?php endif; ?>
				<input type="number" name="testimonial-priority"
						id="testimonial-priority"
						class="form-control"
						value="<?php echo esc_attr( $args['priority'] ); ?>"
						min="0">
				<?php if ( function_exists( 'help_tip' ) ) : ?>
					<?php help_tip( 'コメントは「優先順位の高さ＞日付の新しい順」で表示されます。重要なものの順位を高くしてください。' ); ?>
				<?php endif; ?>
			<?php
			if ( $input_wrap ) :
				?>
				</div><?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! $args['twitter'] ) : ?>
		<div class="<?php echo esc_attr( $layout_class ); ?>">
			<label for="testimonial-text" class="<?php echo esc_attr( $label_class ); ?>">
				レビュー本文
				<?php
				if ( ! $is_edit ) :
					?>
					<small class="badge bg-danger">twitter以外必須</small><?php endif; ?>
			</label>
			<?php
			if ( $input_wrap ) :
				?>
				<div class="<?php echo esc_attr( $input_wrap ); ?>"><?php endif; ?>
				<?php if ( $is_edit && isset( $comment ) && 'review' !== $comment->comment_type ) : ?>
					<textarea class="form-control" id="testimonial-text" name="testimonial-excerpt"
								rows="3"><?php echo esc_textarea( $args['excerpt'] ); ?></textarea>
					<div class="form-text">
						投稿へ付けられたコメントの一部を抜粋できます。含まれていない文字列は無効です。
						抜粋がない場合は全文が表示されます。
					</div>
					<pre><?php echo esc_html( $args['text'] ); ?></pre>
				<?php else : ?>
					<textarea rows="5" class="form-control" id="testimonial-text" name="testimonial-text"><?php echo esc_textarea( $args['text'] ); ?></textarea>
				<?php endif; ?>
			<?php
			if ( $input_wrap ) :
				?>
				</div><?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $args['show_buttons'] ) : ?>
		<div class="row">
			<div class="col-6">
				<a class="btn btn-secondary" href="#" data-bs-dismiss="modal" data-target="#hametu-modal">キャンセル</a>
			</div>
			<div class="col-6 text-end">
				<input type="submit" value="<?php echo $is_edit ? '更新' : '送信'; ?>" class="btn btn-primary btn-lg"/>
			</div>
		</div>
	<?php endif; ?>

</form>
