<?php

namespace Hametuha\Rest;


use Hametuha\HamePub\Factory;
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


	protected $content_type = 'text/html';

	/**
	 * @var array
	 */
	protected $additional_class = [ ];

	/**
	 * @var bool
	 */
	protected $did_body_class = false;

	/**
	 * @var array
	 */
	protected $factories = [ ];

	/**
	 * ファイルが正しく存在することを確認
	 *
	 * @param int $file_id
	 *
	 * @return mixed|null
	 * @throws \Exception
	 */
	private function validate_file($file_id){
		if( !current_user_can('edit_others_posts') ){
			throw new \Exception( 'あなたにはダウンロードする権限がありません。', 403 );
		}
		if( !( $file = $this->files->get_file($file_id) ) ){
			throw new \Exception( '該当するファイルは存在しません。', 404 );
		}
		if( !file_exists( ($path = $this->files->build_file_path($file))  ) ){
			throw new \Exception( sprintf('ファイルが%sにありません。紛失したようです。', $path), 404 );
		}
		return $file;
	}

	/**
	 * ファイルをダウンロード
	 *
	 * @param $file_id
	 */
	public function get_file($file_id){
		try{
			$file = $this->validate_file($file_id);
			if( !($post = get_post($file->post_id)) ){
				throw new \Exception( 'ファイルに紐付いた投稿が見つかりません。', 404 );
			}
			$this->print_file($this->files->build_file_path($file), 'application/epub+zip', $post->post_title.'_'.$file->name);
		}catch ( \Exception $e ){
			$this->iframe_alert($e->getMessage());
		}
	}

	/**
	 * ファイルをチェックする
	 *
	 * @param int $file_id
	 *
	 * @throws \Exception
	 */
	public function get_check($file_id){
		$file = $this->validate_file($file_id);
		try{
			if( !defined('EPUB_PATH') ){
				throw new \Exception('ePubチェッカーがありません', 500);
			}
			$tmp = tempnam(sys_get_temp_dir(), "epubCheck");

			$path = $this->files->build_file_path($file);
			$command = sprintf("%s %s -out %s", EPUB_PATH, $path, $tmp);
			$result = exec($command, $output);
			$lines = implode("<br />", array_map('esc_html', $output));
			$xml = simplexml_load_file($tmp);
			unlink($tmp);
			$messages = [];
			if( $xml->repInfo->messages->count() ){
				foreach( $xml->repInfo->messages->message as $message ){
					$messages[] = (string) $message;
				}
			}else{
				$messages[] = 'SUCCESS: This ePub is valid!';
			}
			$messages = implode(' ', array_map(function($message){
				$message = esc_html($message);
				$message = str_replace('ERROR:', '<strong style="color: white; background: red; padding: 2px 3px;">ERROR:</strong>', $message);
				$message = str_replace('WARN:', '<strong style="color: white; background: orange; padding: 2px 3px;">WARN:</strong>', $message);
				$message = str_replace('SUCCESS:', '<strong style="color: white; background: green; padding: 2px 3px;">VALID</strong>', $message);
				return "<li>{$message}</li>";
			}, $messages));
			// Show result
			$command = esc_html($command);
			// Create message
			echo <<<HTML
<p><code>{$command}</code></p>
<p>{$lines}</p>
<hr />
<ol style="font-family: monospace;">
{$messages}
</ol>
HTML;
		}catch ( \Exception $e ){
			printf('<div class="error"><p>%s</p></div>', $e->getMessage());
		}
	}

	/**
	 * ファイルを削除する
	 *
	 * @param int $file_id
	 *
	 * @throws \Exception
	 */
	public function get_delete($file_id){
		$file = $this->validate_file($file_id);
		if( !$this->files->delete_file($file_id) ){
			throw new \Exception('ファイルを削除できませんでした。', 500);
		}
		wp_redirect(admin_url('edit.php?post_type=series&page=hamepub-files'));
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

		nocache_headers();
		if ( 'content' == $template ) {
			$post = get_post( $this->input->get('post_id') );
			if( !$post || !current_user_can('edit_post', $post->ID) ){
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
			if ( ! $series || 'series' != $series->post_type || ! current_user_can( 'edit_others_posts') ) {
				throw new \Exception( 'あなたにはePubを取得する権利がありません。', 403 );
			}
			// Check ePub is published
			$factory = $this->factory( $series->ID );
			// Set direction
			$direction = $this->series->get_direction( $series->ID );
			// Get HTMLs
			$html          = [ ];
//			$html['cover'] = [
//				'label' => '表紙',
//				'html'  => $this->get_content( $series_id, $series, 'cover', $direction )
//			];
			$html['titlepage'] = [
				'label' => '扉',
				'html'  => $this->get_content( $series_id, $series, 'titlepage', $direction )
			];

			$html['toc']   = [
				'label' => '目次',
				'html'  => '',
			];
			// Add preface if exists
			if ( $preface = $this->series->get_preface($series->ID) ) {
				$html['foreword'] = [
					'label' => 'はじめに',
					'html'  => $this->get_content( $series_id, $series, 'foreword', $direction )
				];
			}
			// Add children
			foreach (
				get_posts( [
					'post_type'      => 'post',
					'post_parent'    => $series->ID,
					'posts_per_page' => - 1,
					'orderby'        => [
						'menu_order' => 'DESC',
						'post_date'  => 'ASC',
					]
				] ) as $p
			) {
				$html[ 'post-' . $p->ID ] = [
					'label' => get_post_meta($p->ID, '_series_override', true) ?: get_the_title($p),
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
					'colophon' => '書誌情報',
				] as $key => $title
			) {
				$html[ $key ] = [
					'label' => $title,
					'html'  => $this->get_content( $series_id, $series, $key, 'ltr' ),
				];
			}
			// Register all html as toc
			foreach( $html as $key => $h ){
				// Create TOC
				$page_toc = $factory->toc->addChild( $h['label'], $key.'.xhtml' );
				if( false !== strpos($key, 'post-') ){
					// This is post. So grab all headers
					$dom = $factory->parser->html5->loadHTML($h['html']);
					$factory->parser->grabHeaders($page_toc, $dom, true, 3);
					$html[$key]['html'] = $factory->parser->convertToString($dom);
				}
			}
			$html['toc']['html'] = $this->get_content( $series->ID, $series, 'toc', $direction );
			// Create content
			foreach ( $html as $key => $h ) {
				$property = [ ];
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
				$dom = $factory->registerHTML( $key, preg_replace('/[\x08]/', '', $h['html']), $property );
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
					foreach ( $factory->parser->extractAssets( $dom, $tag, $attr, '#https?://(s\.)hametuha\.(com|info|local)/#u', ABSPATH ) as $path ) {
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
				$property = [ ];
				if ( false !== strpos( $h['html'], '<script' ) ) {
					$property[] = 'scripted';
				}
				if ( 'toc' == $key ) {
					$property[] = 'nav';
				}
				// Save add OPF
				$factory->opf->addItem( 'Text/' . $src, $src, $property );
				$factory->parser->saveDom($dom, $src);
			}
			// Add Cover Image
			if ( has_post_thumbnail( $series->ID ) ) {
				$dir   = wp_upload_dir();
				$thumb = get_post_thumbnail_id( $series->ID );
				$src   = wp_get_attachment_image_src( $thumb, 'epub-cover' );
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
			if ( $subtitle = $this->series->get_subtitle($series->ID) ) {
				$factory->opf->setTitle( $subtitle, 'sub-title', 'subtitle', 2 );
			}
			$factory->opf->setModifiedDate( strtotime( $series->post_modified_gmt ) );
			$factory->opf->direction = $direction;
			// Only for KF8
			// TODO: make it conditional
			if( true ){
				$this->set_guide($factory, $html);
			}
			$factory->opf->putXML();
			$factory->container->putXML();
			// Create ePub
			$file_name = current_time('timestamp').'.epub';
			$type = 'kdp';
			$path = sprintf('%swp-content/hamepub/out/%s/%d', ABSPATH, $type, $series_id);
			if( (!is_dir($path) && !mkdir($path, 0755, true)) ){
				throw new \Exception( sprintf('保存用ディレクトリ%sを作成できません。', $path), 403);
			}
			$factory->compile($path.DIRECTORY_SEPARATOR.$file_name);
			$this->files->add_record($type, $series->ID, $file_name);
			if( !WP_DEBUG ){
				try{
					$factory->distributor->delete();
				}catch ( \Exception $e ){
					error_log($e->getMessage());
				}
			}

			throw new \Exception( 'ePubの出力が終わりました。', 200);
		} catch ( \Exception $e ) {
			// Show message with alert
			$this->iframe_alert($e->getMessage(), $e->getCode());
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
			'authors' => get_series_authors( $post ),
			'post'  => $post,
		    'is_vertical' => 'rtl' == $direction
		]);
		switch ( $template ) {
			case 'cover':
				$this->title = '表紙';
//				add_action( 'epub_body_attr', [ $this, 'epub_attr' ] );
				break;
			case 'titlepage':
				$this->title = '扉';
//				add_action( 'epub_body_attr', [ $this, 'epub_attr' ] );
				break;
			case 'colophon':
				$this->title = '書誌情報';
				break;
			case 'contributors':
				$this->title = '著者一覧';
				break;
			case 'foreword':
				$this->title = 'はじめに';
				if( !$this->series->get_preface($post->ID) ){
					throw new \Exception('序文は設定されていません。', 403);
				}
				break;
			case 'content':
				$this->title = get_post_meta($post->ID, '_series_override', true) ?: get_the_title($post);
				$this->set_data( [
					'show_title' => $this->series->get_title_visibility($post->post_parent),
				    'filtered_title' =>    'rtl' == $direction ? $this->factory($id)->parser->tcyiz($this->title) : $this->title,
				    'series_type' => $this->series->get_series_type($post->post_parent),
				] );
				break;
			case 'toc':
				$this->title = '目次';
				if ( ! $this->factory( $id )->toc->length() ) {
					foreach (
						get_posts( [
							'post_parent'    => $post->ID,
							'post_type'      => 'post',
							'post_status'    => 'publish',
							'posts_per_page' => - 1,
							'orderby'        => [
								'menu_order'   => 'DESC',
								'date' => 'ASC',
							]
						] ) as $post
					) {
						$title = get_post_meta($post->ID, '_series_override', true) ?: get_the_title($post);
						$permalink = get_permalink($post);
						$toc = $this->factory( $id )->toc->addChild( $title, $permalink );
						$content = apply_filters('the_content', strip_shortcodes($post->post_content));
						$dom = $this->factory($id)->parser->getDomFromString($content);
						$this->factory($id)->parser->grabHeaders($toc, $dom);
					}
				}
				$this->set_data( $this->factory( $id )->toc->getNavHTML('本文'), 'toc' );
				break;
			case 'afterword':
				$this->title = 'あとがき';
				if( empty( $post->post_content ) ){
					throw new \Exception('あとがきは設定されていません。', 403);
				}
				break;
			default:
				// Do nothing
				break;
		}
		ob_start();
		add_filter( 'the_content', [ $this->factory( $id )->parser, 'format' ], 99999 );
		$this->load_template( "templates/epub/{$template}" );
		$content = ob_get_contents();
		remove_filter( 'the_content', [ $this->factory( $id )->parser, 'format' ], 99999 );
		ob_end_clean();
		return $content;
	}

	/**
	 * Setup guide element
	 *
	 * @param Factory $factory
	 * @param array $html
	 */
	protected function set_guide( Factory $factory, array $html ){
		static $text = false;
		foreach( $html as $key => $html ){
			switch( $key ){
				case 'cover':
				case 'titlepage':
				case 'toc':
				case 'colophon':
					$factory->opf->addGuide($key, "Text/{$key}.xhtml");
					break;
				case 'contributors':
					$factory->opf->addGuide('dedication', "Text/{$key}.xhtml");
					break;
				default:
					if( ( 'foreword' == $key || preg_match('/^post-/', $key) ) && !$text){
						$factory->opf->addGuide('text', "Text/{$key}.xhtml");
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