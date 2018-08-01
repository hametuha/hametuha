<?php
/*
 * Template Name: ヘルプセンター
 */

get_header( 'faq' ) ?>

<section class="help-center-latest">

    <div class="container">

        <div class="col-xs-12 col-sm-8 col-sm-offset-2 text-right">

            <h2 class="help-center-latest-title">よくある質問</h2>
            <ul class="help-center-latest-list">
                <?php foreach ( hametuha_popular_faqs() as $faq ) : ?>
                    <li class="help-center-latest-item">
                        <a href="<?= the_permalink( $faq ) ?>">
                            <?= esc_html( get_the_title( $faq ) ) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>

</section>

<section class="help-center-topic">

<?php
    $terms = get_terms( [ 'taxonomy' => 'faq_cat' ] );
    if ( $terms && ! is_wp_error( $terms ) ) :
?>
<div class="container">

    <h2 class="text-center">トピック</h2>

    <?php
    $rows = [];
    $idx = 0;
    foreach ( $terms as $index => $term ) {
        $index = floor( $index / 4 );
        if ( ! isset( $rows[ $index ] ) ) {
            $rows[ $index ] = [];
        }
        $rows[ $index ][] = $term;
    }
    foreach ( $rows as $row ) :
    ?>
    <div class="row">
        <?php foreach ( $row as $term ) : ?>
        <div class="col col-xs-6 col-sm-3">
            <div class="thumbnail">
                <div class="caption">
                    <h3><?= esc_html( $term->name ) ?></h3>
                    <?php echo wpautop( esc_html( $term->description ) ); ?>
                    <p>
                        <a href="<?php echo get_term_link( $term ) ?>" class="btn btn-primary" role="button">すべてみる</a>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
</section>
<?php endif; ?>

<section class="help-center-misc">

    <h2 class="help-center-misc-title text-center">
        解決しませんでしたか？
    </h2>
    <p class="help-center-misc-lead text-muted text-center">
        問い合わせると解決することがあります。
    </p>

    <div class="container">
        <div class="col col-xs-12 col-sm-4">
            <div class="help-center-misc-item">
                <p class="text-center">
                    <i class="icon-users4"></i>
                </p>
                <h3 class="text-center">コミュニティ</h3>
                <p>
                    破滅派には<a href="<?= home_url( 'thread' ) ?>">掲示板</a>や<a href="https://hametuha.com/faq/what-is-slack/">Slack</a>などの同人同士で連絡が取れる場所があります。
                    お気軽にご利用ください。
                </p>
            </div>
        </div>
        <div class="col col-xs-12 col-sm-4">
            <div class="help-center-misc-item">
                <p class="text-center">
                    <i class="icon-hand"></i>
                </p>
                <h3 class="text-center">リクエスト</h3>
                <p>
                    「このような情報を書いてほしい」「これが知りたい」という方はお気軽にリクエストをください。
                </p>
            </div>
        </div>
        <div class="col col-xs-12 col-sm-4">
            <div class="help-center-misc-item">
                <p class="text-center">
                    <i class="icon-phone6"></i>
                </p>
                <h3 class="text-center">問い合わせ</h3>
                <p>
                    どうしても解決しない場合は<a href="<?= home_url( 'inquiry' ) ?>">お問い合わせ</a>からご連絡ください。
                    誠心誠意ご対応いたします。
                </p>
            </div>
        </div>
    </div>

</section>
<?php get_footer(); ?>
