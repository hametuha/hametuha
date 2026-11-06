/*!
 * イベント参加に関するJSXコンポーネント
 *
 * @handle hametuha-components-event-participants
 *
 * @deps wp-api-fetch, wp-element, hametuha-common, wp-i18n
 */

const { createRoot, useState, useEffect, useCallback, useRef } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;

/**
 * イベント参加管理コンポーネント
 *
 * @param {Object} props
 * @returns {JSX.Element}
 */
const EventParticipants = ( props ) => {
	const [ state, setState ] = useState( {
		loading: false,
		inList: false,
		comment: '',
		participants: [],
		limit: 0,
		count: 0,
		error: null,
		message: null,
	} );

	// デバウンス用タイマー（useRefを使用してレンダリング間で値を保持）
	const commentTimer = useRef(null);

	// DOM要素から投稿IDを取得
	const container = document.getElementById( 'event-participants' );
	const postId = container ? container.dataset.postId : null;

	/**
	 * イベント状態を取得
	 */
	const fetchEventStatus = useCallback( async () => {
		if ( !postId ) {
			return;
		}

		setState( prev => ( { ...prev, loading: true } ) );

		try {
			const data = await apiFetch( {
				path: `hametuha/v1/participants/${ postId }/`,
				method: 'GET',
			} );

			setState( prev => ( {
				...prev,
				loading: false,
				inList: data.in_list,
				comment: data.my_comment || '',
				participants: data.participants || [],
				limit: data.limit,
				count: data.count,
			} ) );
		} catch ( error ) {
			setState( prev => ( {
				...prev,
				loading: false,
				error: 'データの取得に失敗しました。',
			} ) );
		}
	}, [ postId ] );

	/**
	 * イベントに参加
	 */
	const participate = async () => {
		setState( prev => ( { ...prev, loading: true, error: null } ) );

		try {
			const data = await apiFetch( {
				path: `hametuha/v1/participants/${ postId }/`,
				method: 'POST',
				data: {
					text: state.comment,
				},
			} );

			// 状態を更新して再取得
			await fetchEventStatus();
			setState( prev => ( {
				...prev,
				message: '参加登録が完了しました。',
			} ) );

			// メッセージを3秒後に消す
			setTimeout( () => {
				setState( prev => ( { ...prev, message: null } ) );
			}, 3000 );
		} catch ( error ) {
			setState( prev => ( {
				...prev,
				loading: false,
				error: error.message,
			} ) );
		}
	};

	/**
	 * 参加をキャンセル
	 */
	const cancelParticipation = async () => {
		if ( !confirm( '参加をキャンセルしてもよろしいですか？' ) ) {
			return;
		}

		setState( prev => ( { ...prev, loading: true, error: null } ) );

		try {
			const data = await apiFetch( {
				path: `hametuha/v1/participants/${ postId }/`,
				method: 'DELETE',
				data: {
					text: '', // キャンセル理由があれば設定
				},
			} );

			// 状態を更新して再取得
			await fetchEventStatus();
			setState( prev => ( {
				...prev,
				message: '参加をキャンセルしました。',
			} ) );

			// メッセージを3秒後に消す
			setTimeout( () => {
				setState( prev => ( { ...prev, message: null } ) );
			}, 3000 );
		} catch ( error ) {
			setState( prev => ( {
				...prev,
				loading: false,
				error: error.message,
			} ) );
		}
	};

	/**
	 * コメントを更新（デバウンス付き）
	 */
	const updateComment = ( text ) => {
		setState( prev => ( { ...prev, comment: text } ) );

		// 既存のタイマーをクリア
		if ( commentTimer.current ) {
			clearTimeout( commentTimer.current );
		}

		// 参加していない場合は更新しない
		if ( !state.inList ) {
			return;
		}

		// 500ms後に更新
		commentTimer.current = setTimeout( async () => {
			try {
				await apiFetch( {
					path: `hametuha/v1/participants/${ postId }/`,
					method: 'PUT',
					data: {
						text: text,
					},
				} );
			} catch ( error ) {
				console.error( 'Comment update error:', error );
			}
		}, 500 );
	};

	// 初期データを取得
	useEffect( () => {
		fetchEventStatus();
	}, [] );

	// クリーンアップ
	useEffect( () => {
		return () => {
			if ( commentTimer.current ) {
				clearTimeout( commentTimer.current );
			}
		};
	}, [] );

	return (
		<div className={ state.loading ? 'event-participate-react loading' : 'event-participate-react' }>
			{ /* エラーメッセージ */ }
			{ state.error && (
				<div className="alert alert-danger">
					{ state.error }
				</div>
			) }

			{ /* 成功メッセージ */ }
			{ state.message && (
				<div className="alert alert-success">
					{ state.message }
				</div>
			) }

			{ /* 参加/キャンセルボタン */ }
			{ state.inList ? (
				<div className="text-center event-participate-status">
					<span className="text-success text-lg">参加しています</span>
					<br />
					<button
						className="btn btn-delete btn-sm"
						onClick={ cancelParticipation }
						disabled={ state.loading }
					>
						{ __( 'キャンセル', 'hametuha' ) }
					</button>
				</div>
			) : (
				<div className="event-participate-join">
					{ state.count < state.limit && (
						<button
							className="btn btn-success btn-lg btn-block"
							onClick={ participate }
							disabled={ state.loading }
						>
							{ __( '参加する', 'hametuha' ) }
						</button>
					) }
				</div>
			) }

			{ /* 参加コメント */ }
			<div className="form-group event-detail-comment">
				<label htmlFor="event-comment">参加コメント</label>
				<textarea
					id="event-comment"
					className="form-control"
					value={ state.comment }
					onChange={ ( e ) => updateComment( e.target.value ) }
					disabled={ state.loading }
					placeholder={ __( '例・このイベントでとんでもないことが起きるのを楽しみにしています！', 'hametuha' ) }
				/>
				<div className="form-helper">
					参加にあたってなにかコメントがあれば書いてください。
					ログインしているユーザーに表示されます。
				</div>
			</div>
		</div>
	);
};

// DOM が読み込まれたらマウント
document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'event-participants' );
	if ( container ) {
		createRoot( container ).render( <EventParticipants /> );
	}
} );
