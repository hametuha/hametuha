<?php
/** @var array $notifications */
get_header();

?>

<?php get_header('breadcrumb') ?>

<div class="container archive">

    <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-xs-12 col-sm-9 main-container">

	        <div>

		        <!-- Nav tabs -->
		        <ul class="nav nav-tabs">
			        <li role="presentation"<?php if( !$type ) echo ' class="active"' ?>><a href="<?= home_url('notification/all/', 'https') ?>" aria-controls="home">すべての通知</a></li>
			        <li role="presentation"<?php if( 'works' == $type ) echo ' class="active"' ?>><a href="<?= home_url('notification/works/', 'https') ?>" aria-controls="profile">あなたの作品</a></li>
			        <li role="presentation"<?php if( 'general' == $type ) echo ' class="active"' ?>><a href="<?= home_url('notification/general/', 'https') ?>" aria-controls="messages">運営から</a></li>
		        </ul>

		        <!-- Tab panes -->
		        <div class="tab-content">
			        <div class="tab-pane active">
				        <?php if($notifications): ?>
				            <ol class="archive-container media-list">
				            <?php foreach( $notifications as $notification ): ?>
					            <li class="notificaton__item">
					                <?= $notification ?>
					            </li>
				            <?php endforeach; ?>
				            </ol>
				        <?php else: ?>
					        <div class="alert alert-danger">
						        <p>お知らせはありません。</p>
					        </div>
			            <?php endif; ?>
			        </div>
		        </div>

	        </div>



        </div><!-- //.main-container -->

        <?php contextual_sidebar() ?>

    </div><!-- // .offcanvas -->

</div><!-- //.container -->

<?php get_footer(); ?>