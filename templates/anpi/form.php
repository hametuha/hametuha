<form id="new-tweet-form">
	<?php if ( $id ) : ?>
		<input type="hidden" name="post_id" id="new-tweet-id" value="<?php echo esc_attr( $id ); ?>"/>
	<?php endif; ?>
	<div class="form-group">
		<textarea rows="5" class="form-control"
				  placeholder="なにがしか訴えたいことを書いてください。"
				  name="new-anpi-content" id="new-anpi-content"><?php echo esc_textarea( $content ); ?></textarea>
		<?php if ( current_user_can( 'edit_posts' ) ) : ?>
		<p class="help-block">
			長々と書きたいことがある同人は
			<a href="<?php echo wp_nonce_url( home_url( '/anpi/mine/new', 'https' ), 'my-anpi' ); ?>">こちら</a>
			からたくさん書けます。
		</p>
		<?php endif; ?>
	</div>
	<div class="form-group">
		<label for="recommend_to">通知を飛ばす</label>
		<?php
		hameplate(
			'parts/input',
			'user',
			[
				'id'  => 'mention',
				'max' => 10,
				'min' => 0,
			]
		);
		?>
	</div>
	<div class="row">
		<div class="col-xs-6">
			<button type="button" class="btn btn-default btn-block" data-dismiss="modal">キャンセル</button>
		</div>
		<div class="col-xs-6">
			<input type="submit" class="btn btn-primary btn-block" value="<?php echo $id ? '更新' : '報告'; ?>"/>
		</div>
	</div>
</form>
