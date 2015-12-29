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
			           && ! in_array( get_post_type(), [ 'ideas', 'thread' ] )
			           && current_user_can( 'edit_post', get_the_ID() )
			) : ?>
				<a class="btn btn-default btn-sm navbar-edit-btn"
				   href="<?= get_edit_post_link( get_the_ID(), 'display' ) ?>">
					編集
				</a>
			<?php endif; ?>
		</div>

		<a class="logo" rel="home" href="<?= home_url( '/', 'http' ); ?>">
			<i class="icon-hametuha"></i><span>破滅派</span>
		</a>


		<ul id="user-info" class="navbar-nav navbar-right navbar-login navbar-login--user nav nav-pills col-sm-1">
			<?php if ( is_user_logged_in() && current_user_can( 'read' ) ) : ?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle"
					   data-toggle="dropdown"><?= get_avatar( get_current_user_id(), 60 ) ?></a>
					<ul class="dropdown-menu">
						<li class="greeting">
							<strong><?= hametuha_user_name() ?></strong>さん<br/>
							<span class="role"><?= hametuha_user_role() ?></span>
						</li>
						<li class="divider"></li>
				<li>
					<a href="<?= home_url( '/your/comments/', 'http' ) ?>">
						<i class="icon-bubble-dots"></i>
						あなたのコメント
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/your/lists/', 'http' ) ?>">
						<i class="icon-drawer3"></i>
						あなたのリスト
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/your/reviews/', 'http' ) ?>">
						<i class="icon-star2"></i>
						レビューした作品
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/my/ideas/', 'https' ) ?>">
						<i class="icon-lamp4"></i>
						アイデア帳
					</a>
				</li>

				<?php if ( current_user_can( 'edit_posts' ) ) : ?>

					<li class="divider"></li>
					<li><span class="text-muted">管理</span></li>
					<li>
						<a href="<?= home_url( '/statistics/', 'https' ) ?>">
							<i class="icon-chart"></i>
							統計情報
						</a>
					</li>
					<li>
						<a href="<?= home_url( '/sales/', 'https' ) ?>">
							<i class="icon-coins"></i>
							売上管理
						</a>
					</li>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<li>
							<a href="<?= admin_url() ?>">
								<i class="icon-dashboard"></i>
								ダッシュボード
							</a>
						</li>
					<?php endif; ?>
				<?php endif; ?>
				<li class="divider"></li>
				<li>
					<a href="<?= admin_url( 'profile.php' ) ?>">
						<i class="icon-profile"></i>
						プロフィール
					</a>
				</li>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					<li>
						<a href="<?= home_url( '/doujin/follower/', 'https' ) ?>">
							<i class="icon-heart5"></i>
							フォロワー
						</a>
					</li>
				<?php endif; ?>
				<li>
					<a href="<?= wp_logout_url() ?>">
						<i class="icon-exit4"></i>
						ログアウト
					</a>
				</li>
			</ul>
			</li>
			<li class="dropdown" id="notification-link">
				<?php
				$notification = \Hametuha\Rest\Notification::get_instance();
				$latest       = $notification->last_checked();
				?>
				<a href="#" class="dropdown-toggle dropdown--notify" data-toggle="dropdown"
				   data-last-checked="<?= $latest ?>"><i class="icon-earth"></i></a>
				<ul id="notification-container" class="dropdown-menu notification__container">
					<?php if ( ! $notification->recent_blocks() ) : ?>
						<li>
							<span>お知らせはなにもありません。</span>
						</li>
					<?php endif; ?>
					<li class="divider"></li>
					<li class="text-center notification__more">
						<a href="<?= home_url( '/notification/all/', 'https' ) ?>">
							通知一覧へ
							<i class="icon-arrow-right4"></i>
						</a>
					</li>
				</ul>
			</li>
			<?php else : ?>
			<li><a class="login-btn" href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>">ログイン</a></li>
			<?php endif; ?>
			</ul><!-- //#user-info -->


			<nav class="collapse" id="header-navigation">
				<ul class="nav">
					<li>
						<a rel="home" href="<?= home_url( '', 'http' ) ?>">
							<i class="icon-home"></i> ホーム
						</a>
					</li>
					<li>
						<a rel="home" href="<?= home_url( 'announcement', 'http' ) ?>">
							<i class="icon-bullhorn"></i> 告知
						</a>
					</li>
					<li>
						<a href="#" data-toggle="modal" data-target="#searchBox">
							<i class="icon-search2"></i> 探す
						</a>
				</li>
				<li>
					<a href="<?= home_url( '/about/', 'http' ); ?>">
						<i class="icon-ha"></i> 破滅派について
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/merumaga/', 'http' ); ?>">
						<i class="icon-mail"></i> メルマガ購読
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/faq/', 'http' ); ?>">
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


				<?php if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) : ?>
					<div class="subnav__item">
						<a href="<?= wp_registration_url() ?>" class="subnav__link" rel="nofollow">
							<i class="icon-user-plus3"></i> 登録
						</a>
					</div>
				<?php elseif ( ! current_user_can( 'edit_posts' ) ) : ?>
					<div class="subnav__item">
						<a class="subnav__link" href="<?= home_url( '/become-author/', 'https' ) ?>" rel="nofollow">
							<i class="icon-graduation"></i> 同人になる
						</a>
					</div>
				<?php else : ?>
					<div class="subnav__item">
						<a href="#" class="subnav__link subnav__link--toggle" data-target="#write-links">
							<i class="icon-quill3"></i> 書く
						</a>
					</div>
				<?php endif; ?>
				<div class="subnav__item">
					<a href="#" class="subnav__link subnav__link--toggle" data-target="#read-links">
						<i class="icon-book"></i> 読む
					</a>
				</div>
				<div class="subnav__item">
					<?php if ( current_user_can( 'read' ) ) : ?>
						<a href="#" class="subnav__link subnav__link--toggle" data-target="#community-links">
							<i class="icon-users"></i> SNS
						</a>
					<?php else : ?>
						<a href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ) ?>" class="subnav__link" rel="nofollow">
							<i class="icon-users"></i> SNS
						</a>
					<?php endif; ?>
				</div>
			</div><!-- //.subnav__list -->

			<div class="subnav__wrap col-xs-12">

				<ul id="write-links" class="subnav__child toggle clearfix">
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
						<a href="<?= home_url( '/authors/', 'http' ); ?>">
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
						<a href="<?= admin_url( 'post-new.php?post_type=anpi' ) ?>">
							 安否報告
						</a>
					</li>

					<li class="divider">
						<i class="icon-stack-list"></i> 掲示板
					</li>

					<li>
						<a href="<?= home_url( '/thread/', 'http' ); ?>">
							掲示板トップ
						</a>
					</li>
					<li>
						<a href="<?= home_url( '/thread/#thread-add', 'http' ) ?>">
							スレたてする
						</a>
					</li>

				</ul>
			</div>
		</div>
	</div>
</div>
