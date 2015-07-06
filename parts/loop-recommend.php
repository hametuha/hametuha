<li class="recommend__item">
	<a class="recommend__link" href="<?php the_permalink() ?>">
		<?php
		if( 'post' == get_post_type() ) {
			$thumbnail = get_pseudo_thumbnail();
			$style = $thumbnail['url'] ? "background-image: url('{$thumbnail['url']}')" : "";
			echo <<<HTML
			<div class="pseudo-thumbnail" data-category="thumb-score" data-label="{$thumbnail['label']}" data-action="{$thumbnail['action']}" style="{$style}">
			</div>
HTML;
		}
		?>
		<h4 class="recommend__title">
			<?php the_title() ?>
			<small><?= implode(', ', array_map(function($cat){
					return esc_html($cat->name);
				}, get_the_category())) ?></small>
		</h4>
		<div class="recommend__meta">
			<span class="recommend__author"><?= get_avatar(get_the_author_meta('ID'), 32) ?> <?php the_author() ?></span>
			<span class="recommend__date"><i class="icon-calendar"></i><?= hametuha_passed_time($post->post_date) ?></span>
		</div>
		<div class="recommend__excerpt">
			<?php the_excerpt() ?>
		</div>
		<?php if( $score = get_the_score() ): ?>
			<span class="recommend__score"><?= round(10 * $score) ?>%</span>
		<?php endif; ?>
	</a>
</li>