<?php
// Do not delete these lines
defined( 'ABSPATH' ) or die();

if ( post_password_required() ) :
	?>
	<p class="alert alert-warning">
		このページは作成者によってパスワード保護されています。コメントを見るには、パスワードを入力してください。
	</p>
	<?php
	return;
endif;


//
//
//
?>

	<!-- You can start editing here. -->

	<div id="discussion">
		<?php if ( ! is_singular( 'anpi' ) ) : ?>
		<h2 id="comments">
			<i class="icon-bubbles2"></i> &quot;<?php the_title(); ?>&quot;へのコメント
			<small>
				<span
					itemprop="<?= is_singular( 'thread' ) ? 'answerCount' : 'commentCount' ?>"><?= number_format_i18n( get_comments_number() ); ?></span>件
			</small>
		</h2>
		<?php endif; ?>

		<?php if ( have_comments() ) : ?>

			<ul class="commentlist media-list">
				<?php wp_list_comments( [
					'type'     => 'comment',
					'callback' => 'hametuha_commment_display'
				] ); ?>
			</ul>

			<?php if ( get_comment_pages_count() > 1 ): ?>
				<div class="link-pages comment-pages text-center">
					<?= hametuha_format_pagination( paginate_comments_links( [
						'prev_text' => '&laquo; 前',
						'next_text' => '次 &raquo;',
						'echo'      => false
					] ) ) ?>
				</div>
			<?php endif; ?>

		<?php else : // this is displayed if there are no comments so far ?>

			<?php if ( comments_open() ) : ?>
				<?php if ( ! is_singular( 'anpi' ) ) : ?>
				<p class="nocomments">
					コメントがありません。
					<?php switch ( get_post_type() ):
						case 'faq': ?>
							不明な点があったらコメントをお願いします。
							<?php break;
						default: ?>
							寂しいので、ぜひコメントを残してください。
							<?php break; endswitch; ?>
				</p>
				<?php endif; ?>
			<?php else : // comments are closed ?>
				<!-- If comments are closed. -->
				<p class="nocomments">コメントは許可されてません。</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<!-- // #discussion -->


<?php if ( comments_open() ) : ?>
	<div id="respond" class="panel panel-default">

		<div class="panel-heading">
			<i class="icon-bubble-plus"></i>
			<?php comment_form_title( 'コメントを残してください', '%s に返信する' ); ?>
		</div>

		<div class="panel-body">

			<?php if ( is_user_logged_in() || get_post_type() == 'thread' ): ?>

				<div id="cancel-comment-reply">
					<small><?php cancel_comment_reply_link( '返信をキャンセル' ) ?></small>
				</div>

				<?php
				if ( is_user_logged_in() ) {
					$action = home_url( '/wp-comments-post.php', 'https' );
					$label  = sprintf( '%sとしてコメント', $user_identity );
				} else {
					//スレッドへの匿名投稿
					$action = get_permalink();
					show_thread_error();
					$label = 'コメントを送信する';
				}
				?>

				<form action="<?php echo $action; ?>" method="post" id="commentform" class="clearfix">
					<p class="comment-text">
						<textarea placeholder="ここにコメントを記載してください" name="comment" id="comment" class="form-control"
						          rows="10" tabindex="4"><? if ( isset( $_POST['comment'] ) )
								echo esc_textarea( $_POST['comment'] ) ?></textarea>
					</p>

					<p class="comment-allowed-tags">
						<strong>利用できるHTMLタグ: </strong>
						<code class="mono"><?php echo allowed_tags(); ?></code>
					</p>

					<?php if ( ! is_user_logged_in() ): ?>
						<div class="comment-as clearfix">
							<?= WPametu::recaptcha( 'clean', 'ja' ) ?>
							<div class="alert alert-warning">
								<p>
									あなたはログインしていないので、匿名でコメントを行います。スパムロボットによるコメント投稿を防ぐため、キャプチャを入力してください。
									これによって人間であると判断します。<br/>
									ちなみに、<a class="alert-link"
									        href="<?php echo wp_login_url( get_permalink() . '#respond' ); ?>">ログイン</a>して記名コメントにすると、キャプチャを入力しなくて済みますし、<strong>責任感があるように見えます</strong>。
								</p>
							</div>
							<?php wp_nonce_field( 'thread_anonymous_reply', '_anonymous_comment_nonce' ); ?>
						</div>
					<?php endif; ?>


					<p class="text-center">
						<input class="btn btn-primary btn-block" name="submit" type="submit" id="submit" tabindex="5"
						       value="<?= esc_attr( $label ) ?>" onclick="this.value = '送信中...';"/>
						<?php comment_id_fields(); ?>
					</p>

					<?php do_action( 'comment_form', get_the_ID() ); ?>

				</form>
			<?php else : ?>
				<div class="alert alert-warning">
					<p>
						コメントをするには<a class="alert-link" href="<?= wp_registration_url() ?>">ユーザー登録</a>をした上で
						<a class="alert-link" href="<?= wp_login_url( get_permalink() ); ?>">ログイン</a>する必要があります。
					</p>
				</div>
			<?php endif; ?>
		</div><!-- //.panel-body -->

	</div><!-- //.panel -->
<?php else: ?>

	<p class="alert alert-warning">
		このページのコメントはもう閉じられてしまいました。新たにコメントを書くことはできません。
	</p>

<?php endif; // if you delete this the sky will fall on your head
