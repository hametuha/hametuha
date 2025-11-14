/*!
 * 投稿ページのリスト編集画面
 *
 * @feature-group list
 * @handle hametuha-components-list-in-post
 * @deps hametuha-components-list-creator, wp-element, wp-i18n
 * @strategy defer
 */

const $ = jQuery;
const { apiFetch } = wp;
const { createRoot, useState, useEffect, useCallback } = wp.element;
const { __ } = wp.i18n;
const { toast } = wp.hametuha;

// リスト追加フォーム
$( document ).on( 'submit', '.list-save-manager', function ( e ) {
	e.preventDefault();
	var form = $( this );
	form.addClass( 'loading' );
	form.ajaxSubmit( {
		success: function ( result ) {
			form.find( 'input[type=submit]' ).attr( 'disabled', false );
			form.removeClass( 'loading' );
			var msg = $( '<div class="alert alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">閉じる</span></button></div>' );
			msg.addClass( 'alert-' + ( result.success ? 'success' : 'danger' ) )
				.append( '<span>' + result.message + '</span>' );
			form.prepend( msg );
			setTimeout( function () {
				msg.find( 'button' ).trigger( 'click' );
			}, 5000 );
		}
	} );
} );

const ListItem = ( props ) => {
	const { list, postId, onChangeHandler } = props;
	const id = `list-for-${ list.id }`;
	return (
		<div className="form-check form-switch mb-3" key={ id }>
			<input className="form-check-input" type="checkbox" role="switch" id={ id } value={ list.id } checked={ list.includes } onChange={ ( evt ) => {
				evt.preventDefault();
				onChangeHandler( list.id, ! list.includes );
			} } />
			<label className="form-check-label" htmlFor={ id }>
				{ list.title }
				（{list.count}）
				{ ( 'private' === list.status ) && (
					<span className="badge text-bg-danger">{ __( '非公開', 'hametuha' ) }</span>
				) }
				{ ( list.recommended ) && (
					<span className="badge text-bg-success">{ __( 'オススメ', 'hametuha' ) }</span>
				) }
			</label>
		</div>
	);
};

const ListSelector = ( props ) => {
	const { postId } = props;
	const [ posts, updatePosts ] = useState( [] );
	const [ loading, setLoading ] = useState( false );

	const syncLists = useCallback( () => {
		setLoading( true );
		apiFetch( {
			path: `hametuha/v1/lists/all/?includes=${ postId }`
		} ).then( ( res ) => {
			updatePosts( res );
		} ).catch( ( res ) => {
			toast( res.message, 'danger', __( 'エラー', 'hametuha' ) );
		} ).finally( () => {
			setLoading( false );
		} );
	}, [ postId ] );

	// 初期化
	useEffect( () => {
		syncLists();
	}, [ syncLists ] );

	// リストが作成されたときに同期
	useEffect( () => {
		const handleListCreated = ( e, post ) => {
			syncLists();
		};

		// jQueryで監視
		$( document ).on( 'created.hametuha', '.list-create-form', handleListCreated );

		return () => {
			$( document ).off( 'created.hametuha', '.list-create-form', handleListCreated );
		};
	}, [ syncLists ] );
	// リストが変更された
	const onChangeHandler = ( listId, newStatus ) => {
		const action = newStatus ? 'add' : 'remove';
		setLoading( true );
		apiFetch( {
			method: 'put',
			path: `hametuha/v1/lists/${listId}/?post_id=${postId}&action=${action}`,
		} ).then( ( res ) => {
			// 成功
			const newArray = [];
			for ( const list of posts ) {
				if ( list.id === listId ) {
					list.includes = newStatus;
					list.count += newStatus ? 1 : -1;
				}
				newArray.push( list );
			}
			updatePosts( newArray );
			toast( newStatus ? __( 'リストに追加しました。', 'hametuha' ) : __( 'リストから削除しました。', 'hametuha' ) );
		} ).catch( ( res ) => {
			toast( res.message, 'danger', 'エラー' );
		} ).finally( () => {
			setLoading( false );
		} );
	};

	if ( ! posts.length ) {
		return <div className="alert alert-warning">{ __( '登録されているリストがありません。', 'hametuha' ) }</div>
	}
	const classNames = [ 'mb-3', 'd-relative' ];
	if ( loading ) {
		classNames.push( 'loading' );
	}
	return (
		<div className={ classNames.join( ' ' ) }>
			{ posts.map( ( list ) => {
				return <ListItem key={ list.id } list={ list } postId={ postId } onChangeHandler={ onChangeHandler } />
			} ) }
		</div>
	);
};

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'list-form' );
	if ( container ) {
		createRoot( container ).render( <ListSelector postId={ container.dataset.postId } /> );
	}
} );
