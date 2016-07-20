<?php
/** @var \Hametuha\Rest\Sales $this */
/** @var array $endpoint */
/** @var array $account */
/** @var array $address */
?>
<div id="kdp-reward" class="stat">

	<h3><i class="icon-credit2"></i> <?= $breadcrumb ?></h3>

	<p>
		以下の情報を入力することで破滅派から報酬を受け取ることができます。
		これらの情報は株式会社破滅派によって取り扱われ、支払業務以外の目的に利用されることはありません。
	</p>

	<?php if ( ! hametuha_bank_ready() || ! hametuha_billing_ready() ) : ?>
		<div class="alert alert-warning">
			支払いに必要な情報が保存されていません。このままでは収益を受け取ることができません。
		</div>
	<?php else : ?>
		<div class="alert alert-success">
			支払情報は正しく入力されています。
		</div>
	<?php endif; ?>

	<form method="post" action="<?= home_url( '/sales/account/' ) ?>">
		<fieldset class="form-field">
			<legend>振込先</legend>
			<?php $this->nonce_field() ?>
			<p class="text-muted">
				入金先情報を入力してください。
				東京三菱UFJ銀行だと振り込み手数料が安くなります。
			</p>
			<?php foreach (
				[
					[ 'group' => '銀行名', 'branch' => '支店名' ],
					[ 'type' => '口座種別', 'number' => '口座番号' ],
				] as $keys
			) : ?>
				<div class="row">
					<?php foreach ( $keys as $key => $label ) : $id = "bank_{$key}"; ?>
						<div class="col-xs-12 col-sm-6">
							<div class="form-group">
								<label for="<?= esc_attr( $id ) ?>"><?= $label ?></label>
								<?php if ( 'type' == $key ) : ?>
									<select name="<?= $id ?>" id="<?= $id ?>" class="form-control">
										<?php foreach ( [ '普通', '当座' ] as $value ) : ?>
											<option value="<?= $value ?>" <?php selected( $value == $account[ $key ] ) ?>>
												<?= $value ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<input type="text" name="<?= $id ?>" id="<?= $id ?>" class="form-control"
								       value="<?= esc_attr( $account[ $key ] ) ?>"/>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<div class="form-group">
				<label for="bank_name">口座名義</label>
				<input type="text" name="bank_name" id="bank_name" class="form-control"
				       value="<?= esc_attr( $account['name'] ) ?>"/>


			</div>
		</fieldset>

		<fieldset class="form-field">
			<legend>あなたの情報</legend>
			<p class="text-muted">
				あなたの屋号や住所を入力してください。税務上必要な情報となります。
				住所や屋号を間違えると、確定申告の支払調書が無効になります。
			</p>
			<div class="row">
				<?php foreach ( [ 'name' => '名前<small>（会社名・屋号など）</small>', 'number' => 'マイナンバー' ] as $key => $label ) : $id = "billing_{$key}"; ?>
				<div class="col-xs-12 col-sm-6">
					<div class="form-group">
						<label for="<?= $id ?>"><?= $label ?></label>
						<input type="text" class="form-control" name="<?= $id ?>" id="<?= $id ?>" value="<?= esc_attr( $address[ $key ] ) ?>" />
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="form-group">
				<label for="billing_address">住所</label>
				<textarea class="form-control" rows="2" name="billing_address" id="billing_address"><?= esc_textarea( $address['address'] ) ?></textarea>
			</div>

		</fieldset>

		<p>
			<input type="submit" class="btn btn-primary" value="保存する"/>
		</p>
	</form>

</div><!-- .stat -->

<hr/>
