<?php
/**
 * キャンペーンループ
 *
 * @feature-group campaign
 * @var WP_Term $args
 */
$campaign = $args['campaign'];
?>
<div class="widget-campaign-item">
	<a href="<?php echo get_term_link( $campaign ); ?>">
		<span class="widget-campaign-title">
			<?php echo esc_html( $campaign->name ); ?>
			<?php if ( $campaign->count ) : ?>
				<span class="badge text-bg-danger"><?php echo number_format_i18n( $campaign->count ); ?></span>
			<?php endif; ?>
		</span>
		<span class="widget-campaign-date">
			<i class="icon-calendar"></i> <?php echo mysql2date( get_option( 'date_format' ), get_term_meta( $campaign->term_id, '_campaign_limit', true ) ); ?>
			<?php if ( hametuha_is_available_campaign( $campaign ) ) : ?>
				<span class="badge text-bg-success">
					募集中
				</span>
			<?php else : ?>
				<span class="badge text-bg-secondary">
					終了
				</span>
			<?php endif; ?>
		</span>
		<div class="widget-campaign-desc">
			<?php echo esc_html( $campaign->description ); ?>
		</div>
	</a>
</div>
