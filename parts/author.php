<?php

$author_id = (int) get_the_author_meta( 'ID' );
$path = '/assets/js/dist/components/follow-toggle.js';
wp_enqueue_script( 'hametu-follow', get_stylesheet_directory_uri() . $path, [ 'twitter-bootstrap', 'wp-api' ], filemtime( get_stylesheet_directory() . $path ), true );

?>
<div class="author-profile" itemscope itemprop="author" itemtype="http://schema.org/Person">
	<?= get_avatar( $author_id, 160, '', get_the_author(), [ 'itemprop' => 'image' ] ) ?>
	<h3>
		<span itemprop="name"><?php the_author() ?></span>
		<small>
			<span itemprop="affiliation">破滅派</span>
			<span><?= hametuha_user_role( $author_id ) ?></span>
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

	<div class="row">
		<div class="col-xs-6">
			<a class="btn btn-default btn-block btn--author"
			   href="<?= home_url( sprintf( '/doujin/detail/%s/', rawurlencode( get_the_author_meta( 'nicename' ) ) ) ) ?>"
			   itemprop="url">
				詳しく見る
			</a>
		</div>
		<div class="col-xs-6">
			<?php if ( is_user_logged_in() && get_current_user_id() != $author_id ) : ?>
				<?php
					$class_name = \Hametuha\Model\Follower::get_instance()->is_following( get_current_user_id(), $author_id )
						? ' btn-following'
						: '';
				?>
				<a href="#" data-follower-id="<?= $author_id ?>" class="btn btn-primary btn-follow<?= $class_name ?>" rel="nofollow">
					<span class="remove">フォロー中</span>
					<span class="add">
						<i class="icon-user-plus2"></i> フォローする
					</span>
				</a>
			<?php endif; ?>
		</div>
	</div>


</div><!-- //.author-profile -->
