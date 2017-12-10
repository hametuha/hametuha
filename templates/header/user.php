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
                    <a href="<?= home_url( 'dashboard' ) ?>">
                        <i class="icon-cog"></i>
                        設定
                    </a>
                </li>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
                    <li>
                        <a href="<?= admin_url() ?>">
                            <i class="icon-dashboard"></i>
                            作品管理
                        </a>
                    </li>
				<?php endif; ?>
                <li class="divider"></li>
				<li>
					<a href="<?= home_url( '/your/comments/') ?>">
						<i class="icon-bubble-dots"></i>
						あなたのコメント
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/your/lists/') ?>">
						<i class="icon-drawer3"></i>
						あなたのリスト
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/your/reviews/') ?>">
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
                <li>
                    <a href="<?= home_url( '/doujin/follower/', 'https' ) ?>">
                        <i class="icon-heart5"></i>
                        フォロワー
                    </a>
                </li>

                <li class="divider"></li>
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
