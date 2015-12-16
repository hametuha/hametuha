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
		</div>

		<a class="logo" rel="home" href="<?= home_url( '/', 'http' ); ?>">
			<i class="icon-hametuha"></i><span>破滅派</span>
		</a>


		<nav class="collapse navbar-collapse col-sm-8" id="header-navigation">
			<ul class="nav navbar-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-togglen " data-toggle="dropdown">
						<i class="icon-menu2"></i> メニュー
					</a>
					<ul class="dropdown-menu">
						<li>
							<a rel="home" href="<?= home_url( '', 'http' ) ?>">
								<i class="icon-home"></i> ホーム
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a rel="home" href="<?= home_url( 'announcement', 'http' ) ?>">
								<i class="icon-bullhorn"></i> 告知
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="<?= home_url( '/thread/', 'http' ); ?>">
								<i class="icon-stack-list"></i> 掲示板トップ
							</a>
						</li>
						<li class="divider"></li>
						<li><a href="<?= home_url( '/about/', 'http' ); ?>"><i class="icon-ha"></i> 破滅派について</a></li>
						<li><a href="<?= home_url( '/merumaga/', 'http' ); ?>"><i class="icon-mail"></i> メルマガ購読</a></li>
						<li><a href="<?= home_url( '/authors/', 'http' ); ?>"><i class="icon-users"></i> 執筆者一覧</a></li>
						<li><a href="<?= home_url( '/faq/', 'http' ); ?>"><i class="icon-question2"></i> よくある質問</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="icon-book"></i> <span>読む</span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="<?= get_permalink( get_option( 'page_for_posts' ) ) ?>"><i class="icon-books"></i>
								作品新着</a></li>
						<li><a href="<?= get_post_type_archive_link( 'series' ) ?>"><i class="icon-stack"></i> 連載</a>
						</li>
						<li><a href="<?= home_url( 'kdp/' ) ?>"><i class="icon-html5"></i> Kindle本</a></li>
						<li class="divider"></li>
						<li><span class="text-muted"><i class="icon-tags"></i> ジャンル</span></li>
						<?php foreach ( get_categories( [ 'parent' => 0, 'number' => 6 ] ) as $cat ) : ?>
							<li><a href="<?= get_category_link( $cat ) ?>"><?= esc_html( $cat->name ) ?></a></li>
						<?php endforeach; ?>
						<li class="divider"></li>
						<li><a href="<?= home_url( '/ranking/' ) ?>"><i class="icon-crown"></i> ランキング</a></li>
						<li><a href="<?= home_url( '/recommends/' ) ?>"><i class="icon-star3"></i> おすすめ</a></li>
						<li><a href="<?= get_post_type_archive_link( 'lists' ) ?>"><i class="icon-drawer3"></i> みんなのリスト</a>
						</li>
						<li class="divider"></li>
						<li><a href="<?= get_post_type_archive_link( 'anpi' ) ?>"><i class="icon-skull3"></i> 安否情報</a>
						</li>
					</ul>
				</li>
				<?php if ( is_user_logged_in() && current_user_can( 'read' ) ) : ?>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<i class="icon-quill3"></i> <span>書く</span>
							</a>
							<ul class="dropdown-menu">
								<li class="active">
									<a href="<?= admin_url( 'post-new.php' ) ?>">
										<i class="icon-quill3"></i>作品を書く
									</a>
								</li>
								<li class="divider"></li>
								<li>
									<a href="<?= admin_url( 'edit.php' ) ?>">
										<i class="icon-books"></i>自分の作品一覧
									</a>
								</li>
								<li class="divider"></li>
								<li>
									<a href="<?= admin_url( 'post-new.php?post_type=anpi' ) ?>">
										<i class="icon-mic5"></i> 安否情報をお知らせ
									</a>
								</li>
								<li class="divider"></li>
								<li>
									<a href="<?= home_url( '/thread/#thread-add', 'http' ) ?>">
										<i class="icon-fire2"></i> 掲示板にスレたて
									</a>
								</li>
							</ul>
						</li>
						<?php if ( is_singular() && current_user_can( 'edit_post', get_the_ID() ) ) : ?>
							<li class="active">
								<a href="<?= get_edit_post_link( get_the_ID(), 'display' ) ?>">
									<i class="icon-pencil5"></i> 編集
								</a>
							</li>
						<?php endif; ?>
					<?php else : ?>
						<li class="active">
							<a href="<?= home_url( '/become-author/', 'https' ) ?>"><i class="icon-graduation"></i>
								同人になる</a>
						</li>
					<?php endif; ?>
				<?php endif; ?>

				<li>
					<a href="#" data-toggle="modal" data-target="#searchBox">
						<i class="icon-search2"></i> 探す
					</a>

				</li>


				<?php if ( ! is_user_logged_in() ) : ?>
					<li class="active">
						<a href="<?= wp_registration_url() ?>">
							<i class="icon-user-plus3"></i> 登録
						</a>
					</li>
				<?php endif; ?>

			</ul>
		</nav>
		<!-- // .navbar-collapse -->


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
						<?php /*
                        <li><a href="<?= home_url('/your/favorites/', 'http') ?>"><i class="icon-highlight"></i> 保存したフレーズ</a></li>
                        */ ?>
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
		</ul>


	</div>
	<!-- .navbar -->
</header>


<div class="modal fade" id="searchBox" tabindex="-1" role="dialog" aria-labelledby="searchBox">
	<div class="modal-dialog">
		<form action="<?= home_url( '/', 'http' ) ?>" data-action="<?= home_url( '/', 'http' ) ?>">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="icon-search2"></i>検索フォーム</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="searchBoxS">キーワード</label>
					<input class="form-control" type="text" name="s" id="searchBoxS" value="<?php the_search_query() ?>" placeholder="ex. 面白い小説" />
				</div>
				<div class="form-group">
					<label class="radio-inline">
						<input type="radio" name="post_type" value="any" <?php checked( in_array( get_query_var( 'post_type' ), [ '', 'any' ] ) ) ?>/> すべて
					</label>
					<?php
					foreach ( [
						'post' => '作品',
						'thread' => '掲示板',
					    'anpi' => '安否',
					    'faq' => 'よくある質問',
					] as $post_type => $label ) :
					?>
					<label class="radio-inline">
						<input type="radio" name="post_type" value="<?= $post_type ?>" <?php checked( get_query_var( 'post_type' ) === $post_type ) ?>/> <?= $label ?>
					</label>
					<?php endforeach; ?>
					<label class="radio-inline">
						<input type="radio" name="post_type" value="author" <?php checked( 'author' === get_query_var( 'post_type' ) ) ?> /> 著者
					</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
				<input type="submit" class="btn btn-primary" value="検索" />
			</div>
		</div><!-- /.modal-content -->
		</form>
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->