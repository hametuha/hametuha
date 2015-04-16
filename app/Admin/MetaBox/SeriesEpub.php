<?php

namespace Hametuha\Admin\MetaBox;


class SeriesEpub extends SeriesBase
{

	protected $hook = 'post_submitbox_misc_actions';

	protected $nonce_key = '_editepubauthnonce';

	protected $nonce_action = 'edit_epub_auth';

	public function savePost( \WP_Post $post ) {
		$url = get_permalink($post);
		if( current_user_can('edit_others_posts') ){
			// Editor
			$status = min(2, max(0, $this->input->post('publishing_status')));
			update_post_meta($post->ID, '_kdp_status', $status);
			update_post_meta($post->ID, '_asin', $this->input->post('asin'));
			if( 2 === $status ){
				$user = new \WP_User($post->post_author);
				$body = <<<TEXT
{$user->display_name}様


ご利用ありがとうございます。破滅派編集部です。
あなたの作品集「{$post->post_title}」が販売開始されました。

詳しくは以下のURLをご覧ください。

{$url}

ご不明な点などありましたがら、気軽に破滅派までお尋ねください。

http://hametuha.com
TEXT;
				wp_mail($user->user_email, "【破滅派】 電子書籍販売開始のお知らせ", $body);
			}

		}elseif( current_user_can('edit_post', $post->ID) ){
			// Author
			if( $this->input->post('pleaser-publish') ){
				update_post_meta($post->ID, '_kdp_status', 1);
				$body = <<<TEXT
破滅派編集部

以下の作品から申請が来ています。
連絡お願い致します。

{$url}
TEXT;
				wp_mail(get_option('admin_email'), "【破滅派】 電子書籍申請", $body);
			}
			update_post_meta($post->ID, '_series_finished', (bool)$this->input->post('is_finished'));
		}
	}


	/**
	 * Editor form
	 *
	 * @param \WP_Post $post
	 */
	public function editFormX( \WP_Post $post ) {
		$status = $this->series->get_status($post->ID);
		$index = $this->series->get_status($post->ID);
		?>

		<div class="misc-pub-section misc-pub-section--epub, misc-pub-section--finished">
			<label>
				<span class="dashicons dashicons-book"></span> 連載状況:
				<input type="checkbox" name="is_finished" value="1" <?php checked($this->series->is_finished($post->ID)) ?> /> 完結済み
			</label>
		</div>

		<hr />

		<div class="misc-pub-section misc-pub-section--epub misc-pub-section--sold">
			<label>
				<span class="dashicons dashicons-money"></span> 販売状況: <strong><?= $this->series->status_label[$status] ?></strong>
				<?php if( $status > 1 ): ?>
					<br /><small>ASIN: <code><?= $this->series->get_asin($post->ID) ?></code></small>
				<?php endif; ?>
			</label>
		</div>

		<?php if( current_user_can('edit_others_posts') ): ?>
			<div class="misc-pub-section misc-pub-section--epub, misc-pub-section--status">
				<label>
					<span class="dashicons dashicons-admin-settings"></span> ステータス変更:
					<select name="publishing_status">
						<?php foreach( $this->series->status_label as $index => $value ): ?>
							<option value="<?= $index ?>"<?php selected($index === $status) ?>><?= $value ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>

			<div  class="misc-pub-section misc-pub-section--epub, misc-pub-section--asin">
				<label>
					<span class="dashicons dashicons-cart"></span> ASIN:
					<input type="text" name="asin" class="regular-text" value="<?= esc_attr($this->series->get_asin($post->ID)) ?>" />
				</label>
			</div>
		<?php endif; ?>


		<div  class="misc-pub-section misc-pub-section--epub, misc-pub-section--asin">
			<?php if( current_user_can('edit_post', $post->ID) && !$status ): ?>
				<label>
					<input type="checkbox" name="pleaser-publish" value="1" > 販売申請する
				</label>
			<?php endif; ?>
			<p class="description">
				<?php
				switch( $status ){
					case 1:
						echo '現在、販売処理を行っています。しばらくお待ち下さい。';
						break;
					case 2:
						printf('現在、販売中です。販売を取り止めたい場合は<a href="%s" target="_blank">お問い合わせ</a>からご連絡ください。', home_url('/inquiry', 'https'));
						break;
					default:
						echo '販売申請を受け付けたのち、準備が完了したものは販売開始となります。';
						break;
				}
					?>
			</p>
		</div>


		<?php if( current_user_can('edit_others_posts') ): ?>
			<div class="misc-pub-section misc-pub-section--epub misc-pub-section--sold">
				<a class="button" target="epub-publisher" href="<?= home_url("epub/publish/{$post->ID}", 'https') ?>">書き出し</a>
				<iframe name="epub-publisher" style="display: none"></iframe>
			</div>
		<?php endif; ?>
	<?php
	}


}
