<?php

/**
 * アドミンバーを常に非表示
 *
 * @filter show_admin_bar
 * @return boolean
 */
add_filter( 'show_admin_bar', function(){
    return false;
}, 1000);


/**
 * コンテキストに応じたサイドバーを読み込む
 */
function contextual_sidebar(){
    ?>
    <div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar" role="navigation">

        <button id="offcanvas-toggle" type="button" class="btn btn-primary btn-large visible-xs" data-toggle="offcanvas">
            <i class="icon-arrow-left"></i><i class="icon-arrow-right2"></i>
        </button>
    <?php
    $sidebar = '';
    if( is_singular('thread') || is_post_type_archive('thread') || is_tax('topic')  ){
        $sidebar = 'thread';
    }elseif( is_singular('faq') ||  is_post_type_archive('faq') || is_tax('faq_cat') ){
        $sidebar = 'faq';
    }else{
        $sidebar = '';
    }
    get_sidebar($sidebar);
    ?>
    </div><!-- //#sidebar -->
    <?php
}

/**
 * ヘルプ用アイコンを出力する
 *
 * @param string $string
 * @param string $place left top right bottom
 */
function help_tip($string, $place = null){
    printf('<a href="#" class="btn btn-xs btn-default help-tip" data-toggle="tooltip" data-original-title="%s"%s><i class="icon-question5"></i></a>',
           esc_attr($string), ($place ? 'data-placement="'.esc_attr($place).'"' : '') );
}

/**
 * ヴァリデーション用アイコンを出力する
 */
function input_icon(){
    echo <<<HTML
<span class="icon-checkmark form-control-feedback"></span>
<span class="icon-spam form-control-feedback"></span>
<span class="icon-close form-control-feedback"></span>
<span class="icon-loop3 rotation form-control-feedback"></span>

HTML;
}

/**
 * PRGのメッセージを変更する
 *
 * @filter wpametu_prg_message_class
 * @param string $markup
 * @param array $messages
 * @param string $class_name
 * @return string
 */
add_filter('wpametu_prg_message_class', function($markup, $messages, $class_name){
    $class_name = 'alert '.('error' == $class_name ? 'alert-danger' : 'alert-success');
    $html = <<<HTML
        <div class="%s alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
            </button>
            %s
        </div>
HTML;
    return sprintf($html, $class_name, implode('', array_map(function($msg){
        return "<p>{$msg}</p>";
    }, $messages)));
}, 10, 3);

/**
 * Change WP-Pagenavi's output
 *
 * @package hametuha
 * @filter wp_pagenavi
 * @param string $html
 * @return string
 */
add_filter( 'wp_pagenavi', function($html){
    // Remove div.
    $html = trim(preg_replace('/<\/?div([^>]*)?>/u', '', $html));
    // Wrap links with li.
    $html = preg_replace('/(<a[^>]*?>[^<]*<\/a>)/u', '<li>$1</li>', $html);
    // Wrap links with span considering class name.
    $html = preg_replace_callback('/<span([^>]*?)>[^<]*<\/span>/u', function($matches){
        if( false !== strpos($matches[1], 'current') ){
            // This is current page.
            $class_name = 'active';
        }elseif( false !== strpos($matches[1], 'pages') ){
            // This is page number.
            $class_name = 'disabled';
        }elseif( false !== strpos($matches[1], 'extend') ){
            // This is ellipsis.
            $class_name = 'disabled';
        }else{
            // No class.
            $class_name = '';
        }
        return "<li class=\"{$class_name}\">{$matches[0]}</li>";
    }, $html);
    // Wrap with ul as you like.
    return <<<HTML
<div class="row text-center">
    <ul class="pagination pagination-centered">{$html}</ul>
</div>
HTML;
}, 10, 2 );



/**
 * WordPressのリンク出力関数をBootstrap向けに直す
 *
 * @param string $pagination
 * @param string $size sm, lg, ''のいずれか
 * @return string
 */
function hametuha_format_pagination($pagination, $size = ''){
    $out = [];
    foreach(explode("\n", $pagination) as $link){
        if( false !== strpos($link, 'current') ){
            $out[] = sprintf('<li class="active">%s</li>', $link);
        }else{
            $out[] = sprintf('<li>%s</li>', $link);
        }
    }
    if( $size ){
        $size = ' pagination-'.$size;
    }
    return '<ul class="pagination pagination-centered'.$size.'">'.implode("\n", $out).'</ul>';
}


/**
 * コメント表示関数
 *
 * @param object $comment
 * @param array $args
 * @param int $depth
 */
function hametuha_commment_display( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    $class_name = [$comment->comment_type, 'media'];
	$is_author = get_the_author_meta('ID') == $comment->user_id;
	if( get_anonymous_user()->ID == $comment->user_id ){
		$is_author = false;
	}
    $class_name[] = $is_author ? 'author' : 'commentor';
    $pull = $is_author ? 'pull-right' : 'pull-left';
    switch( get_post_type($comment->comment_post_ID) ){
        case 'thread':
            $author_label = 'スレ主';
            break;
        default:
            $author_label = '著者';
            break;
    }
	if( is_singular('thread') ){
		$prop = 'suggestedAnswer';
		$type = 'http://schema.org/Answer';
	}else{
		$prop = 'comment';
		$type = 'http://schema.org/Comment';
	}
    ?>
<li id="comment-<?php comment_ID(); ?>" <?php comment_class(implode(' ', $class_name)) ?> data-depth="<?= $depth ?>" itemprop="<?= $prop ?>" itemscope itemtype="<?= $type ?>">
    <?php switch($comment->comment_type):
        case "pingback":
        case "trackback":
            break;
        default: ?>
            <div class="<?= $pull ?>">
                <?= get_avatar($comment, 120) ?>
            </div>
        <?php break; endswitch; ?>

    <div class="media-body">

        <div class="comment-author vcard">
            <h4>
                <span itemprop="author"><?= get_comment_author_link(); ?></span>
                <small><?php
                    switch($comment->comment_type){
                        case "pingback":
                        case "trackback":
                            echo '外部サイト';
                            break;
                        default:
                            echo hametuha_user_role($comment->user_id);
                            break;
                    }
                    ?> | <i class="icon-clock"></i> <span itemprop=""><?php echo get_comment_date('Y-m-d H:i'); ?></span></small>
            </h4>
        </div><!-- .comment-author .vcard -->

        <div class="comment-content" itemprop="text">
            <?php
            if( $comment->comment_approved == '0' ){
                echo '<em class="comment-awaiting-moderation">このコメントは承認待ちです。</em>';
            }else{
                comment_text();
            }
            ?>
        </div>

	    <div class="hidden" itemprop="url"><?= get_comment_link($comment) ?></div>

        <div class="reply right">
            <?php if( $comment->comment_type == 'comment' || $comment->comment_type === '' ): ?>
                <?php comment_reply_link( array_merge( $args, array( 'reply_text' => '<i class="icon-reply"></i> このコメントに返信', 'login_text' => '<i class="icon-reply"></i> ログインして返信', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
            <?php endif; ?>
            <?php edit_comment_link( '<i class="icon-pencil2"></i> このコメントを編集', '', '' ) ?>
        </div><!-- .reply -->

        <?php if( $is_author ): ?>
            <span class="label label-danger"><?= $author_label ?></span>
        <?php endif; ?>

    </div><!-- //.media-body -->

<?php
}





/**
 * 長過ぎる文字列を短くして返す
 * @param string $sentence
 * @param int $length
 * @param string $elipsis
 * @return string
 */
function trim_long_sentence($sentence, $length = 100, $elipsis = '…'){
	if(mb_strlen($sentence, 'utf-8') <= $length){
		return $sentence;
	}else{
		return mb_substr($sentence, 0, $length - 1, 'utf-8').$elipsis;
	}
}
