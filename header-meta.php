<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?> ng-app="hametuha">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?> ng-app="hametuha">
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?> ng-app="hametuha">
<!--<![endif]-->
<head prefix="<?= hametuha_get_ogp_type() ?>">
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<?php wp_head(); ?>
</head>
<body<?php if ( ! is_singular( 'post' ) ) {
	echo ' itemscope itemtype="http://schema.org/WebPage"';
} ?> <?php body_class() ?>>
<div id="fb-root"></div>
<div id="whole-body">
