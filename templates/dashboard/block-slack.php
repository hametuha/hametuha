<?php if ( $time = hameslack_requested_time( get_current_user_id() ) ) : ?>
	<div class="alert alert-success">
		<?= date_i18n( get_option( 'date_format' ), $time ) ?> に申し込みました。
	</div>
<?php else : ?>
	<p class="description">
        破滅派では<a href="https://slack.com/intl/ja-jp" target="_blank">Slack</a>というチャットサービスを使って編集会議を行なっています。
        イベントの企画や今後の運営方針などに興味がある方はぜひご参加ください。
    </p>
    <p>
        <button id="slack-invitation" type="button" class="btn btn-primary">申し込む</button>
        <a class="btn btn-outline-secondary" href="<?= home_url( 'faq/what-is-slack' ) ?>">くわしく</a>
    </p>
<?php endif; ?>
