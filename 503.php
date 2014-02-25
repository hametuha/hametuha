<?php
$theme_dir = '/wp-content/themes/hametuha';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:mixi="http://mixi-platform.com/ns#" xml:lang="ja" lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>503: Service Unavailable｜破滅派｜オンライン文芸誌</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_dir; ?>/css/reset-min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_dir; ?>/style/stylesheets/core.css" />
	<link rel="shortcut icon" href="<?php echo $theme_dir ?>/img/favicon.ico" />
</head>
<?php flush(); ?>
<body class="login-page login error503">
	<div id="login-form">
		
		<div id="login-body">
			<h1>
				<?php echo (isset($r, $r['response'])) ? $r['response'].': '.get_status_header_desc($r['response']) : '503: Service Unavailable'; ?>
			</h1>
			<a id="login-logo" href="http://hametuha.com" rel="home" title="破滅派に戻る"><img src="<?php echo $theme_dir; ?>/img/header-logo.png" alt="破滅派" width="140" height="50" /></a>
			<div class="error-container">
				<?php 
					if(isset($this) && method_exists($this, 'mamo_template_tag_message')){
						$mamo_msg = $this->mamo_template_tag_message();
						echo preg_replace('/<h1.*h1>/', '', $mamo_msg);
					}else{
						echo $message;
					}
				?>
			</div>
		</div>

		<div id="footer-login" class="footer-note">
			<p class="copy-right serif center">&copy; 2007-<?php echo date('Y'); ?> HAMETUHA</p>
		</div>
	</div>
</body>
</html>