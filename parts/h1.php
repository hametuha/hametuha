<?php
if( hametuha_is_profile_page() ){
    echo '著者一覧';
}elseif( is_ranking() ){
    echo ranking_title();
}elseif(is_home()){
	single_post_title();
}elseif(is_tax('faq_cat')){
	single_term_title('カテゴリー: ');
}elseif(is_tag()){
	single_tag_title('タグ: ');
}elseif(is_category()){
	single_cat_title('ジャンル: ');
}elseif(is_tax('topic')){
    single_term_title('掲示板トピック: ');
}elseif(is_tax()){
	single_term_title();
}elseif(is_search()){
	echo '「';
	echo the_search_query();
	echo '」の検索結果';
}elseif(is_author()){
	$author = get_queried_object();
	echo $author->display_name.'の作品一覧';
}elseif(is_singular('series')){
	echo '<span>シリーズ</span>';
	the_title();
}elseif(is_post_type_archive('thread')){
	echo '破滅派BBS';
}elseif(is_post_type_archive()){
	wp_title('');
	echo '一覧';
}elseif(is_archive()){
	echo 'アーカイブ';
}elseif(is_404()){
	echo '404: ページが見つかりませんでした';
}	