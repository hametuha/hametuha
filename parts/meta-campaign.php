<?php
$campaign = get_queried_object();
$length = hametuha_campaign_length( $campaign );
$has_limit = hametuha_campaign_has_limit( $campaign->term_id );
?>

<div class="event-detail clearfix">
	<dl class="dl-horizontal">
		<?php if ( $has_limit ) : ?>
		<dt>〆切</dt>
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
			雑誌掲載作品は「非公開」を選べば表示されません。
		</dd>
	</dl>
</div>
