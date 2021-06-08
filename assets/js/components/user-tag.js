/*!
 * User tag UI
 *
 * @handle hametuha-user-tag
 * @deps backbone, jquery-ui-autocomplete
 */

/*global Backbone: true*/
/*global _: true*/
/*global HametuhaUserTag: true*/

const $ = jQuery


/**
 * タグオブジェクト
 *
 * @type {Hametuha.models.userTag}
 */
Hametuha.models.userTag = Backbone.Model.extend(
	/**
	 * @lends {Hametuha.models.userTag}
	 */
	{
		defaults: {
			me: false,
			name: '',
			taxonomy_id: 0,
			url: '',
			number: 0
		}
	}
);

/**
 * タグコレクション
 *
 * @type {Hametuha.collections.tagCollection}
 */
Hametuha.collections.tagCollection = Backbone.Collection.extend(
	/**
	 * @lends {Hametuha.collections.tagCollection}
	 */
	{
		models: Hametuha.models.userTag
	}
);

/**
 * タグボタン
 *
 * @type {Hametuha.views.Tag}
 */
Hametuha.views.Tag = Backbone.View.extend(
	/**
	 * @lends {Hametuha.views.Tag}
	 */
	{
		tagName: 'a',

		events: {
			'click i': 'clickHandler'
		},

		initialize: function () {
			_.bindAll( this, 'render', 'clickHandler' );
			this.model.bind( 'change', this.render );
		},

		render: function () {
			this.$el.html(
				this.model.get( 'name' ) +
				'(' + ( this.model.get( 'number' ) > 100 ? '100+' : this.model.get( 'number' ) ) + ')' +
				'<i></i>'
			);
			this.$el.attr( 'href', this.model.get( 'url' ) )
				.attr( 'data-taxonomy-id', this.model.get( 'taxonomy_id' ) )
				.attr( 'data-term', this.model.get( 'name' ) )
				.attr( 'data-number', this.model.get( 'number' ) );
			if ( this.model.get( 'me' ) ) {
				this.$el.addClass( 'me' );
			} else {
				this.$el.removeClass( 'me' );
			}
			return this;
		},

		/**
		 * Update tag
		 *
		 * @param {Event} e
		 * @returns {boolean}
		 */
		clickHandler: function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			const self = this;
			$.ajax( this.model.get( 'me' ) ? HametuhaUserTag.tagRemove : HametuhaUserTag.tagAdd, {
				type: 'POST',
				dataType: 'json',
				data: {
					taxonomy_id: this.model.get( 'taxonomy_id' )
				},
				success: function ( result ) {
					if ( result.success ) {
						if ( result.tag ) {
							self.model.set( result.tag );
						} else {
							self.$el.remove();
							self.model.destroy();
						}
					} else {
						Hametuha.alert( result.message, true );
					}
				},
				error: function () {
					Hametuha.alert( 'タグを更新できませんした。', true );
				}
			} );
			return false;
		}
	}
);


/**
 * タグマネージャー
 *
 * @type {Hametuha.views.TagManager}
 */
Hametuha.views.TagManager = Backbone.View.extend(
	/**
	 * @lends {Hametuha.views.TagManager}
	 */
	{
		el: '#post-tags',

		/**
		 * @type {Backbone.Collection}
		 */
		collection: null,

		tagList: null,

		post_id: 0,

		events: {
			'submit #user-tag-editor': 'submitTag'
		},

		initialize: function () {
			_.bindAll( this, 'grepItem', 'appendItem', 'watchCollectionCount', 'submitTag' );
			this.tagList = $( '#user-tag-list' );
			this.post_id = this.tagList.attr( 'data-post-id' );
			// コレクションを初期化
			this.collection = new Hametuha.collections.tagCollection();
			this.tagList.find( 'a' ).each( this.grepItem );
			this.collection.bind( 'add', this.appendItem );
			// コレクションをリッスン
			this.collection.bind( 'remove', this.watchCollectionCount );
		},

		/**
		 * 現在表示されているタグを探してコレクションに追加
		 *
		 * @param {number} index
		 * @param {Object} a
		 */
		grepItem: function ( index, a ) {
			const model = new Hametuha.models.userTag();
			model.set( {
				me: $( a ).hasClass( 'me' ),
				name: $( a ).attr( 'data-term' ),
				taxonomy_id: $( a ).attr( 'data-taxonomy-id' ),
				url: $( a ).attr( 'href' ),
				number: $( a ).attr( 'data-number' )
			} );
			this.collection.add( model );
			this.appendItem( model );
		},

		/**
		 * Append tag element
		 *
		 * @param {Object} model
		 */
		appendItem: function ( model ) {
			const tag = new Hametuha.views.Tag( {
				model: model
			} );
			const a = this.$el.find( 'a[data-taxonomy-id=' + model.get( 'taxonomy_id' ) + ']' );
			if ( a.length ) {
				// Existing element.
				tag.setElement( a.get( 0 ) );
			} else {
				// New element.
				this.tagList.append( tag.render().$el );
				this.watchCollectionCount();
			}
		},

		/**
		 * タグリストにクラスを追加する
		 */
		watchCollectionCount: function () {
			if ( this.collection.length ) {
				this.tagList.removeClass( 'no-tag' );
			} else {
				this.tagList.addClass( 'no-tag' );
			}
		},

		/**
		 * タグフォーム送信
		 *
		 * @param {Event} e
		 * @returns {boolean}
		 */
		submitTag: function ( e ) {
			const input = $( e.target ).find( 'input[type=text]' );
			const term = input.val();
			const self = this;
			if ( term.length ) {
				$.ajax( HametuhaUserTag.tagCreate, {
					type: 'POST',
					dataType: 'json',
					data: {
						term: term
					},
					success: function ( result ) {
						if ( result.success ) {
							let exists = false;
							self.collection.each( function ( model ) {
								if ( model.get( 'taxonomy_id' ) === result.tag.taxonomy_id ) {
									exists = true;
									model.set( result.tag );
								}
							} );
							if ( !exists ) {
								const newTag = new Hametuha.models.userTag();
								newTag.set( result.tag );
								self.collection.add( newTag );
							}
						} else {
							Hametuha.alert( result.message, true );
						}
						input.val( '' );
						$( e.target ).find( 'input[type=submit]' ).prop( 'disabled', false );
					},
					error: function () {
						$( e.target ).find( 'input[type=submit]' ).prop( 'disabled', false );
						Hametuha.alert( 'タグを追加できませんした。', true );
					}
				} );
			}
			return false;
		}
	}
);


$( document ).ready( function () {
	// オートコンプリート
	$( 'input[type=text]', '#user-tag-editor' ).autocomplete( {
		source: HametuhaUserTag.tagSearch,
		minLength: 1,
		delay: 500,
		select: function ( e, ui ) {
			if ( ui.item && ui.item.label ) {
				$( '#user-tag-editor' ).submit();
			}
		}
	} );
	// ビューを初期化
	new Hametuha.views.TagManager();
} );
