
<div class="alert alert-info">
	<h4 class="panel-title"><i class="icon-question2"></i> 気軽にご質問ください</h4>
	<p>
		破滅派を利用する際に不明な点が遭った場合は<a class="alert-link" href="https://twitter.com/hametuha">twitter</a>でリプライをいただくか、
		<a class="alert-link" href="<?= home_url( '/inquiry/' ) ?>">お問い合わせ</a>からご要望をいただければ問題解決にご協力できます。
	</p>
</div>

<?php if ( ! is_post_type_archive( 'faq' ) ) : ?>
<ul class="nav nav-pills">
	<li><a href="<?= get_post_type_archive_link( 'faq' ) ?>">FAQトップ</a></li>
	<?php foreach ( get_terms( 'faq_cat' ) as $term ) : ?>
		<li>
			<a href="<?= get_term_link( $term ) ?>"><?= esc_html( $term->name ) ?></a>
		</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
