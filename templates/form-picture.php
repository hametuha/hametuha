<?php

/** @var array $pictures */

get_header('login')
?>

    <div id="login-body" class="profile-pic-editor">

        <?php do_action(\WPametu\Http\PostRedirectGet::PUBLIC_HOOK); ?>

	    <h3><i class="icon-upload"></i> 画像のアップロード</h3>

        <div class="form-group">
            <label>現在のプロフィール写真</label>
            <p class="gravatar text-center">
                <?= get_avatar(get_current_user_id(), 300) ?>
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
                </p>
            </div>

            <p>
                <input type="submit" class="btn btn-primary btn-block btn-lg" value="ファイルをアップロード">
            </p>

        </form>

        <?php if( $pictures ): ?>

        <hr />

	        <h3><i class="icon-stack-picture"></i> アップロード済み画像</h3>

		    <div id="pic-file-list" class="row">
			    <?php foreach( $pictures as $picture ): ?>
				    <div class="col-xs-3">
					    <input type="radio" name="picture" id="pic-<?= $picture['attachment_id'] ?>" value="<?= $picture['attachment_id'] ?>" />
					    <label for="pic-<?= $picture['attachment_id'] ?>">
						    <?= $picture['img'] ?>
					    </label>
					    <?php if( $picture['attachment_id'] == $selected ): ?>
					    <span class="label label-success">使用中</span>
					    <?php endif; ?>
				    </div>
		        <?php endforeach; ?>
		    </div><!-- //#pic-file-list -->

	        <div class="row">
		        <div class="col-xs-5">
			        <form id="delete-picture-form" method="post" action="<?= $delete_action ?>">
				        <?= $nonce ?>
				        <input type="hidden" class="attachment_id_holder" name="delete_picture" id="delete_picture" value="" />
				        <input type="submit" class="btn btn-danger btn-block" data-confirm="削除してしまってよろしいですか？　この操作は取り消せません。" value="削除">
				        <p class="text-muted">
					        選択した画像を削除します。
				        </p>
			        </form>
		        </div>
		        <div class="col-xs-2"></div>
		        <div class="col-xs-5">
			        <form id="select-picture-form" method="post" action="<?= $select_action ?>">
				        <?= $nonce ?>
				        <input type="hidden" class="attachment_id_holder" name="select_picture" id="select_picture" value="" />
				        <input type="submit" class="btn btn-success btn-block" value="変更">
				        <p class="text-muted">
					        選択した画像をプロフィール画像にします。
				        </p>

			        </form>
		        </div>
	        </div>


        <?php endif; ?>

    </div><!--  -->

    <p class="text-center">
        <a href="<?= admin_url('profile.php') ?>">プロフィール編集へ戻る</a>
    </p>

<?php get_footer('login') ?>
