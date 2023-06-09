<?php
/** @var \Hametuha\Rest\Statistics $this */
/** @var bool $breadcrumb */
/** @var bool $current */
/** @var string $graph */
$from = $this->input->get( 'from' ) ?: date_i18n( 'Y-m-d', strtotime( '1 month ago', current_time( 'timestamp' ) ) );
$to   = $this->input->get( 'to' ) ?: date_i18n( 'Y-m-d' );
?>
<?php get_header(); ?>

	<div id="breadcrumb" itemprop="breadcrumb">
		<div class="container">
			<i class="icon-location5"></i>
			<a href="<?php echo home_url( '' ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			&gt;
			<?php if ( $breadcrumb ) : ?>
				<a href="<?php echo home_url( '/statistics/', 'https' ); ?>">統計情報</a>
				&gt; <?php echo $breadcrumb; ?>
			<?php else : ?>
				統計情報
			<?php endif; ?>
		</div>
	</div>


	<div class="container single">

		<div class="row row-offcanvas row-offcanvas-right">

			<div class="col-xs-12">

				<div>

					<!-- Nav tabs -->
					<ul class="nav nav-tabs nav-tabs--analytics">
						<li role="presentation" class="<?php echo $current ? '' : 'active'; ?>">
							<a href="<?php echo home_url( '/statistics/', 'https' ); ?>" aria-controls="statistics">
								アクセス
							</a>
						</li>
						<li role="presentation" class="<?php echo 'readers' == $current ? 'active' : ''; ?>">
							<a href="<?php echo home_url( '/statistics/readers/', 'https' ); ?>" aria-controls="readers">
								読者
							</a>
						</li>
						<li role="presentation" class="<?php echo 'traffic' == $current ? 'active' : ''; ?>">
							<a href="<?php echo home_url( '/statistics/traffic/', 'https' ); ?>" aria-controls="traffic">
								集客
							</a>
						</li>
						<?php
						/*
						<li role="presentation" class="<?= 'feedback' == $current ? 'active' : '' ?>">
							<a href="<?= home_url( '/statistics/feedback/', 'https' ) ?>" aria-controls="feedback">
								感想
							</a>
						</li>
						 */
						?>
					</ul>

					<div class="statistics statistics--main">

						<form class="form-inline statistics__form" id="analytics-date-form" method="get"
							  action="">
							<div class="form-group">
								<input type="text" class="form-control datepicker" id="from" name="from"
									   value="<?php echo $from; ?>"/>
								<label for="from">から</label>
							</div>
							<div class="form-group">
								<input type="text" class="form-control datepicker" id="to" name="to"
									   value="<?php echo $to; ?>"/>
								<label for="to">まで</label>
							</div>
							<button type="submit" class="btn btn-primary">更新</button>
						</form>

						<hr />

						<?php $this->load_template( 'templates/statistics/graph', $graph ); ?>

					</div>

					<div class="alert alert-info">
						このページでは、<strong>2014年11月以降から計測している統計情報</strong>を表示しています。
						投稿を削除した場合、それらのアクセス統計も削除されているので、正確な値は出なくなります。
						わからないことがあったら、
						<a class="alert-link" href="<?php echo home_url( 'thread/機能要望/' ); ?>" target="_blank">スレッド</a>
						で質問してください。<br/>
						<small class="description">
							【編注】2015年6月25日〜7月12日ぐらいまで不具合で集計が取れていませんでした。
							この期間は0になります。すみません。
						</small>
					</div><!-- //.alert -->

				</div>

			</div>

		</div>
		<!-- //.row-offcanvas -->
	</div><!-- //.container -->

<?php get_footer(); ?>
