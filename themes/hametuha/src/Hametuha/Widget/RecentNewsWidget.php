<?php
namespace Hametuha\Widget;


use WPametu\UI\Widget;

/**
 * Recent news
 *
 * @package hametuha
 */
class RecentNewsWidget extends Widget {

	public $id_base = 'recent-news-widget';

	public $title = '最新ニュース';

	public $description = '最新ニュースを表示します。';

	/**
	 * フォームを表示する
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$atts = shortcode_atts( array(
			'title' => '最新ニュース',
		), $instance );
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
		?>
		<ul class="news-list news-list__vertical">
			<?php
			$recent = new \WP_Query( [
				'post_type'      => 'news',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
			] );
			while ( $recent->have_posts() ) {
				$recent->the_post();
				get_template_part( 'parts/loop', 'news' );
			}
			wp_reset_postdata();
			?>
		</ul>
		<p class="m20">
			<a href="<?php echo get_post_type_archive_link( 'news' ); ?>" class="btn btn-default btn-block">もっと見る</a>
		</p>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}
