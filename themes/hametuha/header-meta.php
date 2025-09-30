<!DOCTYPE html>
<html <?php language_attributes(); ?> ng-app="hametuha">
<!--<![endif]-->
<head prefix="<?php echo hametuha_get_ogp_type(); ?>">
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<?php wp_head(); ?>
</head>
<body
<?php
if ( ! is_singular( 'post' ) ) {
	echo ' itemscope itemtype="http://schema.org/WebPage"';
}
?>
 <?php body_class(); ?>>
<?php wp_body_open(); ?>
<!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<div id="whole-body">
