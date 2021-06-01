<?php
namespace Hametuha\Widget;


use Hametuha\Model\Series;
use WPametu\UI\Widget;

/**
 * Kdp Widget
 *
 * @package hametuha
 */
class KdpWidget extends Widget {

	public $id_base = 'kdp-list-widget';

	public $title = 'KDPカルーセル';

	public $description = 'KDPの書籍を表示します。';

	/**
	 * コンテンツを表示する
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	protected function widget_content( array $instance = [] ) {
		$series = Series::get_instance();
		extract( $instance );
		/** @var string $number */
		/** @var string $desc */
		ob_start();
		$query = new \WP_Query(
			[
				'post_type'      => 'series',
				'post_status'    => 'publish',
				'meta_query'     => [
					[
						'key'   => '_kdp_status',
						'value' => 2,
					],
				],
				'posts_per_page' => $number,
				'orderby'        => [
					'menu_order' => 'DESC',
					'date'       => 'DESC',
				],
			]
		);
		?>
		<div class="widget-kdp-lead">
			<?php echo esc_html( $desc ); ?>
		</div>
		<div class="widget-kdp-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				?>
				<div class="widget-kdp-item">
					<a class="widget-kdp-link" href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'medium', [ 'alt' => get_the_title() ] ); ?>
					</a>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<a href="<?php echo home_url( '/kdp/' ); ?>" class="btn btn-amazon btn-block">
			<i class="icon-amazon"></i> 電子書籍一覧
		</a>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function form( $instance ) {
		$atts = shortcode_atts(
			array(
				'title'  => '破滅派@KDP',
				'number' => 10,
				'desc'   => '',
			),
			$instance
		);
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
		<p>
			<label for="<?php echo $this->get_field_id( 'desc' ); ?>">
				説明<br/>
				<textarea name="<?php echo $this->get_field_name( 'desc' ); ?>"
						  id="<?php echo $this->get_field_id( 'desc' ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
			</label>
		</p>
		<?php
	}

}
