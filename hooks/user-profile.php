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
 * Filter for arguments.
 */
add_filter( 'hashboard_field_groups', function ( $args, $user, $group, $page ) {
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
		$args['names']['fields']['description']['description'] = 'この情報は公開されます。あなたのことを簡潔に説明する文章を入力してください。読者があなたを知るための手助けとなるでしょう。';
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
