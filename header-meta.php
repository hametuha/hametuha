<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head prefix="<?= hametuha_get_ogp_type() ?>">
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title><?php wp_title("|", true, 'right'); bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body<?php if( !is_singular('post') ) echo ' itemscope itemtype="http://schema.org/WebPage"'; ?> <?php body_class() ?>>
<div id="fb-root"></div>
<script>
	window.fbAsyncInit = function() {
		FB.init({
			appId      : '196054397143922',
			xfbml      : true,
			version    : 'v2.3'
		});
	};
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/ja_JP/sdk.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
</script>
<script>
window.twttr = (function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0],
t = window.twttr || {};
if (d.getElementById(id)) return;
js = d.createElement(s);
js.id = id;
js.src = "https://platform.twitter.com/widgets.js";
js.async = true;
fjs.parentNode.insertBefore(js, fjs);

t._e = [];
t.ready = function(f) {
t._e.push(f);
};

return t;
}(document, "script", "twitter-wjs"));
</script>
