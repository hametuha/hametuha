<div class="author-profile" itemscope itemprop="author" itemtype="http://schema.org/Person">
	<?= get_avatar( get_the_author_meta( 'ID' ), 160, '', get_the_author(), [ 'itemprop' => 'image' ] ) ?>
	<h3>
		<span itemprop="name"><?php the_author() ?></span>
		<small>
			<span itemprop="affiliation">破滅派</span>
			<span><?= hametuha_user_role( get_the_author_meta( 'ID' ) ) ?></span>
		</small>
	</h3>
	<div class="author-desc">
		<?= wpautop( esc_html( get_the_author_meta( 'description' ) ) ) ?>
	</div>
	<ul class="list-inline">
		<li><i class="icon-calendar"></i> <?= hametuha_passed_time( get_the_author_meta( 'user_registered' ) ) ?>登録</li>
		<li>
			<i class="icon-quill3"></i> <?= number_format( get_author_work_count() ) ?>作品
		</li>
	</ul>

	<a class="btn btn-default btn-block btn--author"
	   href="<?= home_url( sprintf( '/doujin/detail/%s/', rawurlencode( get_the_author_meta( 'nicename' ) ) ) ) ?>"
	   itemprop="url">
		詳しく見る
	</a>

</div><!-- //.author-profile -->
