<?php
namespace Hametuha\Widget;


use WPametu\UI\Widget;

/**
 * Recent news
 *
 * @package hametuha
 */
class CategoryWidget extends Widget {

	public $id_base = 'hametuha-category-widget';

	public $title = '破滅派カテゴリーリスト';

	public $description = 'カテゴリーリストを表示します。';

	/**
	 * Outputs the settings update form.
	 *
	 * @since 2.8.0
	 *
	 * @param array $instance Current settings.
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance ) {
		$atts       = wp_parse_args( $instance, array(
			'title'    => 'カテゴリー',
			'taxonomy' => '',
		) );
		$taxonomies = get_taxonomies( [
			'public' => true,
		], OBJECT );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				タイトル<br/>
				<input name="<?php echo $this->get_field_name( 'title' ); ?>"
					   id="<?php echo $this->get_field_id( 'title' ); ?>"
					   value="<?php echo esc_attr( $atts['title'] ); ?>"/>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>">
				タクソノミー<br />
				<select name="<?php echo $this->get_field_name( 'taxonomy' ); ?>"
					   id="<?php echo $this->get_field_id( 'taxonomy' ); ?>">
					<option value=""<?php selected( '', $atts['taxonomy'] ); ?>>選択してください</option>
					<?php foreach ( $taxonomies as $taxonomy => $tax_object ) : ?>
						<option value="<?php echo esc_attr( $taxonomy ); ?>" <?php selected( $taxonomy, $atts['taxonomy'] ); ?>><?php echo esc_html( $tax_object->label ); ?></option>
					<?php endforeach; ?>
				</select>
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
		if ( ! taxonomy_exists( $instance['taxonomy'] ) ) {
			return '';
		}
		ob_start();
		$terms = get_terms( [
			'taxonomy' => $instance['taxonomy'],
		] );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}
		$terms = $this->sort_terms( $terms );
		echo $this->get_list( $terms[0], $terms );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Get nested category list.
	 *
	 * @param array $terms
	 * @param array $store
	 * @return string
	 */
	protected function get_list( $terms, $store ) {
		$uls = [];
		foreach ( $terms as $term ) {
			$li = sprintf(
				'<li class="widget-tax-list-item %1$s"><a href="%2$s">%3$s</a>',
				esc_attr( $term->taxonomy ),
				esc_url( get_term_link( $term ) ),
				esc_html( $term->name )
			);
			if ( isset( $store[ $term->term_id ] ) ) {
				$li .= $this->get_list( $store[ $term->term_id ], $store );
			}
			$li   .= '</li>';
			$uls[] = $li;
		}
		if ( ! $uls ) {
			return '';
		}
		return sprintf( '<ul class="widget-tax-list">%s</ul>', implode( '', $uls ) );
	}

	/**
	 * Sort category
	 *
	 * @param $terms
	 * @return array
	 */
	protected function sort_terms( $terms ) {
		$sorted = [];
		foreach ( $terms as $term ) {
			if ( ! isset( $sorted[ $term->parent ] ) ) {
				$sorted[ $term->parent ] = [];
			}
			$sorted[ $term->parent ][] = $term;
		}
		ksort( $sorted );
		return $sorted;
	}
}
