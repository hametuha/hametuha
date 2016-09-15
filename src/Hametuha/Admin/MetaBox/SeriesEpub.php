<?php

namespace Hametuha\Admin\MetaBox;

/**
 * シリーズページのカスタマイズ
 *
 * @package Hametuha\Admin\MetaBox
 */
class SeriesEpub extends SeriesBase {

	protected $hook = 'post_submitbox_misc_actions';

	protected $nonce_key = '_editepubauthnonce';

	protected $nonce_action = 'edit_epub_auth';

	public function savePost( \WP_Post $post ) {
		$url = get_permalink( $post );
		// シリーズが完結したかどうか
		update_post_meta( $post->ID, '_series_finished', (bool) $this->input->post( 'is_finished' ) );
		// 編集者のみ可能なアクション
		if ( current_user_can( 'edit_others_posts' ) ) {
			// Editor
			$current = $this->series->get_status( $post->ID );
			$status = min( 2, max( 0, $this->input->post( 'publishing_status' ) ) );
			update_post_meta( $post->ID, '_kdp_status', $status );
			update_post_meta( $post->ID, '_asin', $this->input->post( 'asin' ) );
			if ( 2 === $status && 2 !== $current ) {
				$user = new \WP_User( $post->post_author );
				$kdp_url = $this->series->get_kdp_url( $post->ID );
				// メールで通知
				$body = <<<TEXT
{$user->display_name}様


ご利用ありがとうございます。破滅派編集部です。
あなたの作品集「{$post->post_title}」が販売開始されました。

作品ページ: {$url}
Amazon: {$kdp_url}

ご不明な点などありましたがら、気軽に破滅派までお尋ねください。
表紙画像などを販売開始後に変更されても、Amazonには反映されません。
破滅派編集部に必ず連絡をするようにしてください。


https://hametuha.com

TEXT;
				wp_mail( $user->user_email, '【破滅派】 電子書籍販売開始のお知らせ', $body );
				// Slackに通知
				$title = sprintf( '『%s』%s', get_the_title( $post ), $user->display_name );
				hametuha_slack( '電子書籍が販売開始されました', [[
					'fallback' => $title,
					'title' => $title,
					'title_link' => $kdp_url,
					'author_name' => $user->display_name,
					'author_link' => home_url( "/doujin/detail/{$user->user_nicename}/" ),
				    'color' => '#00928D',
				    'text' => "Amazonで確認できます。破滅派のページは<{$url}|こちら>です。",
				]] );
			}
		} elseif ( current_user_can( 'edit_post', $post->ID ) ) {
			// Author
			if ( $this->input->post( 'please-publish' ) ) {
				update_post_meta( $post->ID, '_kdp_status', 1 );
				$admin_url  = get_edit_post_link( $post->ID, 'mail' );
				$url = get_permalink( $post );
				$author = get_userdata( $post->post_author );
				// Slackに通知
				$title = sprintf( '『%s』%s', get_the_title( $post ), $author->display_name );
				hametuha_slack( '電子書籍販売申請がありました', [[
					'fallback' => $title,
					'title' => $title,
					'title_link' => $url,
					'author_name' => $author->display_name,
					'author_link' => home_url( "/doujin/detail/{$author->user_nicename}/" ),
					'color' => '#E80000',
					'text' => "<{$admin_url}|管理画面> から確認し、問題なければ公開してください。",
				]] );
				// メールで通知
				$body = <<<TEXT
破滅派編集部

以下の作品から申請が来ています。
連絡お願い致します。

『{$post->post_title}』
{$author->display_name}

公開画面: {$url}
管理画面: {$admin_url}

TEXT;
				wp_mail( get_option( 'admin_email' ), '【破滅派】 電子書籍申請', $body );
			}
		}
	}


	/**
	 * Editor form
	 *
	 * @param \WP_Post $post
	 */
	public function editFormX( \WP_Post $post ) {
		$status = $this->series->get_status( $post->ID );
		$index  = $this->series->get_status( $post->ID );
		$errors = $this->series->validate( $post );
		?>

		<div class="misc-pub-section misc-pub-section--epub, misc-pub-section--finished">
			<label>
				<span class="dashicons dashicons-book"></span> 連載状況:
				<input type="checkbox" name="is_finished"
				       value="1" <?php checked( $this->series->is_finished( $post->ID ) ) ?> /> 完結済み
			</label>
		</div>

		<hr/>

		<div class="misc-pub-section misc-pub-section--epub misc-pub-section--sold">
			<label>
				<span class="dashicons dashicons-money"></span> 販売状況:
				<strong><?= $this->series->status_label[ $status ] ?></strong>
				<?php if ( $status > 1 ) : ?>
					<br/>
					<small>ASIN: <code><?= $this->series->get_asin( $post->ID ); ?></code></small>
				<?php elseif ( $status && $errors ) : ?>
					<br />
					<strong style="color: red">この作品集には不備があるので販売できません。</strong>
				<?php endif; ?>
			</label>
		</div>

		<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>

			<div class="misc-pub-section misc-pub-section--epub misc-pub-section--order">
				<label>
					<span class="dashicons dashicons-thumbs-up"></span> 優先順位:
					<input type="number" class="regular-text" name="menu_order" value="<?= intval( $post->menu_order ) ?>" />
				</label>
				<p class="description">
					順位が高いほど優先して表示されます。編集者のみ変更できます。
				</p>
			</div>


			<div class="misc-pub-section misc-pub-section--epub misc-pub-section--status">
				<label>
					<span class="dashicons dashicons-admin-settings"></span> ステータス変更:
					<select name="publishing_status">
						<?php foreach ( $this->series->status_label as $index => $value ) : ?>
							<option value="<?= $index ?>"<?php selected( $index === $status ) ?>><?= $value ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>

			<div class="misc-pub-section misc-pub-section--epub misc-pub-section--asin">
				<label>
					<span class="dashicons dashicons-cart"></span> ASIN:
					<input type="text" name="asin" class="regular-text"
					       value="<?= esc_attr( $this->series->get_asin( $post->ID ) ) ?>"/>
				</label>
			</div>
		<?php endif; ?>


		<div class="misc-pub-section misc-pub-section--epub misc-pub-section--enroll">
			<?php if ( current_user_can( 'edit_post', $post->ID ) && ! $status ) : ?>
				<label>
					<input type="checkbox" name="please-publish" value="1"> 販売申請する
				</label>
			<?php endif; ?>
			<p class="description">
				<?php
				switch ( $status ) {
					case 1:
						if ( $errors ) {
							echo implode( '<br />', array_map( function( $string ){
								return sprintf( '<span style="color: red;"><i class="dashicons dashicons-no"></i> %s</span>', esc_html( $string)  );
							}, $errors->get_error_messages() ) );
						} else {
							echo '現在、販売処理を行っています。しばらくお待ち下さい。';
						}
						break;
					case 2:
						printf( '現在、販売中です。販売を取り止めたい場合は<a href="%s" target="_blank">お問い合わせ</a>からご連絡ください。', home_url( '/inquiry', 'https' ) );
						break;
					default:
						echo '販売申請を受け付けたのち、準備が完了したものは販売開始となります。';
						break;
				}
				?>
			</p>
		</div>

		<?php if ( current_user_can( 'publish_epub', $post->ID ) ) : ?>
			<div class="misc-pub-section misc-pub-section--epub misc-pub-section--files">
				<label>
					<span class="dashicons dashicons-format-aside"></span> ファイル:
					<?php if ( $this->files->record_exists( $post->ID ) ) : ?>
						<a href="<?php echo admin_url( 'edit.php?post_type=series&page=hamepub-files&p=' . $post->ID ) ?>">一覧</a>
					<?php else : ?>
						なし
					<?php endif; ?>
				</label>
			</div>
			<div class="misc-pub-section misc-pub-section--epub misc-pub-section--sold">
				<a class="button" target="epub-publisher" href="<?= home_url( "epub/publish/{$post->ID}", 'https' ) ?>">書き出し</a>
				<iframe name="epub-publisher" style="display: none"></iframe>
			</div>
		<?php endif; ?>
		<?php
	}


}
