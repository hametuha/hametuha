<?php
/* @var string $your_rating */
/* @var array $reviews */
/* @var array $review_label */

/*

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
*/
