<?php
namespace Hametuha\Widget;


use WPametu\UI\Widget;

/**
 * Campaign Widget
 * @package hametuha
 */
class CampaignWidget extends Widget {

	public $id_base = 'campaing-list-widget';

	public $title = '絶賛募集中！';

	public $description = '最新のキャンペーンを5件表示します。';

	/**
	 * コンテンツを表示する
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	protected function widget_content( array $instance = [] ) {
		extract( $instance );
		/** @var string $number */
		ob_start();
		$campaigns = hametuha_recent_campaigns( $number );
		if ( $campaigns ) :
		?>
		<div class="widget-campaign-lead text-center">
			破滅派のイベント・キャンペーン
		</div>
		<div class="widget-campaign-list">
			<?php foreach ( $campaigns as $campaign ) : ?>
				<div class="widget-campaign-item">
					<a href="<?= get_term_link( $campaign ) ?>">
						<span class="widget-campaign-title">
							<?= esc_html( $campaign->name ) ?>
							<?php if ( $campaign->count ) : ?>
								<span class="badge"><?= number_format_i18n( $campaign->count ) ?></span>
							<?php endif; ?>
						</span>
						<span class="widget-campaign-date">
							<i class="icon-calendar"></i> <?= mysql2date( get_option( 'date_format' ), get_term_meta( $campaign->term_id, '_campaign_limit', true ) ) ?>
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
							<?= esc_html( $campaign->description ) ?>
						</div>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<?php else : ?>
			<div class="widget-campaign-lead text-center">
				現在募集はありません。
			</div>
		<?php endif;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function form( $instance ) {
		$atts = shortcode_atts( array(
			'title'          => '絶賛募集中！',
			'number'         => 5,
		), $instance );
		extract( $atts );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				タイトル<br/>
				<input name="<?php echo $this->get_field_name( 'title' ); ?>"
					   id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">
				件数<br/>
				<input name="<?php echo $this->get_field_name( 'number' ); ?>"
					   id="<?php echo $this->get_field_id( 'number' ); ?>" value="<?php echo (int) $number; ?>"/>
			</label>
		</p>
		<?php
	}

}
