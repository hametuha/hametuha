<li data-post-id="<?php the_ID() ?>" <?php post_class('media loop-lists') ?>>

	<a class="media__link" href="<?php the_permalink() ?>">


		<?php if( has_post_thumbnail() ): ?>
			<div class="pull-right">
				<?= get_the_post_thumbnail(null, 'thumbnail', array('class' => 'media-object')) ?>
			</div>
		<?php endif; ?>

		<div class="media-body">

			<!-- Title -->
			<h2>
				<?php the_title(); ?>
				<?php if( is_recommended() ): ?>
				<small class="label label-danger">オススメ</small>
				<?php endif; ?>
			</h2>

			<!-- Post Data -->
			<ul class="list-inline">
				<li class="author-info">
					<?php echo get_avatar(get_the_author_meta('ID'), 40); ?>
					<?php the_author(); ?> 編
				</li>
				<li class="date">
					<i class="icon-calendar2"></i> <?= hametuha_passed_time($post->post_date) ?>
					<?php if( is_recent_date($post->post_date, 3) ): ?>
						<span class="label label-danger">New!</span>
					<?php elseif( is_recent_date($post->post_modified, 7) ): ?>
						<span class="label label-info">更新</span>
					<?php endif; ?>
				</li>
				<li>
					<span class="<?= $post->num_children ? '' : 'text-danger' ?>">
						<i class="icon-books"></i> <?= number_format_i18n($post->num_children); ?>作収録
					</span>
				</li>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt">
				<p class="text-muted"><?= trim_long_sentence(get_the_excerpt(), 98); ?></p>
			</div>

		</div>
	</a>
</li>
