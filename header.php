<?php get_header( 'meta' ); ?>
<header id="header" class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">

		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#header-navigation">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?php if ( is_user_logged_in()
			           && current_user_can( 'read' )
			           && is_singular()
			           && ! in_array( get_post_type(), [ 'ideas', 'thread', 'anpi' ] )
			           && current_user_can( 'edit_post', get_the_ID() )
			) : ?>
				<a class="btn btn-default btn-sm navbar-edit-btn"
				   href="<?= get_edit_post_link( get_the_ID(), 'display' ) ?>">
					編集
				</a>
			<?php endif; ?>
		</div>

		<a class="logo" rel="home" href="<?= home_url( '/'); ?>">
			<i class="icon-hametuha"></i><span>破滅派</span>
		</a>

		<?php get_template_part( 'templates/header/user' ) ?>

		<nav class="collapse" id="header-navigation">
			<ul class="nav">
				<li>
					<a rel="home" href="<?= home_url( '') ?>">
						<i class="icon-home"></i> ホーム
					</a>
				</li>
				<li>
					<a rel="home" href="<?= home_url( 'announcement') ?>">
						<i class="icon-bullhorn"></i> 告知
					</a>
				</li>
				<li>
					<a href="#" data-toggle="modal" data-target="#searchBox">
						<i class="icon-search2"></i> 探す
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/about/'); ?>">
						<i class="icon-ha"></i> 破滅派について
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/merumaga/'); ?>">
						<i class="icon-mail"></i> メルマガ購読
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/faq/'); ?>">
						<i class="icon-question2"></i> よくある質問
					</a>
				</li>

			</ul>
		</nav>
		<!-- // .navbar-collapse -->


	</div>
	<!-- .navbar -->

</header>

<div class="subnav">
	<div class="container">
		<div class="subnav__nav">

			<div class="subnav__list row">
				<div class="subnav__item">
					<a href="#" class="subnav__link subnav__link--toggle" data-target="#write-links">
						<i class="icon-quill3"></i> 書く
					</a>
				</div>
				<div class="subnav__item">
					<a href="#" class="subnav__link subnav__link--toggle" data-target="#read-links">
						<i class="icon-book"></i> 読む
					</a>
				</div>
				<div class="subnav__item">
					<a href="#" class="subnav__link subnav__link--toggle" data-target="#community-links">
						<i class="icon-users"></i> SNS
					</a>
				</div>
			</div><!-- //.subnav__list -->

			<div class="subnav__wrap col-xs-12">

				<ul id="write-links" class="subnav__child toggle clearfix">
					<?php if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) : ?>
						<li class="divider">
							<i class="icon-enter"></i> 投稿には会員登録が必須です
						</li>
						<li>
							<a href="<?= wp_registration_url() ?>" rel="nofollow">
								新規登録
							</a>
						</li>
						<li>
							<a href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>" rel="nofollow">
								ログイン
							</a>
						</li>
					<?php elseif ( ! current_user_can( 'edit_posts' ) ) : ?>
						<li class="divider">
							<i class="icon-graduation"></i>同人になる
						</li>
						<li>
							<a href="<?= home_url( '/become-author/', 'https' ) ?>" rel="nofollow">
								 同人になる
							</a>
						</li>
						<li>
							<a href="<?= home_url( '/faq/how-to-post/' ) ?>">
								ヘルプ
							</a>
						</li>
					<?php else : ?>
					<li class="divider">
						作品管理
					</li>
					<li>
						<a href="<?= admin_url( 'post-new.php' ) ?>">
							<i class="icon-quill3"></i> 作品を書く
						</a>
					</li>
					<li>
						<a href="<?= admin_url( 'edit.php' ) ?>">
							<i class="icon-books"></i> 作品一覧
						</a>
					</li>
					<?php endif; ?>
					<li class="divider">
						<i class="icon-lamp"></i> アイデア
					</li>
					<li>
						<a href="<?= get_post_type_archive_link( 'ideas' ) ?>">
							アイデアを探す
						</a>
					</li>
					<li>
						<a href="<?= home_url( '/my/ideas/new/' ) ?>" data-action="post-idea">
							アイデア投稿
						</a>
					</li>
				</ul>


				<ul id="read-links" class="subnav__child toggle clearfix">

					<li class="divider">
						おすすめ作品
					</li>

					<li>
						<a href="<?= home_url( '/ranking/' ) ?>">
							<i class="icon-crown"></i> ランキング</a>
					</li>
					<li>
						<a href="<?= home_url( '/recommends/' ) ?>">
							<i class="icon-star3"></i> おすすめ</a>
					</li>
					<li>
						<a href="<?= home_url( 'kdp/' ) ?>">
							<i class="icon-amazon"></i> Kindle本
						</a>
					</li>
					<li>
						<a href="<?= get_post_type_archive_link( 'series' ) ?>">
							<i class="icon-stack"></i> 作品集
						</a>
					</li>

					<li class="divider">
						<i class="icon-tags"></i> ジャンル
					</li>

					<?php foreach ( get_categories( [ 'parent' => 0, 'number' => 6 ] ) as $cat ) : ?>
						<li>
							<a href="<?= get_category_link( $cat ) ?>"><?= esc_html( $cat->name ) ?></a>
						</li>
					<?php endforeach; ?>

				</ul>

				<ul id="community-links" class="subnav__child toggle clearfix">

					<li class="divider">
						<i class="icon-user"></i> コミュニティ
					</li>

					<li>
						<a href="<?= home_url( '/authors/'); ?>">
							執筆者一覧
						</a>
					</li>

					<li>
						<a href="<?= get_post_type_archive_link( 'lists' ) ?>">
							みんなのリスト
						</a>
					</li>

					<li class="divider">
						<i class="icon-mic5"></i> 安否情報
					</li>

					<li>
						<a href="<?= get_post_type_archive_link( 'anpi' ) ?>">
							みんなの安否
						</a>
					</li>
					<li>
						<?php if ( ! current_user_can( 'read' ) ) : ?>
						<a href="<?= esc_url( wp_login_url( get_post_type_archive_link( 'anpi' ) ) ) ?>" rel="nofollow">
							安否報告
						</a>
						<?php else : ?>
						<a class="anpi-new" href="#">
							安否報告
						</a>
						<?php endif; ?>
					</li>

					<li class="divider">
						<i class="icon-stack-list"></i> 掲示板
					</li>

					<li>
						<a href="<?= home_url( '/thread/'); ?>">
							掲示板トップ
						</a>
					</li>
					<li>
						<a href="<?= home_url( '/thread/#thread-add') ?>">
							スレたてする
						</a>
					</li>

				</ul>
			</div>
		</div>
	</div>
</div>
