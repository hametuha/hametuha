<?php if ( $post->_event_title ) : ?>
	<div class="row news-event">

		<div class="col-sm-12 col-md-6 news-event__info">

			<h2 class="news-event__title"><?= esc_html( $post->_event_title ) ?></h2>

			<?php if ( $post->_event_start ) : ?>
				<p class="news-event__date">
					<strong><i class="icon-calendar"></i>
						日時</strong> <?= hamenew_event_date( $post->_event_start, $post->_event_end ) ?>
					<?php if ( strtotime( $post->_event_end ?: $post->_event_start ) < current_time( 'timestamp', true ) ) : ?>
						<span class="label label-default">終了しました</span>
					<?php endif; ?>
				</p>
			<?php elseif ( $post->_event_end ) : ?>
				<p class="news-event__date">
					<strong><i class="icon-calendar"></i>
						〆切</strong> <?= mysql2date( 'Y年n月j日（D）', $post->_event_end ) ?>
					<?php if ( strtotime( $post->_event_end ) < current_time( 'timestamp', true ) ) : ?>
						<span class="label label-default">終了しました</span>
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<?php if ( $post->_event_address ) : ?>
				<p class="news-event__address">
					<strong><i class="icon-map"></i>
						場所</strong> <?= esc_html( $post->_event_address . ' ' . $post->_event_bld ) ?>
					<a href="https://maps.google.com/?q=<?= rawurlencode( $post->_event_address ) ?>"
					   target="_blank">地図</a>
				</p>
			<?php endif; ?>

			<?php if ( $post->_event_desc ) : ?>
				<p class="news-event__desc">
					<?= nl2br( esc_html( $post->_event_desc ) ) ?>
				</p>
			<?php endif; ?>

		</div>


		<?php if ( $post->_event_address ) : ?>

			<div class="col-sm-12 col-md-6 news-event__map">
				<div class="news-event__map--inner">
					<iframe width="100%" height="100%" frameborder="0" scrolling="no"
					        marginheight="0" marginwidth="0"
					        src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDDZqyowmW69rWqntGmiYRg1u3tira2Wm8&q=<?= rawurlencode( $post->_event_address ) ?>"></iframe>
				</div>
			</div><!-- -->

		<?php endif; ?>


	</div>
<?php endif; ?>