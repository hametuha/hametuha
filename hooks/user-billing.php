<?php
/**
 * ユーザーの支払い情報に関するページ
 */

/**
 * 支払い情報タブを追加
 */
add_filter( 'hashboard_screen_children', function( $children, $slug ) {
	if ( 'account' === $slug ) {
		$children[ 'billing' ] = '支払い情報';
	}
	return $children;
}, 10, 2 );

/**
 * 説明文を変更する
 *
 * @param string $desc
 * @param \Hametuha\Hashboard\Pattern\Screen $screen
 * @param string $page
 */
add_filter( 'hashboard_page_description', function( $desc, \Hametuha\Hashboard\Pattern\Screen $screen, $page ){
	if ( 'billing' === $page && 'account' === $screen->slug() ) {
		$desc = '以下の情報を入力することで破滅派から報酬を受け取ることができます。 これらの情報は株式会社破滅派によって取り扱われ、支払業務以外の目的に利用されることはありません。';
	}
	return $desc;
}, 10, 3 );

/**
 * アカウントページを追加
 */
add_filter( 'hashboard_field_groups', function( $args, $user, $group, $page ) {
	if ( 'account' !== $group || 'billing' !== $page ) {
		return $args;
	}
	//
	// 銀行口座
	//
	$billing_fields = [
		'label' => '振込先',
		'description' => '入金先情報を入力してください。 東京三菱UFJ銀行だと振り込み手数料が安くなるので、破滅派的に助かります。',
		'submit' => '保存',
		'action' => rest_url( '/hametuha/v1/user/billing/bank' ),
		'method' => 'POST',
		'fields' => [],
	];
	foreach ( hametuha_bank_account( $user->ID ) as $key => $value ) {
		$label = '';
		$placeholder = '';
		$options = [];
		$row = '';
		$type = 'text';
		$default = '';
		switch ( $key ) {
			case 'group':
				$label = '銀行名';
				$row = 'open';
				break;
			case 'branch':
				$label = '支店名';
				$row = 'close';
				break;
			case 'type':
				$label = '口座種別';
				$type = 'select';
				$options = [
					'普通' => '普通',
                    '当座' => '当座',
				];
				$default = '普通';
				$row = 'open';
				break;
			case 'number':
				$label = '口座番号';
				$row = 'close';
				break;
			case 'name':
				$label = '口座名義';
				break;
		}
		$field = [
            'type' => $type,
			'label' => $label,
			'value' => $value,
			'placeholder' => $placeholder,
			'group' => $row,
			'col' => $row ? 2 : 1,
		];
		if ( $options ) {
			$field['options'] = $options;
		}
		if ( $default ) {
		    $field['default'] = $default;
        }
		$billing_fields['fields'][ '_bank_' . $key ] = $field;
	}
	//
	// 請求先
	//
	$address_fields = [
		'label' => '請求情報',
		'description' => 'あなたの屋号や住所を入力してください。税務上必要な情報となります。 住所や屋号を間違えると、確定申告の支払調書が無効になります。',
		'submit' => '保存',
		'action' => rest_url( '/hametuha/v1/user/billing/address' ),
		'method' => 'POST',
		'fields' => [],
	];
	foreach ( hametuha_billing_address( $user->ID ) as $key => $value ) {
		$label = '';
		$placeholder = '';
		$options = [];
		$row = '';
		$type = 'text';
		switch ( $key ) {
			case 'name':
				$label = '名前（会社名・屋号など）';
				$row = 'open';
				break;
			case 'number':
				$label = 'マイナンバー';
				$row = 'close';
				break;
			case 'address':
				$label = '住所';
				$placeholder = 'ex. 東京都港区南青山2-11-13';
				break;
			case 'address2':
				$label = "建物・アパート";
				$row = 'open';
				$placeholder = 'ex. 南青山ビル4F';
				break;
            case 'zip':
                $label = "郵便番号";
                $row = 'close';
                $placeholder = 'ex. 107-0062';
                break;
		}
		$field = [
			'label' => $label,
			'value' => $value,
			'placeholder' => $placeholder,
			'group' => $row,
			'col' => $row ? 2 : 1,
		];
		$address_fields['fields'][ '_billing_' . $key ] = $field;
	}
	$args = [
		'billing' => $billing_fields,
		'address' => $address_fields,
	];
	return $args;
}, 10, 4 );

// ステータス表示
add_action( 'hashboard_before_fields_rendered', function( $slug, $page, $field_name ) {
	if ( 'account' !== $slug || 'billing' !== $page ) {
		return;
	}
	wp_enqueue_script( 'hametuha-hb-status-holder' );
	switch ( $field_name ) {
        case 'billing':
            $endpoint = rest_url( 'hametuha/v1/user/billing/bank' );
            break;
        case 'address':
			$endpoint = rest_url( 'hametuha/v1/user/billing/address' );
            break;
    }
	?>
    <div class="row">
        <div class="col s12">
            <div class="hb-status-display" data-endpoint="<?php echo esc_attr( $endpoint ) ?>">
                &nbsp;
            </div>
        </div>
    </div>
	<?php
}, 10, 3 );
