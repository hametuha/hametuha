<?php
/**
 * 固有名詞のタグ
 */



/**
 * 固有名詞のフォーム
 */
add_action( "nouns_edit_form_fields", function( $tag, $taxonomy ) {
	wp_nonce_field( 'update_nouns', '_nounsnonce', false );
	?>
	<tr>
		<th>
			<label for="noun_type">固有名詞の種類</label>
		</th>
		<td>
			<?php $current = get_term_meta( $tag->term_id, 'noun_category', true ); ?>
			<select name="noun_category" id="noun_type">
				<option value=""<?= selected( ! $current ) ?>>指定なし</option>
				<?php foreach ( [
					'person'   => '人',
					'company' => '出版社',
					'magazine' => '雑誌',
					'prize'    => '文学賞'
				] as $key => $genre ) : ?>
					<option value="<?= $key ?>"<?php selected( $current == $key ) ?>>
						<?= $genre ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize">
		<th><label for="noun_genre_category">ジャンル</label></th>
		<td>
			<input type="text" name="noun_genre_category" id="noun_genre_category"
				   value="<?= esc_attr( get_term_meta( $tag->term_id, 'noun_genre_category', true ) ) ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="company,magazine,prize,person">
		<th><label for="noun_genre_url">URL</label></th>
		<td>
			<input type="text" name="noun_genre_url" id="noun_genre_url"
				   value="<?= esc_attr( get_term_meta( $tag->term_id, 'noun_genre_url', true ) ) ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize">
		<th><label for="noun_genre_publisher">出版社</label></th>
		<td>
			<input type="text" name="noun_genre_publisher" id="noun_genre_publisher"
				   value="<?= esc_attr( get_term_meta( $tag->term_id, 'noun_genre_publisher', true ) ) ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize">
		<th><label for="noun_genre_month">月（発売・応募）</label></th>
		<td>
			<input type="text" name="noun_genre_month" id="noun_genre_month"
				   value="<?= esc_attr( get_term_meta( $tag->term_id, 'noun_genre_month', true ) ) ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize,person">
		<th><label for="noun_genre_start">開始年（生年）</label></th>
		<td>
			<input type="number" name="noun_genre_start" id="noun_genre_start"
				   value="<?= esc_attr( get_term_meta( $tag->term_id, 'noun_genre_start', true ) ) ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="magazine,prize,person">
		<th><label for="noun_genre_end">終了年（没年）</label></th>
		<td>
			<input type="number" name="noun_genre_end" id="noun_genre_end"
				   value="<?= esc_attr( get_term_meta( $tag->term_id, 'noun_genre_end', true ) ) ?>" />
		</td>
	</tr>
	<tr class="noun-row" data-type="prize">
		<th><label for="noun_genre_magazine">掲載誌</label></th>
		<td>
			<input type="text" name="noun_genre_magazine" id="noun_genre_magazine"
				   value="<?= esc_attr( get_term_meta( $tag->term_id, 'noun_genre_magazine', true ) ) ?>" />
		</td>
	</tr>

	<?php
	add_action( 'admin_footer', function() {
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
	} );
}, 10, 2 );


/**
 * コンテンツを入力するフィールドを保存
 */
add_action( 'edit_terms', function ( $term_id, $taxonomy ) {
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

}, 20, 2 );
