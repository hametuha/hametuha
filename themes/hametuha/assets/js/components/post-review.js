/*!
 * ユーザーがレビューを送信する。
 *
 * @feature-group feedback
 * @handle hametuha-components-post-review
 * @deps wp-element, wp-i18n, wp-api-fetch, hametuha-toast
 * @strategy defer
 */

/* global ReviewObjects:false */

const { createRoot, useState } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { toast } = wp.hametuha;

const ReviewForm = ( props ) => {
	const { postId } = props;
	const [ loading, setLoading ] = useState( false );

	// 初期状態を作成：既にユーザーがつけているレビューがあればそれを、なければ空文字列
	const initialReviews = {};
	ReviewObjects.tags.forEach( ( tag ) => {
		initialReviews[ tag.slug ] = '';
		// ユーザーが既につけているレビューを探す
		for ( const term of ReviewObjects.user ) {
			if ( term.name === tag.positive || term.name === tag.negative ) {
				initialReviews[ tag.slug ] = term.name;
				break;
			}
		}
	} );

	const [ reviews, setReviews ] = useState( initialReviews );

	const classNames = [ 'feeling-wrapper position-relative' ];
	if ( loading ) {
		classNames.push( 'loading' );
	}

	return (
		<div className={ classNames.join( ' ' ) }>
			{ ReviewObjects.tags.map( ( tag ) => {
				const options = [
					{
						label: tag.negative,
						value: tag.negative,
					},
					{
						label: __( '評価なし', 'hametuha' ),
						value: '',
					},
					{
						label: tag.positive,
						value: tag.positive,
					},
				];

				return (
					<div key={ `${ tag.slug }-container` } className="mb-2 pt-2 pb-2 feeling-control">
						<h3 className="feeling-control__title text-center mb-2">{ tag.label }</h3>
						<div className="feeling-control__inputs row">
							{ options.map( ( option, index ) => {
								return (
									<div className="col-4 text-center" key={ `${ tag.slug }-${ index }` }>
										<label>
											<input
												type="radio"
												name={ `feeling-${ tag.slug }` }
												value={ option.value }
												checked={ reviews[ tag.slug ] === option.value }
												onChange={ ( evt ) => {
													setReviews( {
														...reviews,
														[ tag.slug ]: evt.target.value,
													} );
												} }
											/>
											<span>{ option.label }</span>
										</label>
									</div>
								);
							} ) }
						</div>
					</div>
				);
			} ) }
			<div className="text-center"><button disabled={ loading } className="btn btn-primary" onClick={ () => {
				setLoading( true );
				// REST APIに送るデータを構築
				const data = {};
				ReviewObjects.tags.forEach( ( tag ) => {
					data[ tag.slug ] = reviews[ tag.slug ] || '';
				} );

				apiFetch( {
					path: `hametuha/v1/feedback/review/${ postId }/`,
					method: 'POST',
					data,
				} ).then( ( res ) => {
					toast( res.message );
				} ).catch( ( res ) => {
					toast( res.message, 'danger', __( 'エラー', 'hametuha' ) );
				} ).finally( () => {
					setLoading( false );
				} );
			} }>{ __( 'レビューを送信', 'hametuha' ) }</button></div>
		</div>
	);
};

const container = document.getElementById( 'review-container' );
if ( container ) {
	createRoot( container ).render( <ReviewForm postId={ container.dataset.postId } /> );
}
