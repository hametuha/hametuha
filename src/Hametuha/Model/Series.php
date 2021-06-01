<?php

namespace Hametuha\Model;

use WPametu\DB\Model;


/**
 * Series model
 *
 * @package Hametuha\Model
 *
 * @property CompiledFiles $files
 */
class Series extends Model {

	/**
	 * This seems a bug.
	 *
	 * @todo Fix this and make patch.
	 * @var array|string
	 */
	public $image_size = [ 1201, 1921 ];

	/**
	 * Status Label
	 *
	 * @var array
	 */
	public $status_label = [
		'未販売',
		'販売申請中',
		'販売中',
	];

	/**
	 * Return array of WP_Users
	 *
	 * @deprecated
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_authors( $post_id ) {
		$users = [];
		foreach (
			$this->select( "{$this->db->users}.*" )
				 ->from( $this->db->users )
				 ->join( $this->db->posts, "{$this->db->posts}.post_author = {$this->db->users}.ID" )
				 ->where( "{$this->db->posts}.post_parent = %d", $post_id )
				 ->where( "{$this->db->posts}.post_type = %s", 'post' )
				 ->group_by( "{$this->db->users}.ID" )->result() as $user
		) {
			$users[] = new \WP_User( $user );
		}

		return $users;
	}

	/**
	 * Get owning count
	 *
	 * @param int $author_id
	 *
	 * @return int
	 */
	public function get_owning_series( $author_id ) {
		return (int) $this->select( "COUNT({$this->db->posts}.ID)" )
						  ->from( $this->db->posts )
						  ->join( $this->db->postmeta, "{$this->db->postmeta}.meta_key = '_kdp_status' AND {$this->db->postmeta}.post_id = {$this->db->posts}.ID" )
						  ->where( "{$this->db->postmeta}.meta_value = %s", 2 )
						  ->where( "{$this->db->posts}.post_author = %d", $author_id )
						  ->get_var();
	}

	/**
	 * Get social score
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function social_score( $post_id ) {
		return (int) $this->select( 'SUM( CAST(pm.meta_value AS SIGNED))' )
						  ->from( "{$this->db->postmeta} as pm" )
						  ->join( "{$this->db->posts} AS p", 'p.iD = pm.post_id' )
						  ->where( 'p.post_parent = %d', $post_id )
						  ->where_like( 'meta_key', '_feedback_' )->get_var();
	}

	/**
	 * Get selling status
	 *
	 * @param int $post_id ID of post.
	 *
	 * @return int 2 is selling, 1 is prepareing, 0 is not serring.
	 */
	public function get_status( $post_id ) {
		return (int) get_post_meta( $post_id, '_kdp_status', true );
	}

	/**
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_asin( $post_id ) {
		return (string) get_post_meta( $post_id, '_asin', true );
	}

	/**
	 * Get amazon URL
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_kdp_url( $post_id ) {
		$asin = $this->get_asin( $post_id );
		return $asin ? sprintf( 'http://www.amazon.co.jp/dp/%s/?t=hametuha-22', $asin ) : '';
	}

	/**
	 * Get direction
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_direction( $post_id ) {
		return 'vertical' == (string) get_post_meta( $post_id, 'orientation', true ) ? 'rtl' : 'ltr';
	}

	/**
	 * Get visibility of title
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function get_title_visibility( $post_id ) {
		return (int) get_post_meta( $post_id, '_show_title', true );
	}

	/**
	 * Get series type
	 *
	 * @param int $post_id
	 *
	 * @return int 1 or 0
	 */
	public function get_series_type( $post_id ) {
		return (int) get_post_meta( $post_id, '_series_type', true );
	}

	/**
	 * Get subtitle
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_subtitle( $post_id ) {
		return (string) get_post_meta( $post_id, 'subtitle', true );
	}

	/**
	 * Get series range
	 *
	 * @param int $post_id
	 *
	 * @return mixed|null
	 */
	public function get_series_range( $post_id ) {
		return $this->select( 'MAX(post_date) AS last_date, MIN(post_date) AS start_date' )
					->from( $this->db->posts )
					->wheres(
						[
							'post_type = %s'   => 'post',
							'post_status = %s' => 'publish',
							'post_parent = %d' => $post_id,
						]
					)->get_row();
	}

	/**
	 * Get current query
	 *
	 * @return int
	 */
	public function get_published_count() {
		return (int) $this->select( 'COUNT(p.ID)' )
						  ->from( "{$this->db->posts} AS p" )
						  ->join( "{$this->db->postmeta} AS pm", "pm.post_id = p.ID AND pm.meta_key = '_kdp_status'" )
						->wheres(
							[
								'p.post_type = %s'   => 'series',
								'p.post_status = %s' => 'publish',
								'pm.meta_value = %d' => 2,
							]
						)->get_var();
	}

	/**
	 * Get preface
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_preface( $post_id ) {
		return (string) get_post_meta( $post_id, '_preface', true );
	}

	/**
	 * Is finished
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_finished( $post_id ) {
		return (bool) get_post_meta( $post_id, '_series_finished', true );
	}

	/**
	 * Get visibility of series
	 *
	 * @param int $series_id
	 *
	 * @return int|string|array
	 */
	public function get_visibility( $series_id ) {
		$key    = get_post_meta( $series_id, '_visibility', true );
		$values = array_unique(
			array_filter(
				array_map(
					function( $var ) {
						$number = trim( $var );
						if ( is_numeric( $var ) ) {
							  return (int) $var;
						} else {
							return '';
						}
					},
					explode( ',', $key )
				),
				function( $numeric ) {
					return is_numeric( $numeric );
				}
			)
		);
		if ( empty( $values ) ) {
			return '';
		} elseif ( 1 === count( $values ) ) {
			return $values[0];
		} else {
			return $values;
		}
	}

	/**
	 * Should hide?
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return bool
	 */
	public function should_hide( $post = null ) {
		$post = get_post( $post );
		if ( $external = hametuha_external_url( $post ) ) {
			return true;
		}
		$limit_index = $this->get_visibility( $post->post_parent );
		// No limit
		if ( 0 === $limit_index ) {
			return true;
		} elseif ( ! $limit_index ) {
			return false;
		}
		$cur_index = $this->get_index( $post );
		if ( is_array( $limit_index ) ) {
			return false === array_search( $cur_index, $limit_index );
		} else {
			return $cur_index > $limit_index;
		}
	}

	/**
	 * Get external URL
	 *
	 * @param int $series_id
	 *
	 * @return string
	 */
	public function get_external( $series_id ) {
		return (string) get_post_meta( $series_id, '_external_url', true );
	}

	/**
	 * Get total count
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return int
	 */
	public function get_total( $post = null ) {
		$post = get_post( $post );

		return (int) $this->select( 'COUNT(ID)' )->from( $this->db->posts )
						  ->where( 'post_type = %s', 'post' )
						  ->where( 'post_status = %s', 'publish' )
						  ->where( 'post_parent = %d', $post->ID )
						  ->get_var();
	}

	/**
	 * Get current index
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return int
	 */
	public function get_index( $post = null ) {
		static $store = [];
		$post         = get_post( $post );
		if ( ! $post || ! $post->post_parent ) {
			return 1;
		}
		if ( ! isset( $store[ $post->ID ] ) ) {

			$query              = <<<SQL
			post_parent = %d
			AND post_type = 'post'
			AND post_status = 'publish'
			AND (
				menu_order > %d
				OR
				( post_date < %s AND menu_order = %d )
			)
SQL;
			$index              = (int) $this->select( 'COUNT(ID)' )
											 ->from( $this->db->posts )
											->where(
												$query,
												[
													$post->post_parent,
													$post->menu_order,
													$post->post_date,
													$post->menu_order,
												]
											)
											 ->get_var();
			$store[ $post->ID ] = $index;
		}

		return 1 + $store[ $post->ID ];
	}

	/**
	 * Return index label
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return string
	 */
	public function index_label( $post = null ) {
		$post  = get_post( $post );
		$index = $this->get_index( $post );
		$total = $this->get_total( $post->post_parent );
		if ( $total == $index ) {
			return $this->is_finished( $post->post_parent ) ? '最終話' : '最新話';
		} else {
			return sprintf( '第%s話', $index );
		}
	}

	/**
	 * Get Sibling post
	 *
	 * @param int $offset
	 * @param null|\WP_Post|int $post
	 *
	 * @return mixed|null
	 */
	public function get_sibling( $offset = 0, $post = null ) {
		$post = get_post( $post );
		if ( 1 > $offset ) {
			return null;
		}

		return $this->select( '*' )->from( $this->db->posts )
					->where( "post_type = 'post' AND post_status = 'publish' AND post_parent = %d", $post->post_parent )
					->order_by( 'menu_order', 'DESC' )
					->order_by( 'post_date' )
					->limit( 1, $offset - 1 )
					->get_row();
	}

	/**
	 * Get next link
	 *
	 * @param string $before
	 * @param string $after
	 * @param null|\WP_Post|int $post
	 *
	 * @return string
	 */
	public function next( $before = '<li>', $after = '</li>', $post = null ) {
		return $this->prev( $before, $after, $post, true );
	}

	/**
	 * Get previous link
	 *
	 * @param string $before
	 * @param string $after
	 * @param null|\WP_Post|int $post
	 * @param bool $next
	 *
	 * @return string
	 */
	public function prev( $before = '<li>', $after = '</li>', $post = null, $next = false ) {
		$post    = get_post( $post );
		$index   = $this->get_index( $post );
		$icon    = $next ? 'right2' : 'left';
		$link    = <<<'HTML'
			<a class="series-pager-link" href="%3$s">
				<small class="series-pager-nombre">第%2$s話</small>
				<span class="series-pager-text hidden-xs">%1$s</span>
				<i class="series-pager-icon icon-arrow-%6$s"></i>
			</a>
HTML;
		$operand = $next ? 1 : - 1;
		$target  = $this->get_sibling( $index + $operand, $post );
		if ( ( $index < 0 ) || ! $index || ( $index < 2 && ! $next ) || ! $target ) {
			return '';
		}
		return sprintf(
			'%4$s' . $link . '%5$s',
			esc_html( get_the_title( $target ) ),
			$index + $operand,
			get_permalink( $target ),
			$before,
			$after,
			$icon
		);
	}

	/**
	 * Update posts order
	 *
	 * @param int $post_id
	 * @param int $order
	 *
	 * @return bool
	 */
	public function update_order( $post_id, $order ) {
		return (bool) $this->update(
			[
				'menu_order' => $order,
			],
			[
				'ID' => $post_id,
			],
			[ '%d' ],
			[ '%d' ],
			$this->db->posts
		);
	}

	/**
	 * レビューを取得する
	 *
	 * @param int $series_id
	 * @param bool|true $only_public
	 * @param int $paged
	 * @param int $per_page
	 *
	 * @return array|null|object
	 */
	public function get_reviews( $series_id, $only_public = true, $paged = 1, $per_page = 20 ) {
		if ( $only_public ) {
			$where1 = "AND pm.meta_value = '1'";
			$where2 = "AND comment_approved = '1'";
		} else {
			$where1 = '';
			$where2 = '';
		}
		$query                = <<<SQL
        SELECT SQL_CALC_FOUND_ROWS
        cs.*, cm2.meta_value AS rank, cm.meta_value AS priority FROM (
            (
              SELECT *
              FROM {$this->db->comments}
              WHERE comment_post_ID = %d
                AND comment_type = 'review'
                {$where2}
            )
            UNION ALL
            (
              SELECT c.*
              FROM {$this->db->comments} AS c
              LEFT JOIN {$this->db->posts} AS p
              ON c.comment_post_ID = p.ID
              LEFT JOIN {$this->db->commentmeta} AS pm
              ON c.comment_ID = pm.comment_id AND pm.meta_key = 'as_testimonial'
              WHERE p.post_parent = %d
                AND c.comment_type = ''
                {$where1}
            )
        ) AS cs
        LEFT JOIN {$this->db->commentmeta} AS cm
        ON cs.comment_ID = cm.comment_id AND cm.meta_key = 'testimonial_order'
        LEFT JOIN {$this->db->commentmeta} AS cm2
        ON cs.comment_ID = cm2.comment_id AND cm2.meta_key = 'testimonial_rank'
        ORDER BY CAST( cm.meta_value AS SIGNED) DESC,
                 cs.comment_date DESC
        LIMIT %d, %d
SQL;
		$return               = [
			'rows'     => $this->db->get_results(
				$this->db->prepare(
					$query,
					$series_id,
					$series_id,
					( $paged - 1 ) * $per_page,
					$per_page
				)
			),
			'total'    => (int) $this->db->get_var( 'SELECT FOUND_ROWS()' ),
			'cur_page' => $paged,
			'per_page' => $per_page,
		];
		$return['total_page'] = ceil( $return['total'] / $per_page );
		foreach ( $return['rows'] as &$row ) {
			$row->twitter = $this->is_service( $row->comment_author_url, 'twitter' );
			$row->amazon  = $this->is_service( $row->comment_author_url, 'amazon' );
			if ( preg_match( '#^https?://([^/]+)#u', $row->comment_author_url, $match ) ) {
				$row->domain = $match[1];
			} else {
				$row->domain = false;
			}
			if ( $row->comment_post_ID != $series_id ) {
				// 子投稿へのコメント
				$row->display = ( ( '1' === $row->comment_approved ) && (bool) get_comment_meta( $row->comment_ID, 'as_testimonial', true ) );
			} else {
				// レビュー
				$row->display = '1' === $row->comment_approved;
			}
		}

		return $return;
	}

	/**
	 * 指定したURLが特定のサービスのものか
	 *
	 * @param string $url
	 * @param string $service
	 *
	 * @return bool
	 */
	public function is_service( $url, $service ) {
		switch ( $service ) {
			case 'twitter':
				return (bool) preg_match( '#^https?://(www\.)?twitter\.com/[^/]+/status/[0-9]+/?#u', $url );
				break;
			case 'amazon':
				return (bool) preg_match( '#^https?://([a-z0-9\-\.].*\.)?amazon.(co\.jp|com)#u', $url );
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * 作品集が公開可能かチェックする
	 *
	 * @param null|int|\WP_Post $post
	 * @return false|\WP_Error
	 */
	public function validate( $post = null ) {
		$post   = get_post( $post );
		$errors = new \WP_Error();
		if ( 'series' != $post->post_type ) {
			$errors->add( 'fatal', 'これは作品集ではありません' );
		}
		// 表紙画像
		if ( ! has_post_thumbnail( $post ) ) {
			$errors->add( 'fatal', '表紙画像が設定されていません' );
		} else {
			// TODO: これはバグでは？ 4.7.3から？
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $this->image_size );
			if ( 1200 != $thumbnail[1] || 1920 != $thumbnail[2] ) {
				$errors->add( 'fatal', '表紙画像のサイズが不正です。サイズは幅1200px 高さ1920pxでなくてはなりません。これ以上大きい解像度でアップロードしてください。' );
			}
		}
		// リード
		if ( ! $post->post_excerpt ) {
			$errors->add( 'fatal', 'リード文が設定されていません。' );
		} elseif ( 100 > mb_strlen( $post->post_excerpt, 'utf-8' ) ) {
			$errors->add( 'warning', 'リード文が短すぎます。もう少し読んでもらえるようなリード文にしましょう。' );
		}
		// 登録された文章
		$length = get_post_length( $post );
		if ( 2000 > $length ) {
			if ( ! count(
				get_posts(
					[
						'post_type'      => 'post',
						'post_status'    => 'publish',
						'post_parent'    => get_the_ID(),
						'posts_per_page' => - 1,
						'orderby'        => [
							'menu_order' => 'DESC',
							'date'       => 'ASC',
						],
						'paged'          => max( 1, intval( get_query_var( 'paged' ) ) ),
					]
				)
			)
			) {
				$errors->add( 'fatal', '作品が1つも登録されていません。' );
			} else {
				$errors->add( 'fatal', sprintf( '%s文字では短すぎます……', number_format_i18n( $length ) ) );
			}
		}
		// 完結済みか
		if ( ! $this->is_finished( $post->ID ) ) {
			$errors->add( 'fatal', 'この作品集はまだ完結していません。' );
		}
		if ( $errors->errors ) {
			return $errors;
		} else {
			return false;
		}
	}

	public function get_list( $type = 'sales', $limit = 10 ) {

	}

	/**
	 * Get latest published date.
	 *
	 * @param int $series_id
	 * @return string
	 */
	public function last_published( $series_id ) {
		$post = get_post( $series_id );
		if ( ! $post || 'series' !== $post->post_type ) {
			return '';
		}
		$published = $this->files->published( $post->ID );
		return $published ? $published->published : '';
	}

	/**
	 * Check if series is safe for
	 *
	 * @param int|\WP_Post $series_id
	 * @return bool|\WP_Error
	 */
	public function safe_after_published( $series_id ) {
		$error  = new \WP_Error();
		$series = get_post( $series_id );
		if ( ! $series || 'series' !== $series->post_type ) {
			return false;
		}
		$published = $this->last_published( $series->ID );
		if ( ! $published ) {
			return false;
		}
		// Check series itself.
		if ( $series->post_modified > $published ) {
			// TODO: How can we fix series is updated after publication?
		}
		// Check series posts.
		foreach ( self::get_series_posts( $series->ID ) as $post ) {
			if ( $published < $post->post_modified ) {
				$error->add( 'outdated_publication', sprintf( '「%s」は販売開始後に修正されています。', get_the_title( $post ) ) );
			}
		}
		return $error->get_error_messages() ? $error : true;
	}

	/**
	 * Get series arg
	 *
	 * @param int $series_id
	 *
	 * @return array
	 */
	protected static function get_series_args( $series_id, $status = 'any' ) {
		return [
			'post_type'      => 'post',
			'post_parent'    => $series_id,
			'post_status'    => $status,
			'posts_per_page' => - 1,
			'orderby'        => [
				'menu_order' => 'DESC',
				'post_date'  => 'ASC',
			],
		];
	}

	/**
	 * Get series posts.
	 *
	 * @param int    $series_id
	 * @param string $status Default 'any'.
	 * @param bool   $as_wp_query If set true, returns WP_Query. Otherwise, returns WP_Post array.
	 *
	 * @return \WP_Post[]|\WP_Query
	 */
	public static function get_series_posts( $series_id, $status = 'any', $as_wp_query = false ) {
		$args = self::get_series_args( $series_id, $status );
		return $as_wp_query ? new \WP_Query( $args ) : get_posts( $args );
	}

	/**
	 * Return series query
	 *
	 * @param int $series_id
	 *
	 * @return \WP_Query
	 */
	public static function get_series_query( $series_id ) {
		return new \WP_Query( self::get_series_args( $series_id ) );
	}

	/**
	 * 設定値があればそれを返す
	 *
	 * @param int $author_id
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	public function override_meta( $author_id, $key, $default ) {
		return get_user_meta( $author_id, $key, true ) ?: $default;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'files':
				return CompiledFiles::get_instance();
			default:
				return parent::__get( $name );
		}
	}


}
