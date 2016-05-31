<?php get_header( 'meta' ); ?>
<header id="header" class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">

		<div class="navbar-header">
			<a class="navbar-toggle" href="#header-navigation">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>

			<?php if ( ! is_hamenew() ) : ?>
			<button class="navbar-toggle navbar-write write-panel-btn">
				<i class="icon-quill"></i>
			</button>
			<?php endif; ?>

		</div>

		<?php if ( is_hamenew() ) : ?>
			<a class="logo" rel="home" href="<?= home_url( '/news' ); ?>">
				<img src="<?= get_template_directory_uri() ?>/assets/img/hamenew-logo.png" alt="はめにゅー" width="90" height="50" />
			</a>
		<?php else : ?>
			<a class="logo" rel="home" href="<?= home_url( '/' ); ?>">
				<i class="icon-hametuha"></i><span>破滅派</span>
			</a>
		<?php endif; ?>

		<?php if ( ! is_hamenew() ) : ?>
		<?php get_template_part( 'templates/header/user' ) ?>
		<?php endif; ?>

	</div><!-- .container -->
</header>

<nav id="header-navigation">
	<ul>
		<li>
			<a rel="home" href="<?= home_url() ?>">
				<i class="icon-home"></i> ホーム
			</a>
		</li>
		<li>
			<a href="<?= get_post_type_archive_link( 'news' ) ?>">
				<i class="icon-newspaper"></i> はめにゅー
			</a>
			<ul>
				<?php foreach ( get_terms( 'genre' ) as $term ) : ?>
				<li>
					<a href="<?= get_term_link( $term ) ?>">
						<?= esc_html( $term->name ) ?><small><?= number_format_i18n( $term->count ) ?></small>
					</a>
				</li>
				<?php endforeach; ?>
				<li>
					<span><i class="icon-tag5"></i> キーワード</span>
					<ul>
						<?php foreach ( get_terms( 'nouns' ) as $term ) : ?>
						<li>
							<a href="<?= get_term_link( $term ) ?>">
								<?= esc_html( $term->name ) ?><small><?= number_format_i18n( $term->count ) ?></small>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</li>
			</ul>
		</li>
		<li class="mm-divider">作品</li>
		<li>
			<a href="<?= get_permalink( get_option( 'page_for_posts' ) ) ?>">全ジャンル</a>
			<ul>
				<?php foreach ( get_categories( [ 'parent' => 0 ] ) as $cat ) : ?>
					<li>
						<a href="<?= get_category_link( $cat ) ?>">
							<?= esc_html( $cat->name ) ?>
							<small><?= number_format_i18n( $cat->count ) ?>作品</small>
						</a>
					</li>
				<?php endforeach; ?>
				<li>
					<span>すべてのタグ</span>
					<ul>
						<?php foreach ( get_tags() as $tag ) : ?>
							<li>
								<a href="<?= get_tag_link( $tag ); ?>">
									<?= esc_html( $tag->name ) ?>
									<small><?= number_format_i18n( $tag->count ); ?>件</small>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			</ul>
		</li>
		<li>
			<a href="<?= home_url( '/ranking/' ) ?>">
				<i class="icon-crown"></i> 厳粛なランキング
			</a>
		</li>
		<li>
			<a href="<?= home_url( '/recommends/' ) ?>">
				<i class="icon-heart"></i> 編集部おすすめ</a>
		</li>
		<li>
			<a href="<?= home_url( '/kdp/' ) ?>">
				<i class="icon-amazon"></i> Kindle電子書籍
			</a>
		</li>
		<li>
			<a href="<?= get_post_type_archive_link( 'series' ) ?>">
				<i class="icon-stack"></i> 連載
			</a>
		</li>
		<li class="mm-divider">コミュニティ</li>
		<li>
			<a href="<?= home_url( '/authors/' ); ?>">
				<i class="icon-user"></i> 執筆者一覧
			</a>
		</li>
		<li>
			<a href="<?= get_post_type_archive_link( 'thread' ); ?>">
				<i class="icon-stack-list"></i> なんでも掲示板
			</a>
			<ul>
				<?php foreach ( get_terms( 'topic' ) as $term ) : ?>
				<li>
					<a href="<?= get_term_link( $term ) ?>">
						<?= esc_html( $term->name ) ?>
						<small><?= number_format_i18n( $term->count ); ?>件</small>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</li>
		<li>
			<a href="<?= get_post_type_archive_link( 'lists' ) ?>">
				<i class="icon-library2"></i> みんなで作るリスト
			</a>
		</li>
		<li>
			<span>
				<i class="icon-bubble"></i> 読者によるレビュー
			</span>
			<ul>
				<?php foreach ( get_terms( 'review', [ 'hide_empty' => false ] ) as $term ) : ?>
					<li>
						<a href="<?= home_url( "/reviewed/{$term->term_id}/" ) ?>"><?= esc_html( $term->name ) ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</li>
		<li>
			<a href="<?= get_post_type_archive_link( 'ideas' ) ?>">
				<i class="icon-lamp"></i> 作品のアイデア
			</a>
			<ul>
				<?php foreach ( get_tags( [
					'meta_query' => [
						[
							'field' => 'tag_type',
						    'value' => 'idea',
						],
					],
				] ) as $tag ) : ?>
				<li>
					<a href="<?= get_tag_link( $tag ); ?>">
						<?= esc_html( $tag->name ) ?>
						<small><?= number_format_i18n( $tag->count ); ?>件</small>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</li>
		<li>
			<a href="<?= get_post_type_archive_link( 'anpi' ) ?>">
				<i class="icon-thumbs-up"></i> 同人の安否情報
			</a>
		</li>

		<li>
			<a href="<?= get_post_type_archive_link( 'announcement' ) ?>">
				<i class="icon-bullhorn"></i> 告知
			</a>
		</li>
		<li class="mm-divider">About</li>
		<li>
			<a href="<?= home_url( '/about/' ); ?>">
				<i class="icon-ha"></i> 破滅派とは
			</a>
			<ul>
				<li>
					<a href="<?= home_url( '/inquiry/' ) ?>">
						<i class="icon-envelop"></i> お問い合わせ
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/sanka/' ); ?>">
						<i class="icon-enter"></i> 参加する
					</a>
				</li>
				<li>
					<a href="<?= home_url( '/merumaga/' ); ?>">
						<i class="icon-mail"></i> メルマガ購読
					</a>
				</li>
				<li>
					<a href="<?= get_post_type_archive_link( 'newsletters' ) ?>">
						メルマガバックナンバー
					</a>
				</li>
			</ul>
		</li>
		<li>
			<a href="<?= get_post_type_archive_link( 'faq' ); ?>">
				<i class="icon-question2"></i> よくある質問
			</a>
			<ul>
				<?php foreach ( get_terms( 'faq_cat' ) as $term ) : ?>
					<li>
						<a href="<?= get_term_link( $term ) ?>">
							<?= esc_html( $term->name ) ?>について
							<small><?= number_format_i18n( $term->count ) ?>件</small>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</li>

	</ul>
</nav>
<!-- // .navbar-collapse -->
