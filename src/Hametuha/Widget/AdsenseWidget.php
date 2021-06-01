<?php
namespace Hametuha\Widget;


use WPametu\UI\Widget;

/**
 * Kdp Widget
 *
 * @package hametuha
 */
class AdsenseWidget extends Widget {

	public $id_base = 'adsense-widget';

	public $title = 'Google Adsense';

	public $description = '広告を表示を表示します。';

	/**
	 * フォームを表示する
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$atts = shortcode_atts(
			array(
				'title' => '最新ニュース',
			),
			$instance
		);
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				タイトル<br/>
				<input name="<?php echo $this->get_field_name( 'title' ); ?>"
					   id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $atts['title'] ); ?>"/>
			</label>
		</p>
		<?php
	}

	/**
	 * コンテンツを表示する
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	protected function widget_content( array $instance = [] ) {
		ob_start();
		google_adsense( 3 );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}
