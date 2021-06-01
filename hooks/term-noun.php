<?php
/**
 * 固有名詞のタグ
 */



/**
 * 固有名詞のフォーム
 */
add_action(
	'nouns_edit_form_fields',
	function( $tag, $taxonomy ) {
		wp_nonce_field( 'update_nouns', '_nounsnonce', false );
		?>
	<tr>
		<th>
			<label for="noun_type">固有名詞の種類</label>
		</th>
		<td>
			<?php $current = get_term_meta( $tag->term_id, 'noun_category', true ); ?>
			<select name="noun_category" id="noun_type">
				<option value=""<?php echo selected( ! $current ); ?>>指定なし</option>
				<?php
				foreach ( [
					'person'   => '人',
					'company'  => '企業・団体',
					'magazine' => '雑誌',
					'prize'    => '文学賞',
				] as $key => $genre ) :
					?>
					<option value="<?php echo $key; ?>"<?php selected( $current == $key ); ?>>
						<?php echo $genre; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize">
		<th><label for="noun_genre_category">ジャンル</label></th>
		<td>
			<input type="text" name="noun_genre_category" id="noun_genre_category"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_category', true ) ); ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="company,magazine,prize,person">
		<th><label for="noun_genre_url">URL</label></th>
		<td>
			<input type="text" name="noun_genre_url" id="noun_genre_url" class="regular-text"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_url', true ) ); ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize">
		<th><label for="noun_genre_publisher">出版社</label></th>
		<td>
			<input type="text" name="noun_genre_publisher" id="noun_genre_publisher"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_publisher', true ) ); ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="prize">
		<th><label for="noun_genre_month">公募〆切月</label></th>
		<td>
			<input type="text" name="noun_genre_month" id="noun_genre_month"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_month', true ) ); ?>" />月
			<p class="description">
				毎月、随時なら<code>*</code>を入力。複数月ある場合はカンマ区切り（6,12）
			</p>
		</td>
	</tr>
	<tr class="noun-row" data-type="prize">
		<th><label for="noun_genre_money">賞金</label></th>
		<td>
			<input type="text" name="noun_genre_money" id="noun_genre_money"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_money', true ) ); ?>" />円
		</td>
	</tr>
	<tr class="noun-row" data-type="prize">
		<th><label for="noun_genre_limit">募集枚数</label></th>
		<td>
			<input type="text" name="noun_genre_limit" id="noun_genre_limit" placeholder="ex. 250, 300-500"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_limit', true ) ); ?>" />枚
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine">
		<th><label for="noun_genre_frequency">月の発売回数</label></th>
		<td>
			月<input type="text" name="noun_genre_frequency" id="noun_genre_frequency"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_frequency', true ) ); ?>" />回
			<p class="description">
				月刊誌なら1、週刊誌なら4、隔月なら0.5。
			</p>
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize,person,company">
		<th><label for="noun_genre_start">開始年（生年）</label></th>
		<td>
			<input type="number" name="noun_genre_start" id="noun_genre_start" placeholder="ex. <?php echo date( 'Y' ); ?>"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_start', true ) ); ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize,person,company">
		<th><label for="noun_genre_end">終了年（没年）</label></th>
		<td>
			<input type="number" name="noun_genre_end" id="noun_genre_end"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_end', true ) ); ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="prize">
		<th><label for="noun_genre_magazine">掲載誌</label></th>
		<td>
			<input type="text" name="noun_genre_magazine" id="noun_genre_magazine"
				   value="<?php echo esc_attr( get_term_meta( $tag->term_id, 'noun_genre_magazine', true ) ); ?>" />
		</td>
	</tr>

		<?php
		add_action(
			'admin_footer',
			function() {
				?>
		<style>
			.noun-row{
				display: none;
			}
		</style>
		<script>
			jQuery(document).ready(function($){
			  var $select = $('#noun_type');
			  var label = function(){
				var curVal = $select.val();
				$('.noun-row').each(function(i, row){
					if (curVal) {
					  if ($(row).attr('data-type').indexOf(curVal) > -1) {
						$(row).css('display', 'table-row');
					  } else {
						$(row).css('display', 'none');
					  }
					}else{
					  $(row).css('display', 'none');
					}
				});
			  };
			  label();
			  $select.change(label);
			});
		</script>
				<?php
			}
		);
	},
	10,
	2
);


/**
 * コンテンツを入力するフィールドを保存
 */
add_action(
	'edit_terms',
	function ( $term_id, $taxonomy ) {
		if ( 'nouns' !== $taxonomy ) {
			return;
		}
		if ( ! isset( $_POST['_nounsnonce'] ) || ! wp_verify_nonce( $_POST['_nounsnonce'], 'update_nouns' ) ) {
			return;
		}
		foreach ( $_POST as $key => $val ) {
			if ( 0 !== strpos( $key, 'noun_' ) ) {
				continue;
			}
			update_term_meta( $term_id, $key, $val );
		}

	},
	20,
	2
);
