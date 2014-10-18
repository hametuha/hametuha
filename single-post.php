<?php get_header(); ?>

<?php if(have_posts()): while(have_posts()): the_post(); ?>

<article id="viewing-content" <?php post_class() ?> itemscope itemtype="http://schema.org/Article">

    <div id="content-wrapper">
        <?php if( has_post_thumbnail() ): ?>

            <div class="single-post-thumbnail text-center">
                <?php the_post_thumbnail('large', array('item-prop' => 'image')); ?>
            </div>

        <?php elseif( has_pixiv() ): ?>

            <div class="single-post-thumbnail pixiv text-center">
                <?php pixiv_output(); ?>
            </div>

        <?php endif; ?>

        <div class="work-wrapper container">

            <div class="work-meta row">

                <div class="inner">

                    <h1 itemprop="name"><?php the_title(); ?></h1>

                    <?php the_series('<p class="series">', '</p>'); ?>

                    <p class="author">
                        <a href="#post-author"><?php the_author(); ?></a>
                    </p>

                    <p class="genre">
                        <?php the_category(' ');?>
                    </p>

                    <p class="length">
                        <?php the_post_length('<span>', '</span>', '-');?>文字
                    </p>

                    <?php if( has_excerpt() ): ?>
                        <div class="desc">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- //.post-meta-single -->


            <div class="work-content row" itemprop="text">
                <?php the_content(); ?>

                <?php if( is_last_page() ):?>
                    <p id="work-end-ranker" class="text-center" data-post="<?php the_ID() ?>"><i class="icon-ha"></i></p>
                <?php endif; ?>

                <?php wp_link_pages(array('before' => '<p class="link-pages">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>')); ?>
            </div><!-- //.single-post-content -->

            <p class="text-center pub-date">
                <span itemprop="dateCreated"><?php the_time('Y年n月j日') ?></span>公開
            </p>

            <?php if( is_series() ): ?>
                <p class="series-pager-title text-center">
                    作品集『<?php the_series(); ?>』より
                </p>
                <ul class="pager post-pager">
                    <?php prev_series_link('<li class="previous">'); ?>
                    <?php next_series_link('<li class="next">'); ?>
                </ul>
            <?php endif; ?>

            <div id="single-post-footernote" class="row">
                &copy; <span itemprop="copyrightYear"><?php the_time('Y'); ?></span> <?php the_author(); ?>
            </div>

            <div id="post-author" class="row author-container">
                <?php get_template_part('parts/author') ?>
            </div>

	        <div id="post-share" class="share-panel text-center">
		        <h4>この作品をシェアする</h4>
		        <?php hametuha_share( get_the_title(), get_permalink() ) ; ?>
		        <div class="input-group">
			        <span class="input-group-addon">URL</span>
			        <input class="form-control" id="post-short-link" type="text" value="<?= esc_attr(wp_get_shortlink()); ?>" onclick="this.select();" />
		        </div>
		        <?php if( get_current_user_id() == get_the_author_meta('ID') ):?>
			        <div class="alert alert-info">
				        これはあなたの作品です。積極的に宣伝し、たくさんの読者に読んでもらいましょう。
				        いいねやTwitterでの宣伝など、周囲に疎まれる限界まで宣伝してください。
			        </div>
		        <?php endif; ?>
	        </div><!-- #post-share -->


	        <p class="finish-nav">
		        読み終えたらレビューしてください<br />
		        <i class="icon-point-down"></i>
	        </p>

        </div><!-- // .work-wrapper -->


    </div><!-- //#content-wrapper -->



    <div id="reading-nav">
        <div class="container">
            <div id="slider"></div>
            <a href="#" class="reset-viewer"><i class="icon-close3"></i></a>
        </div>
    </div>

    <div id="finish-wrapper" class="overlay-container">
        <div class="container">

	        <h3>リストに追加する</h3>

	        <p class="text-muted">
		        リスト機能とは、気になる作品をまとめておける機能です。公開と非公開が選べますので、
		        短編集として公開したり、お気に入りのリストとしてこっそり楽しむこともできます。
	        </p>

	        <hr />

	        <?php if( is_user_logged_in() ): ?>

		        <form class="list-save-manager" method="post" action="<?= esc_url(Hametuha\Rest\ListCreator::save_link(get_the_ID())) ?>">
			        <?php wp_nonce_field('list-save') ?>
					<div id="list-changer">
				        <?php
				            $lists = new WP_Query([
					            'post_type' => 'lists',
					            'post_author' => get_current_user_id(),
					            'post_status' => ['publish', 'private'],
					            'orderby' => 'post_title',
					            'order' => 'DESC',
				            ]);
				            $current_post_id = get_the_ID();
				            if( $lists->have_posts() ){
					            $html = <<<HTML
								<div class="checkbox">
									<label>
					                    <input type="checkbox" name="lists[]" value="%d"%s>
					                    %s
									</label>
								</div>
HTML;
					            while($lists->have_posts()){
						            $lists->the_post();
						            printf($html, get_the_ID(),
							            checked(in_lists($current_post_id, get_the_ID()), true, false),
							            esc_html( ($post->post_status == 'publish' ? '公開　: ' : '').get_the_title() )
						            );
					            }
					            wp_reset_postdata();
				            }
				        ?>

					</div>

			        <p class="text-muted">リストを選んで保存ください。<strong><?php the_title() ?></strong>がリストに追加されます。リストは新たに作成することもできます。</p>

			        <div class="row">

				        <div class="col-xs-6 text-left">
							<input type="submit" class="btn btn-primary" value="変更を保存" />
				        </div>

				        <div class="col-xs-6 text-right">
					        <a class="btn btn-success list-creator" title="リストを作成する" href="<?= esc_url(Hametuha\Rest\ListCreator::form_link()) ?>"><i class="icon-plus-circle"></i> リストを作成</a>
				        </div>

			        </div>

		        </form>

	        <?php else: ?>

		        <p class="alert alert-warning">
			        リスト機能を利用するには<a class="alert-link" href="<?= wp_login_url(get_permalink()) ?>">ログイン</a>する必要があります。
		        </p>

			<?php endif; ?>


        </div>
    </div>


    <div id="reviews-wrapper" class="overlay-container">
        <div class="container">
	        <div>
		        <?php Hametuha\Ajax\Feedback::form('parts/feedback', 'you', ['id' => 'review-form']) ?>
	        </div>

	        <hr />

            <?php Hametuha\Ajax\Feedback::all_review(get_the_ID()) ?>
        </div>
    </div>

    <div id="tags-wrapper" class="overlay-container">
        <div id="post-tags" class="container">
            <?php Hametuha\Rest\UserTag::view('parts/feedback', 'tag') ?>
        </div><!-- //#post-tags -->
    </div>

    <div id="comments-wrapper" class="overlay-container">
        <div id="post-comment" class="container">
            <?php comments_template(); ?>
        </div>
    </div>


    <a class="overlay-close reset-viewer" href="#">
        <i class="icon-esc"></i> 作品に戻る
    </a>

</article>

<?php endwhile; endif; ?>



<footer id="footer-single">
    <nav class="container">
        <ul class="clearfix">
            <li>
                <a href="#reading-nav">
                    <i class="icon-book"></i><br />
                    <span>移動</span>
                </a>
            </li>
            <li>
                <a href="#finish-wrapper">
                    <i class="icon-books"></i><br />
                    <span>リスト</span>
                </a>
            </li>
            <li class="finished-container">
                <a href="#reviews-wrapper">
                    <i class="icon-star6"></i><br />
                    <span>レビュー</span>
                </a>
            </li>
            <li>
                <a href="#comments-wrapper">
                    <i class="icon-bubbles"></i><br />
                    <span>コメント</span>
                </a>
            </li>
            <li>
                <a href="#tags-wrapper">
                    <i class="icon-tags"></i><br />
                    <span>タグ</span>
                </a>
            </li>
        </ul>
    </nav><!-- //.container -->
</footer>

<?php get_footer('single'); ?>