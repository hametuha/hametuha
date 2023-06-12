<?php
/**
 * 著者プロフィール、おもに投稿ページでのみ利用。
 */
$author_id = (int) get_the_author_meta( 'ID' );
?>
<div class="author-profile row" itemscope itemprop="author" itemtype="http://schema.org/Person">
	<div class="col-sm-3 col-xs-12 text-center">
		<?= get_avatar( $author_id, 160, '', get_the_author(), [ 'itemprop' => 'image' ] ) ?>
	</div>
	<div class="col-sm-9 col-xs-12">
		<h3>
			<span itemprop="name"><?php the_author() ?></span>
			<small>
				<span itemprop="affiliation">破滅派</span>
				<span><?= hametuha_user_role( $author_id ) ?></span>
			</small>
		</h3>
		<?php
		$desc     = get_the_author_meta( 'description' );
		$too_long = 100 < mb_strlen( $desc, 'utf-8' );
		?>
		<div class="author-desc<?= $too_long ? ' author-desc--long' : '' ?>">
			<?= wpautop( esc_html( $desc ) ) ?>
		</div>
		<ul class="list-inline">
			<li>
                <i class="icon-calendar"></i> <?= hametuha_passed_time( get_the_author_meta( 'user_registered' ) ) ?>登録
			</li>
			<li>
				<i class="icon-quill3"></i> <?= number_format( get_author_work_count() ) ?>作品
			</li>
		</ul>

		<div class="author-profile__actions">
            <a class="btn btn-default btn--author"
               href="<?= home_url( sprintf( '/doujin/detail/%s/', rawurlencode( get_the_author_meta( 'nicename' ) ) ) ) ?>"
               itemprop="url">
                詳しく見る
            </a>
            <?php hametuha_follow_btn( $author_id, false ); ?>
            <?php if ( hametuha_user_allow_contact( get_the_author_meta( 'ID' ) ) ) : ?>
                <a class="btn btn-success" href="<?= hametuha_user_contact_url() ?>">
                    問い合わせ
                </a>
            <?php endif; ?>
		</div>

	</div>


</div><!-- //.author-profile -->
