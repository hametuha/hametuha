<?php
/**
 * User related functions.
 */

/**
 * 現在のユーザーの名前を返す
 *
 * @return string
 */
function hametuha_user_name() {
	$user = wp_get_current_user();
	if ( $user->ID ) {
		return $user->display_name;
	} else {
		return 'ゲスト';
	}
}

/**
 * Get author's name.
 *
 * @param null|int|WP_Post $post
 * @return string
 */
function hametuha_author_name( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		$user = hametuha_get_anonymous_user();
	} else {
		$user = get_userdata( $post->post_author );
	}
	switch ( $post->post_type ) {
		case 'series':
			$owner_label  = get_post_meta( $post->ID, '_owner_label', true );
			$display_name = get_post_meta( $post->ID, '_owner_label', true ) ?: $user->display_name;
			return $display_name;
		default:
			return $user->display_name;
	}
}

/**
 * Get author's detail page URL.
 *
 * @param int $user_id
 * @return string
 */
function hametuha_author_url( $user_id ) {
	return home_url( sprintf( 'doujin/detail/%s', get_the_author_meta( 'user_nicename', $user_id ) ) );
}

/**
 * ユーザーが可能なアクションを返す
 *
 * @return array
 */
function hametuha_user_write_actions() {
	$actions = [
		'fire' => [
			get_post_type_archive_link( 'thread' ) . '#thread-add',
			'掲示板にスレ立て',
			'破滅派のなんでも相談掲示板です。ログイン有無にかぎらず匿名で投稿できます。',
			'',
			'',
		],
	];
	if ( current_user_can( 'read' ) ) {
		$actions = array_merge( [
			'megaphone' => [ '#', '安否報告をする', '最近活動が滞っている人は同人諸氏に安否をお知らせしましょう。', 'anpi-new', '' ],
			'lamp'      => [ get_post_type_archive_link( 'ideas' ) . '#create-idea', 'アイデアを投稿', false, '', '' ],
		], $actions );
	} else {
		$actions = array_merge( [
			'enter' => [ wp_login_url( $_SERVER['REQUEST_URI'] ), 'ログインする', 'すでにアカウントをお持ちの方はこちらからログインしてください。', '', '' ],
			'key3'  => [ wp_registration_url(), '登録する', 'アカウントをお持ちでない方は新たに登録してください。', false, false ],
		], $actions );
	}
	if ( current_user_can( 'edit_posts' ) ) {
		// 投稿用のリンク
		$editor_actions = [
			'file-plus' => [ admin_url( 'post-new.php' ), '新規投稿を作成', false, '', false ],
			'books'     => [ admin_url( 'edit.php' ), '作品一覧', false, '', false ],
			'stack'     => [ admin_url( 'edit.php?post_type=series' ), '作品集／連載', false, '', false ],
			'newspaper' => [ admin_url( 'post-new.php?post_type=news' ), 'ニュースを投稿する', false, '', false ],
		];
		if ( is_singular( [ 'post', 'page', 'announcement', 'series' ] ) && current_user_can( 'edit_post', get_queried_object_id() ) ) {
			// この投稿の編集権限がある
			$editor_actions = array_merge( [
				'pencil6' => [ get_edit_post_link( get_queried_object_id() ), 'このページを編集', false, '', false ],
			], $editor_actions );
		}
		if ( ( is_tax() || is_tag() || is_category() ) && current_user_can( 'manage_categories' ) ) {
			// タームの編集権限がある
			$term = get_queried_object();
			if ( is_a( $term, 'WP_Term' ) ) {
				$taxonomy = get_taxonomy( $term->taxonomy );
				$editor_actions = array_merge( [
					// translators: %1$s is taxonomy name, %2$s is term name.
					'tag' => [ get_edit_term_link( $term ), sprintf( __( '%1$s「%2$s」を編集', 'hametuha' ), $taxonomy->label, $term->name ), false, '', false ],
				], $editor_actions );
			}
		}
		$actions = array_merge( $editor_actions, $actions );
	} elseif ( current_user_can( 'read' ) ) {
		$actions['unlocked'] = [ home_url( '/become-author/' ), '同人になる', '小説・詩などの作品を破滅派で公開するには同人になる必要があります。', '', '' ];
	}

	return $actions;
}

/**
 * 現在のユーザーの登録日を返す
 *
 * @param string|bool $format
 *
 * @return string
 */
function hametuha_user_registered( $format = false ) {
	if ( ! $format ) {
		$format = get_option( 'date_format' );
	}
	$user = wp_get_current_user();
	if ( $user->ID ) {
		return mysql2date( $format, get_date_from_gmt( $user->user_registered ) );
	} else {
		return '-';
	}
}


/**
 * 現在のユーザーの役割を返す
 *
 * @param null|WP_User $user
 *
 * @return string
 */
function hametuha_user_role( $user = null ) {
	if ( is_null( $user ) ) {
		$user = wp_get_current_user();
	}
	if ( is_numeric( $user ) ) {
		$user = new WP_User( $user );
		if ( ! $user ) {
			return esc_html__( 'ゲスト', 'hametuha' );
		}
	}
	if ( ! method_exists( $user, 'has_cap' ) ) {
		return esc_html__( 'ゲスト', 'hametuha' );
	}
	if ( $user->has_cap( 'manage_options' ) ) {
		return esc_html__( '編集長', 'hametuha' );
	} elseif ( $user->has_cap( 'edit_others_posts' ) ) {
		return esc_html__( '編集者', 'hametuha' );
	} elseif ( $user->has_cap( 'edit_posts' ) ) {
		return esc_html__( '投稿者', 'hametuha' );
	} elseif ( $user->has_cap( 'subscriber' ) ) {
		return esc_html__( '読者', 'hametuha' );
	} else {
		return esc_html__( 'ゲスト', 'hametuha' );
	}
}


/**
 * 投稿作者の役割を返す
 * @global WP_Roles $wp_roles
 *
 * @param int $user_id
 *
 * @return array
 */
function get_the_author_roles( $user_id = null ) {
	global $wp_roles;
	if ( is_null( $user_id ) ) {
		$user_id = get_the_author_meta( 'ID' );
	} else {
		$user_id = (int) $user_id;
	}
	$user      = new WP_User( $user_id );
	$role_name = array();
	foreach ( $user->roles as $role ) {
		$role_name[] = $wp_roles->role_names[ $role ];
	}

	return array_map( 'translate_user_role', $role_name );
}

/**
 * 投稿作者の役割を出力する
 *
 * @param int $user_id
 * @param boolean $echo
 *
 * @return string
 */
function the_author_roles( $user_id = null, $echo = true ) {
	if ( ! is_null( $user_id ) && $user_id == 0 ) {
		$roles = 'ゲスト';
	} else {
		$roles = get_the_author_roles( $user_id );
		if ( ! empty( $roles ) ) {
			$roles = implode( ', ', array_map( '__', $roles ) );
		} else {
			$roles = '';
		}
	}
	if ( $echo ) {
		echo $roles;
	}

	return $roles;
}

/**
 * 指定した投稿者の投稿作品数を返す
 *
 * @param int $author_id 指定しない場合は現在の投稿の作者
 *
 * @return int
 */
function get_author_work_count( $author_id = null ) {
	global $wpdb;
	if ( is_null( $author_id ) ) {
		$author_id = get_the_author_meta( 'ID' );
	}
	$sql = <<<SQL
		SELECT COUNT(ID)
		FROM {$wpdb->posts}
		WHERE post_author = %d
		  AND post_type = 'post'
		  AND post_status = 'publish'
SQL;
	return (int) $wpdb->get_var( $wpdb->prepare( $sql, $author_id ) );
}

/**
 * 指定した投稿者が最後に発表した日を返す
 * @global wpdb $wpdb
 *
 * @param int $author_id
 *
 * @return string
 */
function get_author_latest_published( $author_id ) {
	global $wpdb;
	$sql = <<<SQL
		SELECT post_date FROM {$wpdb->posts}
		WHERE post_status = 'publish' AND post_type = 'post'
		 AND  post_author = %d
		ORDER BY post_date DESC
		LIMIT 1
SQL;

	return (string) $wpdb->get_var( $wpdb->prepare( $sql, $author_id ) );
}



/**
 * 登録されている投稿者の数を返す
 *
 * @param bool $doujin falseにすると投稿者以外のすべてのユーザー
 *
 * @return int
 */
function get_author_count( $doujin = true ) {
	global $wpdb;
	$sql = <<<SQL
		SELECT COUNT(ID) FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->usermeta} AS um
		ON u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}user_level'
SQL;
	if ( $doujin ) {
		$sql .= ' WHERE um.meta_value > 0';
	}

	return (int) $wpdb->get_var( $sql );
}

/**
 * ペンディング中のユーザーか否か
 * @return boolean
 */
function is_pending_user() {
	$user_id = get_current_user_id();
	if ( $user_id ) {
		return (bool) ( false !== array_search( 'pending', get_userdata( $user_id )->roles ) );
	} else {
		return false;
	}
}

/**
 * 同人の詳細ページならtrue
 *
 * @return bool|string
 */
function is_doujin_profile_page() {
	if ( \Hametuha\Rest\Doujin::class == str_replace( '\\\\', '\\', get_query_var( 'api_class' ) ) && preg_match( '#^/detail/([^/]+)/?$#', get_query_var( 'api_vars' ), $match ) ) {
		return $match[1];
	} else {
		return false;
	}
}

/**
 * 最近追加されたユーザーを返す
 *
 * @global wpdb $wpdb
 * @param int $num
 * @param int $days
 *
 * @return WP_User[]
 */
function hametuha_recent_authors( $num = 5, $days = 30 ) {
	global $wpdb;
	$now  = current_time( 'timestamp' ) - $days * 60 * 60 * 24;
	$time = date_i18n( 'Y-m-d H:i:s', $now );
	// 最近のユーザーを取得
	$query = <<<EOS
		SELECT u.*, p.ID AS post_id FROM {$wpdb->posts} AS p
		INNER JOIN {$wpdb->users} AS u
		ON p.post_author = u.ID
		WHERE p.post_type   = 'post'
		  AND p.post_status = 'publish'
		  AND p.post_date >= %s
		  AND u.user_registered >= %s
		GROUP BY u.ID
		ORDER BY u.user_registered DESC
		LIMIT %d
EOS;
	$query = $wpdb->prepare( $query, $time, $time, $num );
	$users = $wpdb->get_results( $query );
	return array_map( function( $user ) {
		return new WP_User( $user );
	}, $users );
}


/**
 * 投稿数の多い同人を返す
 *
 * @param int $period 遡る日数。0にするとすべての期間
 * @param int $num
 *
 * @return array
 */
function get_vigorous_author( $period = 0, $num = 5 ) {
	/** @var wpdb $wpdb */
	global $wpdb;
	$sub_query = '';
	if ( $period ) {
		$date      = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 60 * 60 * 24 * $period );
		$sub_query = $wpdb->prepare( 'AND p.post_date >= %s', $date );
	}
	$subquery = $period > 0 ? 'AND TO_DAYS(NOW()) - TO_DAYS(p.post_date) <= 30' : '';
	$sql      = <<<SQL
		SELECT DISTINCT u.*, COUNT(p.ID) AS count, SUM(CHAR_LENGTH(p.post_content)) AS length
		FROM {$wpdb->users} AS u
		LEFT JOIN {$wpdb->posts} AS p
		ON u.ID = p.post_author
		WHERE p.post_type = 'post'
		  AND p.post_status = 'publish'
		  {$subquery}
		GROUP BY u.ID
		ORDER BY length DESC
		LIMIT 0, {$num}
SQL;

	return $wpdb->get_results( $sql );
}


/**
 * ユーザーのプロフィール情報の埋まり具合を%で返す
 *
 * @param int $user_id
 * @param boolean $doujin
 *
 * @return int
 */
function get_user_status_sufficient( $user_id, $doujin = true ) {
	global $wpdb, $gianism;
	if ( $doujin ) {
		$total  = 1;
		$filled = 1;
		//メタキーを数える
		$meta_keys    = [
			'last_name',
			'description',
			'location',
			'birth_place',
			'favorite_words',
			'favorite_authors',
			'twitter',
		];
		$placeholders = array();
		foreach ( $meta_keys as $key ) {
			$placeholders[] = '%s';
		}
		$args           = array(
			"SELECT COUNT(umeta_id) FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key IN (" . implode( ', ', $placeholders ) . ") AND meta_value != ''",
			$user_id,
		);
		$meta_key_found = $wpdb->get_var( call_user_func_array( array(
			$wpdb,
			'prepare',
		), array_merge( $args, $meta_keys ) ) );
		//プロフィール写真
		$total ++;
		if ( has_original_picture( $user_id ) || has_gravatar( $user_id ) ) {
			$filled ++;
		}
		//パーセントを計算
		$total  += count( $meta_keys );
		$filled += $meta_key_found;

		return min( 100, round( $filled / $total * 100 ) );
	} else {
		return 100;
	}
}

/**
 * Show user selector
 *
 * @param string $name
 * @param int $selected
 * @param string $id
 * @param string $mode
 * @param array $classes
 */
function hametuha_user_selector( $name, $selected = 0, $id = '', $mode = 'any', $classes = [] ) {
	if ( ! $id ) {
		$id = $name;
	}
	$option = '';
	if ( $selected && ( $user = get_userdata( $selected ) ) ) {
		$option = sprintf(
			'<option selected value="%d">%s（%s）</option>',
			$user->ID,
			esc_html( $user->display_name ),
			esc_html( hametuha_user_role( $user ) )
		);
	}
	printf(
		'<select name="%4$s" id="%5$s" data-module="user-select" class="%2$s" data-mode="%3$s">%1$s</select>',
		$option,
		$classes ? esc_attr( implode( ' ', $classes ) ) : '',
		esc_attr( $mode ),
		esc_attr( $name ),
		esc_attr( $id )
	);
	wp_enqueue_script( 'hametuha-user-select', get_template_directory_uri() . '/assets/js/dist/components/user-select.js', [ 'select2' ], hametuha_version(), true );
	wp_localize_script( 'hametuha-user-select', 'HametuhaUserSelect', [
		'endpoint' => rest_url( '/hametuha/v1/doujin/search/' ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
	] );
	wp_enqueue_style( 'select2' );
}

/**
 * Detect if user can contact.
 *
 * @param int $user_id
 * @return bool
 */
function hametuha_user_allow_contact( $user_id ) {
	return (bool) get_user_meta( $user_id, 'allow_contact', true );
}

/**
 * Get author contact URL.
 *
 * @return string
 */
function hametuha_user_contact_url( $post = null ) {
	$post = get_post( $post );
	$page = get_page_by_path( 'inquiry/for-author' );
	if ( $page ) {
		return add_query_arg( [
			'work' => $post->ID,
		], get_permalink( $page ) );
	} else {
		return home_url( 'inquiry' );
	}

}

/**
 * Send user email
 *
 * @param int $user_id
 * @param string $subject
 * @param string $body
 */
function hametuha_notify( $user_id, $subject, $body ) {
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}
	if ( false !== strpos( $user->user_email, '@pseudo.' ) ) {
		// this is pseudo!
		return;
	}
	$body = <<<TXT
{$user->display_name} 様


お世話になります。破滅派編集部です。

{$body}

===============
破滅派オンライン文芸誌
https://hametuha.com/

このメールは自動送信です。ご質問は以下のアドレスまでお願い致します。
info@hametuha.com
TXT;

	wp_mail( $user->user_email, "[破滅派] $subject", $body, [
		'From: 破滅派編集部 <no-reply@hametuha.com>',
		'Reply-To: info@hametuha.com',
	] );
}

/**
 * Detect if this comment is
 *
 * @param int|null|WP_Comment $comment Comment object.
 * @return bool Is deleted user's comment.
 */
function hametuha_is_deleted_users_comment( $comment ) {
	$comment = get_comment( $comment );
	if ( 0 < $comment->user_id ) {
		$user = get_userdata( $comment->user_id );
		if ( ! $user ) {
			return true;
		}
	}
	return false;
}
