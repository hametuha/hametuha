<?php get_header('login') ?>

    <div id="login-body">

        <?php do_action(\WPametu\Http\PostRedirectGet::PUBLIC_HOOK); ?>

        <div class="form-group">
            <label>現在のプロフィール写真</label>
            <p class="gravatar text-center">
                <?= get_avatar(get_current_user_id(), 240) ?>
            </p>

            <?php if( $uploaded ): ?>
                <p class="text-success text-center">
                    <i class="icon-checkmark"></i> アップロード画像を使用中
                </p>
            <?php elseif( $has_gravatar ): ?>
                <p class="text-success text-center">
                    <i class="icon-checkmark"></i> Gravatarが有効
                </p>
            <?php else: ?>
                <p class="text-danger text-center">
                    <i class="icon-close"></i> 画像がアップロードされていません！
                </p>
            <?php endif; ?>

        </div>

        <form id="upload-picture-form" method="post" action="<?= $upload_action ?>" enctype="multipart/form-data">
            <?= $nonce ?>

            <div class="form-group has-feedback">
                <label for="new_picture">新しい画像のアップロード</label>
                <div class="input-group pseudo-uploader">
                    <input type="text" class="form-control" readonly placeholder="画像ファイル名">
                    <span class="input-group-btn">
                    <button class="btn btn-default" type="button">選択</button>
                    </span>
                </div><!-- /input-group -->
                <input class="hidden" type="file" id="new_picture" name="new_picture"  />
                <p class="help-block">
                    アップロードできるのは<?= $max_size ?>までです。
                    すでにアップロード済みの場合は上書きされます。
                </p>
            </div>

            <p>
                <input type="submit" class="btn btn-primary btn-block btn-lg" value="ファイルをアップロード">
            </p>

        </form>

        <?php if( $uploaded ): ?>

        <hr />

        <form id="delete-picture-form" method="post" action="<?= $delete_action ?>">
            <?= $nonce ?>

            <div class="checkbox">
                <label>
                    <input class="form-unlimiter" type="checkbox" name="delete_picture" value="1" />
                    現在アップロードされている画像を削除する
                </label>
                <p class="help-block">
                    アップロードされている写真を削除すると、Gravatarまたは画像なしになります。
                </p>
            </div>
            <p>
                <input type="submit" class="btn btn-danger btn-block btn-lg" data-confirm="削除してしまってよろしいですか？　この操作は取り消せません。" disabled value="削除">
            </p>


        </form>

        <?php endif; ?>

    </div><!--  -->

    <p class="text-center">
        <a href="<?= admin_url('profile.php') ?>">プロフィール編集へ戻る</a>
    </p>

<?php get_footer('login') ?>
