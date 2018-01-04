<?php

/**
 * デフォルトのコンタクトフィールドを削除する
 *
 * @param array $contactmethods
 *
 * @return array
 * @author WP Beginners
 * @url http://www.wpbeginner.com/wp-tutorials/how-to-remove-default-author-profile-fields-in-wordpress/
 */
add_filter( 'user_contactmethods', function ( $contact_methods ) {
	$contact_methods[ 'aim' ] = 'Webサイト名';
	unset( $contact_methods[ 'jabber' ] );
	unset( $contact_methods[ 'yim' ] );
	$contact_methods[ 'twitter' ] = 'twitterアカウント';
	$contact_methods[ 'location' ] = '場所';
	$contact_methods[ 'birth_place' ] = '出身地';
	$contact_methods[ 'favorite_authors' ] = '好きな作家';
	$contact_methods[ 'favorite_words' ] = '好きな言葉';

	return $contact_methods;
}, '_hide_profile_fields', 10, 1 );

/**
 * ログイン変更リンクを追加
 *
 * @param \Hametuha\Hashboard\Pattern\Screen $page
 * @param string $child
 */
add_action( 'hashboard_after_main', function( \Hametuha\Hashboard\Pattern\Screen $page, $child ) {
	if ( 'sales' !== $page->slug() ) {
		return;
	}
	$current_user = wp_get_current_user();
	?>
	<hr />
	<h3>ニュース報酬</h3>
	<p class="description text-muted">ニュース記事を書いて1記事あたり貰える金額です。</p>
	<p>
		<a class="btn btn-primary" href="<?= home_url( '/faq-cat/news/' ) ?>">もっと詳しく</a>
	</p>
	<p>
		<strong>2,000PVを超えた記事に関して500円</strong>を受け取ることができます。
		<?php if ( $news_gurantee = \Hametuha\Model\Sales::get_instance()->get_guarantee( $current_user->ID, 'news' ) ) : ?>
			ただし、あなたの場合は<strong>最低保証額として1記事あたり<?= number_format( $news_gurantee ) ?>円が保証</strong>されています。
		<?php endif; ?>
	</p>
	<?php
}, 10, 3 );


/**
 * Filter for arguments.
 */
add_filter( 'hashboard_field_groups', function ( $args, WP_User $user, $group, $page ) {
	if ( 'profile' !== $group ) {
		return $args;
	}
	if ( ! $page ) {
		unset( $args['names']['fields']['first_name'] );
		unset( $args['names']['fields']['display_name'] );
		$args['names']['fields']['nickname']['label'] = '筆名';
		$args['names']['fields']['nickname']['group'] = 'open';
		$args['names']['fields']['nickname']['required'] = true;
		$args['names']['fields']['last_name']['label'] = 'よみがな（ひらがな）';
		$args['names']['fields']['description']['label'] = '自己紹介文';
		if ( $user->has_cap( 'edit_posts' ) ) {
			$args['names']['fields']['description']['description'] = 'この情報は公開されます。あなたのことを簡潔に説明する文章を入力してください。読者があなたを知るための手助けとなるでしょう。';
		} else {
			$args['names']['fields']['description']['description'] = '現在、破滅派では投稿者ではない人のプロフィールは表示されませんが、SNS的な機能がついた場合は表示されるようになります。さしつかえない範囲で入力してください。';
		}

	} else if ( 'contacts' == $page ) {
		$fields = [
			'url_sep' => [
				'type' => 'separator',
				'label' => 'Webサイト',
			],
			'url' => 1,
			'aim' => 2,
			'social_sep' => [
				'type' => 'separator',
				'label' => 'ソーシャル',
			],
			'twitter' => 0,
			'loc_sep' => [
				'type' => 'separator',
				'label' => '地域情報',
			],
			'location' => 1,
			'birth_place' => 2,
			'fav_sep' => [
				'type' => 'separator',
				'label' => '好きなもの',
			],
			'favorite_authors' => 0,
			'favorite_words' => 0,

		];
		foreach ( $fields as $key => $val ) {
			if ( !is_numeric( $val ) ) {
				continue;
			}
			$field = $args[ 'contacts' ][ 'fields' ][ $key ];
			switch ( $val ) {
				case 1:
					$field[ 'group' ] = 'open';
					$field[ 'col' ] = 2;
					break;
				case 2:
					$field[ 'col' ] = 2;
					$field[ 'group' ] = 'close';
					break;
			}
			switch ( $key ) {
				case 'url':
					$field[ 'description' ] = 'Webサイトやブログをお持ちの場合は記入してください。';
					break;
				case 'twitter':
					$field[ 'placeholder' ] = 'hametuha';
					break;
				case 'location':
					$field[ 'placeholder' ] = '例： 東京都港区';
					$field[ 'description' ] = '現在住んでいる地域や主な活動場所を入れてください。';
					break;
				case 'birth_place':
					$field[ 'placeholder' ] = '例： アメリカ合衆国ネブラスカ州';
					$field[ 'description' ] = '自分のアイデンティティが育まれた場所を入力してください。';
					break;
				case 'favorite_authors':
					$field[ 'description' ] = '好きな作家をカンマ区切りで入力してください。';
					break;
				case 'favorite_words':
					$field[ 'type' ] = 'textarea';
					$field[ 'description' ] = '好きな言葉を出典付きで入力してください。';
					break;
				default:
					// Do nothing.
					break;
			}
			$fields[ $key ] = $field;
		}
		$args[ 'contacts' ][ 'fields' ] = $fields;
	}
	return $args;
}, 10, 4 );
