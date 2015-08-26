<?php
/**
 * メール関係の関数
 */

/**
 * メルマガ購読ページはno cache headers
 */
add_action('template_redirect', function(){
	if ( is_page( 'merumaga' ) ) {
		nocache_headers();
	}
});

/**
 * FromがWordPressにならないように
 */
add_filter( 'wp_mail_from_name', function ( $from_name ) {
	if ( 'WordPress' == $from_name ) {
		$from_name = get_bloginfo( 'name' );
	}
	return $from_name;
} );

/**
 * Fromのアドレスの初期値を設定
 */
add_filter( 'wp_mail_from', function ( $from_mail ) {
	if ( 0 === strpos( $from_mail, 'wordpress@' ) ) {
		$from_mail = 'no-reply@hametuha.com';
	}
	return $from_mail;
} );

/**
 * メールのURLを変更する
 *
 * @param string $html
 * @param WP_Post $post
 * @param stdClass $recipient
 *
 * @return string $html
 *
 */
add_filter( 'alo_easymail_newsletter_content', function ( $html, WP_Post $post, $recipient ) {
	// Apply CSS style
	$parser = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
	$css    = file_get_contents( get_template_directory() . '/assets/css/mail.css' );
	$parser->setHTML( $html );
	$parser->setCss( $css );
	$html = $parser->convert();
	$html = str_replace( '<p style="margin: 0.5em 0;"> </p>', '<p style="margin: 0.5em 0;">&nbsp;</p>', $html );
	// Fix url
	$html = preg_replace( '#http://(s\.)?hametuha\.(com|info)/wp-content#u', 'https://$1hametuha.$2/wp-content', $html );
	// Fix link

	$html = preg_replace_callback( '@href="(http://hametuha\.(info|com)([^"]+))"@u', function ( $matches ) use ( $post ) {
		$url = add_query_arg( [
			'utm_source'   => 'Email',
			'utm_medium'   => 0,
			'utm_campaign' => 'NewsLetter-' . $post->ID,
		], $matches[1] );
		return "href=\"{$url}\"";
	}, $html );
	// Add tracking code
	if ( 'alo-easymail-admin-preview.php' !== basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
		$query = [
			'tid' => 'UA-1766751-2',
			't'   => 'event',
			'cid' => md5( $recipient->email ),
			'ec'  => 'email',
			'ea'  => 'open',
			'el'  => $post->ID,
			'cs'  => 'Email',
			'cm'  => 0,
			'cn'  => 'NewsLetter-' . $post->ID,
		];
		if ( isset( $recipient->ID ) ) {
			$query['uid'] = $recipient->ID;
		}
		$tracking_code = add_query_arg( $query, 'https://www.google-analytics.com/collect' );
		$html          = str_replace( '</body>', "<img src=\"{$tracking_code}\" /></body>", $html );
	}

	return $html;
}, 10, 3 );
