<?php if( is_ranking('best') ): ?>

    <ul class="nav nav-pills">
        <li class="<?= !get_query_var('category_name') ? ' active' : '' ?>"><a href="<?= home_url('/best/', 'http'); ?>">全体ランキング</a></li>
        <?php foreach( get_categories() as $cat ){
            printf('<li class="%s"><a href="%s">%s部門</a></li>', get_query_var('category_name') == $cat->slug ? 'active' : '' ,home_url('/best/'.$cat->slug.'/', 'http'), esc_html($cat->name));
        } ?>
    </ul>

<?php elseif( is_ranking('yearly') || is_ranking('top') ): ?>
    <?php $year = is_ranking('top') ? date_i18n('Y') : get_query_var('year'); ?>

    <table class="calendar-year">
        <caption><?= $year ?>年月別ランキング</caption>
        <tbodY>
            <?php for( $i = 0; $i < 2; $i++ ): ?>
            <tr>
                <?php for($l = 1; $l <= 6; $l++): $month = $i * 6 + $l; ?>
                    <td>
                        <?php if( $year >= date_i18n('Y') && $month > date_i18n('n') ): ?>
                            <span><?= $month ?>月</span>
                        <?php else: ?>
                            <a href="<?= home_url(sprintf('/ranking/%d/%02d/', $year, $month)) ?>"><?= $month ?>月</a>
                        <?php endif; ?>
                    </td>
                <?php endfor;?>
            </tr>
            <?php endfor; ?>
        </tbodY>
    </table>

    <ul class="pager post-pager">
        <li class="previous">
            <?php if( $year - 1 >= 2014 ): ?>
                <a href="<?= home_url('/ranking/'.($year - 1).'/') ?>">&laquo; <?= $year - 1 ?>年間ランキング</a>
            <?php endif; ?>
        </li>
        <li class="next">
            <?php if( $year + 1 <= date_i18n('Y') ): ?>
            <a href="<?= home_url('/ranking/'.($year + 1).'/') ?>"><?= $year + 1 ?>年間ランキング &raquo;</a>
            <?php endif; ?>
        </li>
    </ul>

<?php else: ?>
    <?php
        $year = get_query_var('year');
        $month = get_query_var('monthnum');
        $day = get_query_var('day');
        $monthnum = ($year * 12) + $month;
        $prev = $monthnum - 1;
        $next = $monthnum + 1;
        $prev_year = $prev % 12 ? $year : $year - 1;
        $next_year = $monthnum % 12 ? $year : $year + 1;
        $calc_starts = strtotime('2014-08-23 00:00:00');
        $week = ['月', '火', '水', '木', '金', '土', '日', '週間'];
        $start_of_month = sprintf('%d-%02d-01 00:00:00', $year, $month);
        $limit_of_month = date_i18n("t", mktime(0, 0, 0, $month, 1, $year));
        $start_of_date = array_search(date_i18n('D', strtotime($start_of_month)), $week) + 1;
        $starting = false;
        $ended = false;
        $out_date = 0;
    ?>

    <table class="calendar-year">
        <caption>
            <a href="<?= home_url('/ranking/'.$year.'/') ?>"><?= $year ?>年</a>
            <?php printf('%d月', $month) ?>
        </caption>
        <thead>
            <tr>
                <?php foreach( $week as $date): ?>
                    <th><?= $date ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php for($i = 0; $i < 5; $i++): ?>
        <tr>
            <?php for($l = 1; $l <= 7; $l++): ?>
                <?php
                    if( !$starting && $l === $start_of_date && !$ended ){
                        $starting = true;
                    }elseif( $starting && $out_date >= $limit_of_month ){
                        $ended = true;
                    }
                    $unfixed = true;
                ?>
                <td>
                        <?php
                            if( $starting && !$ended ):
                                $out_date++;
                                $calc_date = current_time('timestamp') - 60 * 60 * 72;
                                $date_to_ouput = strtotime(sprintf('%d/%02d/%02d', $year, $month, $out_date));
                                if( $out_date == get_query_var('day') ):
                                    ?>
                                    <span class="on"><?= $out_date ?></span>
                                    <?php
                                elseif( $date_to_ouput > $calc_date || $date_to_ouput < $calc_starts ) :
                                    ?>
                                    <span><?= $out_date ?></span>
                                <?php
                                    else:
                                    $unfixed = false;
                                ?>
                                    <a href="<?= home_url(sprintf('/ranking/%d/%02d/%02d/', $year, $month, $out_date)) ?>"><?= $out_date ?></a>
                                <?php endif; ?>
                        <?php else: ?>
                            &nbsp;
                        <?php endif; ?>
                </td>
                <?php if( $l == 7 ): ?>
                    <?php if( !$unfixed ): ?>
                        <td><a href="<?= home_url(sprintf('/ranking/weekly/%04d%02d%02d/', $year, $month, $out_date)) ?>"><i class="icon-trophy-star"></i></a></td>
                    <?php else: ?>
                        <td><span><i class="icon-trophy2"></i></span></td>
                    <?php endif;  ?>
                <?php endif; ?>
            <?php endfor; ?>
        </tr>
        <?php if( $ended ) break; endfor; ?>
        </tbody>
    </table>


    <ul class="pager post-pager">
        <li class="previous">
            <a href="<?= home_url(sprintf('/ranking/%d/%02d/', $prev_year, ($prev % 12 ?: 12))) ?>">
                &laquo; <?= $prev_year ?>年<?= $prev % 12 ?: 12 ?>月
            </a>
        </li>
        <li class="next">
            <?php if( $next <= ((int)date_i18n('Y') * 12) + (int)date_i18n('n')  ): ?>
            <a href="<?= home_url(sprintf('/ranking/%d/%02d/', $next_year, ($next % 12 ?: 12))) ?>">
                <?= $next_year ?>年<?= $next % 12 ?: 12 ?>月 &raquo;
            </a>
            <?php endif; ?>
        </li>
    </ul>

<?php endif; ?>

<?php if( !is_ranking('top') ): ?>
<p>
    <a class="btn btn-lg btn-primary btn-block" href="<?= home_url('/ranking') ?>">ランキングトップへ</a>
</p>
<?php endif; ?>

<hr />

<div id="ranking-detail" class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">ランキングの仕組み</h2>
    </div>
    <div class="panel-body">

        <h3><i class="icon-certificate"></i> 基本原則</h3>
        <ul>
            <li>ランキングは任意の期間でページビュー（以下PV）が多い順に決定されます。</li>
            <li>PVとは、そのページが表示された回数です。これにより「その作品を読もうとした人」の数を擬似的に表現しています。</li>
            <li>この基本原則は変わることがあります。</li>
        </ul>

        <h3><i class="icon-database"></i> データ収集の仕組み</h3>
        <ul>
            <li>Google Analyticsという計測ツールを利用し、誰かが作品ページを開いたときにPVを取得してします。</li>
            <li>現在はPVであるため、同じ人が何回も同じページを開いたときもカウントされます。<small>（※今後は改善する予定です）</small></li>
            <li>毎日深夜に前日のPVを記録し、集計用データとして保存します。</li>
            <li>集計中のランキングには「現在集計中」と表示されます。確定したランキングには「確定」と表示されます。</li>
        </ul>

        <h3><i class="icon-gift2"></i> おまけ</h3>
        <ul>
            <li>2014年9月現在、毎週一回木曜日にランキングを作成し、1位になった作品にAmazonギフト券500円をプレゼントしています。<small>（※これは暫定的なキャンペーンです）</small></li>
        </ul>
    </div>
</div>
