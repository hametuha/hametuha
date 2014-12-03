<?php



/**
 * チケット一覧を表示する
 * 
 * @param int $parent_id
 * @param int $index
 */
function _hametuha_lwp_ticket_table($parent_id, $index = 0){
	?>
<tr class="meta">
	<td class="no" rowspan="2">No. <?php echo number_format($index); ?></td>
	<th scope="row">
		<?php the_title(); ?>
	</th>
	<td class="price">
		<?php lwp_the_price(); ?>
	</td>
	<td class="stock">
		<?php echo number_format( ( $total = lwp_get_ticket_stock(true) ) ); ?>枚
		<?php
			$stock = lwp_get_ticket_stock();
			if( 1 > $stock){
				echo '<span style="color: red;">売り切れ</span>';
			}elseif ($stock / $total < 0.25 ){
				echo '<span style="color: orange;">在庫僅少</span>';
			}
		?>
	</td>
</tr>
<tr class="desc">
	<td colspan="2" class="ticket-detail">
		<?php the_content(); ?>
		<?php if( lwp_is_owner() ): ?>
			<br />
			<small class="lwp-ticket-owner"><?php printf('このチケットは購入済みです。<a href="%1$s">購入履歴</a>または<a href="%2$s">チケットページ</a>でご確認ください。', trailingslashit(admin_url()).'profile.php?page=lwp-history', lwp_ticket_url($parent_id)); ?></small>
		<?php endif; ?>
	</td>
	<td class="buy-ticket">
		<?php if( !lwp_is_event_available($parent_id) ): ?>
		<span style="color: grey;">販売終了</span>
		<?php elseif( $stock > 0 ):  ?>
		<a class="button" href="<?= lwp_endpoint('buy', array('lwp-id' => get_the_ID())) ?>" rel="noindex,nofollow">
			購入
		</a>
		<?php else: ?>;
		<span style="color: red;">売り切れ</span>
		<?php endif; ?>
	</td>
</tr>
	<?php
}


/**
 * 購入できる数を変更する
 * 
 * @param int $quentity
 * @param object $post
 * @return int
 */
function _hametuha_ticket_limit($quentity, $post){
	return min(lwp_get_ticket_stock(false, $post), 10);
}
add_filter('lwp_cart_available_quantity', '_hametuha_ticket_limit', 10, 3);
