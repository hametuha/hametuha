<?php
/**
 * Help center related hooks.
 *
 * @package hametuha
 */


/**
 * Facebookチャットを表示する
 */
add_action( 'wp_footer', function () {
	static $did = false;
	if ( $did ) {
		return;
	}
	if ( is_singular( 'faq' ) || is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) || is_page( 'help' ) ) {
		?>
		<!-- Your customer chat code -->
		<div class="fb-customerchat"
			attribution=setup_tool
			page_id="196103120449777"
			theme_color="#000000"
			logged_in_greeting="めつかれさまです。なにかお困りですか？"
			logged_out_greeting="めつかれさまです。なにかお困りですか？">
		</div>
		<?php
	}
} );

/**
 * FAQの閲覧を制限する
 */
add_filter( 'hamelp_access_type', function ( $types ) {
	if ( isset( $types['contributor'] ) ) {
		unset( $types['contributor'] );
	}
	unset( $types['author'] );
	$types['writer'] = [
		'label'    => '著者',
		'callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	];
	return $types;
} );

/**
 * よくある質問に関連記事を追加する
 */
add_filter( 'related_posts_post_types', function ( $post_types ) {
	$post_types[] = 'faq';
	return $post_types;
} );

/**
 * よくある質問のスコアを調整する
 */
add_filter( 'related_posts_taxonomy_score', function ( $scores, $post_type ) {
	if ( 'faq' === $post_type ) {
		$scores = [
			'faq_cat' => 10,
		];
	}
	return $scores;
}, 10, 2 );

/**
 * よくある質問のメインタクソノミーを変更する
 */
add_filter( 'related_post_patch_main_taxonomy', function ( $taxonomy, $post ) {
	if ( 'faq' === $post->post_type ) {
		$taxonomy = 'faq_cat';
	}
	return $taxonomy;
}, 10, 2 );

/**
 * Hamelp AI Overview のユーザーロール表示を日本語化
 *
 * @param string[] $roles 表示するロール（ホワイトリストでフィルタ済み）
 * @param WP_User  $user  ユーザーオブジェクト
 * @return string[]
 */
add_filter( 'hamelp_display_user_roles', function ( $roles, $user ) {
	if ( $user->has_cap( 'manage_options' ) ) {
		return [ '編集長' ];
	} elseif ( $user->has_cap( 'edit_others_posts' ) ) {
		return [ '編集者' ];
	} elseif ( $user->has_cap( 'edit_posts' ) ) {
		return [ '作家' ];
	} else {
		return [ '読者' ];
	}
}, 10, 2 );

/**
 * Hamelp AI Overview のユーザーコンテキストをカスタマイズ
 *
 * ロールごとに異なる対応方針を AI に指示する。
 *
 * @param string  $context ユーザーコンテキスト
 * @param WP_User $user    ユーザーオブジェクト
 * @return string
 */
add_filter( 'hamelp_user_context', function ( $context, $user ) {
	if ( $user->has_cap( 'edit_others_posts' ) ) {
		// 編集長・編集者（運営スタッフ）
		$context .= "\n\n[Response Guidelines] This user is a staff member.";
		$context .= ' After answering, if there are any improvements for the FAQ, add a brief "📝 FAQ Improvement Note" section.';
	} elseif ( $user->has_cap( 'edit_posts' ) ) {
		// 作家
		$work_count  = get_author_work_count( $user->ID );
		$registered  = mysql2date( 'Y', $user->user_registered );
		$latest      = get_author_latest_published( $user->ID );
		$latest_date = $latest ? mysql2date( 'Y-m', $latest ) : null;

		$context .= sprintf( "\nPublished works: %d", $work_count );
		$context .= sprintf( "\nMember since: %s", $registered );
		if ( $latest_date ) {
			$context .= sprintf( "\nLast published: %s", $latest_date );
		}

		$context .= "\n\n[Response Guidelines] This user is a writer.";
		if ( $work_count <= 3 ) {
			$context .= ' They are a newcomer with few works. Explain posting methods and basic features politely.';
		} elseif ( $latest && strtotime( $latest ) < strtotime( '-1 year' ) ) {
			$context .= ' They have not posted recently. Welcome them warmly with "Welcome back! We look forward to your new work."';
		} else {
			$context .= ' They are an experienced writer. Add advanced tips if relevant.';
		}
		$context .= ' For feature requests or site improvement suggestions, guide them to the forum (/thread/).';
	} else {
		// 読者
		$context .= "\n\n[Response Guidelines] This user is a reader.";
		$context .= ' Actively introduce features for enjoying reading (favorites, following authors, comments, etc.).';
	}
	return $context;
}, 10, 2 );
