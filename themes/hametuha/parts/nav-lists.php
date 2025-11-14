<?php
/**
 * リストのナビゲーション
 *
 * @feature-group list
 */
if ( current_user_can( 'read' ) ) {
	// ユーザーがログインしてたらリスト用のフォームを追加
	wp_enqueue_script( 'hametuha-components-list' );
}
?>
<div id="about-list" class="alert alert-warning">
	<h4 class="panel-title"><i class="icon-question2"></i> リストとは</h4>
	<p>
		破滅派の読者が作成した作品のリストです。編集部作成したオススメリストもあります。
		<?php if ( ! is_user_logged_in() ) : ?>
		作成するには<a class="alert-link" href="<?php echo wp_login_url( '/your/lists/' ); ?>">ログイン</a>する必要があります。
		<?php endif; ?>
	</p>
	<p>
		リストへの作品追加は、個別の作品ページから行えます。<a class="alert-link" href="<?php echo home_url( '/faq/how-to-make-list/' ); ?>">詳しいやり方を見る&raquo;</a>
	</p>
	<?php if ( is_singular( 'lists' ) ) : ?>
		<div class="mt-3">
			<?php get_template_part( 'parts/share' ); ?>
		</div>
	<?php endif; ?>
</div>


<ul class="nav nav-pills">
	<?php if ( current_user_can( 'read' ) ) : ?>
		<li class="nav-item">
			<a class="nav-link active list-creator" title="リストを追加" href="#">リストを追加</a>
		</li>
	<?php endif; ?>
	<li class="nav-item"><a class="nav-link" href="<?php echo get_post_type_archive_link( 'lists' ); ?>">破滅派選書</a></li>
	<li class="nav-item"><a class="nav-link" href="<?php echo home_url( '/recommends/' ); ?>">編集部オススメ</a></li>
	<?php if ( is_user_logged_in() ) : ?>
		<li class="nav-item"><a class="nav-link" href="<?php echo home_url( '/your/lists/', 'https' ); ?>">あなたのリスト</a></li>
	<?php endif; ?>
	<li class="nav-item"><a class="nav-link" href="<?php echo home_url( '/faq/how-to-make-list/' ); ?>">リストの作り方</a></li>
</ul>
