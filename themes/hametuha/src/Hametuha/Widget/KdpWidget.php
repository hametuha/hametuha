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
		?>
		<div class="widget-kdp-lead">
			<?php echo esc_html( $desc ); ?>
		</div>
		<?php
		$key   = 'kdp_widget_' . $number;
		$cache = wp_cache_get( $key, 'kdp_widget' );
		if ( false !== $cache ) {
			$posts = $cache;
		} else {
			$query = new \WP_Query( [
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
			] );
			$posts = $query->posts;
			wp_cache_set( $key, $posts, 'kdp_widget', 60 * 30 );
		}
		?>
		<div class="widget-kdp-list">
			<?php foreach ( $posts as $post ) : ?>
				<div class="widget-kdp-item">
					<a class="widget-kdp-link" href="<?php echo esc_url( get_the_permalink( $post ) ); ?>">
						<?php
						echo geT_the_post_thumbnail(  $post, 'medium', [
							'alt'     => get_the_title( $post ),
							'loading' => 'lazy',
							'width'   => '1200',
							'height'  => '1920',
						] );
						?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<a href="<?php echo home_url( '/kdp/' ); ?>" class="btn btn-amazon btn-block">
			<i class="icon-amazon"></i> <?php esc_html_e( '電子書籍一覧', 'hametuha' ); ?>
		</a>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function form( $instance ) {
		$atts = shortcode_atts( array(
			'title'  => '破滅派@KDP',
			'number' => 10,
			'desc'   => '',
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
