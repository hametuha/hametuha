<?php
/** @var \Hametuha\Rest\Doujin $this */
/** @var bool $breadcrumb */
/** @var bool $current */
?>
<?php get_header(); ?>

	<div id="breadcrumb" itemprop="breadcrumb">
		<div class="container">
			<i class="icon-location5"></i>
			<a href="<?= home_url( '', 'http' ) ?>" rel="home"><?php bloginfo( 'name' ) ?></a>
			&gt;
			<a href="<?= get_post_type_archive_link( 'ideas' ) ?>">アイデア</a>
			&gt; <?= $breadcrumb ?>
		</div>
	</div>


	<section id="doujin-ideas" ng-app="hametuIdeas">

		<div class="container">

			<div class="row ideaList">

				<div class="col-xs-12 ideaList__container" ng-controller="ideaList"
				     ng-class="( loading ? 'ideaList__container--loading' : '')">

					<div class="page-header">
						あなたのアイデア帳
						<small ng-if="ideasTotal > 1">（{{ ideasTotal }}件）</small>
					</div>

					<div class="input-group ideaList__form">
						<input type="text" class="form-control" placeholder="アイデアを絞りこみます..." ng-model="query">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" ng-click="search(query)">絞り込み</button>
                        </span>
					</div>

					<!-- Idea List -->
					<div class="alert alert-warning ideaList__alert" ng-if="ideasTotal < 1">
						<p>
							アイデア帳は空です。
							<a href="<?= home_url( '/my/ideas/new/' ) ?>" class="alert-link"
							   data-action="post-idea">投稿する</a>か、
							<a href="<?= get_post_type_archive_link( 'ideas' ) ?>" class="alert-link">こちら</a>から見つけましょう。
						</p>
					</div>

					<ul ng-class="'ideaList__list'+ ( loading ? ' ideaList__list--loading' : '')" ng-cloak ng-init="initIdeas()">
						<li ng-class="'ideaList__item row' + (idea.location == 0.5 ? ' ideaList__item--recommended' : '')" ng-repeat="idea in ideas">
							<div class="col-xs-3 col-md-2 text-center">
								<img class="ideaList__avatar img-circle" ng-src="{{idea.avatar}}"/>

								<p class="author">
									<span ng-if="idea.own">あなた</span>
									<span ng-if="!idea.own">{{idea.author}}</span>
								</p>

								<div class="btn-group" uib-dropdown>
									<button type="button" class="btn btn-default btn-sm" uib-dropdown-toggle ng-disabled="disabled">
										<i class="icon-cog"></i> <span class="hidden-xs">アクション</span>
									</button>
									<ul class="uib-dropdown-menu" role="menu">
										<li role="menuitem">
											<a href="{{idea.permalink}}">
												確認
											</a>
										</li>
										<li role="menuitem">
											<a href="/my/ideas/recommend/{{idea.ID}}/" data-recommend="<?php the_ID(); ?>">
												他の人に薦める
											</a>
										</li>
										<li ng-if="idea.location == 0.5">
											<a href="#" ng-click="stock(idea.ID)">
												採用
											</a>
										</li>
										<li ng-if="idea.location == 0.5">
											<a href="#" ng-click="unstock(idea.ID)">
												却下
											</a>
										</li>
										<li ng-if="idea.location == 1">
											<a href="#" ng-click="unstock(idea.ID)">
												ストック解除
											</a>
										</li>
										<li role="menuitem" ng-if="idea.own">
											<a href="/my/ideas/edit/{{idea.ID}}/" data-action="edit-idea">
												編集
											</a>
										</li>
										<li role="menuitem" ng-if="idea.own">
											<a href="#" ng-click="removeIdea(idea.ID)">
												削除
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="col-xs-9 col-md-10">
								<h3 class="ideaList__title">
									{{ idea.post_title}}
								</h3>

								<p class="ideaList__metas">
									<span class="ideaList__meta ideaList__meta--calendar">
										<i class="icon-calendar"></i> {{idea.date}}
									</span>
									<span class="ideaList__meta ideaList__meta--tags">
										<i class="icon-tags"></i> {{idea.category}}
									</span>
									<span class="label label-primary" ng-if="idea.location == 1">ストック中</span>
									<span class="label label-warning ideaList__meta--recommended" ng-if="idea.location == 0.5">提案されました</span>
									<span ng-if="'private' === idea.post_status" class="label label-danger">{{idea.status}}</span>
									<span ng-if="'private' !== idea.post_status" class="label label-success">{{idea.status}}</span>
								</p>

								<p class="ideaList__content">{{idea.post_content}}</p>

								<p class="ideaList__recommended text-warning" ng-if="idea.recommendor">
									<strong>{{idea.recommendor}}</strong>さんから勧められました。
								</p>



							</div>
						</li>
					</ul>
					<a class="btn btn-default btn-block btn-lg" href="#" ng-click="nextIdeas()"
					   ng-if="(ideasMore && ideasTotal)">さらに読み込む</a>

				</div>

			</div><!-- ideas -->

		</div>
	</section>

<?php get_footer();
