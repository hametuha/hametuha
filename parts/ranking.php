
<div id="ranking-header">

    <h1>
        <?= ranking_title() ?>
        <?php if( is_fixed_ranking() ): ?>
        <span class="label label-success">確定済み</span>
        <?php endif; ?>
    </h1>

    <?php if( !is_fixed_ranking() ): ?>
        <div class="alert alert-info">
            <p><i class="icon-info"></i> このランキングは現在集計中です。順位は変動する可能性があります。</p>
        </div>
    <?php endif; ?>

    <p class="descrpition">
        ページビューを元に計算しています。ページビューを出すと悲しいかな？　と思いまして、
        出さないようにしています。
        <strong>※ 今後、集計方法の変更などを予定しています。</strong>
    </p>
</div>

