<?php get_header() ?>

<?php get_header('breadcrumb') ?>

<div class="container single">

    <div class="row row-offcanvas row-offcanvas-right">

        <?php if( have_posts() ): while( have_posts() ): the_post(); ?>

        <article itemscope itemtype="http://schema.org/Question" <?php post_class('col-xs-12 col-sm-9 main-container')?>>


            <?php get_template_part('parts/bar', 'posttype') ?>

            <div class="page-header thread-header clearfix">

                <div class="thread-info col-sm-3 col-xs-12 text-center clearfix">
                    <div class="col-xs-6 col-sm-12">
                        <p>
                            <?= get_avatar(get_the_author_meta('ID'), 160, '', esc_attr(get_the_author()), ['extra_attr' => 'itemprop="image"']) ?>
                        </p>
                        <p class="author">
                            <small class="text-muted"><?= hametuha_user_role(get_the_author_meta('ID')) ?></small><br />
                            <span itemprop="author"><?php the_author(); ?></span>
                        </p>
                    </div>
                    <div class="col-xs-6 col-sm-12">
                        <p>
                            <strong><i class="icon-stack-list"></i> スレ立て</strong><br />
                            <span><?= number_format_i18n(get_author_thread_count(get_the_author_meta('ID')));?>件</span>
                        </p>
                        <p>
                            <strong><i class="icon-bubble"></i> コメント</strong><br />
                            <span><?= number_format_i18n(get_author_response_count(get_the_author_meta('ID')));?>件</span>
                        </p>
                        <?php if( user_can(get_the_author_meta('ID'), 'edit_posts') ): ?>
                            <p>
                                <a class="btn btn-info btn-block" href="<?= get_author_posts_url(get_the_author_meta('ID'));?>">
                                    投稿一覧
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div><!-- //.thread-info -->



                <div class="thread-body col-sm-9 col-xs-12">

                    <h1 itemprop="name"><?php the_title(); ?></h1>

                    <?php get_template_part('parts/meta', 'single') ?>

                    <?php if( isset($_GET['action']) && $_GET['action'] == 'edit' && current_user_can('edit_posts', get_the_ID()) ): ?>

                        <!-- Form  -->
                        <?php show_thread_error();?>

                        <?php if( isset($_REQUEST['_wpnonce']) && !get_thread_error() ): ?>
                            <p class="alert alert-success">スレッドを更新しました。</p>
                        <?php endif;?>

                        <form method="post" action="<?php the_permalink(); ?>?action=edit">

                            <?php wp_nonce_field('hametuha_thread_edit'); ?>

                            <div class="form-group">
                                <label for="thread_title">タイトル</label>
                                <input type="text" class="form-control" name="thread_title" id="thread_title" value="<?php echo esc_attr(get_the_title()); ?>" />
                            </div>

                            <div class="form-group">
                                <label for="thread_content">詳細</label>
                                <textarea rows="8" class="form-control" name="thread_content" id="thread_content"><?php echo strip_tags(get_the_content()); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="topic_id">トピック</label>
                                <?php
                                    $topics = get_the_terms(get_the_ID(), 'topic');
                                    $topic = current($topics);
                                    wp_dropdown_categories([
                                        'class' => 'form-control',
                                        'taxonomy' => 'topic',
                                        'name' => 'topic_id',
                                        'selected' => $topic->term_id,
                                        'hide_empty' => false
                                    ]);
                                ?>
                            </div>

                            <p>
                                <input type="submit" value="スレッドを更新" class="btn btn-primary btn-block" onclick="this.value = '送信中...';" />
                            </p>

                        </form>

                        <div class="clearfix">
                            <div class="col-xs-6">
                                <a class="btn btn-link" href="<?php the_permalink(); ?>"><i class="icon-close"></i> 編集を終了</a>
                            </div>
                            <div class="col-xs-6 text-right">
                                <a class="btn btn-danger" href="<?= wp_nonce_url(get_permalink().'?action=delete', 'hametuha_thread_delete');?>" onclick="return confirm('本当にこのスレッドを削除してよろしいですか？');">スレッドを削除</a>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- Content -->
                        <?php if( current_user_can('edit_post', get_the_ID()) ): ?>
                            <p class="text-right">
                                <a class="btn btn-sm btn-primary" href="<?php the_permalink() ?>?action=edit"><i class="icon-pencil5"></i> このスレッドを編集</a>
                            </p>
                        <?php endif;  ?>
                        <div class="thread-inner" itemprop="text">
                            <?= wpautop(WPametu::helper()->str->auto_link(strip_tags(get_the_content()))) ?>
                        </div><!-- //.thread-inner -->

                    <?php endif; ?>

                </div><!-- //.thread-body -->

            </div><!-- //.thread-header -->


		    <?php get_template_part('parts/share') ?>

            <?php get_template_part('parts/pager') ?>

            <hr>

            <div class="more">
                <?php comments_template(); ?>
            </div>

            <?php get_template_part('parts/nav', 'thread') ?>

            <hr>

        </article><!-- //.single-container -->
	
        <?php endwhile; endif; ?>

        <?php contextual_sidebar() ?>

    </div><!-- //.row-offcanvas -->
</div><!-- //.container -->

<?php get_footer() ?>