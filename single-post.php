<?php
$series = Hametuha\Model\Series::get_instance();
get_header(); ?>

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

                    <?php the_series('<p class="series">', sprintf('（全%s話）</p>', $series->get_total($post->post_parent))); ?>

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

            <?php if( ($should_hide = $series->should_hide()) ){
		        if( !current_user_can('edit_post', get_the_ID()) ){
		            add_filter('the_content', 'hametuha_series_hide', 100);
		        }else{
			        echo <<<HTML
					<div class="alert alert-info">
						<p>
			                この投稿は販売中のため閲覧制限がかかっていますが、あなたには編集権限があるので表示しています。
						</p>
					</div>
HTML;
		        }
            } ?>

            <div class="work-content row" itemprop="text">


                <?php the_content(); ?>

                <?php if( is_last_page() ):?>
                    <p id="work-end-ranker" class="text-center" data-post="<?php the_ID() ?>"><i class="icon-ha"></i></p>
                <?php endif; ?>

                <?php wp_link_pages(array('before' => '<p class="link-pages">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>')); ?>

            </div><!-- //.single-post-content -->

	        <?php get_template_part('parts/alert', 'kdp'); ?>

            <p class="text-center pub-date">
                <span itemprop="dateCreated"><?php the_time('Y年n月j日') ?></span>公開
            </p>

            <?php if( is_series() ): ?>
                <p class="series-pager-title text-center">
                    作品集『<?php the_series(); ?>』<?= $series->index_label() ?>（全<?= $series->get_total($post->post_parent) ?>話）
                </p>
                <ul class="pager post-pager">
                    <?= $series->prev('<li class="previous">'); ?>
                    <?= $series->next('<li class="next">'); ?>
                </ul>
            <?php endif; ?>

            <div id="single-post-footernote" class="row">
                &copy; <span itemprop="copyrightYear"><?php the_time('Y'); ?></span> <?php the_author(); ?>
            </div>

            <div id="post-author" class="row author-container">
                <?php get_template_part('parts/author') ?>
            </div>

	        <?php get_template_part('parts/share') ?>

	        <p class="finish-nav">
		        読み終えたらレビューしてください<br />
		        <i class="icon-point-down"></i>
	        </p>

	        <hr />

	        <?php get_template_part('parts/list', 'author') ?>

	        <?php
	            // Yarpp関連記事
		        if( function_exists('related_posts') ){
			        related_posts();
		        }
	        ?>

	        <div class="row row--recommend row--catNav">
		        <h3 class="recommend__header recommend_header--catNav">
			        <i class="icon-folder"></i><br />
			        ジャンルごとに見てみよう<br />
			        <small>どれも面白いよ</small>
		        </h3>
		        <?php $counter = 0; foreach( get_categories(['parent' => 0]) as $cat ): $counter++; ?>
		        <div class="col-xs-4 col-sm-2 recommend__item--catNav">
			        <a href="<?= get_category_link($cat) ?>" class="recommend__link--catNav<?php if( in_category($cat->term_id) ) echo ' current' ?>">
				        <?= esc_html($cat->name) ?><span class="badge"><?= $cat->count > 100 ? '99<sup>+</sup>' : intval($cat->count) ?></span>
			        </a>
		        </div>
		        <?php if( $counter >= 6 ) break; endforeach; ?>
	        </div>

	        <div class="row row--recommend row--search">
		        <h3 class="recommend__header recommend_header--catNav">
			        <i class="icon-search3"></i><br />
			        検索してみよう<br />
			        <small>どんな言葉があるかな？</small>
		        </h3>
		        <?php get_search_form() ?>
	        </div>

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
					            'my-content' => 'lists',
					            'post_type' => 'lists',
					            'post_author' => 0,
					            'post_status' => ['publish', 'private'],
					            'orderby' => 'post_title',
					            'order' => 'DESC',
					            'posts_per_page' => -1,
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