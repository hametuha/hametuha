<?php
/* @var int $id */
/* @var int $max */
/* @var int $min */
if ( ! isset( $max ) ) {
	$max = 1;
}
if ( isset( $min ) ) {
	$min = 1;
}
?>
<div class="user-picker" data-max="<?= esc_attr( $max ) ?>" data-min="<?= esc_attr( $min ) ?>" data-target="#<?= esc_attr( $id ) ?>">
	<input type="text" class="form-control user-picker__input" placeholder="名前を入力して選択してください"/>
	<ul class="user-picker__placeholder">
	</ul>
	<div class="user-picker__loader text-center"></div>
	<input type="hidden" name="<?= esc_attr( $id ) ?>" id="<?= esc_attr( $id ) ?>"/>
	<script type="text/x-jsrender" id="<?= esc_attr($id) ?>-template">
		<li class="user-picker__item" data-user-id="{{:ID}}">
			<a class="user-picker__link" href="#" data-user-id="{{:ID}}">
				<img src="{{:avatar}}"> {{:display_name}} <i class="icon-close"></i>
			</a>
		</li>
	</script>
</div><!-- //.user-picker -->
