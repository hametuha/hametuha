<?php get_header('meta'); ?>

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

        <a class="logo" rel="home" href="<?php bloginfo('url', 'http'); ?>">
            <i class="icon-hametuha"></i><span>破滅派</span>
        </a>


        <div class="collapse navbar-collapse col-sm-8" id="header-navigation">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-ha"></i> <span>移動</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a rel="home" href="<?= home_url('', 'http') ?>"><i class="icon-home"></i>トップページ</a></li>
                        <li class="divider"></li>
                        <li><a rel="home" href="<?= home_url('announcement', 'http') ?>"><i class="icon-bullhorn"></i> 告知</a></li>
                        <li class="divider"></li>
                        <li><a href="<?= home_url('/thread/', 'http'); ?>"><i class="icon-stack-list"></i> 掲示板トップ</a></li>
                        <li class="divider"></li>
                        <li><a href="<?= home_url('/faq/', 'http'); ?>"><i class="icon-question2"></i> よくある質問</a></li>
                        <li class="divider"></li>
                        <li><a href="<?= home_url('/about/', 'http'); ?>"><i class="icon-ha"></i> 破滅派について</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-book"></i> <span>読む</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= get_permalink(get_option('page_for_posts')) ?>"><i class="icon-books"></i> 作品新着</a></li>
                        <li class="divider"></li>
                        <li><span class="text-muted"><i class="icon-tags"></i> ジャンル</span></li>
                        <?php foreach( get_categories(['parent' => 0, 'number' => 6]) as $cat): ?>
                        <li><a href="<?= get_category_link($cat) ?>"><?= esc_html($cat->name) ?></a></li>
                        <?php endforeach; ?>
                        <li class="divider"></li>
                        <li><a href="<?= home_url('/ranking/') ?>"><i class="icon-crown"></i> ランキング</a></li>
	                    <li class="divider"></li>
	                    <li><a href="<?= get_post_type_archive_link('anpi') ?>"><i class="icon-skull3"></i> 安否情報</a></li>
                    </ul>
                </li>
                <?php if( is_user_logged_in() && current_user_can('read') ): ?>
                    <?php if( current_user_can('edit_posts') ): ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-quill3"></i> <span>書く</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="active">
                                    <a href="<?= admin_url('post-new.php') ?>"><i class="icon-quill3"></i>作品を書く</a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?= admin_url('edit.php') ?>"><i class="icon-books"></i>自分の作品一覧</a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?= admin_url('post-new.php?post_type=anpi') ?>"><i class="icon-mic5"></i> 安否情報をお知らせ</a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?= admin_url('post-new.php?post_type=thread') ?>"><i class="icon-fire2"></i> 掲示板にスレたて</a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="active">
                           <a href="<?= home_url('/become-author/', 'https') ?>"><i class="icon-graduation"></i> 同人になる</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div><!-- // .navbar-collapse -->

        <ul id="user-info" class="navbar-nav navbar-right nav nav-pills col-sm-4">
            <?php if( is_user_logged_in() && current_user_can('read') ): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= get_avatar(get_current_user_id(), 60) ?></a>
                    <ul class="dropdown-menu">
                        <li class="greeting">
                            <strong><?= hametuha_user_name() ?></strong>さん<br />
                            <span class="role"><?= hametuha_user_role() ?></span><br />
                        </li>
                        <li class="divider"></li>
                        <?php /*
                        <li><a href="<?= home_url('/your/favorites/', 'http') ?>"><i class="icon-highlight"></i> 保存したフレーズ</a></li>
                        */ ?>
                        <li><a href="<?= home_url('/your/comments/', 'http') ?>"><i class="icon-bubble-dots"></i> あなたのコメント</a></li>
	                    <li><a href="<?= home_url('/your/lists/', 'http') ?>"><i class="icon-drawer3"></i> あなたのリスト</a></li>
                        <li><a href="<?= home_url('/your/reviews/', 'http') ?>"><i class="icon-star2"></i> レビューした作品</a></li>
                        <li class="divider"></li>
                        <li><a href="<?= admin_url('profile.php') ?>"><i class="icon-profile"></i> プロフィール</a></li>
                        <?php if( current_user_can('edit_posts') ): ?>
                            <li><a href="<?= admin_url() ?>"><i class="icon-dashboard"></i> ダッシュボード</a></li>
                        <?php endif; ?>
                        <li><a href="<?= wp_logout_url() ?>"><i class="icon-exit4"></i> ログアウト</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a class="login-btn" href="<?= wp_login_url('/') ?>"><i class="icon-key"></i> ログイン</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

