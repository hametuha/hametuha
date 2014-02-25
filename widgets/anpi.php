<?php
class Anpi_About_Widget extends WP_Widget{
	function Anpi_About_Widget(){
		parent::WP_Widget(false, '安否情報とは？', array('description' => '安否情報とは何かを説明します'));
		
	}
	
	/**
	 *
	 * @global wpdb $wpdb
	 * @param array $args
	 * @param array $instance
	 * @return void 
	 */
	function widget($args, $instance) {
		global $wpdb;
		extract($args);
		echo <<<EOS
{$before_widget}
{$before_title}安否情報とは？{$after_title}
EOS;
?>
<div>
	<p class="center">
		<a href="<?php echo get_post_type_archive_link('anpi');?>">
			<img src="<?php echo get_template_directory_uri();?>/img/banner-anpi-about.jpg" alt="破滅派安否情報" width="250" height="100" />
		</a>
	</p>
	<p class="anpi-about">
		安否情報とは、破滅派同人が安否を報告するページです。<strong>身辺報告</strong>や<strong>読書日記</strong>の他、<strong>災害時・精神的危機</strong>を迎えている時などにご利用ください。
	</p>
	<?php if(current_user_can('edit_posts')): ?>
		<?php if(!$wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'anpi' AND post_status = 'publish' AND (TO_DAYS(NOW()) - TO_DAYS(post_date) <= 30) AND post_author = %d", get_current_user_id()))):?>
			<p class="small-message warning">
				最近書いていませんね？　お元気ですか。
			</p>
		<?php endif; ?>
		<p class="center">
			<a class="small-button" href="<?php echo admin_url('post-new.php?post_type=anpi'); ?>">安否情報を書く</a>
		</p>
	<?php else: ?>
		<p class="center anpi-about-login">
			<span>誰でも書けますよ。</span>
			<a class="small-button" href="<?php echo wp_login_url(admin_url('post-new.php?post_type=anpi'));?>">ログイン</a>
			または
			<a class="small-button" href="<?php echo preg_replace("/.*?href=\"([^\"]*?)\".*?/", "$1", wp_register('', '', false));?>">登録する</a>
		</p>
	<?php endif; ?>
</div>
<?php
		echo $after_widget;
	}
}
register_widget('Anpi_About_Widget');

