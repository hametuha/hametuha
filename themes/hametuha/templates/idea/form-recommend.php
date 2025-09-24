<?php
/**
 * アイデアの推薦フォーム
 *
 * @var array $args
 */
/* @var \WP_Post $idea */
$idea = $args['idea'] ?? null;
wp_enqueue_script( 'hametuha-components-user-picker' );
?>
<div id="ideaRecommendForm" class="collapse">
<form id="ideaRecommendForm" data-post-id="<?php echo esc_attr( get_post( $idea )->ID ); ?>" class="form-filter">
	<div class="form-group">
		<label class="form-label" for="recommend_to">「<?php echo get_the_title( $idea ); ?>」を薦める</label>
		<?php
		hameplate( 'parts/input', 'user', [
			'id'       => 'recommend_to',
			'max'      => 1,
			'min'      => 1,
			'required' => true,
		] );
		?>
		<span class="helper-block">フォローしているユーザーが表示されます。</span>
	</div>
	<div class="d-flex justify-content-between mt-3">
		<button type="button" class="btn btn-secondary"  data-dismiss="modal">キャンセル</button>
		<input type="submit" class="btn btn-primary" value="薦める" />
	</div>
</form>
</div>
