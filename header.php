<?php get_header('meta'); ?>
<body <?php body_class(); ?>>
	<?php hametuha_fb_root(); ?>
	<div id="header">
		<div class="margin clearfix">
			<a class="logo alignleft" rel="home" href="<?php bloginfo('url'); ?>">
				<img src="<?php bloginfo('template_directory'); ?>/img/header-logo.png" alt="<?php bloginfo('name'); ?>" width="140" height="50" />
				<span>後ろ向きのまま前に進め!</span>
			</a>
			
			<ul id="globalnavi" class="clearfix">
				<li class="nav first home">
					<a class="link" rel="home" href="<?php echo home_url();?>">
						<span class="icon"></span><br />ホーム
					</a>
				</li>
				<li class="nav has-nav works">
					<span class="icon"></span><br />作品
					<?php wp_nav_menu(array('theme_location' => 'hametuha_global_works', 'container' => 'div', 'container_class' => 'submenu')); ?>
				</li>
				<li class="nav has-nav about">
					<span class="icon"></span><br />破滅派？
					<?php wp_nav_menu(array('theme_location' => 'hametuha_global_about', 'container' => 'div', 'container_class' => 'submenu')); ?>
				</li>
				<li class="nav has-nav info">
					<span class="icon"></span><br />おしらせ
					<?php wp_nav_menu(array('theme_location' => 'hametuha_global_info', 'container' => 'div', 'container_class' => 'submenu')); ?>
				</li>
				<li class="nav contact">
					<a class="link" href="<?php echo home_url('inquiry'); ?>">
						<span class="icon"></span><br />コンタクト
					</a>
				</li>
			</ul>
			
			<p class="ads alignright"><?php google_ads('header'); ?></p>
		</div>
	</div>

	<?php $class = (needs_left_sidebar()) ? " archive" : ''; ?>
	<div id="main" class="margin clearfix<?php echo $class; ?>">
		<?php if(!is_front_page()): ?>
		<p class="breadcrumb">
			<?php if(function_exists('bcn_display')) bcn_display();?>
		</p>
		<?php endif; ?>
		<div id="content">
	
