<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" <?php body_class(); ?>>
<head>
	<meta charset="UTF-8" />
	<title><?php wp_title( '' ); ?></title>
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/epub.css?ver=<?php echo current_time( 'mysql' ); ?>" type="text/css" />
</head>
<body<?php do_action( 'epub_body_attr' ); ?>>
