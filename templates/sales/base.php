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
			<a href="<?= home_url( '' ) ?>" rel="home"><?php bloginfo( 'name' ) ?></a>
			&gt;
			<?php if ( $breadcrumb ) : ?>
				<a href="<?= home_url( '/sales/' ) ?>">売上管理</a>
				&gt; <?= $breadcrumb ?>
			<?php else : ?>
				売上管理
			<?php endif; ?>
		</div>
	</div>


	<div class="container single">

		<div class="row row-offcanvas row-offcanvas-right">

			<div class="col-xs-12">

				<div>

					<!-- Nav tabs -->
					<ul class="nav nav-tabs nav-tabs--analytics">
						<li role="presentation" class="<?= $current ? '' : 'active' ?>">
							<a href="<?= home_url( '/sales/' ) ?>" aria-controls="statistics">
								売り上げ
							</a>
						</li>
						<li role="presentation" class="<?= 'reward' == $current ? 'active' : '' ?>">
							<a href="<?= home_url( '/sales/reward/' ) ?>" aria-controls="readers">
								確定報酬
							</a>
						</li>
						<li role="presentation" class="<?= 'payment' == $current ? 'active' : '' ?>">
							<a href="<?= home_url( '/sales/payment/' ) ?>" aria-controls="readers">
								入金履歴
							</a>
						</li>
						<li role="presentation" class="<?= 'account' == $current ? 'active' : '' ?>">
							<a href="<?= home_url( '/sales/account/' ) ?>" aria-controls="readers">
								支払い情報
							</a>
						</li>
					</ul>

					<div class="statistics statistics--main">

						<?php if ( isset( $endpoint ) ) : ?>

						<form class="form-inline statistics__form" id="analytics-date-form" method="get"
							  action="">
							<div class="form-group">
								<input type="text" class="form-control datepicker" id="from" name="from"
									   value="<?= $from ?>"/>
								<label for="from">から</label>
							</div>
							<div class="form-group">
								<input type="text" class="form-control datepicker" id="to" name="to"
									   value="<?= $to ?>"/>
								<label for="to">まで</label>
							</div>
							<button type="submit" class="btn btn-primary">更新</button>
						</form>

						<hr/>

						<?php endif; ?>

						<?php $this->load_template( 'templates/sales/graph', $graph ) ?>

					</div>

					<?php if ( ! ( $work_count = $this->series->get_owning_series( get_current_user_id() ) ) ) : ?>
						<div class="alert alert-danger">
							あなたはまだ作品を登録していません。
							破滅派経由でAmazonに出品するには、
							<a class="alert-link" href="<?= home_url( '/announcement/we-will-publish-10-ebooks/' ) ?>">KDP販売代行の経緯</a>をご覧ください。
						</div>
					<?php else : ?>
					<?php endif; ?>


				</div>

			</div>

		</div>
		<!-- //.row-offcanvas -->
	</div><!-- //.container -->

<?php get_footer(); ?>