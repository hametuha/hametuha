<?php
/** @var WP_Post $post */
hameplate( 'templates/editor/header', '', [
	'title'  => $this->title,
	'return' => $post->ID ? get_permalink( $post ) : home_url( '/anpi/mine/', 'https' ),
] );
?>

<div ng-controller="hametuhaEditor">

	<div class="container container--hameditor">

		<form class="hameditor hameditor--anpi" id="hameditor" data-post-type="anpi">

			<input type="hidden" name="post_id" id="post_id" value="<?= esc_attr( $post->ID ) ?>" ng-model="post.id"/>
			<input type="hidden" name="status" id="status" value="<?= esc_attr( $post->post_status ) ?>"
			       ng-model="post.status"/>

			<div class="form-group">
				<input class="form-control hameditor__title" type="text" name="post_title" id="post_title"
				       value="<?= esc_attr( $post->post_title ) ?>" ng-model="post.title"
				       placeholder="タイトルを入力してください" />
			</div>

			<div class="row" ng-cloak>
				<div class="col-xs-8">
					<p class="hameditor__meta">
						<span class="hameditor__date">
							<i class="icon-clock"></i> {{post.date | date: 'yyyy/MM/dd(EEE) HH:mm'}}
						</span>
						<small class="hameditor__modified hidden-xs">
							最終更新: {{post.modified | date: 'yy/MM/dd(EEE) HH:mm'}}
						</small>
					</p>
				</div>
				<div class="col-xs-4 text-right">
					<post-status status="{{post.status}}"></post-status>
				</div>
			</div>

			<?php
			wp_editor( $post->post_content, 'hamce', [
				'quicktags' => false,
			] )
			?>

		</form>

	</div><!-- //.container--edit -->

	<footer class="hameditor__actions">

		<div class="container">

			<button class="btn btn-default" data-target="#hameditor" ng-click="save()">
				<i class="icon-disk"></i> 保存
			</button>

			<button ng-if="'publish' != post.status" class="btn btn-primary" ng-click="publish()">公開</button>

			<a class="btn btn-link" href="{{post.url}}" ng-if="'publish' == post.status" target="_blank">
				表示
			</a>

			<button class="btn btn-link btn-link--danger" data-target="#hameditor"
			        ng-click="private()"
			        ng-if="-1 < ['publish', 'future'].indexOf(post.status)">
				非公開
			</button>

			<button class="btn btn-link btn-link--danger" data-target="#hameditor"
			        ng-click="delete('<?= home_url( '/anpi/mine/' ) ?>')">
				削除
			</button>


		</div>

		<div class="indicator">
			<i class="icon-loa"></i>
		</div>
	</footer>

</div><!-- // hameditor -->

<?php hameplate( 'templates/editor/footer' ) ?>
