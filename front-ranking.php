<?php get_header() ?>

<?php get_header('breadcrumb') ?>

<div class="container archive">

    <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-xs-12 col-sm-9 main-container">

            <?php get_template_part('parts/jumbotron', 'ranking'); ?>


            <!-- 先週のランキング -->
            <?php
                $prev_thursday = strtotime('Previous Thursday', current_time('timestamp'));
                $sunday = strtotime('Previous Sunday', $prev_thursday);
                $monday = strtotime('Previous Monday', $sunday);
                $latest_week = new WP_Query([
                    'ranking' => 'last_week',
                    'posts_per_page' => 3,
                ]);
                if( $latest_week->have_posts() ):
            ?>
                <h2><i class="icon-calendar4"></i> 最新週間ランキング <span class="label label-success">確定済み</span></h2>
                <p><?= date_i18n('Y年n月j日（D）', $monday) ?>〜<?= date_i18n('Y年n月j日（D）', $sunday) ?></p>
                <ol class="archive-container media-list">
                    <?php while($latest_week->have_posts()): $latest_week->the_post(); ?>
                        <?php get_template_part('parts/loop', 'ranking') ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ol>
                <p>
                    <a class="btn btn-default btn-lg btn-block" href="<?= home_url('/ranking/weekly/'.date_i18n('Ymd/', $sunday)) ?>">最新週間ランキングを見る</a>
                </p>

                <hr />

            <?php endif; ?>

            <!-- 直近のランキング -->
            <?php
                $latest_date = strtotime('4 days ago', current_time('timestamp'));
                $latest_day = new WP_Query([
                    'ranking' => 'daily',
                    'year' => date_i18n('Y', $latest_date),
                    'monthnum' => date_i18n('m', $latest_date),
                    'day' => date_i18n('d', $latest_date),
                    'posts_per_page' => 3,
                ]);
                if( $latest_day->have_posts() ):
            ?>
                <h2><i class="icon-new"></i> <?= date_i18n('Y年n月j日（D）', $latest_date) ?>のランキング</h2>
                <ol class="archive-container media-list">
                    <?php while($latest_day->have_posts()): $latest_day->the_post(); ?>
                        <?php get_template_part('parts/loop', 'ranking') ?>
                    <?php endwhile; wp_reset_postdata() ?>
                </ol>
                <p>
                    <a class="btn btn-default btn-lg btn-block" href="<?= home_url('/ranking'.date_i18n('/Y/m/d/', $latest_date)) ?>">
                        <?= date_i18n('Y年n月j日（D）', $latest_date) ?>のランキングを見る
                    </a>
                </p>

                <hr />
            <?php endif; ?>


            <!-- 今月のランキング -->
            <?php
                $this_month = date_i18n('j') >= 5 ? current_time('timestamp') : current_time('timestamp') - (60 * 60 * 24 * 5);
                $latest_month = new WP_Query([
                    'ranking' => 'monthly',
                    'year' => date_i18n('Y', $this_month),
                    'monthnum' => date_i18n('m', $this_month),
                    'posts_per_page' => 3,
                ]);
                if( $latest_month->have_posts() ):
                ?>
                <h2><i class="icon-calendar5"></i> <?= date_i18n('Y年n月', $this_month) ?>のランキング</h2>
                <ol class="archive-container media-list">
                    <?php while($latest_month->have_posts()): $latest_month->the_post(); ?>
                        <?php get_template_part('parts/loop', 'ranking') ?>
                    <?php endwhile; wp_reset_postdata() ?>
                </ol>
                <p>
                    <a class="btn btn-default btn-lg btn-block" href="<?= home_url('/ranking'.date_i18n('/Y/m/', $this_month)) ?>">
                        <?= date_i18n('Y年n月', $this_month) ?>のランキングを見る
                    </a>
                </p>

                <hr />
            <?php endif; ?>

            <?php get_template_part('parts/ranking', 'calendar') ?>

        </div><!-- //.main-container -->

        <?php contextual_sidebar() ?>

    </div><!-- // .offcanvas -->

</div><!-- //.container -->

<?php get_footer(); ?>