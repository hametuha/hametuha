<?php if( is_ranking('yearly') ): ?>
    <?php $year = get_query_var('year'); ?>

    <table class="calendar-year">
        <caption><?= $year ?>年</caption>
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
        $monthnum = $year * 12 + $month;
        $prev = $monthnum - 1;
        $next = $monthnum + 1;
        $week = ['月', '火', '水', '木', '金', '土', '日'];
        $start_of_month = sprintf('%d-%02d-01 00:00:00', $year, $month);
        $limit_of_month = date_i18n("t", mktime(0, 0, 0, $month, 1, $year));
        $start_of_date = array_search(date_i18n('D', strtotime($start_of_month)), $week) + 1;
        $starting = false;
        $ended = false;
        $out_date = 0;
    ?>

    <table class="calendar-year">
        <caption><a href="<?= home_url('/ranking/'.$year.'/') ?>"><?= $year ?>年</a></caption>
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
                ?>
                <td>
                    <?php if( $starting && !$ended): $out_date++; ?>
                        <?php if( current_time('timestamp') - strtotime(sprintf('%d/%02d/%02d', $year, $month, $out_date)) < 60 * 60 * 48 ): ?>
                            <span><?= $out_date ?></span>
                        <?php else: ?>
                            <a href="<?= home_url(sprintf('/ranking/%d/%02d/%02d/', $year, $month, $out_date)) ?>"><?= $out_date ?></a>
                        <?php endif; ?>
                    <?php else: ?>
                        &nbsp;
                    <?php endif; ?>
                </td>
            <?php endfor; ?>
        </tr>
        <?php if( $ended ) break; endfor; ?>
        </tbody>
    </table>


    <ul class="pager post-pager">
        <li class="previous">
            <a href="<?= home_url(sprintf('/ranking/%d/%02d/', floor( $prev / 12 ), ($prev % 12))) ?>">
                &laquo; <?= floor( $prev / 12 ) ?>年<?= $prev % 12 ?>月
            </a>
        </li>
        <li class="next">
            <?php if( $next <= ((int)date_i18n('Y') * 12) + (int)date_i18n('n')  ): ?>
            <a href="<?= home_url(sprintf('/ranking/%d/%02d/', floor( $next / 12 ), ($next % 12))) ?>">
                <?= floor( $next / 12 ) ?>年<?= $next % 12 ?>月 &raquo;
            </a>
            <?php endif; ?>
        </li>
    </ul>

<?php endif; ?>