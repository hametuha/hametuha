<h3 class="text-center">みんなの反応</h3>

<?php the_post_rank($all_rating, $total) ?>

<?= $chart ?>

<p class="text-center text-muted">
    <small>破滅チャートとは<?php help_tip('破滅派読者が入力した感想を元に生成されるチャートです。赤いほど破滅度が高く、青いほど健全な作品です。');?></small>
</p>