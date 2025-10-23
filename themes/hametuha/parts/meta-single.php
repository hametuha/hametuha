<ul class="metadata list-inline">

	<?php if ( is_singular( 'lists' ) || is_singular( 'series' ) ) : ?>
		<!-- 作成者 -->
		<li class="list-inlien-item">
			<i class="icon-user"></i>
			<?php if ( author_can( $post, 'edit_posts' ) ) : ?>
				<a itemprop="editor"
				   href="<?php echo home_url( '/doujin/detail/' . get_the_author_meta( 'nicename' ) . '/' ); ?>"><?php the_author(); ?></a> 編
			<?php else : ?>
				<span itemprop="editor"><?php the_author(); ?></span>
			<?php endif; ?>
		</li>
		<li class="list-inlien-item">
			<i class="icon-books"></i> <span><?php echo ( $volume_count = number_format_i18n( loop_count() ) ); ?></span> 作品収録
		</li>
	<?php endif; ?>


	<!-- taxonomies -->
	<?php
	$before = '<li class="genre list-inline-item" itemprop="genre"><i class="icon-tags"></i> ';
	$after  = '</li>';
	switch ( get_post_type() ) {
		case 'thread':
			the_terms( get_the_ID(), 'topic', $before, ', ', $after );
			break;
		case 'faq':
			the_terms( get_the_ID(), 'faq_cat', $before, ', ', $after );
			break;
		case 'anpi':
			the_terms( get_the_ID(), 'anpi_cat', $before, ', ', $after );
			break;
		default:
			// Do nothing
			break;
	}
	?>

	<!-- Date -->
	<li class="date list-inline-item">
		<i class="icon-clock"></i>
		<span><?php the_time( 'Y年m月d日（D）' ); ?></span>
		<small><?php echo hametuha_passed_time( $post->post_date ); ?></small>
		<meta itemprop="dateModified" content="<?php echo mysql2date( DateTime::ISO8601, $post->post_modified_gmt ); ?>">
		<meta itemprop="datePublished" content="<?php echo mysql2date( DateTime::ISO8601, $post->post_date_gmt ); ?>">
	</li>

	<?php if ( in_array( get_post_type(), [ 'page', 'faq' ] ) && hametuha_remarkably_updated() ) : ?>
	<li class="date list-inline-item">
		<i class="icon-loop4"></i>
		<?php the_modified_date( 'Y年m月d日（D）' ); ?>更新
	</li>
	<?php endif; ?>

	<!-- Comments -->
	<?php if ( post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<li class="list-inline-item">
			<i class="icon-bubbles2"></i>
			<?php comments_number( 'なし', '1件', '%件' ); ?>
		</li>
	<?php endif; ?>

	<!-- Edit link -->
	<?php if ( current_user_can( 'edit_post', get_the_ID() ) && ! is_hamenew() ) : ?>
		<li class="list-inline-item">
			<?php if ( 'lists' == get_post_type() ) : ?>
				<a class="list-creator btn btn-primary btn-sm" title="リストを編集する"
				   href="<?php echo esc_url( \Hametuha\Rest\ListCreator::form_link( get_the_ID() ) ); ?>"><i
						class="icon-pencil5"></i> 編集</a>
				<a class="list-eraser btn btn-danger btn-sm" title="このリストを削除します。よろしいですか？　この操作は取り消せません"
				   href="<?php echo esc_url( \Hametuha\Rest\ListCreator::delete_link( get_the_ID() ) ); ?>"><i
						class="icon-close3"></i> 削除</a>
			<?php elseif ( ! is_singular( 'thread' ) ) : ?>
				<i class="icon-pen3"></i> <?php edit_post_link(); ?>
			<?php endif; ?>
		</li>
	<?php endif; ?>

	<?php if ( is_hamenew() ) : ?>
	   <!-- News -->
		<?php if ( $terms = get_the_terms( get_post(), 'genre' ) ) : ?>
		<li class="list-inline-item">
			<?php
			echo implode( ' ', array_map( function( $term ) {
				return sprintf( '<a class="btn btn-sm btn-link" href="%s"><i class="icon-tag5"></i> %s</a>', get_term_link( $term ), esc_html( $term->name ) );
			}, $terms ) );
			?>
		</li>
		<?php endif; ?>

		<?php if ( hamenew_is_pr() ) : ?>
		<li class="list-inline-item">
			Promoted by <?php echo esc_html( hamenew_pr_label() ); ?>
		</li>
		<?php endif; ?>

	<?php endif; ?>

</ul><!-- //.metadata -->
