<?php
/**
 * 報酬関係のフィルター
 */

/**
 * 報酬の種別を追加
 */
add_filter( 'sharee_labels', function( $labels ) {
	return array_merge( $labels, [
		'kdp'  => 'KDP',
		'task' => '依頼',
		'news' => 'ニュース',
		'lent' => '立替金'
	] );
} );

/**
 * 報酬登録アクション
 */
add_action( 'wp_ajax_hametuha_user_reward', function() {
	$input = \WPametu\Http\Input::get_instance();
	try {
		$model = \Hametuha\Sharee\Models\RevenueModel::get_instance();
		if ( ! $input->verify_nonce( 'add_user_reward' ) || ! current_user_can( 'administrator' ) ) {
			throw new \Exception( 'あなたには権限がありません。', 401 );
		}
		$user_id = $input->post( 'user_id' );
		if ( ! ( $user = get_userdata( $user_id ) ) ) {
			throw new \Exception( '該当するユーザーは存在しません。', 404 );
		}
		$type = $input->post( 'reward-type' );
		if ( ! array_key_exists( $type, $model->get_labels()) ) {
			throw new \Exception( '無効な支払い種別です。', 404 );
		}
		if ( ! ( $created = $input->post( 'created' ) ) ) {
			$created = current_time( 'mysql' );
		}
		$price = $input->post( 'price' );
		if ( ! $price || ! is_numeric( $price ) ) {
			throw new \Exception( sprintf( '金額が不正です: %s', $price ), 500 );
		}
		$vat_status = (int) $input->post( 'vat' );
		$unit       = max( 1, $input->post( 'unit' ) );
		if ( ! ( $desc = $input->post( 'description' ) ) ) {
			throw new \Exception( '適用が入力されていません。', 500 );
		}
		$needs_deducting = (bool) $input->post( 'deducting' );
		list( $price, $unit, $tax, $deducting, $total ) = \Hametuha\Master\Calculator::revenue( $price, $unit, $vat_status, $needs_deducting );
		if ( ! $model->add_revenue( $type, $user_id, $price, [
			'total'       => $total,
			'unit'        => $unit,
			'tax'         => $tax,
			'deducting'   => $deducting,
			'description' => $desc,
			'created'     => $created,
		] ) ) {
			throw new \Exception( 'データの保存に失敗しました。', 500 );
		}
		$url = home_url( '/sales/reward/' );
		$body = <<<TXT
破滅派で報酬が登録されました。

【適用】
{$desc}

【金額】
{$price}円

詳細は以下のURLをご覧ください。
{$url}
TXT;
		hametuha_notify( $user_id, '報酬が登録されました', $body );
		wp_redirect( admin_url( 'users.php?page=user-reward' ) );
		exit;
	} catch ( \Exception $e ) {
		add_filter( 'wp_die_ajax_handler', function(){
			return '_default_wp_die_handler';
		} );
		wp_die( $e->getMessage(), get_status_header_desc( $e->getCode() ), [
			'response'  => $e->getCode(),
			'back_link' => true,
		] );
	}
} );

/**
 * 報酬追加フォームを表示
 */
add_action( 'sharee_after_table', function( $table_class ) {
	if ( \Hametuha\Sharee\Table\RewardListTable::class !== $table_class ) {
		return;
	}
	if ( current_user_can( 'administrator' ) ) :
		?>
		<hr />
		<form id="user-reward-add-form" action="<?= admin_url( 'admin-ajax.php' ) ?>" method="post">
			<input type="hidden" name="action" value="hametuha_user_reward" />
			<?php wp_nonce_field( 'add_user_reward' ) ?>
			<h2>ユーザーの報酬を追加</h2>
			<table class="form-table">
				<tr>
					<th><label for="user_id">ユーザーID</label></th>
					<td>
						<?php hametuha_user_selector( 'user_id', 0, 'user_id', 'any', [ 'user-reward-select' ] ) ?>
					</td>
				</tr>
				<tr>
					<th><label for="reward-type">種別</label></th>
					<td>
						<select id="reward-type" name="reward-type">
							<option value="task">報酬</option>
							<option value="lent">立替金</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="description">適用</label></th>
					<td>
						<input class="regular-text" type="text" name="description" id="description" value="" />
					</td>
				</tr>
				<tr>
					<th><label for="price">金額</label></th>
					<td>
						<input class="regular-text" type="number" name="price" id="price" value="" />
					</td>
				</tr>
				<tr>
					<th><label for="unit">数量</label></th>
					<td>
						<input class="regular-text" type="number" name="unit" id="unit" value="1" />
					</td>
				</tr>
				<tr>
					<th>消費税</th>
					<td>
						<label>
							<input type="radio" name="vat" value="1" checked />
							内税
						</label>
						<br />
						<label>
							<input type="radio" name="vat" value="0" />
							外税
						</label>
						<br />
						<label>
							<input type="radio" name="vat" value="-1" />
							非課税
						</label>
					</td>
				</tr>
                <tr>
                    <th><?php esc_html_e( '源泉徴収', 'hametuha' ); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="deducting" value="1" checked />
                            <?php esc_html_e( 'する', 'hametuha' ); ?>
                        </label>
                        <br />
                        <label>
                            <input type="radio" name="deducting" value="0" />
							<?php esc_html_e( 'しない', 'hametuha' ); ?>
                        </label>
                    </td>
                </tr>
				<tr>
					<th><label for="created">支払い日</label></th>
					<td>
						<input class="regular-text" type="date" name="created" id="created" value="" />
					</td>
				</tr>
			</table>
			<?php submit_button( '追加' ) ?>
		</form>
	<?php
	endif;

} );

