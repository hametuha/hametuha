<?php
/**
 * ニュース執筆者募集ジャンボトロン
 *
 * @feature-group news
 */
?>
<div class="news-recruit row">

	<div class="col-12 col-sm-6 news-recruit__eyecatch">
		<img class="news-recruit__img" src="<?php echo get_template_directory_uri(); ?>/assets/img/jumbotron/hamenew-recruit.jpg?201608" alt="募集中……" />
	</div>

	<div class="col-12 col-sm-6 news-recruit__copy">
		<h3 class="news-recruit__title"><i class="icon-users"></i> 執筆者募集中</h3>
		<p class="news-recruite__desc">
			はめにゅーでは独自性の高い文学関連の記事を増やすべく、執筆者を募集しています。
			コンテキストなき文学を生き抜くための貴重な情報を一緒に集めましょう。
			タレコミや情報提供も随時受け付けています。
		</p>
		<?php
		if ( has_nav_menu( 'hamenew_actions' ) ) :
			$locations = get_nav_menu_locations();
			?>
		<p class="text-center">
			<?php foreach ( wp_get_nav_menu_items( $locations['hamenew_actions'] ) as $item ) : ?>
			<a href="<?php echo esc_url( $item->url ); ?>" class="btn btn-success btn-sm"><?php echo get_the_title( $item ); ?></a>
			<?php endforeach; ?>
		</p>
		<?php endif; ?>
		<div class="text-center">
			<a href="https://twitter.com/minico_me" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @minico_me</a><br />
			<div class="fb-like" data-href="https://www.facebook.com/minicome/" data-layout="button" data-action="like" data-size="small" data-show-faces="false" data-share="false"></div>
		</div>
	</div>
	

</div>
