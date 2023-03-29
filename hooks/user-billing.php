<?php
/**
 * ユーザーの支払い情報に関するページ
 */

/**
 * 振込リストを有効化する
 */
add_filter( 'sharee_should_enable', function ( $enabled, $service ) {
	switch ( $service ) {
		case 'billing':
			return true;
		default:
			return $enabled;
	}
}, 10, 2 );

/**
 * 説明文を変更する
 *
 * @param string $desc
 * @param \Hametuha\Hashboard\Pattern\Screen $screen
 * @param string $page
 */
add_filter( 'hashboard_page_description', function ( $desc, \Hametuha\Hashboard\Pattern\Screen $screen, $page ) {
	if ( 'billing' === $page && 'account' === $screen->slug() ) {
		$desc = '以下の情報を入力することで破滅派から報酬を受け取ることができます。 これらの情報は株式会社破滅派によって取り扱われ、支払業務以外の目的に利用されることはありません。';
	}
	return $desc;
}, 11, 3 );

/**
 * 税務情報の変更
 */
add_filter( 'sharee_billing_info_desc', function ( $desc ) {
	return 'あなたの屋号や住所を入力してください。税務上必要な情報となります。 住所や屋号を間違えると、確定申告の支払調書が無効になります。';
} );
add_filter( 'sharee_bank_account_desc', function ( $desc ) {
	return '入金先情報を入力してください。 三菱UFJ銀行だと振り込み手数料が安くなるので、破滅派的に助かります。';
} );

/**
 * 報酬ページに価格を追加
 *
 * @param \Hametuha\Hashboard\Pattern\Screen $page
 * @param string $child
 */
add_action( 'hashboard_after_main', function ( \Hametuha\Hashboard\Pattern\Screen $page, $child ) {
	if ( 'sales' !== $page->slug() ) {
		return;
	}
	$current_user = wp_get_current_user();
	?>
	<hr/>
	<h3>ニュース報酬</h3>
	<p class="description text-muted">ニュース記事を書いて1記事あたり貰える金額です。</p>
	<p>
		<a class="btn btn-primary" href="<?= home_url( '/faq-cat/news/' ) ?>">もっと詳しく</a>
	</p>
	<p>
		<strong>2,000pvを超えた記事に関して500円</strong>を受け取ることができます。
		<?php if ( $news_gurantee = \Hametuha\Model\Sales::get_instance()->get_guarantee( $current_user->ID, 'news' ) ) : ?>
			ただし、あなたの場合は<strong>最低保証額として1記事あたり<?= number_format( $news_gurantee ) ?>円が保証</strong>されています。
		<?php endif; ?>
	</p>
	<?php
}, 10, 3 );

/**
 * ユーザーの詳細画面に住所を表示する
 */
add_action( 'edit_user_profile', function ( WP_User $user ) {
	$address = new \Hametuha\Sharee\Master\Address( $user->ID );
	?>
	<h3>住所</h3>
	<table class="form-table">
		<tr>
			<th>郵便番号</th>
			<td><input type="text" class="regular-text" readonly
					   value="<?php echo esc_attr( $address->get_value( 'zip' ) ) ?>"/></td>
		</tr>
		<tr>
			<th>住所</th>
			<td>
				<textarea class="regular-text"
						  readonly><?php echo esc_textarea( $address->get_value( 'address' ) . "\n" . $address->get_value( 'address2' ) ) ?></textarea>
			</td>
		</tr>
		<tr>
			<th>氏名</th>
			<td><input type="text" class="regular-text" readonly
					   value="<?php echo esc_attr( $address->get_value( 'name' ) ) ?>"/></td>
		</tr>
		<tr>
			<th>電話</th>
			<td>
				<?php if ( $tel = $address->get_value( 'tel' ) ) : ?>
					<?php printf( '<a href="tel:%s">%s</a>', esc_attr( $tel ) ); ?>
				<?php else : ?>
					<span style="color:lightgrey">----</span>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<?php
} );

/**
 * 源泉出力用フォームを作成
 */
add_action( 'sharee_after_table', function ( $table_class ) {
	if ( \Hametuha\Sharee\Table\BillingListTable::class !== $table_class ) {
		return;
	}
	?>
	<h2>源泉徴収票のダウンロード</h2>
	<iframe id="gensen-downloader" name="gensen-downloader" style="display: none;"></iframe>
	<form target="gensen-downloader" method="post" action="<?= admin_url( 'admin-ajax.php' ) ?>">
		<input type="hidden" name="action" value="hametuha_gensen"/>
		<?php wp_nonce_field( 'gensen' ) ?>
		<table class="form-table">
			<tr>
				<th>
					年月
				</th>
				<td>
					<?php
					$prev_month = (int) date_i18n( 'm' ) - 1 ?: 12;
					$current_year = (int) date_i18n( 'Y' );
					if ( 12 === $prev_month ) {
						$current_year--;
					}
					?>
					<select name="year">
						<?php foreach ( \Hametuha\Sharee\Models\RevenueModel::get_instance()->available_years() as $year ) {
							printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( $year ),
								selected( $year, $current_year, false ),
								sprintf( esc_html_x( '%d年', 'Year with suffix', 'hametuha' ), $year )
							);
						} ?>
					</select>

					<select name="month">
						<?php
						for ( $i = 0; $i <= 12; $i++ ) {
							$label = $i ? sprintf( _x( '%d月', 'month num', 'hametuha' ), $i ) : esc_html__( 'すべての月', 'hametuha' );
							printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( $i ),
								selected( $i, $prev_month, false ),
								esc_html( $label )
							);
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'フォーマット', 'hametuha' ) ?>
				</th>
				<td>
					<?php foreach ( [
										'csv' => __( 'カンマ区切りテキスト', 'hametuha' ),
										'tsv' => __( 'タブ区切りテキスト', 'hametuha' ),
									] as $value => $label ) {
						printf(
							'<label style="%s"><input type="radio" name="format" value="%s" /> %s</label>',
							esc_attr( 'display: block; margin: 5px 0' ),
							esc_attr( $value ),
							esc_html( $label )
						);
					} ?>
				</td>
			</tr>
		</table>
		<p class="submit">
			<?php submit_button( 'ダウンロード' ) ?>
		</p>
	</form>
	<?php
} );

/**
 * 源泉徴収票をダウンロードする
 */
add_action( 'wp_ajax_hametuha_gensen', function () {
	try {
		if ( !wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce' ), 'gensen' ) ) {
			throw new Exception( '不正なアクセスです。' );
		}
		$format = filter_input( INPUT_POST, 'format' );
		switch ( $format ) {
			case 'csv':
				$delimiter = ',';
				break;
			case 'tsv':
				$delimiter = "\t";
				break;
			default:
				throw new \Exception( __( 'フォーマットの指定が不正です。', 'hametuha' ) );
		}
		$list = \Hametuha\Sharee\Models\RevenueModel::get_instance()->get_fixed_billing(
			filter_input( INPUT_POST, 'year' ),
			filter_input( INPUT_POST, 'month' ),
			[],
			true
		);
		if ( ! $list ) {
			throw new Exception( '該当するデータがありませんでした。' );
		}
		header( "Content-Type: application/octet-stream" );
		header( sprintf( "Content-Disposition: attachment; filename=deducting-%s.csv", date_i18n( 'YmdHis' ) ) );
		header( "Content-Transfer-Encoding: binary" );
		$csv = new SplFileObject( 'php://output', 'w' );
		foreach ( $list as $line ) {
			$address = new Hametuha\Sharee\Master\Address( $line->object_id );
			// 月、日、支払い先、適用、源泉前金額、源泉額、消費税、源泉徴収後金額、住所
			$csv->fputcsv( [
				mysql2date( 'm', $line->fixed ),
				mysql2date( 'd', $line->fixed ),
				$address->get_value( 'name' ),
				'原稿料ほか',
				round( $line->before_tax ),
				round( $line->deducting ),
				round( $line->tax ),
				round( $line->total ),
				$address->format_line(),
				get_the_author_meta( 'display_name', $line->object_id ),
			], $delimiter );
		}
		exit;
	} catch ( \Exception $e ) {
		$message = esc_html( $e->getMessage() );
		echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<title>${message}</title>
<body>
<script>
alert(document.getElementsByTagName('title')[0].innerHTML);
</script>
</body>
</head>
</html>
HTML;
	}
} );
