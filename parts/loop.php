<?php

if( 'post' == get_post_type() ){
	$thumbnail = get_pseudo_thumbnail();
	$class = $thumbnail['url'] ? '' : ' media__link--nopad';
}else{
	$class = '';
}

?>
<li data-post-id="<?php the_ID() ?>" <?php post_class('media') ?>>
	<a href="<?php the_permalink() ?>" class="media__link<?= $class ?>">

		<?php
		if( 'post' == get_post_type() ){
			// 投稿の場合はサムネ出力実験
			$style = $thumbnail['url'] ? "background-image: url('{$thumbnail['url']}')" : "";
			echo <<<HTML
			<div class="pseudo-thumbnail" data-category="thumb-score" data-label="{$thumbnail['label']}" data-action="{$thumbnail['action']}" style="{$style}">
			</div>
HTML;

		}else{
			// それ以外は今まで通り
			if( has_post_thumbnail() ){
				?>
		        <div class="pull-right">
		            <?= get_the_post_thumbnail(null, 'thumbnail', array('class' => 'media-object')) ?>
		        </div>
				<?php
			}
		}
		?>

	    <div class="media-body">

	        <!-- Title -->
	        <h2 class="media-body__title">
	            <?php the_title(); ?>
		        <?php switch( get_post_type() ){
			        case 'post':
						if( $post->post_parent ){
							printf('<small class="media-title-label">%s</small> | ', get_the_title($post->post_parent));
						}
						echo implode(' ', array_map(function($term){
		                    printf('<small>%s</small>', esc_html($term->name));
						}, get_the_category()));
				        break;
		            case 'anpi':
						if( is_search() ){
							echo '<small>安否情報</small>';
						}else{
							if( $terms = get_the_terms(null, 'anpi_cat') ){
								echo implode(' ', array_map(function($term){
									printf('<small>%s</small>', esc_html($term->name));
								}, $terms));
							}
			            }
			            break;
		            default:
						// Do nothing
			            break;
		        } ?>
	        </h2>

	        <!-- Post Data -->
	        <ul class="list-inline">
	            <?php switch(get_post_type()): case 'faq': ?>
		            <li>
			            <i class="icon-tags"></i>
			            <?php if( ( $terms = get_the_terms(get_the_ID(), 'faq_cat') ) && !is_wp_error($terms) ): ?>
		                    <?= implode(', ', array_map(function($term){
			                    return esc_html($term->name);
		                    }, $terms)); ?>
			            <?php else: ?>
			            <span class="text-muted">分類なし</span>
			            <?php endif; ?>
		            </li>
	            <?php break; case 'info': ?>
	            <?php break; default: ?>
	                <li class="author-info">
	                    <?= get_avatar(get_the_author_meta('ID'), 40); ?>
	                    <?php the_author(); ?>
	                </li>
	            <?php break; endswitch; ?>
	            <li class="date">
	                <i class="icon-calendar2"></i> <?= hametuha_passed_time($post->post_date) ?>
	                <?php if( is_recent_date($post->post_date, 3) ): ?>
	                    <span class="label label-danger">New!</span>
	                <?php elseif( is_recent_date($post->post_modified, 7) ): ?>
	                    <span class="label label-info">更新</span>
	                <?php endif; ?>
	            </li>
	            <li class="static"><i class="icon-reading"></i> <?= number_format(get_post_length()) ?>文字</li>
	        </ul>

	        <!-- Excerpt -->
	        <div class="archive-excerpt">
	            <p class="text-muted"><?= trim_long_sentence(get_the_excerpt(), 98); ?></p>
	        </div>


	    </div>
	</a>
</li>
