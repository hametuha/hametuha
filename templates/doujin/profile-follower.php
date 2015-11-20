<?php
/** @var \Hametuha\Rest\Doujin $this */
?>


<section id="doujin-follower" ng-app="hametuFollower">
	<div class="container">

		<div class="row followers">

			<div class="col-xs-12">

				<!-- Nav tabs -->
				<ul class="nav nav-tabs followers__tabs" role="tablist">
					<li role="presentation" class="active">
						<a href="#list-followers" aria-controls="list-followers" role="tab" data-toggle="tab">あなたのフォロワー</a>
					</li>
					<li role="presentation">
						<a href="#list-following" aria-controls="list-following" role="tab" data-toggle="tab">あなたがフォロー</a>
					</li>
				</ul>

				<!-- Tab panes -->
				<div class="tab-content followers__tabPanel">
					<div role="tabpanel" class="tab-pane active" id="list-followers" ng-controller="followed">
						<div class="alert alert-warning" ng-if="total < 1">
							<p>フォロワーが一人もいません。頑張って増やしましょう。</p>
						</div>
						<ul ng-cloak ng-init="getFollowers(0)">
							<li class="follower" ng-repeat="follower in followers">
								{{ follower.display_name }}
								{{ follower.ID }}
								{{ follower.user_email }}
								<img class="follower__avatar" ng-src="{{follower.avatar}}" />
							</li>
						</ul>
						<a class="btn btn-default btn-block btn-lg" href="#" ng-click="next()" ng-if="more && total">さらに読み込む</a>
					</div>
					<div role="tabpanel" class="tab-pane" id="list-following">
						<ul ng-cloak>

						</ul>
					</div>
				</div>

			</div>

		</div>

	</div>
</section>
