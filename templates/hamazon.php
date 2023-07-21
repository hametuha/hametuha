<?php
/* @var $wp_hamazon_parser WP_Hamazon_Parser */
global $wpdb, $wp_hamazon_parser, $hamazon_list, $paged;
$paged = max( 1, absint( $paged ) );
if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
	$index    = isset( $_GET['SearchIndex'] ) ? (string) $_GET['SearchIndex'] : 'Blended';
	$per_page = ( $index == 'Blended' ) ? 5 : 10;
	$results  = $wp_hamazon_parser->search_with( $_GET['s'], $paged, $index );
	$total    = ( is_wp_error( $results ) || $results->Items->Request->Errors ) ? 0 : $results->Items->TotalResults;
} else {
	$per_page      = 0;
	$hamazon_posts = get_hamazon_posts();
	$total         = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
}
if ( $total > 0 ) {
	author_pagination( $total, $per_page );
}
?>
<div class="form-wrap">
	<form method="GET" action="<?php the_permalink(); ?>">
		<label>
			Amazonを検索:&nbsp;
			<input type="text" value="
			<?php
			if ( isset( $_GET['s'] ) ) {
				echo esc_attr( $_GET['s'] );}
			?>
			" name="s" />&nbsp;
		</label>
		<select name="SearchIndex">
			<?php foreach ( $wp_hamazon_parser->searchIndex as $k => $v ) : ?>
			<option value="<?php echo $k; ?>"
									  <?php
										if ( ( isset( $_GET['SearchIndex'] ) && $_GET['SearchIndex'] == $k ) || ( ! isset( $_GET['s'] ) && $k == 'Books' ) ) {
											echo ' selected="selected"';}
										?>
			>
				<?php echo $v; ?>
			</option>
			<?php endforeach; ?>
		</select>
		<input class="button" type="submit" style="cursor:pointer;" value="検索" />
	</form>
</div>
<div class="post-content">
<?php
the_content();
?>
<?php
if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) :
	//Amazon検索
	if ( $total > 0 ) :
		?>
		<p class="message notice">
			&quot;<?php the_search_query(); ?>&quot;でAmazonを検索しました。<?php echo $total; ?>件見つかりました。
		</p>
		<ol>
			<?php
			$counter = 0;
			foreach ( $results->Items->Item as $item ) :
				$counter++;
				?>
			<li>
				<?php echo $hamazon_list->format_amazon( $item->ASIN ); ?>
			</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="message warning">
			&quot;<?php the_search_query(); ?>&quot;に該当する商品は見つかりませんでした。
		</p>
		<?php
	endif;
else :
	//普通の一覧
	if ( empty( $hamazon_posts ) ) :
		?>
		<p class="message warning">
			紹介された書籍は見つかりませんでした。
		</p>
	<?php else : ?>
		<dl>
		<?php
		$counter = 0;
		foreach ( $hamazon_posts as $p ) :
			$counter++;
			?>
			<dt class="clearfix">
				<?php $obj = get_post_type_object( $p->post_type ); ?>
				<strong><?php echo $counter + $paged * get_option( 'posts_per_page' ); ?>. </strong>
				［<?php echo $obj->labels->name; ?>］&nbsp;
				<a href="<?php echo get_permalink( $p->ID ); ?>"><?php echo $p->post_title; ?></a>
			</dt>
			<dd>
				<p>
					<?php echo get_avatar( $p->post_author, 20 ); ?>
					<?php echo $wpdb->get_var( $wpdb->prepare( "SELECT display_name FROM {$wpdb->users} WHERE ID = %d", $p->post_author ) ); ?>
					@<?php echo mysql2date( 'Y/m/d', $p->post_date ); ?>
				</p>
				<?php echo_hamazon( $p->post_content ); ?>
			</dd>
				<?php endforeach; ?>
		</dl>
		<?php
	endif;
endif;
?>
</div>
<?php
if ( $total > 0 ) {
	author_pagination( $total, $per_page );
}
