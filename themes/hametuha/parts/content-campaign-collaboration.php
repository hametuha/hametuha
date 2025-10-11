<?php
/**
 * コラボ型の公募についての詳細を表示する
 *
 * @param array $atts
 */
$term = get_queried_object();
$controller = \Hametuha\Hooks\CampaignController::get_instance();
?>

<div class="campaign-collaboration">

	<h2 class="campaign-collaboration-title">公募への参加状況</h2>

	<p class="description">
		<?php if ( hametuha_is_available_campaign( $term ) ) : ?>
			この公募「<?php echo esc_html( $term->name ); ?>」は協力して実現する公募です。
			参加するためには、期限内に作品を投稿するか、サポーターとして登録してください。
		<?php else : ?>
			この公募「<?php echo esc_html( $term->name ); ?>」は協力して実現する公募ですが、すでに締め切られました。
		<?php endif; ?>
	</p>

	<?php if ( ! is_user_logged_in() ) : ?>
		<a href="<?php echo esc_url( wp_login_url( get_term_link( $term ) ) ); ?>" rel="nofollow">ログイン</a>してください。
	<?php elseif ( $controller->is_user_participating( $term, get_current_user_id()) ) : ?>
		<p class="description">
			<i class="icon-checkmark"></i>
			<u>あなたはこの公募に寄稿者として参加中です。</u>
		</p>
	<?php elseif ( $controller->is_user_supporting( $term, get_current_user_id() ) ) : ?>
		<p class="description">
			<i class="icon-checkmark"></i>
			<u>あなたはこの公募にサポーターとして参加しています。</u>
		</p>
	<?php else : ?>
		<p class="description">
			<i class="icon-close"></i>
			<u>あなたはこの公募に参加していません。</u>
		</p>
		<?php
		if ( hametuha_is_available_campaign( $term ) ) :
			wp_enqueue_script( 'hametuha-campaign-participants' );
			?>
			<p>
				<button id="campaign-support-action" class="btn btn-primary" data-action="<?php echo esc_attr( $term->term_id ); ?>">
					サポーターとして参加する
				</button>
			</p>
		<?php endif; ?>
	<?php endif; ?>
	<hr />
	<h2 class="campaign-collaboration-title">
		<?php
		$supporters = $controller->get_supporters( $term );
		esc_html_e( 'サポーター登録状況', 'hametuha' );
		if ( count( $supporters ) ) {
			printf( '（%d名）', count( $supporters ) );
		}
		?>
	</h2>
	<?php
	if ( empty( $supporters ) ) {
		?>
		<p class="description">
			<?php esc_html_e( 'サポーターは一人もいません……', 'hametuha' ); ?>
		</p>
		<?php
	} else {
		get_template_part( 'parts/list-participants', '', [
			'users' => $supporters,
		] );
	}
	?>
</div><!-- //.campaign-collaboration -->

<?php
