<?php
/** @var \Hametuha\Rest\Testimonial $this */
?>
<form id="testimonial-form"
	  action="<?= home_url( '/testimonials/add/' . $post->ID . '/', is_ssl() ? 'https' : 'http' ) ?>" method="post">
	<?php wp_nonce_field( $this->action ) ?>

	<div class="form-group">
		<label for="testimonial-source">
			名前または引用元
			<small class="label label-danger">twitter以外必須</small>
		</label>
		<input type="text" class="form-control" id="testimonial-source" name="testimonial-source"
			   value="<?= esc_attr( get_userdata( get_current_user_id() )->display_name ) ?>" placeholder="ex. 破滅太郎"/>
	</div>

	<div class="form-group">
		<label for="testimonial-url">
			URL
			<small class="text-muted">オプション</small>
		</label>
		<input type="text" class="form-control" id="testimonial-url" name="testimonial-url" value=""
			   placeholder="ex. http://example.jp/review/1234"/>
		<p class="help-block">twitterの場合はURLを入れるだけで大丈夫です。
			<a href="<?= home_url( '/faq/how-to-get-twitter-url/', 'http' ) ?>">詳しく&raquo;</a>
		</p>
	</div>

	<div class="form-group">
		<label for="testimonial-rank">
			五段階評価
			<small class="text-muted">オプション</small>
		</label>
		<select class="form-control" id="testimonial-rank" name="testimonial-rank">
			<option value="0" selected>指定しない</option>
			<option value="5">5 とても良い</option>
			<option value="4">4 良い</option>
			<option value="3">3 普通</option>
			<option value="2">2 悪い</option>
			<option value="1">1 とても悪い</option>
		</select>
	</div>


	<div class="form-group">
		<label for="testimonial-text">
			レビュー本文
			<small class="label label-danger">twitter以外必須</small>
		</label>
		<textarea rows="5" class="form-control" id="testimonial-text" name="testimonial-text"></textarea>
	</div>

	<div class="row">
		<div class="col-xs-6">
			<a class="btn btn-default" href="#" data-dismiss="modal" data-target="#hametu-modal">キャンセル</a>
		</div>
		<div class="col-xs-6 text-right">
			<input type="submit" value="送信" class="btn btn-primary btn-lg"/>
		</div>
	</div>


</form>
