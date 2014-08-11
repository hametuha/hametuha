<?php
/**
 * ヘッダーなどのメタ情報に関する処理
 *
 */


/**
 * wp_titleを変更
 *
 * @param string $title
 * @param string $sep
 * @param string $seplocation
 * @return string
 */
add_filter('wp_title', function($title, $sep, $seplocation){
    if(is_singular('post')){
        global $post;
        $cats = get_the_category($post->ID);
        if(!empty($cats)){
            $cat = current($cats)->name;
        }else{
            $cat = "投稿";
        }
        $title .= "$cat {$sep} ";
    }elseif(is_singular('info')){
        $title .= "おしらせ {$sep} ";
    }elseif(is_singular('faq')){
        $title .= "よくある質問 {$sep} ";
    }elseif(is_singular('announcement')){
        $title .= "告知 {$sep} ";
    }elseif(is_singular('anpi')){
        $title .= "安否情報 {$sep} ";
    }elseif(is_singular('series')){
        $title .= "作品集 {$sep} ";
    }elseif(is_singular('thread')){
        $title .= "BBS {$sep} ";
    }elseif(is_category()){
        $title = "ジャンル: {$title}";
    }elseif(is_tag()){
        $title = "タグ: $title";
    }elseif(is_tax('faq_cat')){
        $title = "よくある質問: {$title}";
    }elseif(is_post_type_archive('thread')){
        $title = "破滅派BBS {$sep} ";
    }elseif(is_tax('topic')){
        $title = "破滅派BBSトピック: {$title}";
    }
    return $title;
}, 10, 3);



/**
 * Faviconの表示
 */
function _hametuha_favicon(){
    ?>
    <link rel="shortcut icon" href="<?= get_stylesheet_directory_uri(); ?>/assets/img/favicon.ico" />
<?php
}
add_action('admin_head', '_hametuha_favicon');
add_action('wp_head', '_hametuha_favicon');


/**
 * OGPのprefixを取得する
 *
 * @return string
 */
function hametuha_get_ogp_type(){
    // TODO: 投稿タイプや条件によってOGPを変更する
    return 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#';
}

/**
 * JetpackのOGPを消す
 *
 * @action wp_head
 */
add_action('wp_head', function(){
    remove_action('wp_head','jetpack_og_tags');
}, 1);

/**
 * OGPを出力する
 */
add_action('wp_head', function(){
    if(is_front_page() || is_singular() || is_post_type_archive() || is_author()){
        //画像の初期値を設定
        $image = get_template_directory_uri()."/assets/img/facebook-logo.jpg";
        //個別設定
        if(is_front_page()){
            $title = get_bloginfo('name');
            $url = trailingslashit(get_bloginfo('url'));
            $type = "website";
            $desc = str_replace("\n", "", get_bloginfo('description'));
            $author = '';
        }elseif(is_post_type_archive()){
            $post_obj = get_post_type_object(get_post_type());
            $title = wp_title('|', false, "right").get_bloginfo('name');
            $url = get_post_type_archive_link(get_post_type());
            $type = 'article';
            $desc = $post_obj->description;
            $author = '';
            switch(get_post_type()){
                case 'anpi':
                    $image = get_template_directory_uri().'/assets/img/banner-anpi-about.jpg';
                    break;
                case 'thread':
                    $image = get_template_directory_uri().'/assets/img/facebook-logo-bbs.jpg';
                    break;
            }
        }elseif(is_author()){
            global $wp_query;
            $user = get_userdata($wp_query->query_vars['author']);
            $title = $user->display_name;
            $type = 'profile';
            $url = get_author_posts_url($user->ID, $user->user_nice_name);
            $image = preg_replace("/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar($user->ID, 150));
            $desc = str_replace("\n", "", get_user_meta($user->ID, 'description', true));
            $author = '<meta property="profile:username" content="'.$user->user_login.'" />';
        }else{
            the_post();
            $title = wp_title('|', false, "right").get_bloginfo('name');
            $url =  get_permalink();
            $type =  'article';
            $desc = str_replace("\n", "", get_the_excerpt());
            $author = '<meta property="article:author" content="'.  get_author_posts_url(get_the_author_ID()).'" />';
            if(is_singular('thread')){
                $image = preg_replace("/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar(get_the_author_meta('ID'), 150));
            }elseif(has_post_thumbnail()){
                $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
                $image = $image[0];
            }else{
                $images = get_children("post_parent=".get_the_ID()."&post_mime_type=image&orderby=menu_order&order=ASC&posts_per_page=1");
                if(!empty($images)){
                    $image = wp_get_attachment_image_src(current($images)->ID, 'large');
                    $image = $image[0];
                }
            }
            rewind_posts();
        }
        echo <<<EOS
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="@hametuha">
<meta property="og:title" content="{$title}"/>
<meta property="og:url" content="{$url}" />
<meta property="og:image" content="{$image}" />
<meta property="og:description" content="{$desc}" />
<meta name="description" content="{$desc}" />
<meta property="og:type" content="{$type}" />
{$author}
<meta property="article:publisher" content="https://www.facebook.com/cnn" />
<meta property="og:site_name" content="破滅派｜オンライン文芸誌" />
<meta property="og:locale" content="ja_jp" />
<meta property="fb:app_id" content="196054397143922" />
<meta property="fb:admins" content="1034317368" />
EOS;
    }
}, 1);


