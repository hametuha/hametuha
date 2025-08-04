<?php

// タイトルを決める
if ( isset( $r['response'] ) && function_exists( 'get_status_header_desc' ) ) {
	$title      = $r['response'] . ': ' . get_status_header_desc( $r['response'] );
	$error_code = $r['response'];
} elseif ( ! isset( $title ) || empty( $title ) ) {
	$title = '503: Service Temporary Unavailable';
}
// メッセージを決める
if ( isset( $this ) && method_exists( $this, 'mamo_template_tag_message' ) ) {
	// メンテナンスモード
	$message    = preg_replace( '/<h1.*h1>/', '', $this->mamo_template_tag_message() );
	$error_code = 503;
} elseif ( ! isset( $message ) || empty( $message ) ) {
	// それ以外にメッセージがなければ
	$message = '申し訳ございません、現在破滅派は利用できません。時間を置いてもう一度来てください。';
}

if ( ! isset( $error_code ) ) {
	$error_code = 500;
}

$theme_dir = '/wp-content/themes/hametuha/assets/';
$ver       = 'error-' . date( 'YmdH' ); // 時間まで記載してキャッシュされるように
?>
<!DOCTYPE html>
<html>
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars( $title, ENT_QUOTES, 'utf-8' ); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_dir; ?>/css/app.css?ver=<?php $ver ?>" />
	<link rel="shortcut icon" href="<?php echo $theme_dir; ?>/img/favicon.ico" />
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-GX6ZTNEEW8"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		var config = {
			link_attribution: true,
			page_type: "error"
		};
		gtag('config', 'G-GX6ZTNEEW8', config );
	</script>
</head>
<body class="error error<?php echo $error_code; ?>">
	<div class="container">

		<p class="error-header text-center">
			<a id="login-logo" href="/" rel="home" title="破滅派に戻る"><i class="icon-hametuha"></i></a>
		</p>

		<div class="error-body">
			<h1 class="text-center text-muted"><?php echo $title; ?></h1>
			<div class="error-container"><?php echo $message; ?></div>
			<h2>最新のステータス</h2>
			<p><a href="https://x.com/hametuha">破滅派のX（旧twitter）</a>を確認すると、更新情報がわかるかもしれません。</p>
		</div>

		<footer class="error-footer">
			<p class="copy-right text-center text-muted">&copy; 2007 破滅派</p>
		</footer>
	</div><!-- //.container -->
</body>
</html>
