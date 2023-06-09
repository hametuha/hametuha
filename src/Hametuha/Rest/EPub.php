<?php

namespace Hametuha\Rest;


use Hametuha\HamePub\Factory;
use Hametuha\Model\Collaborators;
use Hametuha\Model\CompiledFiles;
use Hametuha\Model\Series;
use WPametu\API\Rest\RestTemplate;

/**
 * EPub generator
 *
 * @package Hametuha\Rest
 * @property-read Series $series
 * @property-read CompiledFiles $files
 */
class EPub extends RestTemplate {

	protected $models = [
		'series' => Series::class,
		'files'  => CompiledFiles::class,
	];

	/**
	 * @var string
	 */
	public static $prefix = 'epub';

	/**
	 * @var string
	 * @todo これはHamePubに移す
	 */
	protected $no_indent = ' 　【《〔〝『「（”"\'’—\(\)&';

	protected $content_type = 'text/html';

	/**
	 * @var array
	 */
	protected $additional_class = [];

	/**
	 * @var bool
	 */
	protected $did_body_class = false;

	/**
	 * @var array
	 */
	protected $factories = [];

	/**
	 * Get list of series
	 */
	public function rest_api_init() {
		register_rest_route( 'hametuha/v1', '/covers/(?P<id>\\d+|me)/?', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'api_series_list' ],
			'args'                => [
				'id'       => [
					'validate_callback' => function ( $var ) {
						return 'me' === $var || is_numeric( $var );
					},
					'default'           => 0,
				],
				'paged'    => [
					'validate_callback' => 'is_numeric',
					'default'           => 0,
				],
				'per_page' => [
					'validate_callback' => 'is_numeric',
					'default'           => 0,
				],
			],
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		] );
		register_rest_route( 'hametuha/v1', '/cover/(?P<id>\\d+)/?', [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'api_post_cover' ],
				'args'                => [
					'id'    => [
						'validate_callback' => 'is_numeric',
						'required'          => true,
					],
					'url'   => [
						'required'          => true,
						'validate_callback' => function( $url ) {
							return preg_match( '#^https?://#', $url );
						},
					],
					'title' => [
						'default' => '',
					],
				],
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			],
		] );
	}


	/**
	 * Get Series list
	 *
	 * @param array $params
	 *
	 * @return \WP_REST_Response
	 */
	public function api_series_list( $params ) {
		$per_page = ( 1 > $params['per_page'] ) ? - 1 : (int) $params['per_page'];
		$paged    = max( 1, $params['paged'] );
		$posts    = [];
		$user_id  = 'me' === $params['id'] ? get_current_user_id() : $params['id'];
		$query    = new \WP_Query( [
			'post_type'      => 'series',
			'author'         => $user_id,
			'posts_per_page' => $per_page,
			'offset'         => max( 0, $paged - 1 ) * $per_page,
		] );
		global $post;
		while ( $query->have_posts() ) {
			$query->the_post();
			$data = [
				'id'     => get_the_ID(),
				'title'  => get_the_title(),
				'status' => $post->post_status,
			];
			if ( has_post_thumbnail( get_the_ID() ) ) {
				$data['thumbnails'] = [
					'full'   => wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' )[0],
					'medium' => wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'medium' )[0],
				];
			} else {
				$data['thumbnails'] = [
					'full'   => false,
					'medium' => false,
				];
			}
			$posts[] = $data;
		}

		return new \WP_REST_Response( $posts );
	}

	/**
	 * Set post thumbnail
	 *
	 * @param array $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function api_post_cover( $params ) {
		if ( ! current_user_can( 'edit_post', $params['id'] ) ) {
			return new \WP_Error( 'permission_denied', 'この投稿を編集する権限がありません。', [ 'status' => 403 ] );
		}
		$post          = get_post( $params['id'] );
		$attachment_id = hametuha_sideload_image( $params['url'], $post->ID, $params['title'] );
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}
		if ( set_post_thumbnail( $params['id'], $attachment_id ) ) {
			return new \WP_REST_Response( [
				'id'         => $post->ID,
				'title'      => get_the_title( $post ),
				'status'     => $post->post_status,
				'thumbnails' => [
					'full'   => wp_get_attachment_image_src( $attachment_id, 'full' )[0],
					'medium' => wp_get_attachment_image_src( $attachment_id, 'medium' )[0],
				],
			] );
		} else {
			return new \WP_Error( 'save_failure', '表紙画像の保存に失敗しました。', [ 'status' => 500 ] );
		}
	}

	/**
	 * 印刷用レイアウトを表示する
	 *
	 * @param int $series_id
	 *
	 * @throws \Exception
	 */
	public function get_print( $series_id = 0 ) {
		if ( ! current_user_can( 'edit_post', $series_id ) ) {
			throw new \Exception( 'あなたには印刷する権利がありません。', 403 );
		}
		$query = Series::get_series_query( $series_id );
		if ( ! $query->have_posts() ) {
			throw new \Exception( 'この作品集には投稿が紐づけられていません', 404 );
		}
		nocache_headers();
		$this->title = get_the_title( $series_id );
		add_filter( 'body_class', function( $classes ) {
			$classes[] = 'single-post';
			$classes[] = 'series-print';
			return $classes;
		} );
		add_filter( 'the_content', function( $content ) {
			return preg_replace( "#<p>([^{$this->no_indent}])#u", '<p class="indent">$1', $content );
		} );
		$this->set_data([
			'series' => get_post( $series_id ),
			'query'  => $query,
		]);
		$this->load_template( 'templates/epub/print' );
	}

	/**
	 * プレビュー画面
	 *
	 * @param string $template
	 * @param int $series_id
	 *
	 * @throws \Exception
	 */
	public function get_preview( $template, $series_id = 0 ) {
		global $post;
		$post = get_post( $series_id );

		if ( 'series' != $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
			throw new \Exception( 'あなたにはプレビューする権利がありません。', 403 );
		}

		$id  = 'preview';
		$dir = 'rtl' == $this->input->get( 'direction' ) ? 'rtl' : 'ltr';
		if ( false !== array_search( $template, [ 'colophon', 'titlepage', 'contributors' ] ) ) {
			$dir = 'lrt';
		}
		nocache_headers();
		if ( 'content' == $template ) {
			$post = get_post( $this->input->get( 'post_id' ) );
			if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
				throw new \Exception( 'あなたにはプレビューする権利がありません。', 403 );
			}
			echo $this->get_content( $id, $post, $template, $dir );
		} else {
			echo $this->get_content( $id, $post, $template, $dir );
		}
	}

	/**
	 * Publish ePub
	 *
	 * @param int $series_id
	 *
	 * @throws \Exception
	 */
	public function get_publish( $series_id = 0 ) {
		$series = get_post( $series_id );
		try {
			// Avoid time out
			set_time_limit( 0 );
			// Check capability
			if ( ! $series || 'series' != $series->post_type || ! current_user_can( 'publish_epub', $series->ID ) ) {
				throw new \Exception( 'あなたにはePubを取得する権利がありません。', 403 );
			}
			// Check ePub is published
			$factory = $this->factory( $series->ID );
			// Set direction
			$direction = $this->series->get_direction( $series->ID );
			// Get HTMLs
			$html = [];
			//          $html['cover'] = [
			//              'label' => '表紙',
			//              'html'  => $this->get_content( $series_id, $series, 'cover', $direction )
			//          ];
			$html['titlepage'] = [
				'label' => '扉',
				'html'  => $this->get_content( $series_id, $series, 'titlepage', $direction ),
			];

			$html['toc'] = [
				'label' => '目次',
				'html'  => '',
			];
			// Add preface if exists
			if ( $preface = $this->series->get_preface( $series->ID ) ) {
				$html['foreword'] = [
					'label' => 'はじめに',
					'html'  => $this->get_content( $series_id, $series, 'foreword', $direction ),
				];
			}
			// Add children
			foreach ( Series::get_series_posts( $series->ID ) as $p ) {
				$html[ 'post-' . $p->ID ] = [
					'label' => get_post_meta( $p->ID, '_series_override', true ) ?: get_the_title( $p ),
					'html'  => $this->get_content( $series_id, $p, 'content', $direction ),
				];
			}
			// Add afterwords
			if ( ! empty( $series->post_content ) ) {
				$html['afterword'] = [
					'label' => 'あとがき',
					'html'  => $this->get_content( $series_id, $series, 'afterword', $direction ),
				];
			}
			// Authors, colophon
			foreach (
				[
					'contributors' => '著者一覧',
					'colophon'     => '書誌情報',
				] as $key => $title
			) {
				$html[ $key ] = [
					'label' => $title,
					'html'  => $this->get_content( $series_id, $series, $key, 'ltr' ),
				];
			}
			// Add ads
			if ( ! hametuha_is_secret_book( $series ) ) {
				$html['ads'] = [
					'label' => '電子書籍近刊',
					'html'  => $this->get_content( $series_id, $series, 'ads', $direction ),
				];
			}
			// Register all html as toc
			foreach ( $html as $key => $h ) {
				// Create TOC
				$page_toc = $factory->toc->addChild( $h['label'], $key . '.xhtml' );
				if ( false !== strpos( $key, 'post-' ) ) {
					// This is post. So grab all headers
					$dom = $factory->parser->html5->loadHTML( $h['html'] );
					$factory->parser->grabHeaders( $page_toc, $dom, true, 3, 2 );
					$html[ $key ]['html'] = $factory->parser->convertToString( $dom );
				}
			}
			$html['toc']['html'] = $this->get_content( $series->ID, $series, 'toc', $direction );
			// Create content
			foreach ( $html as $key => $h ) {
				$property = [];
				switch ( $key ) {
					case 'toc':
						$property[] = 'nav';
						break;
					case 'cover':
						$property[] = 'cover';
						break;
					default:
						// Do nothing
						break;
				}
				// Fix some html
				$dom = $factory->registerHTML( $key, preg_replace( '/[\x08]/', '', $h['html'] ), $property );
				$src = $key . '.xhtml';
				if ( ! $dom ) {
					throw new \Exception( 'EPubの生成に失敗しました', 500 );
				}
				// Copy local files
				foreach (
					[
						'img'    => 'src',
						'link'   => 'href',
						'script' => 'src',
					] as $tag => $attr
				) {
					foreach ( $factory->parser->extractAssets( $dom, $tag, $attr, '#https?://(s\.hametuha|hametuha)\.(com|info|local)/#u', ABSPATH ) as $path ) {
						$factory->opf->addItem( $path, '' );
						// If this is css, load all assets
						if ( false !== strpos( $path, '.css' ) ) {
							$css_path = $factory->distributor->oebps . DIRECTORY_SEPARATOR . $path;
						}
					}
				}
				// Handle remote files
				foreach ( $factory->parser->pullRemoteAssets( $dom ) as $path ) {
					$factory->opf->addItem( $path, '' );
				}
				// Assign properties
				$property = [];
				if ( false !== strpos( $h['html'], '<script' ) ) {
					$property[] = 'scripted';
				}
				if ( 'toc' == $key ) {
					$property[] = 'nav';
				}
				// Save add OPF
				$factory->opf->addItem( 'Text/' . $src, $src, $property );
				$factory->parser->saveDom( $dom, $src );
			}
			// Add Cover Image
			if ( has_post_thumbnail( $series->ID ) ) {
				$thumb = get_post_thumbnail_id( $series->ID );
				$src   = wp_get_attachment_image_src( $thumb, $this->series->image_size );
				$url   = preg_replace( '/(https?):\/\/(s\.)?/', '$1://', $src[0] );
				$path  = str_replace( home_url( '/' ), ABSPATH, $url );
				if ( file_exists( $path ) ) {
					$factory->addCover( $path );
				}
			}
			// Setup opf
			$factory->opf->setIdentifier( get_permalink( $series ) );
			$factory->opf->setLang( 'ja' );
			$factory->opf->setTitle( get_the_title( $series ), 'main-title' );
			if ( $subtitle = $this->series->get_subtitle( $series->ID ) ) {
				$factory->opf->setTitle( $subtitle, 'sub-title', 'subtitle', 2 );
			}
			$factory->opf->setModifiedDate( strtotime( $series->post_modified_gmt ) );
			$factory->opf->direction = $direction;
			// Only for KF8
			// TODO: make it conditional
			if ( true ) {
				$this->set_guide( $factory, $html );
			}
			$factory->opf->putXML();
			$factory->container->putXML();
			// Create ePub
			$file_name = current_time( 'timestamp' ) . '.epub';
			$type      = 'kdp';
			$path      = sprintf( '%swp-content/hamepub/out/%s/%d', ABSPATH, $type, $series_id );
			if ( ( ! is_dir( $path ) && ! mkdir( $path, 0755, true ) ) ) {
				throw new \Exception( sprintf( '保存用ディレクトリ%sを作成できません。', $path ), 403 );
			}
			$factory->compile( $path . DIRECTORY_SEPARATOR . $file_name );
			$this->files->add_record( $type, $series->ID, $file_name );
			if ( ! WP_DEBUG ) {
				try {
					$factory->distributor->delete();
				} catch ( \Exception $e ) {
					error_log( $e->getMessage() );
				}
			}

			throw new \Exception( 'ePubの出力が終わりました。', 200 );
		} catch ( \Exception $e ) {
			// Show message with alert
			$this->iframe_alert( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * Get ePub HTML with content
	 *
	 * @param string $id
	 * @param \WP_Post $post
	 * @param string $template
	 * @param string $direction 'ltr' or 'rtl'
	 * @param \WP_User $user
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	protected function get_content( $id, \WP_Post $post, $template = 'content', $direction = 'ltr', $user = null ) {
		$this->additional_class = [ "epub-{$template}", $direction ];
		if ( ! $this->did_body_class ) {
			$this->did_body_class = true;
			add_filter( 'body_class', [ $this, 'body_class' ] );
		}
		remove_action( 'epub_body_attr', [ $this, 'epub_attr' ] );
		$post->post_content = $this->page_break( $post->post_content );
		setup_postdata( $post );
		$this->set_data( [
			'authors'     => Collaborators::get_instance()->get_published_collaborators( $post->ID ),
			'post'        => $post,
			'is_vertical' => 'rtl' == $direction,
		] );
		switch ( $template ) {
			case 'cover':
				$this->title = '表紙';
				break;
			case 'titlepage':
				$this->title = '扉';
				break;
			case 'colophon':
				$this->title = '書誌情報';
				break;
			case 'contributors':
				$this->title = '著者一覧';
				break;
			case 'foreword':
				$this->title = 'はじめに';
				if ( ! $this->series->get_preface( $post->ID ) ) {
					throw new \Exception( '序文は設定されていません。', 403 );
				}
				break;
			case 'content':
				$this->title = get_post_meta( $post->ID, '_series_override', true ) ?: get_the_title( $post );
				$this->set_data( [
					'show_title'     => $this->series->get_title_visibility( $post->post_parent ),
					'filtered_title' => 'rtl' == $direction ? $this->factory( $id )->parser->tcyiz( $this->title ) : $this->title,
					'series_type'    => $this->series->get_series_type( $post->post_parent ),
				] );
				break;
			case 'toc':
				$this->title = '目次';
				if ( ! $this->factory( $id )->toc->length() ) {
					foreach ( Series::get_series_posts( $post->ID ) as $post ) {
						$title     = get_post_meta( $post->ID, '_series_override', true ) ?: get_the_title( $post );
						$permalink = get_permalink( $post );
						$toc       = $this->factory( $id )->toc->addChild( $title, $permalink );
						$content   = apply_filters( 'the_content', strip_shortcodes( $post->post_content ) );
						$dom       = $this->factory( $id )->parser->getDomFromString( $content );
						$this->factory( $id )->parser->grabHeaders( $toc, $dom, true, 3, 2 );
					}
				}
				$this->set_data( $this->factory( $id )->toc->getNavHTML( '本文' ), 'toc' );
				break;
			case 'afterword':
				$this->title = 'あとがき';
				if ( empty( $post->post_content ) ) {
					throw new \Exception( 'あとがきは設定されていません。', 403 );
				}
				break;
			case 'ads':
				$this->title = '破滅派電子書籍近刊';
				if ( hametuha_is_secret_book( $post ) ) {
					throw new \Exception( 'シークレットブックに近刊は表示されません。', 400 );
				}
				break;
			default:
				// Do nothing
				break;
		}
		ob_start();
		add_filter( 'the_content', [ $this, 'fix_wptexturize' ], 99998 );
		add_filter( 'the_content', [ $this->factory( $id )->parser, 'format' ], 99999 );
		$this->load_template( "templates/epub/{$template}" );
		$content = ob_get_contents();
		if ( 'rtl' === $direction ) {
			// Fix style="padding-left: npx"
			$content = preg_replace_callback( '#style="padding-left: ([0-9]+)px;"#u', function ( array $match ) {
				return sprintf( 'style="padding-top: %dpx;"', $match[1] );
			}, $content );
		}
		$content = preg_replace( '# sizes="[^"]+"#u', '', $content );
		remove_filter( 'the_content', [ $this, 'fix_wptexturize' ], 99998 );
		remove_filter( 'the_content', [ $this->factory( $id )->parser, 'format' ], 99999 );
		ob_end_clean();

		return $content;
	}

	/**
	 * fix wptexturize's behavior
	 *
	 * @see wptexturize
	 *
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function fix_wptexturize( $content ) {
		// Fix apostorophy problem.
		$content = str_replace( '&#8217;', '\'', $content );

		return $content;
	}

	/**
	 * Setup guide element
	 *
	 * @param Factory $factory
	 * @param array $html
	 */
	protected function set_guide( Factory $factory, array $html ) {
		static $text = false;
		foreach ( $html as $key => $htm ) {
			switch ( $key ) {
				case 'cover':
				case 'titlepage':
				case 'toc':
				case 'colophon':
					$factory->opf->addGuide( $key, "Text/{$key}.xhtml" );
					break;
				case 'contributors':
					$factory->opf->addGuide( 'dedication', "Text/{$key}.xhtml" );
					break;
				default:
					if ( ( 'foreword' == $key || preg_match( '/^post-/', $key ) ) && ! $text ) {
						$factory->opf->addGuide( 'text', "Text/{$key}.xhtml" );
						$text = true;
					}
					break;
			}
		}
	}

	/**
	 * Get factory
	 *
	 * @param string $id
	 *
	 * @return Factory
	 * @throws \Hametuha\HamePub\Exception\EnvironmentException
	 * @throws \Hametuha\HamePub\Exception\SettingException
	 */
	public function factory( $id ) {
		if ( ! isset( $this->factories[ $id ] ) ) {
			$this->factories[ $id ] = Factory::init( $id, ABSPATH . 'wp-content/hamepub/tmp' );
		}

		return $this->factories[ $id ];
	}

	/**
	 * Body class
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function body_class( $classes ) {
		return array_merge( $classes, $this->additional_class );
	}

	/**
	 * Convert page break
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function page_break( $content ) {
		return str_replace( '<!--nextpage-->', '<div class="pagebreak"></div>', $content );
	}

	/**
	 * Add epub:type to body
	 */
	public function epub_attr() {
		echo ' epub:type="cover"';
	}

}
