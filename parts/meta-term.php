<?php
if ( ! is_tax( 'nouns' ) ) {
	return;
}
if ( ! ( $term_type = get_term_meta( get_queried_object_id(), 'noun_category', true )   ) ) {
	return;
}
$keys = [
	'person' => [
		'url' => '関連URL',
	],
	'magazine' => [
		'category' => 'ジャンル',
		'publisher' => '版元',
		'frequency' => '発売月',
		'url' => '関連URL',
	],
	'company' => [
		'url' => '関連URL',
	],
	'prize' => [
		'money' => '賞金',
		'month' => '〆切',
		'limit' => '応募枚数',
		'magazine' => '掲載誌',
		'url' => '関連URL',
	],
];
if ( ! isset( $keys[$term_type] ) ) {
	return;
}
?>

<table class="metadata-table">

<?php foreach ( $keys[$term_type] as $meta_key => $label ) : ?>
	<?php if ( '' === ( $value = get_term_meta( get_queried_object_id(), "noun_genre_{$meta_key}", true ) ) ) {
		continue;
	} ?>
	<tr>
		<th class="metadata-cell metadata-th"><?= esc_html( $label ) ?></th>
		<td class="metadata-cell">
			<?php
			switch ( $meta_key ){
				case 'frequency':
					if ( 4 == $value ) {
						echo '毎週';
					} elseif ( 1 > $value ) {
						printf( '年%d回', 12 * $value );
					} elseif ( is_numeric( $value ) ) {
						printf( '月%d回', $value );
					} else {
						echo esc_html( $value );
					}
					break;
				case 'money':
					echo number_format( $value ) . '円';
					break;
				case 'limit':
					echo esc_html( $value ) . '枚';
					break;
				case 'month':
					if ( '*' == $value ) {
						echo '随時';
					} else {
						echo esc_html( $value ) . '月';
					}
					break;
				case 'url':
					printf( '<a href="%s" target="_blank">%s</a>', esc_url( $value ), hametuha_grab_domain( $value ) );
					break;
				default:
					echo esc_html( $value );
					break;
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>

</table>
