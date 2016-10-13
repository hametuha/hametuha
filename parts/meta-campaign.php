<?php
$campaign = get_queried_object();
$length = hametuha_campaign_length( $campaign );
$has_limit = hametuha_campaign_has_limit( $campaign->term_id );
$url = get_term_meta( $campaign->term_id, '_campaign_url', true );
$desc = get_term_meta( $campaign->term_id, '_campaign_detail', true );
?>

<div class="event-detail clearfix">
	<dl class="dl-horizontal">
		<?php if ( $has_limit ) : ?>
		<dt>応募〆切</dt>
		<dd>
			<?= mysql2date( get_option( 'date_format' ), get_term_meta( $campaign->term_id, '_campaign_limit', true ) ) ?>
			<?php if ( hametuha_is_available_campaign( $campaign ) ) : ?>
				<span class="label label-danger">募集中！</span>
			<?php else : ?>
				<span class="label label-default">募集終了</span>
			<?php endif; ?>
		</dd>
		<?php endif; ?>
		<?php if ( $length ) : ?>
		<dt>長さ</dt>
		<dd>
			<?= $length ?>
		</dd>
		<?php endif; ?>
		<dt>応募方法</dt>
		<dd>
			応募タグ <strong><?= esc_html( $campaign->name ) ?></strong> をつけて、所定の期日までに作品を投稿してください。
			雑誌掲載作品は「非公開」を選べば表示されません。（<a href="<?= home_url( 'faq/how-to-participate-campaign' ) ?>">詳しく</a>）
		</dd>
		<?php ?>

		<?php ?>
		<dt>備考</dt>
		<dd>
			<?=  $desc ? nl2br( strip_tags( $desc, '<strong>' ) ) : '---'; ?>
		</dd>
		<dt>関連リンク</dt>
		<dd>
			<?=  $url ? sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( explode( '/', preg_replace( '#https?://#', '', $url ) )[0] ) ) : '---'; ?>
		</dd>
	</dl>
</div>
