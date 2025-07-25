<?php
/* @var string $your_rating */
/* @var array $reviews */
/* @var array $review_label */
if ( get_the_author_meta( 'ID' ) != get_current_user_id() ) :
	?>
	<h3 class="text-center">あなたの反応</h3>

	<p class="star-rating<?php echo is_user_logged_in() ? ' active' : ''; ?>">
		<?php for ( $i = 1; $i <= 5; $i ++ ) : ?>
			<i data-value="<?php echo $i; ?>" class="icon-star6<?php echo $i <= $your_rating ? ' active' : ''; ?>"></i>
		<?php endfor; ?>
		<input type="hidden" name="rating" value="<?php echo $your_rating; ?>"/>
	</p>

	<?php if ( ! is_user_logged_in() ) : ?>
	<p class="alert alert-warning"><a href="<?php echo wp_login_url( get_permalink() ); ?>" class="alert-link">ログイン</a>すると、星の数によって冷酷な評価を突きつけることができます。
	</p>
<?php endif; ?>


	<input type="hidden" name="post_id" value="<?php the_ID(); ?>"/>

	<?php foreach ( $reviews as $key => $review ) : ?>
	<div class="review-labels">
		<h4><?php echo esc_html( $review_label[ $key ] ); ?></h4>

		<div class="btn-group btn-group-justified" data-toggle="buttons">
			<?php foreach ( $review as $index => list( $label, $value, $checked ) ) : ?>
				<label class="btn btn-xs btn-default<?php echo $checked ? ' active' : ''; ?>">
					<input type="radio" class="btn btn-xs btn-info" name="<?php echo $key; ?>"
						   value="<?php echo $value; ?>" <?php checked( $checked ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</div>
	</div>
<?php endforeach; ?>
	<input type="submit" value="送信" data-complete-text="評価済み" data-loading-text="送信中..."
		   class="btn btn-lg btn-primary btn-block"/>
	<?php
endif;
