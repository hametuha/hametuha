<?php
/**
 * キャンペーンループ
 *
 * @var WP_Term $args
 */
$campaign = $args['campaign'];
?>
<div class="widget-campaign-item">
	<a href="<?php echo get_term_link( $campaign ); ?>">
		<span class="widget-campaign-title">
			<?php echo esc_html( $campaign->name ); ?>
			<?php if ( $campaign->count ) : ?>
				<span class="badge"><?php echo number_format_i18n( $campaign->count ); ?></span>
			<?php endif; ?>
		</span>
		<span class="widget-campaign-date">
							<i class="icon-calendar"></i> <?php echo mysql2date( get_option( 'date_format' ), get_term_meta( $campaign->term_id, '_campaign_limit', true ) ); ?>
			<?php if ( hametuha_is_available_campaign( $campaign ) ) : ?>
				<span class="label label-success">
								募集中
							</span>
			<?php else : ?>
				<span class="label label-default">
								終了
							</span>
			<?php endif; ?>
						</span>
		<div class="widget-campaign-desc">
			<?php echo esc_html( $campaign->description ); ?>
		</div>
	</a>
</div>
