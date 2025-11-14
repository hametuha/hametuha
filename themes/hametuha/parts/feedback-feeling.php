<?php
/**
 * ユーザータグによる評価を表示する
 *
 * @feature-group feedback
 */
$feedback = \Hametuha\Model\Review::get_instance();
?>
<div class="feeling">
	<nav>
		<div class="nav nav-tabs" id="feeling-tab" role="tablist">
			<button class="nav-link active" id="nav-all-feeling" data-bs-toggle="tab" data-bs-target="#all-feeling" type="button" role="tab" aria-controls="all-feeling" aria-selected="true">
				破滅チャート<?php help_tip( '破滅派読者が入力した感想を元に生成されるチャートです。赤いほど破滅度が高く、青いほど健全な作品です。' ); ?>
			</button>
			<button class="nav-link" id="nav-your-feeling" data-bs-toggle="tab" data-bs-target="#your-feeling" type="button" role="tab" aria-controls="your-feeling" aria-selected="false">
				<?php esc_html_e( 'あなたの感想', 'hametuha' ); ?>
			</button>
		</div>
	</nav>
	<div class="tab-content" id="feeling-tab-content">
		<div class="tab-pane fade show active" id="all-feeling" role="tabpanel" aria-labelledby="nav-all-feeling" tabindex="0">
			<?php echo $feedback->get_chart( get_post() ); ?>
		</div>
		<div class="tab-pane fade" id="your-feeling" role="tabpanel" aria-labelledby="nav-your-feeling" tabindex="0">
			<?php if ( ! current_user_can( 'read' ) ) : ?>
				<div class="alert alert-warning mt-3">
					<?php esc_html_e( 'ログインするとレビュー感想をつけられるようになります。', 'hametuha' ); ?>
					<a rel="nofollow" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="btn btn-warning mt-3"><?php esc_html_e( 'ログインする', 'hametuha' ); ?></a>
				</div>
			<?php elseif ( get_current_user_id() === (int) get_the_author_meta( 'ID' ) ) : ?>
				<div class="alert alert-warning mt-3">
					<?php esc_html_e( '自分の作品にはレビューをつけられません。', 'hametuha' ); ?>
				</div>
				<?php
			else :
				$tags = [];
				foreach ( $feedback->feedback_tags as $group => $terms ) {
					$tags[] = [
						'slug'     => $group,
						'positive' => $terms[0],
						'negative' => $terms[1],
						'label'    => $feedback->review_tag_label( $group ),
					];
				}
				wp_localize_script( 'hametuha-components-post-review', 'ReviewObjects', [
					'tags' => $tags,
					'user' => $feedback->user_voted_tags( get_current_user_id(), get_the_ID() ),
				] );
				wp_enqueue_script( 'hametuha-components-post-review' );
				?>
				<div id="review-container" data-post-id="<?php the_ID(); ?>"></div>
			<?php endif; ?>
		</div>
	</div>
</div>
