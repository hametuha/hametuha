<div class="row row--share share__container">

	<?php
	if ( is_singular() ) {
		$post    = get_post();
		$title   = get_the_title( $post );
		$url     = get_permalink( $post );
		$post_id = $post->ID;
	} else {
		$title   = get_bloginfo( 'name' );
		$url     = home_url( '/' );
		$post_id = 0;
	}
	$encoded_url   = rawurlencode( $url );
	$encoded_title = rawurlencode( $title . ' | 破滅派' );
	$data_title    = esc_attr( $title );
	$links         = [];
	foreach ( [ 'facebook', 'twitter', 'googleplus', 'hatena', 'line' ] as $brand ) :
		$link = '';
		switch ( $brand ) {
			case 'facebook':
				$link = sprintf(
					'<div class="fb-like" data-href="%s" data-layout="box_count" data-action="like" data-size="small" data-show-faces="false" data-share="true"></div>',
					hametuha_user_link( $url, 'share-single', 'Facebook' )
				);
				break;
			case 'twitter':
				if ( is_hamenew() ) {
					$via = 'minico_me';
					$related = 'hametuha';
					$hash_tag = 'はめにゅー';
				} else {
					$via = 'hametuha';
					$related = 'minico_me';
					$hash_tag = '破滅派';
				}
				$link = sprintf(
					'<div class="share__box"><a class="share__baloon" href="%s" target="_blank">反応</a><a data-text="%s" href="%s" class="twitter-share-button" data-via="%s" data-related="%s" data-hashtags="%s">つぶやく</a></div>',
					esc_url( sprintf( 'https://twitter.com/search?f=tweets&vertical=default&q=%s&src=typd', rawurlencode( preg_replace( '#^https?://#', '', $url ) ) ) ),
					esc_attr( $title ),
					hametuha_user_link( $url, 'share-single', 'Twitter' ),
					esc_attr( $via ),
					esc_attr( $related ),
					esc_attr( $hash_tag )
				);
				break;
			case 'googleplus':
				$link = sprintf(
					'<div class="g-plusone" data-size="tall" data-callback="plusOneCallBack" data-href="%s"></div>',
					hametuha_user_link( $url, 'share-single', 'Google+' )
				);
				break;
			case 'hatena':
				$link = sprintf(
					'<a href="%s" class="hatena-bookmark-button" data-hatena-bookmark-layout="vertical-balloon" data-hatena-bookmark-lang="ja" title="このエントリーをはてなブックマークに追加"><img src="https://b.st-hatena.com/images/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a>',
					esc_url( $url )
				);
				break;
			case 'line':
				$link = sprintf(
					'<a class="share__button share-line" href="line://msg/text/%s"><i class="icon-line"></i> <span class="share__text">送る</span></a>',
					rawurlencode( hametuha_user_link( $url, 'share-single', 'Line' ) )
				);
				break;
			default:
				// Do nothing
				break;

		}
		?>
		<div class="share__item--<?= $brand ?>">
			<?= $link ?>
		</div>
	<?php endforeach; ?>
</div>
