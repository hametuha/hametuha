/*!
 * フォロワーを表示する
 *
 * @handle hametuha-hb-followers
 * @deps wp-api-fetch, wp-element, wp-i18n, hametuha-loading-indicator, hametuha-pagination, wp-url
 */

const { createRoot, useState, useEffect, useCallback } = wp.element;
const { __ } = wp.i18n;
const { LoadingIndicator, Pagination, classNames } = wp.hametuha;

/**
 * Custom hook for followers API
 */
const useFollowers = () => {
	const [data, setData] = useState({
		followers: { users: [], total: 0, offset: 0 },
		following: { users: [], total: 0, offset: 0 },
	});
	const [loading, setLoading] = useState({ followers: false, following: false });
	const [search, setSearch] = useState({ followers: '', following: '' });

	const fetchData = useCallback(async (type, offset = 0, searchQuery = '') => {
		setLoading(prev => ({ ...prev, [type]: true }));
		try {
			const params = new URLSearchParams({
				offset: offset.toString(),
				...(searchQuery && { s: searchQuery }),
			});

			const response = await wp.apiFetch({
				path: `/hametuha/v1/doujin/${type}/me?${params}`,
			});

			setData(prev => ({
				...prev,
				[type]: {
					users: offset === 0 ? response.users : [...prev[type].users, ...response.users],
					total: response.total,
					offset: response.offset,
				},
			}));
		} catch (error) {
			console.error(`Failed to fetch ${type}:`, error);
		} finally {
			setLoading(prev => ({ ...prev, [type]: false }));
		}
	}, []);

	const removeFollowing = useCallback(async (userId) => {
		try {
			await wp.apiFetch({
				path: `/hametuha/v1/doujin/follow/${userId}`,
				method: 'DELETE',
			});

			setData(prev => ({
				...prev,
				following: {
					...prev.following,
					users: prev.following.users.filter(user => user.ID !== userId),
					total: prev.following.total - 1,
				},
			}));
		} catch (error) {
			console.error('Failed to unfollow:', error);
		}
	}, []);

	const searchUsers = useCallback((type, query) => {
		setSearch(prev => ({ ...prev, [type]: query }));
		fetchData(type, 0, query);
	}, [fetchData]);

	const loadMore = useCallback((type) => {
		const currentData = data[type];
		if (currentData.users.length < currentData.total) {
			fetchData(type, currentData.offset + 20, search[type]);
		}
	}, [data, search, fetchData]);

	return {
		data,
		loading,
		search,
		fetchData,
		removeFollowing,
		searchUsers,
		loadMore,
	};
};

/**
 * User item component
 */
const UserItem = ({ user, showUnfollow, onUnfollow }) => (
	<div className="follower__item row">
		<div className="col-xs-3">
			<img className="follower__avatar img-circle" src={user.avatar} alt={user.display_name} />
		</div>
		<div className="col-xs-9">
			<h4>
				<strong>{user.display_name}</strong>
				{user.isEditor && <small className="follower__label">編集者</small>}
				{!user.isEditor && user.isAuthor && <small className="follower__label">著者</small>}
				{!user.isAuthor && <small className="follower__label">読者</small>}
			</h4>
			<div className="follower__actions">
				{showUnfollow && (
					<button
						className="btn btn-danger btn-sm"
						onClick={() => onUnfollow && onUnfollow(user.ID)}
					>
						フォローを解除
					</button>
				)}
				{user.isAuthor && (
					<a
						href={`/doujin/detail/${user.user_nicename}/`}
						className="btn btn-link btn-sm"
					>
						作品を見る
					</a>
				)}
			</div>
		</div>
	</div>
);

/**
 * Tab component
 */
const TabContent = ({
	type,
	data,
	loading,
	searchQuery,
	onSearch,
	onLoadMore,
	onUnfollow
}) => {
	const [localSearch, setLocalSearch] = useState(searchQuery);

	const handleSearchSubmit = (e) => {
		e.preventDefault();
		onSearch(localSearch);
	};

	const showUnfollow = type === 'following';
	const emptyMessage = type === 'followers' ? 'フォロワーはいません。' : 'フォローしている人はいません。';

	return (
		<div>
			<form onSubmit={handleSearchSubmit} className="mb-3">
				<div className="input-group">
					<input
						type="text"
						className="form-control"
						placeholder="ユーザー名で検索..."
						value={localSearch}
						onChange={(e) => setLocalSearch(e.target.value)}
					/>
					<span className="input-group-btn">
						<button className="btn btn-primary" type="submit">
							検索
						</button>
					</span>
				</div>
			</form>

			{loading && data.users.length === 0 ? (
				<LoadingIndicator />
			) : (
				<>
					{data.total === 0 ? (
						<div className="alert alert-info">{emptyMessage}</div>
					) : (
						<>
							<div className="follower__wrap">
								{data.users.map((user) => (
									<UserItem
										key={user.ID}
										user={user}
										showUnfollow={showUnfollow}
										onUnfollow={onUnfollow}
									/>
								))}
							</div>

							{data.users.length < data.total && (
								<div className="text-center mt-3">
									<button
										className="btn btn-default btn-lg"
										onClick={() => onLoadMore(type)}
										disabled={loading}
									>
										{loading ? '読み込み中...' : 'さらに読み込む'}
									</button>
								</div>
							)}
						</>
					)}
				</>
			)}
		</div>
	);
};

/**
 * Main component
 */
const FollowerWrap = () => {
	const [activeTab, setActiveTab] = useState('followers');
	const { data, loading, search, fetchData, removeFollowing, searchUsers, loadMore } = useFollowers();

	// Initialize tab from URL hash
	useEffect(() => {
		const hash = window.location.hash;
		if (hash === '#following') {
			setActiveTab('following');
		}
	}, []);

	// Load data when tab changes or on initial load
	useEffect(() => {
		if (data[activeTab].users.length === 0 && !loading[activeTab]) {
			fetchData(activeTab);
		}
	}, [activeTab]); // eslint-disable-line react-hooks/exhaustive-deps

	const handleTabChange = (tab) => {
		setActiveTab(tab);
		// Update URL hash
		window.history.replaceState(null, '', `#${tab}`);
	};

	const handleUnfollow = useCallback((userId) => {
		if (window.confirm('フォローを解除してよろしいですか？')) {
			removeFollowing(userId);
		}
	}, [removeFollowing]);

	const containerClasses = ['follower__container'];
	if (loading.followers || loading.following) {
		containerClasses.push('follower__container--loading');
	}

	return (
		<div className={ containerClasses.join( ' ' ) }>
			{/* Tab Navigation */ }
			<ul className="nav nav-tabs" role="tablist">
				<li className="nav-item">
					<a
						className={ activeTab === 'followers' ? 'nav-link active' : 'nav-link' }
						href="#followers"
						onClick={ ( e ) => {
							e.preventDefault();
							handleTabChange( 'followers' );
						} }
					>
						{ __( 'フォロワー', 'hametuha' ) }
						{ data.followers.total > 0 && (
							<span className="badge text-bg-secondary">
    							{ data.followers.total }
								<span className="visually-hidden">人</span>
  							</span>
						) }
					</a>
				</li>
				<li className="nav-item">
					<a
						className={ activeTab === 'followers' ? 'nav-link active' : 'nav-link' }
						href="#following"
						onClick={ ( e ) => {
							e.preventDefault();
							handleTabChange( 'following' );
						} }
					>
						{ __( 'フォロー中', 'hametuha' ) }
						{ data.following.total > 0 && (
							<span className="badge text-bg-secondary">
    							{ data.following.total }
								<span className="visually-hidden">人</span>
  							</span>
						) }
					</a>
				</li>
			</ul>
			{/* Tab Content */ }
	<div className="tab-content mt-3">
		<TabContent
			type={ activeTab }
					data={ data[ activeTab ] }
					loading={ loading[ activeTab ] }
					searchQuery={ search[ activeTab ] }
					onSearch={ ( query ) => searchUsers( activeTab, query ) }
					onLoadMore={ loadMore }
					onUnfollow={ activeTab === 'following' ? handleUnfollow : null }
				/>
			</div>
		</div>
	);
};

const container = document.getElementById( 'hametuha-follower-container' );
if ( container ) {
	createRoot( container ).render( <FollowerWrap /> );
}
