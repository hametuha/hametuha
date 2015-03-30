<?php
/** @var $profile Hametuha\ThePost\Profile */
$profile = $post->helper;
?>
<li <?php post_class('media media--profile') ?>>

    <a class="pull-left comment-face" href="<?= $profile->permalink() ?>">
        <?= $profile->avatar(120) ?>
    </a>

    <div class="media-body">

        <!-- Title -->
        <h2 class="comment-title">
            <a href="<?= $profile->permalink() ?>">
                <ruby><?php the_author(); ?><rp>（</rp><rt><?= esc_html($profile->furigana) ?></rt><rp>）</rp></ruby>
            </a>
             <small><?= $profile->role ?></small>
        </h2>

	    <?php if( $profile->score): ?>
		    <div class="media__score">
				    <?php for( $i = 0; $i < $profile->score; $i++ ): ?>
					    ★
				    <?php endfor; ?>
		    </div>
		    <div class="media__rating">
			    <strong>
				    <?= number_format($profile->work_count) ?>作品で
				    <?= number_format($profile->score) ?>回
			    </strong>
			    <small>
				    平均 <?= number_format($profile->score / $profile->work_count, 1) ?> 回
			    </small>
		    </div>
	    <?php endif; ?>

        <!-- Post Data -->
        <ul class="list-inline">
            <?php if( !$profile->score ): ?>
			    <li>
			            <i class="icon-book"></i> <?= number_format($profile->work_count) ?>作品
			    </li>
            <?php endif; ?>
            <li class="date">
                <i class="icon-calendar2"></i> <?= $profile->registered_date() ?>登録
                <?php if( is_recent_date($profile->registered_date(false), 30) ): ?>
                    <span class="label label-danger">新人</span>
                <?php endif; ?>
            </li>
        </ul>

        <!-- Excerpt -->
        <div class="archive-excerpt">
            <p class="text-muted"><?= trim_long_sentence(strip_tags($profile->description), 200); ?></p>
        </div>


    </div>
</li>
