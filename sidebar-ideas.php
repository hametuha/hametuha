<?php
$ideas = \Hametuha\Model\Ideas::get_instance();

?>
<div class="widget widget_sp_image">
	<h2 class="widget-title">他人の褌で相撲を取ろう！</h2>
	<div class="widget-content">

		<a href="<?= get_post_type_archive_link( 'ideas' ) ?>" class="widget_sp_image-link">
			<img src="<?= get_template_directory_uri() ?>/assets/img/banner/fundoshi.jpg" width="431" height="450"
			     alert="褌"/>
		</a>

		<div class="widget_sp_image-description">
			<p>
				現在、<a href="<?= get_post_type_archive_link( 'ideas' ) ?>"><?= number_format( $ideas->get_total() ) ?>件のアイデア</a>が登録されています。
			<p>
		</div>
	</div>
</div>

<div class="widget">
	<h2 class="widget-title">人気のタグ</h2>
	<div class="widget-content">
		<ul class="widget-content recent-widgets">
			<?php
			$popular_tags = get_transient( 'popular_idea_tags' );
			if ( false === $popular_tags ) {
				$popular_tags = $ideas->popular_tags();
				set_transient( 'popular_idea_tags', $popular_tags, 60 * 60 );
			}
			foreach ( $popular_tags as $tag ) :
				?>
				<li class="recent-post-list">
					<a href="<?= get_term_link( $tag ) ?>?post_type=ideas">
						<?= esc_html( $tag->name ) ?>
						<small>(<?= number_format( $tag->total ) ?>)</small>
					</a>
				</li>
			<?php endforeach; ?>

		</ul>
	</div>
</div>

<div class="widget">
	<h2 class="widget-title">人気のアイデア</h2>
	<div class="widget-content">
		<ul class="widget-content recent-widgets">
			<?php
			$popular_ideas = get_transient( 'popular_ideas' );
			if ( false === $popular_ideas ) {
				$popular_ideas = $ideas->popular_ideas( 5 );
				set_transient( 'popular_ideas', $popular_ideas, 60 * 30 );
			}
			foreach ( $popular_ideas as $post ) :
				setup_postdata( $post );
				?>
				<li class="recent-post-list">
					<a href="<?php the_permalink() ?>">
						<h3><?php the_title() ?>
							<small>(<?= number_format( $post->total ) ?>)</small>
						</h3>
						<p class="meta">
							<i class="icon-folder"></i>
						<span>
						<?= implode( ', ', array_map( function ( $tag ) {
							return esc_html( $tag->name );
						}, get_the_tags( get_the_ID() ) ) ); ?>
						</span>
							<i class="icon-calendar"></i> <?= the_date() ?>
						</p>
						<p class="author">
							<?= get_avatar( get_the_author_meta( 'ID' ), 48 ) ?>
							<?php the_author() ?>
						</p>
					</a>

				</li>
			<?php endforeach;
			wp_reset_postdata(); ?>

		</ul>
	</div>
</div>

<div class="widget widget-recent-post-widget">
	<h2 class="widget-title">最新のアイデア</h2>
	<ul class="widget-content recent-widgets">
		<?php
		$query = new WP_Query( [
			'post_type'      => 'ideas',
			'posts_per_page' => 5,
		] );
		while ( $query->have_posts() ): $query->the_post();
			?>
			<li class="recent-post-list">
				<a href="<?php the_permalink() ?>">
					<h3><?php the_title() ?></h3>
					<p class="meta">
						<i class="icon-folder"></i>
						<span>
						<?= implode( ', ', array_map( function ( $tag ) {
							return esc_html( $tag->name );
						}, get_the_tags( get_the_ID() ) ) ); ?>
						</span>
						<i class="icon-calendar"></i> <?= the_date() ?>
					</p>
					<p class="author">
						<?= get_avatar( get_the_author_meta( 'ID' ), 48 ) ?>
						<?php the_author() ?>
					</p>
				</a>

			</li>
		<?php endwhile;
		wp_reset_postdata(); ?>
	</ul>
	<p class="right">
		<a class="btn btn-sm btn-default btn-block" href="<?= get_post_type_archive_link( 'ideas' ) ?>">一覧</a>
	</p>
</div>
