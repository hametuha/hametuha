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

?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8">
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html>
<!--<![endif]-->
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars( $title, ENT_QUOTES, 'utf-8' ); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_dir; ?>/css/app.css?ver=1.0" />
	<link rel="shortcut icon" href="<?php echo $theme_dir; ?>/img/favicon.ico" />
	<script>
		// Adsense
		window.google_analytics_uacct = "UA-5329295-5";
		// analytics.js
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('create', 'UA-5329295-5', window.location.hostname);
		ga('require', 'displayfeatures');
		ga('require', 'linkid', 'linkid.js');
		ga('set', 'dimension2', '<?php echo $error_code; ?>');
		ga('send', 'pageview');
	</script>
</head>
<body class="error error<?php echo $error_code; ?>">
	<div class="container">

		<p class="text-center">
			<a id="login-logo" href="/" rel="home" title="破滅派に戻る"><i class="icon-hametuha"></i></a>
		</p>

		<div>
			<h1 class="text-center text-muted"><?php echo $title; ?></h1>
			<div class="error-container"><?php echo $message; ?></div>
		</div>

		<div class="footer-note">
			<p class="copy-right text-center text-muted">&copy; 2007 破滅派</p>
		</div>

	</div><!-- //.container -->
</body>
</html>
