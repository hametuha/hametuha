<?php
if( hametuha_is_profile_page() ){

    echo '著者一覧';

}elseif( is_ranking() ){

    echo ranking_title();
    if( is_fixed_ranking() ){
        echo '<span class="label label-success">確定済み</span>';
    }

}elseif( is_home() ){

	wp_title('');

}elseif( is_tax('faq_cat') ){

	single_term_title('カテゴリー: ');

}elseif( is_tag() ){

	single_tag_title('タグ: ');

}elseif( is_category() ){

	single_cat_title('ジャンル: ');

}elseif( is_tax('topic') ){

    single_term_title('掲示板トピック: ');

}elseif( is_tax() ){

	single_term_title();

}elseif( is_search() ){

	echo '「';
	the_search_query();
	echo '」の検索結果';

}elseif(is_author()){

	$author = get_queried_object();
	echo $author->display_name.'の作品一覧';

}elseif( is_singular('series') ){

	echo '<span>シリーズ</span>';
	the_title();

}elseif( is_post_type_archive('thread') ){

	echo '破滅派BBS';

}elseif( is_post_type_archive('lists') ) {

	switch( get_query_var('my-content') ){
		case 'lists':
			echo 'あなたのリスト';
			break;
		case 'recommends':
			echo '編集部オススメ';
			break;
		default:
			echo 'みんなのリスト';
			break;
	}

}elseif( is_post_type_archive() ){

	wp_title('');
	echo '一覧';

}elseif( is_date() ){

	$date = get_query_var('year').'年';
	if( !is_year() ){
		$date .= sprintf('%d月', get_query_var('monthnum'));
	}
	if( is_day() ){
		$date .= sprintf('%d日', get_query_var('day'));
	}
	echo $date.'の投稿一覧';

}elseif( is_archive() ){

	echo 'アーカイブ';

}elseif( is_404() ) {

	echo '404: ページが見つかりませんでした';

}else{
	wp_title('');

}