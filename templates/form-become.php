<?php get_header('login') ?>

    <div id="login-body">

        <div class="alert alert-info">
            <p>
                こんにちは、<strong><?= esc_html($name) ?></strong>さん。あなたは同人になろうとしています。<br />
                以下の注意事項と<a class="alert-link" href="<?= home_url('/contract/') ?>" target="_blank">利用規約</a>をご覧になり、
                同意の上で「同人になる」ボタンをクリックしてください。
            </p>
        </div>

        <form id="become-author-form" method="post" action="<?= $action ?>">
            <?= $nonce ?>

            <div class="form-group">
                <label>注意事項</label>
                <ul class="notice-list">
                    <li>一度同人になると、読者に戻ることはできません。</li>
                    <li>同人はプロフィール、名前などが公開されます。筆名などがそのままでもよいかどうか、<a href="<?= admin_url('profile.php') ?>">プロフィール編集</a>で検討してから同人になってください。</li>
                    <li>作品の著作権はあなたに所属します。と同時に、公開状態や削除するかどうかなどもあなたに委ねられます。注意して操作を行ってください。</li>
                </ul>
            </div>

            <div class="checkbox">
                <label>
                    <input class="form-unlimiter" type="checkbox" name="review_contract" value="1" />
                    利用規約に同意する
                </label>
            </div>

            <p>
                <input type="submit" class="btn btn-primary btn-block btn-lg" disabled value="同人になる">
            </p>

        </form>

    </div><!--  -->

    <p class="text-center">
        <a href="<?= admin_url('profile.php') ?>">プロフィール編集へ戻る</a>
    </p>

<?php get_footer('login') ?>
