<?php
if( is_search() ){
    get_template_part('index');
    exit;
}
?>
<?php get_header() ?>

<?php get_header('breadcrumb') ?>

<div class="container archive">

    <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-xs-12 col-sm-9 main-container">

            <?php get_template_part('parts/jumbotron', 'thread')?>

            <?php if( $topics = get_terms('topic', array('hide_empty' => false)) ) foreach($topics as $topic): ?>

                <div class="topic-container panel panel-primary">

                    <div class="panel-heading">
                        <h2 class="panel-title">
                            <a href="<?= get_term_link($topic) ?>"><?= esc_html($topic->name); ?></a>
                            <span class="badge"><?= number_format($topic->count); ?></span>
                        </h2>
                    </div><!-- //.panel-title -->

                    <div class="panel-body text-muted">
                        <?= esc_html($topic->description); ?>
                        <a class="btn btn-xs btn-primary" href="<?= get_term_link($topic) ?>">一覧</a>
                    </div>

                    <?php $query = new WP_Query("post_type=thread&posts_per_page=10&topic={$topic->slug}"); if($query->have_posts()): ?>
                        <div class="list-group">
                            <?php while($query->have_posts()): $query->the_post(); ?>
                            <a class="list-group-item" href="<?php the_permalink(); ?>">
                                <span class="badge"><?= get_comments_number() ?></span>
                                <?= get_avatar(get_the_author_meta('ID'), 32); ?>
                                <?php the_title(); ?>
                                <?php if( recently_commented() || is_new_post() ): ?>
                                    <span class="label label-warning">New!</span>
                                <?php endif; ?>
                                <small class="date">
                                    （<?php the_author() ?>, <?= hametuha_passed_time($post->post_date); ?>）
                                </small>
                            </a>
                            <?php endwhile; wp_reset_query();?>
                        </div>
                    <?php endif; ?>

                </div><!-- //.topic-container -->

            <?php endforeach; ?>

            <hr />

            <h2><i class="icon-pencil5"></i> スレッド作成フォーム</h2>

            <form id="thread-add" method="post" action="<?php echo get_post_type_archive_link('thread'); ?>#thread-add">

                <?php wp_nonce_field('hametuha_add_thread');?>

                <?php if(!is_user_logged_in()): ?>
                    <p class="alert alert-warning">匿名のままスレッドを作成するか、<a class="alert-link" href="<?php echo wp_login_url(get_post_type_archive_link('thread'));?>">ログイン</a>してください。</p>
                <?php endif; ?>

                <?php show_thread_error();?>

                <div class="form-group<?php if(get_thread_error('thread_title')) echo ' has-error has-feedback'  ?>">
                    <label for="thread_title">スレッドタイトル <span class="label label-danger">必須</span></label>
                    <input type="text" class="form-control" name="thread_title" id="thread_title" value="<?php if(isset($_REQUEST['thread_title'])) echo esc_attr($_REQUEST['thread_title']);?>" placeholder="ex. なぜ私はこの世界に生まれたのですか？" />
                    <?php if(get_thread_error('thread_title')): ?>
                        <i class="icon-close form-control-feedback"></i>
                    <?php endif; ?>
                    <p class="help-block">30文字を超える部分は切り捨てられます。</p>
                </div>

                <div class="form-group">
                    <label for="thread_content">詳細</label>
                    <textarea class="form-control" rows="8" name="thread_content" id="thread_content" placeholder="ex. この世に生まれてこの方、一度も楽しいと思ったことがありません。どうしてでしょうか？"><?php if(isset($_REQUEST['thread_content'])) echo esc_textarea($_REQUEST['thread_content']);?></textarea>
                    <p class="help-block">HTMLタグは使えません。自動で除去されます。URLは自動でリンクします。</p>
                </div>

                <div class="form-group<?php if(get_thread_error('topic_id')) echo ' has-error has-feedback';?>">
                    <label for="topic_id">トピック <span class="label label-danger">必須</span></label>
                    <select name="topic_id" id="topic_id" class="form-control" >
                        <option value="0"<?php if(!isset($_REQUEST['topic_id'])) echo ' selected="selected"'?>>選択してください</option>
                        <?php foreach($topics as $t): ?>
                            <option value="<?php echo $t->term_id; ?>"<?php if(isset($_REQUEST['topic_id']) && $_REQUEST['topic_id'] == $t->term_id) echo ' selected="selected"'?>><?php echo esc_html($t->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if(is_user_logged_in()): ?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="anonymous" value="1" <?php checked(isset($_REQUEST['anonymous']) && $_REQUEST['anonymous']) ?> />
                            匿名ユーザーとしてスレッドを作成
                        </label>
                        <p class="help-block">匿名ユーザーとしてスレッドを作成した場合、編集はできません。</p>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="recaptcha_response_field">スパム確認 by reCaptcha <span class="label label-danger">必須</span></label>
                        <?= wpametu_recaptcha('clean', 'en') ?>
                        <?php if( ($message = get_thread_error('recaptcha')) ): ?>
                            <p class="text-danger">※キャプチャが難しくて読めない場合は、<i class="icon-spinner10"></i> ボタンを押してください。</p>
                        <?php endif; ?>
                        <p class="help-block">
                            スパムロボットによる投稿を防止するため、画像の文字の入力にご協力をお願いします。
                            <?php help_tip('人間にしか読めないだろう画像の文字を入力することで、スパムロボットでないことを保証する仕組みです。表示されているのはGoogleの電子書籍化プロジェクトで機械が読みとれなかった単語だそうです。', 'right') ?>
                        </p>
                    </div>
                <?php endif; ?>


                <input type="submit" class="btn btn-success btn-lg btn-block" value="この内容でスレッドを作成" onclick="this.value='送信中...'; " />
            </form>

        </div><!-- //.main-container -->

        <?php contextual_sidebar() ?>

    </div><!-- // .offcanvas -->

</div><!-- //.container -->

<?php get_footer(); ?>