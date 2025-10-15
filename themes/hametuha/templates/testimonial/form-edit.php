<?php
?>

<form method="put" class="testimonial-edit-form"
	action="<?php echo rest_url( 'hametuha/v1/testimonials/' . $comment->comment_ID . '/', 'https' ); ?>">
	<?php wp_nonce_field( 'manage_testimonial' ); ?>
	<?php if ( $post->ID == $comment->comment_post_ID ) : ?>
		<?php if ( ! $comment->twitter ) : ?>
			<div class="row mb-3">
				<label for="comment-author"
					class="col-sm-4 col-form-label">名前</label>

				<div class="col-sm-8">
					<input type="text" name="comment-author"
						id="comment-author"
						class="form-control"
						value="<?php echo esc_attr( $comment->comment_author ); ?>" />
				</div>
			</div>
			<div class="row mb-3">
				<label for="comment-rank"
					class="col-sm-4 col-form-label">五段階評価</label>

				<div class="col-sm-8">
					<select class="form-select" id="comment-rank"
						name="comment-rank">
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
							printf( '<option value="%1$s"%2$s>%1$s %3$s</option>', $index, selected( $index == $comment->rank, true, false ), $label );
						}
						?>
					</select>
				</div>
			</div>
		<?php endif; ?>

		<div class="row mb-3">
			<label for="comment-url"
				class="col-sm-4 col-form-label">URL</label>

			<div class="col-sm-8">
				<input type="text" name="comment-url"
					id="comment-url"
					class="form-control"
					value="<?php echo esc_attr( $comment->comment_author_url ); ?>" />
			</div>
		</div>
	<?php endif; ?>

	<div class="row mb-3">
		<label class="col-sm-4 col-form-label">公開状態</label>

		<div class="col-sm-8">
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="comment-status"
					id="comment-status-0-<?php echo $comment->comment_ID; ?>"
					value="0" <?php checked( ! $comment->display ); ?>>
				<label class="form-check-label" for="comment-status-0-<?php echo $comment->comment_ID; ?>">
					公開しない
				</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="comment-status"
					id="comment-status-1-<?php echo $comment->comment_ID; ?>"
					value="1" <?php checked( $comment->display ); ?>>
				<label class="form-check-label" for="comment-status-1-<?php echo $comment->comment_ID; ?>">
					公開する
				</label>
			</div>
		</div>
	</div>

	<div class="row mb-3">
		<label class="col-sm-4 col-form-label"
			for="comment-priority">
			優先順位
		</label>

		<div class="col-sm-8">
			<input type="number" name="comment-priority"
				id="comment-priority"
				class="form-control"
				value="<?php echo esc_attr( $comment->priority ); ?>"
				min="0">
			<?php help_tip( 'コメントは「優先順位の高さ＞日付の新しい順」で表示されます。重要なものの順位を高くしてください。' ); ?>
		</div>
	</div>

	<?php if ( ! $comment->twitter ) : ?>

		<div class="row mb-3">
			<label class="col-sm-4 col-form-label"
				for="comment-content">コメント本文</label>

			<div class="col-sm-8">
				<?php if ( 'review' === $comment->comment_type ) : ?>
					<textarea class="form-control"
						id="comment-content"
						name="comment-content"
						rows="5"><?php echo esc_textarea( $comment->comment_content ); ?></textarea>
				<?php else : ?>
					<textarea class="form-control"
						id="comment-content"
						name="comment-excerpt"
						rows="3"><?php echo esc_textarea( get_comment_meta( $comment->comment_ID, 'comment_excerpt', true ) ); ?></textarea>
					<div class="form-text">
						投稿へ付けられたコメントの一部を抜粋できます。含まれていない文字列は無効です。
						抜粋がない場合は全文が表示されます。
					</div>
					<pre><?php echo esc_html( $comment->comment_content ); ?></pre>
				<?php endif; ?>
			</div>
		</div>

	<?php endif; ?>

</form>
