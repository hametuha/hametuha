<footer id="footer">
	<div id="footer-sidebar" class="container">
		<div class="col-sm-4">
            <h3>破滅派とは？</h3>
            <?php wp_nav_menu(array(
                'theme_location' => 'hametuha_global_about',
                'container' => false,
                'menu_class' => 'nav nav-pills nav-stacked'
            )); ?>
		</div>
        <div class="col-sm-4">
            <h3>関連リンク</h3>
            <ul class="nav nav-pills nav-stacked external-links">
                <li><a href="http://hametuha.co.jp"><i class="icon-ha"></i> <span>株式会社破滅派</span></a></li>
                <li><a href="http://hametuha.tokyo"><i class="icon-cup"></i> <span>破滅サロン</span></a></li>
                <li><a href="https://www.facebook.com/hametuha.inc"><i class="icon-facebook4"></i> <span>Facebook</span></a></li>
                <li><a href="https://twitter.com/hametuha"><i class="icon-twitter"></i> <span>Twitter</span></a></li>
                <li><a href="https://plus.google.com/b/115001047459194790006/115001047459194790006/about/p/pub" rel="publisher"><i class="icon-googleplus3"></i> <span>Google+</span></a></li>
                <li><a href="http://www.ustream.tv/channel/<?= rawurlencode('破滅派') ?>"><i class="icon-ustream"></i> <span>Ustream</span></a></li>
                <li><a href="https://www.youtube.com/user/hametuha"><i class="icon-youtube"></i> <span>Youtube</span></a></li>
                <li><a href="http://minico.me"><i class="icon-minicome"></i> <span>ミニコme!</span></a></li>
                <li><a href="https://github.com/hametuha/"><i class="icon-github3"></i> <span>Github</span></a></li>
            </ul>
        </div>
        <div class="col-sm-4">
            <h3>破滅派通信<small>メルマガ</small></h3>
            <?php if( function_exists('alo_em_show_widget_form') ) echo alo_em_show_widget_form() ?>
            <p class="mail-desc text-muted">
                破滅派がお届けするメルマガです。滅多に送りませんので、ぜひ登録してください。
            </p>

        </div>
	</div>

    <p class="copy-right text-center">
        &copy; <span itemprop="copyrightYear">2007</span> 破滅派
    </p>
</footer>
<?php get_template_part('parts/modal') ?>
<?php wp_footer(); ?>
</body>
</html>
