<?php
/* @var array $args */
/* @var \WP_Post $idea */
$idea = $args['idea'] ?? null;
?>
<form id="recommend-idea-form" data-post-id="<?php echo esc_attr( $idea->ID ); ?>">
	<div class="form-group">
		<label for="recommend_to"><?php echo get_the_title( $idea ); ?>を薦める</label>
		<?php
		hameplate( 'parts/input', 'user', [
			'id'  => 'recommend_to',
			'max' => 1,
			'min' => 1,
		] );
		?>
		<span class="helper-block">フォローしているユーザーが表示されます。</span>
	</div>
	<div class="row">
		<div class="col-xs-6">
			<button type="button" class="btn btn-default btn-block"  data-dismiss="modal">キャンセル</button>
		</div>
		<div class="col-xs-6">
			<input type="submit" class="btn btn-primary btn-block" value="薦める" />
		</div>
	</div>
</form>
