
<div id="about-list" class="alert alert-warning">
	<h4 class="panel-title"><i class="icon-question2"></i> リストとは</h4>
	<p>
		破滅派の読者が作成した作品のリストです。編集部作成したオススメリストもあります。
		<?php if( !is_user_logged_in() ): ?>
		作成するには<a class="alert-link" href="<?= wp_login_url('/your/lists/') ?>">ログイン</a>する必要があります。
		<?php endif; ?>
	</p>
	<p>
		リストへの作品追加は、個別の作品ページから行えます。
	</p>
</div>

<ul class="nav nav-pills">
	<li><a href="<?= home_url('/recommends/', 'http'); ?>">編集部オススメ</a></li>
	<li><a href="<?= get_post_type_archive_link('lists', 'http'); ?>">新着リスト</a></li>
	<?php if( is_user_logged_in() ): ?>
		<li><a href="<?= home_url('/your/lists/', 'https') ?>">あなたのリスト</a></li>
		<li class="active"><a class="list-creator" title="リストを追加" href="<?= esc_url(\Hametuha\Rest\ListCreator::form_link()) ?>">リストを追加</a></li>
	<?php endif; ?>
	<li><a href="<?= home_url('/faq/how-to-make-list/', 'http'); ?>">作り方</a></li>
</ul>