/*!
 * HashBoard Requests
 *
 * @handle hametuha-hb-posts
 * @deps wp-api-fetch, wp-element, wp-i18n, hametuha-loading-indicator, hametuha-pagination, cookie-tasting, wp-url
 */

const { render, createRoot, Component } = wp.element;
const { __, sprintf } = wp.i18n;
const { LoadingIndicator, Pagination, classNames } = wp.hametuha;
const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

const div = document.getElementById( 'post-list-container' );
const { postType, as } = div.dataset;

class PostList extends Component {

	constructor( props ) {
		super( props );
		this.state = {
			loading: false,
			posts: [],
			totalPage: 0,
			currentPage: 1,
			s: '',
		};
	}

	fetch( page ) {
		this.setState( { loading: true }, () => {
			const args = {
				paged: page,
			};
			if ( this.state.s.length ) {
				args.s = this.state.s;
			}
			let path;
			switch ( as ) {
				case 'reader':
					path = 'hametuha/v1/reading/' + this.props.postType;
					break
				case 'author':
				default:
					path = 'hametuha/v1/mine/' + this.props.postType;
					break;
			}
			apiFetch( {
				path: addQueryArgs( path, args ),
			} ).then( res => {
				this.setState( {
					posts: res.posts,
					totalPage: res.total,
					currentPage: page,
				} );
			} ).catch( res => {
				console.log( res );
			} ).finally( () => {
				this.setState( { loading: false } );
			} );
		} );
	}

	componentDidMount() {
		this.fetch( 1 );
	}

	render() {
		const { posts, currentPage, totalPage, loading } = this.state;
		const mainclass =  [ 'hb-posts-list', 'hb-posts-list-' + this.props.postType ];
		return (
			<div className="hb-posts-list-wrapper">

				<div className="input-group mb-3">
					<input type="text" className="form-control" placeholder={ __( '検索ワードを入れてください', 'hametuha' ) }
						   aria-label={ __( '検索ワードを入れてください', 'hametuha' ) } aria-describedby="button-search2" onChange={ e => {
							   this.setState( { s: e.target.value } );
					} } />
					<div className="input-group-append">
						<button className="btn btn-outline-secondary" type="button" id="button-search2" onClick={ e => {
								e.preventDefault();
								this.fetch( 1 );
							} }>
								{ __( '検索', 'hametuha' ) }
						</button>
					</div>
				</div>

				{ posts.length ? (
					<ul className={ mainclass.join( ' ' ) }>
						{ posts.map( ( post ) => {
							const metadata = [];
							Object.keys( post.metas ).forEach( key => {
								metadata.push(
									<span className="hb-posts-list-meta">
										<i className="material-icons">{ key }</i>
										{ post.metas[ key ] }
									</span>
								);
							} );
							return (
								<li className="hb-posts-list-item" key={ post.ID }>
									<header className="hb-posts-list-header">
										<h3 className={ [ 'hb-posts-list-title', 'hb-posts-list-title-' + post.status.name ].join( ' ' ) }>
											{ post.title }
											{ !! post.parent && (
												<small className="hb-posts-list-parent">
													<span className="hb-posts-list-dash">—</span>
													<a href={ post.parent.url }>{ post.parent.title }</a>
												</small>
											) }
										</h3>
										{ post.new && (
											<span className="badge badge-danger">{ __( '新着', 'hametuha' ) }</span>
										) }
									</header>
									<div className="hb-posts-list-body">
										<span className="hb-posts-list-meta">
											<span className={ [ 'hb-posts-list-status', 'hb-posts-list-status-' + post.status.name ].join( ' ' ) }>
												{ post.status.label }
											</span>
										</span>
										{ !!post.terms.length && (
											<span className="hb-posts-list-meta">
												<i className="material-icons">sell</i> { post.terms.map( ( term ) => {
													return <a href={ term.url }>{ term.name }</a>
											} ) }

											</span>
										) }
										{ !! metadata.length && (
											metadata.map( meta => meta )
										) }
										<span className="hb-posts-list-meta">
											<i className="material-icons">calendar_month</i> { post.date }
										</span>
										{ post.updated && (
											<span className="hb-posts-list-meta">
												<i className="material-icons">update</i> { post.modified }
											</span>
										) }
									</div>
									<footer className="hb-posts-list-footer">
										<a className="btn btn-secondary btn-sm" href={ post.url } target="_blank" rel="noopener noreferrer"> { __( '確認', 'hametuha' ) }</a>
										{ !! post.edit_url && (
											<a className="btn btn-primary btn-sm" href={ post.edit_url}>{ __( '編集', 'hametuha' ) }</a>
										) }
									</footer>
								</li>
							);
						} ) }
					</ul>
				) : (
					<div className="alert alert-light" role="alert">
						<h4 className="alert-heading">{ __( '見つかりませんでした', 'hametuha' ) }</h4>
						<p>
							{ __( '該当する条件のデータは見つかりませんでした。', 'hametuha' ) }
						</p>
						<hr />
						<p className="mb-0">
							{ __( '新しい作品を投稿して、破滅派を盛り上げてください。', 'hametuha' ) }
						</p>
					</div>
				) }

				<Pagination current={ currentPage } total={ totalPage } onChange={ ( num ) => {
					this.fetch( num );
				} }/>
				<LoadingIndicator loading={ loading } />
			</div>
		);
	}

}


if ( createRoot ) {
	createRoot( div ).render( <PostList postType={ postType } /> );
} else {
	render( <PostList postType={ postType } />, div );
}
