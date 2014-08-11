<?php
if(is_tax()){
	if(is_tag()){
		
	}elseif(is_category()){
		echo wpautop(category_description());
	}elseif(is_tax('faq_cat')){
		echo wpautop(term_description());
	}elseif(is_tax('topic')){
		echo wpautop(term_description());
	}
}elseif(is_search()){
	echo wpautop('「'.get_search_query()."」で破滅派内を検索しました。"); 
}elseif(is_front_page()){
	
}elseif(is_home()){
	
}elseif(is_post_type_archive('announcement') || is_post_type_archive('faq') || is_post_type_archive('info') || is_post_type_archive('news') || is_post_type_archive('thread')){
	$post_type = get_post_type_object(get_post_type());
	echo wpautop($post_type->description);
}
