<?php
/**
 * キャンペーンへの応募作品がなかったときに表示されるテンプレート
 */
$available = hametuha_is_available_campaign( get_queried_object() );

if ( $available ) {
	$message = esc_html__( 'まだ応募作品がありません。ぜひあなたの作品を投稿して、この公募を盛り上げてください！', 'hametuha' );
} else {
	$message = esc_html__( '応募がないまま終了してしまいました……。このような悲しみを繰り返さぬよう、次回は必ず応募してください。', 'hametuha' );
}
?>
<div class="nocontents-found well" style="margin-top: 40px;">
	<p class="lead">
		<?php echo $message; ?>
	</p>
</div>
