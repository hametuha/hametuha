/*!
 * Series Generator
 *
 * @handle hametuha-epub-generator
 * @deps wp-api-fetch, wp-element, wp-i18n, wp-url, wp-data, wp-notices
 */

const { render, useState, useEffect } = wp.element;
const { __, sprintf } = wp.i18n;
const { apiFetch } = wp;
const { addQueryArgs } = wp.url;
const { hametuhaFileListUrl } = window;

/**
 * Show notice
 *
 * @param {string} message
 * @param {string} type
 */
const notice = ( message, type = 'success' ) => {
};

const HametuhaEpubGenerator =( props ) => {

	const { postId } = props;
	const [ files, setFiles ] = useState( {
		items: [],
		total: 0,
	} );

	const updateFileList = () => {
		return apiFetch( {
			path: addQueryArgs( 'hametuha/v1/epub/files', {
				p: postId,
				'posts_per_page': 3,
			} ),
		} ).then( ( response ) => {
			setFiles( response );
		} ).catch( ( err ) => {
			window.alert( err.message );
		} );
	};

	useEffect( () => {
		updateFileList();
	}, [] );

	return (
		<div>
			<h4>
				<span className="dashicons dashicons-format-aside" />
				{ sprintf( __( 'ファイル: %d件', 'hametuha' ), files.total ) }
			</h4>
			{ ( files.items.length < 1 ) ? (
				<p className="description">{ __( 'ePubファイルはありません。', 'hametuha' ) }</p>
			) : (
				<ol className="hametuha-epub-file-list">
					{
						files.items.map( ( file ) => {
							return (
								<li>
									<code>{ file.name }</code>
									<small style={ { marginLeft: '0.5em' } }>{ file.updated }</small>
								</li>
							);
						} )
					}
				</ol>
			) }
			<p>
				<button className="button" onClick={ ( e ) => {
					e.preventDefault();
					apiFetch( {
						url: window.hametuhaFileGenerateUrl,
						method: 'post',
					} ).then( ( res ) => {
						window.alert( res.message );
						return updateFileList();
					} ).catch( ( err ) => {
						window.alert( err.message );
					} );
				} }>{ __( 'ファイル生成', 'hametuha' ) }</button>
			</p>
			<p style={ { textAlign: 'right' } }>
				<a href={ hametuhaFileListUrl }>{ __( 'ファイル一覧', 'hametuha' ) }</a>
			</p>
		</div>
	);
};

const container = document.getElementById( 'hametuha-epub-file-container' );

render( <HametuhaEpubGenerator postId={ container.dataset[ 'postId' ] } />, container );
