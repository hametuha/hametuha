<?php $categories = get_the_term_list( get_the_ID(), 'genre' ); ?>
<?php if ( $categories ) : ?>
	<li class="amp-wp-tax-category">
		<span class="screen-reader-text">ジャンル</span>
		<?php echo $categories; ?>
	</li>
<?php endif; ?>
