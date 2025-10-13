<?php
namespace Hametuha\Widget;


use WPametu\UI\Widget;

/**
 * FAQ リンクリスト
 *
 * @package hametuha
 * @feature-group faq
 */
class FaqNavWidget extends Widget {

	public $id_base = 'faq-nav-widget';

	public $title = 'FAQリンク';

	public $description = 'FAQのリンクを表示を表示します。';

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
		<dl class="faq__list">
			<?php foreach ( get_terms( 'faq_cat' ) as $term ) : ?>

				<dt class="faq__cat">
					<span class="faq__term"><?php echo esc_html( $term->name ); ?></span>
					<?php if ( $term->description ) : ?>
						<small class="faq__desc">
							<?php echo nl2br( esc_html( $term->description ) ); ?>
						</small>
					<?php endif; ?>
				</dt>
				<dd class="faq__content">
					<ul class="faq__items">
						<?php
						foreach ( get_posts( [
							'post_type'      => 'faq',
							'post_status'    => 'publish',
							'orderby'        => [ 'date' => 'DESC' ],
							'posts_per_page' => 3,
							'tax_query'      => [
								[
									'taxonomy' => 'faq_cat',
									'terms'    => $term->term_id,
									'field'    => 'id',
								],
							],
						] ) as $faq ) :
							?>
							<li class="faq__item">
								<a href="<?php echo get_permalink( $faq ); ?>" class="faq__link">
									<?php echo get_the_title( $faq ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
					<a class="btn btn-default btn-block faq__btn" href="<?php echo get_term_link( $term ); ?>">
						もっと見る
					</a>
				</dd>
			<?php endforeach; ?>
		</dl>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
