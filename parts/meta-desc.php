
<?php if( is_category() ): ?>


    <?= wpautop(category_description()) ?>


<?php elseif( is_tag() || is_tax() ): ?>

    <?= wpautop(term_description()) ?>

<?php elseif( is_search() ): ?>

    <?= wpautop('「'.get_search_query()."」で破滅派内を検索しました。") ?>

<?php elseif( is_ranking() ): ?>

    <?php if( is_ranking('best') ):  ?>
        <p>
            <span class="text-info">
                <i class="icon-info"></i> このランキングは2008年から現在までのものを毎日集計しています。
            </span>
        </p>
    <?php elseif( !is_fixed_ranking() ): ?>
        <p>
            <span class="text-warning">
                <i class="icon-info"></i> このランキングは現在集計中です。順位は変動する可能性があります。
            </span>
        </p>
    <?php endif; ?>

    <p class="description">
        ページビューを元に計算しています。ページビューを出すと悲しいかな？　と思いまして、
        出さないようにしています。
        <strong>※ 今後、集計方法の変更などを予定しています。</strong>
    </p>

<?php elseif( is_date() ): ?>

    <p>投稿作品を日付別に表示しています。</p>

<?php elseif( is_home() && 'latest' == get_query_var('pagename') ): ?>

	<p>投稿を新着順に表示しています。</p>

<?php elseif( is_singular('series') ): ?>

	<?php get_template_part('parts/meta', 'single'); ?>

<?php elseif(is_post_type_archive('announcement') || is_post_type_archive('faq') || is_post_type_archive('info') || is_post_type_archive('news') || is_post_type_archive('thread')): ?>

	<?= wpautop(get_post_type_object(get_post_type())->description) ?>

<?php endif; ?>
