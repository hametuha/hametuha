<?php get_header('login') ?>

    <div id="login-body">

        <div class="alert alert-info">
            <p>
                ログイン情報を変更しようとしています。これは重要な情報なので、注意書きを理解したうえで行ってください。
            </p>
        </div>

        <form id="change-login-form" method="post" action="<?= $action ?>">
            <?= $nonce ?>

            <div class="form-group">
                <label>現在のログイン名</label>
                <input class="form-control" type="text" readonly value="<?= esc_attr($login_name) ?>" />
            </div>

            <div class="form-group has-feedback">
                <label for="login_name">新しいログイン名</label>
                <input class="form-control" type="text" id="login_name" name="login_name" data-check="<?= $check_url ?>" value="" autocomplete="off" />
                <?php input_icon() ?>
                <p class="help-block">
                    ログイン名は半角英数字および半角スペースをはじめとした各種半角記号が利用できます。
                    <span class="help-text help-success">このログイン名は利用できます。</span>
                    <span class="help-text help-error">このログイン名は利用できません。</span>
                </p>
            </div>

            <div class="form-group">
                <label for="login_nicename">URL表示</label>
                <div class="input-group">
                    <span class="input-group-addon"><?= str_replace('http://', '', home_url('/author/', 'http')) ?></span>
                    <input type="text" class="form-control" id="login_nicename" readonly value="<?= $nicename ?>">
                </div>
                <p class="help-block">
                    作品を公開した場合、あなたのプロフィールページはこのURLになります。
                </p>
            </div>

            <div class="form-group">
                <ul class="notice-list">
                    <li>ログイン名は重要な情報なので、変更後にはもう一度ログインしなおす必要があります。</li>
                    <li>他のユーザーが使用しているログイン名は利用できません。</li>
                    <li>すでに作品を公開されている方は、作品一覧URLが以下のように変更になります。</li>
                </ul>

                <p class="text-muted text-center">
                    <?= home_url('/author/', 'http') ?><code>baudelaire</code>/<br />
                    ↓<br />
                    <?= home_url('/author/', 'http') ?><code>rimbaud</code>/
                </p>

            </div>

            <p>
                <input type="submit" class="btn btn-primary btn-block btn-lg" disabled value="ログイン名を変更">
            </p>

        </form>

    </div><!--  -->

    <p class="text-center">
        <a href="<?= admin_url('profile.php') ?>">プロフィール編集へ戻る</a>
    </p>

<?php get_footer('login') ?>
