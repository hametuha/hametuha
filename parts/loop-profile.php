<?php
/** @var $profile Hametuha\ThePost\Profile */
$profile = $post->helper;
?>
<li <?php post_class( 'media media--profile' ); ?>>

	<a class="media__link media__link--nopad"
	   href="<?php echo home_url( sprintf( '/doujin/detail/%s/', get_the_author_meta( 'user_nicename' ) ) ); ?>">

		<div class="pull-left comment-face">
			<?php echo $profile->avatar( 120 ); ?>
		</div>

		<div class="media-body">

			<!-- Title -->
			<h2 class="comment-title">
				<ruby><?php the_author(); ?>
					<rt><?php echo esc_html( $profile->furigana ); ?></rt>
				</ruby>
				<small><?php echo $profile->role; ?></small>
			</h2>

			<?php if ( $profile->score ) : ?>
				<div class="media__score">
					<?php for ( $i = 0; $i < $profile->score; $i ++ ) : ?>
						★
					<?php endfor; ?>
				</div>
				<div class="media__rating">
					<strong>
						<?php echo number_format( $profile->work_count ); ?>作品で
						<?php echo number_format( $profile->score ); ?>回
					</strong>
					<small>
						平均 <?php echo number_format( $profile->score / $profile->work_count, 1 ); ?> 回
					</small>
				</div>
			<?php endif; ?>

			<!-- Post Data -->
			<ul class="list-inline">
				<?php if ( ! $profile->score ) : ?>
					<li>
						<i class="icon-book"></i> <?php echo number_format( $profile->work_count ); ?>作品
					</li>
				<?php endif; ?>
				<li class="date">
					<i class="icon-calendar2"></i> <?php echo $profile->registered_date(); ?>登録
					<?php if ( is_recent_date( $profile->registered_date( false ), 30 ) ) : ?>
						<span class="label label-danger">新人</span>
					<?php endif; ?>
				</li>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt">
				<p class="text-muted"><?php echo trim_long_sentence( strip_tags( $profile->description ), 140 ); ?></p>
			</div>

		</div>
	</a>
</li>
