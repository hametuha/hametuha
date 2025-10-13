<?php
/**
 * 安否情報のシングルページ
 *
 * @feature-group anpi
 */
get_header();

the_post();

$author = get_the_author_meta( 'ID' );
$is_author = user_can( $author, 'edit_posts' );
?>
<div class="container">
	<?php get_header( 'breadcrumb' ); ?>
</div>

<div class="tweet--big">

	<div class="container">

		<article class="tweet__wrap" itemscope itemtype="http://schema.org/BlogPosting">

			<div class="tweet__author">
				<h1 class="tweet__author--header">
					<?php if ( $is_author ) : ?>
					<a href="<?php echo esc_url( home_url( '/doujin/detail/' . get_the_author_meta( 'user_nicename' ) . '/' ) ); ?>">
					<?php endif; ?>
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 96, '', get_the_author(), [ 'class' => 'avatart img-circle' ] ); ?>
						<span class="tweet__author--name"><?php the_author(); ?></span>
						<small><?php echo hametuha_user_role( get_the_author_meta( 'ID' ) ); ?></small>
						<br/>
						<small class="tweet__date text-muted">
							<i class="icon-calendar"></i> <?php the_time( get_option( 'date_format' ) . ' H:i' ); ?>
						</small>
					<?php if ( $is_author ) : ?>
					</a>
					<?php endif; ?>
				</h1>

				<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
					<div class="tweet__action">
						<a class="btn btn-primary" href="<?php echo esc_url( get_edit_post_link( get_post() ) ); ?>">
							<i class="icon-cog"></i> 編集
						</a>
					</div>
				<?php endif; ?>

			</div>

			<?php if ( ! \Hametuha\Model\Anpis::get_instance()->is_tweet() ) : ?>
			<div class="tweet__content">
				<h2><?php the_title(); ?></h2>
			</div>
			<?php endif; ?>

			<div class="tweet__meta">
				<span class="tweet__comment">
					<i class="icon-bubble"></i> | <?php comments_number( '0', '1', '%' ); ?>
				</span>
				<span class="tweet__mentions">
				@ |
					<?php if ( $post->mention_to ) : ?>
						<?php foreach ( $post->mention_to as $user ) : ?>
							<span
								class="help-tip"
								title="<?php echo esc_attr( $user->display_name ); ?>">
							<?php
							echo get_avatar(
								$user->ID, 32,
								'',
								$user->display_name,
								[
									'title' => $user->display_name,
									'class' => 'img-circle avatar tweet__mentions--img',
								]
							);
							?>
						</span>
						<?php endforeach; ?>
					<?php else : ?>
						-
					<?php endif; ?>
			</span>
			</div>

			<div class="post-content" itemprop="articleBody">
				<?php
				if ( \Hametuha\Model\Anpis::get_instance()->is_tweet() ) {
					// _is_tweet が 1 の場合は excerpt を表示
					the_excerpt();
				} else {
					// それ以外は本文を表示
					the_content();
				}
				?>
			</div>

			<?php comments_template(); ?>

		</article>


		<?php get_template_part( 'parts/share' ); ?>

		<?php
		$recent = hametuha_get_author_work_siblings();
		if ( ! empty( $recent ) ) :
			?>
			<div class="card-list row">
			<?php
			foreach ( $recent as $post ) {
				setup_postdata( $post );
				get_template_part( 'parts/loop', 'anpi' );
			}
			?>
			</div>
		<?php
		endif;
		wp_reset_postdata();
		?>

		<div class="mt-5 text-center">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'anpi' ) ); ?>" class="btn btn-primary btn-lg">
				<?php esc_html_e( '安否情報一覧', 'anpi' ); ?>
			</a>
		</div>

	</div>

</div>

<?php
get_footer( 'books' );
get_footer();
