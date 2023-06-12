<?php
/** @var \Hametuha\Rest\Doujin $this */
?>


<section id="doujin-follower">
	<div class="container">

		<div class="row followers">

			<div class="col-xs-12 follower__container" ng-controller="followed"
				 ng-class="(tabs[0].loading || tabs[1].loading ? 'follower__container--loading' : '')">

				<uib-tabset justified="true" ng-init="detectTab()">

					<!-- Followed -->
					<uib-tab active="tabs[0].active" select="initFollowers(0)">
						<uib-tab-heading>
							フォローされている
							<small ng-if="tabs[0].init && followersTotal > 1">（{{ followersTotal }}人）</small>
						</uib-tab-heading>
						<div class="alert alert-warning follower__alert" ng-if="followersTotal < 1">
							<p>フォロワーが一人もいません。頑張って増やしましょう。</p>
						</div>
						<ul class="follower__wrap" ng-cloak>
							<li class="follower__item row" ng-repeat="follower in followers">
								<div class="col-xs-3 col-md-2 text-center">
									<img class="follower__avatar img-circle" ng-src="{{follower.avatar}}"/>
								</div>
								<div class="col-xs-9 col-md-10">
									<h3 class="follower__name">
										{{ follower.display_name }}
										<small class="follower__label" ng-if="follower.isEditor">編集者</small>
										<small class="follower__label" ng-if="!follower.isEditor && follower.isAuthor">
											投稿者
										</small>
										<small class="follower__label" ng-if="!follower.isAuthor">読者</small>
									</h3>

									<a href="#" data-follower-id="{{ follower.ID }}"
									   ng-class="'btn btn-primary btn-follow' + (follower.following ? ' btn-following' : '')"
									   rel="nofollow">
										<span class="remove">フォロー中</span>
										<span class="add">
											<i class="icon-user-plus2"></i> フォローする
										</span>
										<span class="loading">
											<i class="icon-spinner2 rotation"></i> 通信中……
										</span>
									</a>
									<a ng-if="follower.isAuthor" href="/doujin/detail/{{ follower.user_nicename }}/"
									   class="btn btn-default">詳細を見る</a>
								</div>
							</li>
						</ul>
						<a class="btn btn-default btn-block btn-lg" href="#" ng-click="nextFollowers()"
						   ng-if="(followersMore && followersTotal)">さらに読み込む</a>
					</uib-tab>

					<!-- Followers -->
					<uib-tab active="tabs[1].active" select="initFollowers(1)">
						<uib-tab-heading>
							フォローしている
							<small ng-if="tabs[1].init && followingTotal > 1">（{{ followingTotal }}人）</small>
						</uib-tab-heading>
						<div class="alert alert-warning follower__alert" ng-if="followingTotal < 1">
							<p>誰もフォローしていません。一人ぐらいフォローしてみましょう。</p>
						</div>
						<ul class="follower__wrap" ng-cloak>
							<li class="follower__item row clearfix" ng-repeat="follower in followings">
								<div class="col-xs-3 col-md-2 text-center">
									<img class="follower__avatar img-circle" ng-src="{{follower.avatar}}"/>

								</div>
								<div class="col-xs-9 col-md-10">

									<h3 class="follower__name">
										{{ follower.display_name }}
										<small class="follower__label" ng-if="follower.isEditor">編集者</small>
										<small class="follower__label" ng-if="!follower.isEditor && follower.isAuthor">
											投稿者
										</small>
										<small class="follower__label" ng-if="!follower.isAuthor">読者</small>
									</h3>
									<a href="#" ng-click="removeFollowing(follower.ID)" ng-class="'btn btn-danger'"
									   rel="nofollow">
										フォロー解除
									</a>
									<a ng-if="follower.isAuthor" href="/doujin/detail/{{ follower.user_nicename }}/"
									   class="btn btn-default">詳細を見る</a>
								</div>
							</li>
						</ul>
						<a class="btn btn-default btn-block btn-lg" href="#" ng-click="nextFollowing()"
						   ng-if="( followingMore && followingTotal )">さらに読み込む</a>


					</uib-tab>
				</uib-tabset>


			</div><!-- //hametuFollower -->

		</div><!-- followers -->

	</div><!-- #doujin-follower -->
</section>
