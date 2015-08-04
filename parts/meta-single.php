<ul class="metadata list-inline">

	<?php if ( is_singular( 'lists' ) || is_singular( 'series' ) ): ?>
		<!-- 作成者 -->
		<li>
			<i class="icon-user"></i>
			<?php if ( author_can( $post, 'edit_posts' ) ) : ?>
				<a itemprop="editor"
				   href="<?= get_author_posts_url( get_the_author_meta( 'ID' ) ) ?>"><?php the_author() ?></a> 編
			<?php else: ?>
				<span itemprop="editor"><?php the_author() ?></span>
			<?php endif; ?>
		</li>
		<li>
			<i class="icon-books"></i> <span><?= ( $volume_count = number_format_i18n( loop_count() ) ) ?></span> 作品収録
		</li>
	<?php endif; ?>


	<!-- taxonomies -->
	<?php
	$before = '<li class="genre" itemprop="genre"><i class="icon-tags"></i> ';
	$after  = '</li>';
	switch ( get_post_type() ) {
		case 'thread':
			the_terms( get_the_ID(), 'topic', $before, $after );
			break;
		case 'faq':
			the_terms( get_the_ID(), 'faq_cat', $before, $after );
			break;
		case 'anpi':
			the_terms( get_the_ID(), 'anpi_cat', $before, $after );
			break;
		default:
			// Do nothing
			break;
	}
	?>

	<!-- Date -->
	<li class="date">
		<?php switch ( get_post_type() ) : case 'series': ?>
			<i class="icon-calendar"></i>
			<span class="hidden" itemprop="datePublished"><?php the_time( 'Y-m-dTH:i:s+09:00' ) ?></span>
			<?php the_series_range() ?>
			<?php if ( \Hametuha\Model\Series::get_instance()->is_finished( get_the_ID() ) ) : ?>
				<span class="label label-danger">完結済み</span>
			<?php endif; ?>
			<?php break; default: ?>
				<i class="icon-clock"></i>
				<span itemprop="datePublished"><?php the_date( 'Y年m月d日（D）' ); ?></span>
				<small><?= hametuha_passed_time( $post->post_date ) ?></small>
				<meta itemprop="dateModified" content="<?= $post->post_modified ?>">
			<?php endswitch; ?>
	</li>

	<!-- Comments -->
	<?php if ( post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<li>
			<i class="icon-bubbles2"></i>
			<?php comments_number( 'なし', '1件', '%件' ) ?>
		</li>
	<?php endif; ?>

	<!-- Edit link -->
	<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
		<li>
			<?php if ( 'lists' == get_post_type() ) : ?>
				<a class="list-creator btn btn-primary btn-sm" title="リストを編集する"
				   href="<?= esc_url( \Hametuha\Rest\ListCreator::form_link( get_the_ID() ) ) ?>"><i
						class="icon-pencil5"></i> 編集</a>
				<a class="list-eraser btn btn-danger btn-sm" title="このリストを削除します。よろしいですか？　この操作は取り消せません"
				   href="<?= esc_url( \Hametuha\Rest\ListCreator::delete_link( get_the_ID() ) ) ?>"><i
						class="icon-close3"></i> 削除</a>
			<?php elseif ( ! is_singular( 'thread' ) ) : ?>
				<i class="icon-pen3"></i> <?php edit_post_link() ?>
			<?php endif; ?>
		</li>
	<?php endif; ?>
</ul><!-- //.metadata -->