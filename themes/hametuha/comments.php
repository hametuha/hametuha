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

// todo: 安否情報の場合の処理を変更する
?>

	<!-- You can start editing here. -->

	<div id="discussion">
		<?php if ( ! is_singular( 'anpi' ) ) : ?>
		<h2 id="comments">
			<i class="icon-bubbles2"></i> &quot;<?php the_title(); ?>&quot;へのコメント
			<small>
				<span itemprop="commentCount"><?php echo number_format_i18n( get_comments_number() ); ?></span>件
			</small>
		</h2>
		<?php endif; ?>

		<?php if ( have_comments() ) : ?>

			<ul class="commentlist media-list">
				<?php
				wp_list_comments( [
					'type'     => 'comment',
					'callback' => 'hametuha_commment_display',
				] );
				?>
			</ul>

			<?php if ( get_comment_pages_count() > 1 ) : ?>
				<div class="link-pages comment-pages text-center">
					<?php
					echo hametuha_format_pagination( paginate_comments_links( [
						'prev_text' => '&laquo; 前',
						'next_text' => '次 &raquo;',
						'echo'      => false,
					] ) )
					?>
				</div>
			<?php endif; ?>

		<?php else : // this is displayed if there are no comments so far ?>

			<?php if ( comments_open() ) : ?>
				<?php if ( ! is_singular( 'anpi' ) ) : ?>
				<p class="nocomments">
					コメントがありません。
					<?php
					switch ( get_post_type() ) :
						case 'faq':
							?>
							この文書についてわからないこと、不明な点などがあったら気軽にコメントをお願いします。
							お問い合わせフォームなどでも受け付けています。
							<?php
							break;
						default:
							?>
							寂しいので、ぜひコメントを残してください。
							<?php
							break;
endswitch;
					?>
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

		<div class="panel-heading mb-2">
			<i class="icon-bubble-plus"></i>
			<?php comment_form_title( 'コメントを残してください', '%s に返信する' ); ?>
		</div>

		<div class="panel-body">

			<?php if ( is_user_logged_in() ) : ?>

				<div id="cancel-comment-reply">
					<small><?php cancel_comment_reply_link( '返信をキャンセル' ); ?></small>
				</div>

				<form action="<?php echo esc_url( home_url( 'wp-comments-post.php' ) ); ?>" method="post" id="commentform" class="clearfix">
					<p class="comment-text">
						<textarea placeholder="ここにコメントを記載してください" name="comment" id="comment" class="form-control"
								  rows="10" tabindex="4"><?php echo isset( $_POST['comment'] ) ? esc_textarea( $_POST['comment'] ) : ''; ?></textarea>
					</p>

					<p class="comment-allowed-tags">
						<strong>利用できるHTMLタグ: </strong>
						<code class="mono"><?php echo allowed_tags(); ?></code>
					</p>

					<p class="comment-allowed-tags">
						<strong>コメント規約: </strong>
						コメントは一度投稿されると編集・削除できません。よく確認してから投稿してください。
						誹謗中傷・攻撃的・個人情報の披瀝などに対してはアカウントの停止・削除などの措置を取ることがあります。
						また、退会後は名前を非表示の上で保存されますので、<a href="<?php echo home_url( 'copyright' ); ?>" target="_blank" rel="noopener noreferrer">著作権について</a>をご覧になってください。
					</p>

					<p class="text-center">
						<input class="btn btn-outline-primary" name="submit" type="submit" id="submit" tabindex="5"
							   value="<?php esc_attr_e( '規約に同意してコメントを投稿する', 'hametuha' ); ?>"
							   onclick="this.value = '送信中...';" />
						<?php comment_id_fields(); ?>
					</p>

					<?php do_action( 'comment_form', get_the_ID() ); ?>

				</form>
			<?php else : ?>
				<div class="alert alert-warning">
					<p>
						コメントをするには<a class="alert-link" href="<?php echo wp_registration_url(); ?>">ユーザー登録</a>をした上で
						<a class="alert-link" href="<?php echo wp_login_url( get_permalink() ); ?>">ログイン</a>する必要があります。
					</p>
				</div>
			<?php endif; ?>
		</div><!-- //.panel-body -->

	</div><!-- //.panel -->
<?php else : ?>

	<p class="alert alert-warning">
		このページのコメントはもう閉じられてしまいました。新たにコメントを書くことはできません。
	</p>

	<?php
endif; // if you delete this the sky will fall on your head
