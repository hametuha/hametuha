<?php
if ( is_preview() ) {
	return;
}
?>
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
	foreach ( [ 'facebook', 'twitter', 'hatena', 'line' ] as $brand ) :
		$link = '';
		switch ( $brand ) {
			case 'facebook':
			    $href = add_query_arg( [
                    'u' => rawurlencode( hametuha_user_link( $url, 'share-single', 'facebook' ) ),
                ], 'https://www.facebook.com/sharer/sharer.php' );
				$link = sprintf(
					'<a href="%s" target="_blank" class="share__button share__button--facebook"><i class="icon-facebook"></i> <span class="share__text">シェア</span></a>',
					esc_url( $href )
				);
				break;
			case 'twitter':
			    $args = [
                    'url'      => hametuha_user_link( $url, 'share-single', 'twitter' ),
			        'via'      => 'minico_me',
                    'related'  => 'hametuha',
                    'hashtags' => is_hamenew() ? 'はめにゅー,破滅派' : '破滅派',
                ];
				if ( $post_id && $author = get_user_meta( $post->post_author, 'twitter', true ) ) {
				    $args['related'] = $author;
                }
				foreach ( $args as $key => $arg ) {
				    $args[ $key ] = rawurlencode( $arg );
                }
				$href = add_query_arg( $args, 'https://twitter.com/intent/tweet' );
				$link = sprintf(
					'<a href="%s" target="_blank" class="share__button share__button--twitter"><i class="icon-twitter"></i> <span class="share__text">つぶやく</span></a>',
					esc_url( $href )
				);
				break;
			case 'hatena':
			    $href = 'https://b.hatena.ne.jp/entry/';
			    if ( preg_match( '#^https://#u', $url ) ) {
			        $href .= 's/';
                }
			    $href = $href . rawurlencode( preg_replace( '#^https?://#u', '', $url ) );
				$link = sprintf(
					'<a href="%s" target="_blank" class="share__button share__button--hatena"><i class="icon-hatena"></i> <span class="share__text">ブックマーク</span></a>',
					esc_url( $href )
				);
				break;
			case 'line':
			    $href  = add_query_arg( [
                    'url' => rawurlencode( hametuha_user_link( $url, 'share-single', 'Line' ) ),
                ], 'https://social-plugins.line.me/lineit/share' );
				$link = sprintf(
					'<a class="share__button share__button--line" href="%s"><i class="icon-line"></i> <span class="share__text">送る</span></a>',
					 $href
				);
				break;
			default:
			    // Do nothing
                break;
        } ?>
        <div class="share__item--<?= $brand ?>">
			<?= $link ?>
		</div>
	<?php endforeach; ?>
</div>
