<?php

namespace Hametuha\Rest;


use Hametuha\HamePub\Factory;
use WPametu\API\Rest\RestTemplate;


/**
 * EPub generator
 *
 * @package Hametuha\Rest
 */
class EPub extends RestTemplate
{
	/**
	 * @var string
	 */
	public static $prefix = 'epub';


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
	 * @param string $template
	 * @param int $series_id
	 *
	 * @throws \Exception
	 */
	public function get_preview($template, $series_id = 0){
		global $post;
		$post = get_post($series_id);

		if( 'series' != $post->post_type || !current_user_can('edit_post', $post->ID)){
			throw new \Exception('あなたにはプレビューする権利がありません。');
		}

		$id = 'preview';
		$dir = isset($_GET['direction']) && 'rtl' == $_GET['direction'] ? 'rtl' : 'ltr';

		nocache_headers();
		if( 'content' == $template ){
			if( isset($_GET['post_id']) ){
				$post = get_post($_GET['post_id']);
			}
			echo $this->get_content($id, $post, $template, $dir);
		}else{
			echo $this->get_content($id, $post, $template, $dir);
		}
	}

	/**
	 * Publish ePub
	 *
	 * @param int $series_id
	 *
	 * @throws \Exception
	 */
	public function get_publish($series_id = 0){
		$series = get_post($series_id);
		try{
			// Check capability
			if( !$series || 'series' != $series->post_type || !current_user_can('edit_post', $series->ID) ){
				throw new \Exception('あなたにはePubを取得する権利がありません。');
			}
			// Check ePub is published
			$factory = $this->factory($series->ID);
			// Set direction
			$direction = 'vertical' == get_post_meta($series->ID, 'orientation', true) ? 'rtl' : 'ltr';
			// Get HTMLs
			$html = [];
			$html['cover'] = [
				'label' => '表紙',
				'html' => $this->get_content($series_id, $series, 'cover', $direction)
			];
			$html['toc'] = [
				'label' => '目次',
				'html' => '',
			];
			// Add preface if exists
			if( $preface = get_post_meta($series_id, '_preface', true) ){
				$html['preface'] = [
					'label' => '序文',
					'html' => $this->get_content($series_id, $series, 'preface', $direction)
				];
			}
			// Add children
			foreach( get_posts([
				'post_type' => 'post',
				'post_parent' => $series->ID,
				'posts_per_page' => -1,
				'orderby' => [
					'menu_order' => 'DESC',
					'post_date' => 'ASC',
				]
			]) as $p ){
				$html['post-'.$p->ID] = [
					'label' => get_the_title($p),
					'html' => $this->get_content($series_id, $p, 'content', $direction),
				];
			}
			// Add afterwords
			if( !empty($series->post_content) ){
				$html['afterwords'] = [
					'label' => 'あとがき',
					'html'  => $this->get_content($series_id, $series, 'afterword', $direction),
				];
			}
			// Authors, colophon
			foreach([
				'creators' => '著者一覧',
				'colophon' => '書誌情報',
			] as $key => $title){
				$html[$key] = [
					'label' => $title,
					'html'  => $this->get_content($series_id, $series, $key, 'ltr'),
				];
			}
			// Create Toc
			foreach( $html as $key => $h ){
				$factory->toc->addChild($h['label'], $key.'.xhtml');
			}
			$html['toc']['html'] = $factory->toc->getHTML();
			// Create content
			foreach( $html as $key => $h ){
				$property = [];
				switch( $key ){
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
				$html = str_replace('&nbsp;', '&#38;', $h['html']);
				$html = preg_replace('/srcset=\'[^\']*\'/', '', $html);
				$dom = $factory->registerHTML($key, $html, $property);
				$src = $key.'.xhtml';
				if( !$dom ){
					throw new \Exception('EPubの生成に失敗しました', 500);
				}
				foreach([
					'img' => 'src',
					'link' => 'href',
					'script' => 'src',
				] as $tag => $attr){
					foreach([
						home_url('/'), home_url('/', 'http'), 'https://s.hametuha.info/', 'http://s.hametuha.info', 'http://hametuha.local/'
					] as $url){
						foreach( $factory->parser->extractAssets($dom, $tag, $attr, $url, ABSPATH) as $path ){
							$factory->opf->addItem($path, '');
							// If this is css, load all assets
							if( false !== strpos($path, '.css') ){
								$css_path = $factory->distributor->oebps.DIRECTORY_SEPARATOR.$path;

								var_dump($css_path);
							}
						}
					}
				}
				$factory->parser->saveDom($dom, $src);
				// Assign properties
				$property = [];
				if( false !== strpos($h['html'], '<script') ){
					$property[] = 'scripted';
				}
				if( false !== strpos($h['html'], 'epub:type="toc"') ){
					$property[] = 'nav';
				}
				$factory->opf->addItem('Text/'.$src, $src, $property);
				// Add Cover Image
				if( has_post_thumbnail($series->ID) ){

				}
				// Create TOC
				$factory->toc->addChild($h['label'], $src);
			}

			$factory->opf->setIdentifier($series->guid);
			$factory->opf->setLang('ja');
			$factory->opf->setTitle(get_the_title($series), 'main-title');
			if( $subtitle = get_post_meta($series->ID, 'subtitle', true) ){
				$factory->opf->setTitle($subtitle, 'sub-title', 'subtitle', 2);
			}
			$factory->opf->setModifiedDate(strtotime($series->post_modified_gmt));
			$factory->opf->direction = 'rtl';
			$factory->opf->putXML();
			$factory->container->putXML();
			$factory->compile(ABSPATH.'wp-content/epub/'.$series->post_name.'.epub');

			exit;

			throw new \Exception('あなたはバカです。');
		}catch (\Exception $e){
			// Show message with alert
			$message = esc_js($e->getMessage());
			echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title></title>
</head>
<body>
<script>
window.alert('{$message}');
</script>
</body>
</html>
HTML;
			exit;
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
	 * @return string
	 */
	protected function get_content($id, \WP_Post $post, $template = 'content', $direction = 'ltr', $user = null){
		$this->additional_class = ["epub-{$template}", $direction];
		if( !$this->did_body_class ){
			$this->did_body_class = true;
			add_filter('body_class', [$this, 'body_class']);
		}
		remove_action('epub_body_attr', [$this, 'epub_attr']);
		setup_postdata($post);
		$this->set_data(get_series_authors($post), 'authors');
		$this->set_data($post, 'post');
		switch( $template ){
			case 'cover':
				$this->title = '表紙';
				add_action('epub_body_attr', [$this, 'epub_attr']);
				break;
			case 'colophon':
				$this->title = '書誌情報';
				break;
			case 'creators':
				$this->title = '著者一覧';
				break;
			case 'preface':
				$this->title = '序文';
				break;
			case 'content':
				$this->set_data([
					'drop_title' => false,
				]);
				$this->title = get_the_title($post);
				break;
			case 'toc':
				$this->title = '目次';
				$this->factory($id)->toc->init($id, '目次');
				foreach( get_posts([
					'post_type' => 'post',
					'post_parent' => $post->ID,
					'posts_per_page' => -1,
					'orderby' => [
						'menu_order' => 'DESC',
						'post_date' => 'ASC',
					]
				]) as $p ){
					$this->factory($id)->toc->addChild($p->post_title, get_permalink($p));
				}
				$this->set_data($this->factory($id)->toc->getNavHTML(), 'toc');
				break;
			case 'afterword':
				$this->title = 'あとがき';
				break;
			default:
				// Do nothing
				break;
		}
		ob_start();
		$this->load_template("templates/epub/{$template}");
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
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
	public function factory($id){
		if( !isset($this->factories[$id]) ){
			$this->factories[$id] = Factory::init($id, ABSPATH.'wp-content/uploads/hamepub');
		}
		return $this->factories[$id];
	}

	/**
	 * Body class
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function body_class($classes){
		return array_merge($classes, $this->additional_class);
	}

	/**
	 * Add epub:type to body
	 */
	public function epub_attr(){
		echo' epub:type="cover"';
	}

}
