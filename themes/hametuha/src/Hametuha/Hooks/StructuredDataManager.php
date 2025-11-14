<?php
namespace Hametuha\Hooks;


use Hametuha\Model\Collaborators;
use Hametuha\Model\Series;
use Hametuha\Rest\Doujin;
use WPametu\Pattern\Singleton;

/**
 * 構造化データをコントロールする
 */
class StructuredDataManager extends Singleton {

	/**
	 * {@inheritDoc}
	 */
	protected function __construct( array $setting = array() ) {
		add_action( 'wp_head', [ $this, 'render_ogp' ] );
	}

	/**
	 * OGPを出力する
	 *
	 * @return void
	 */
	public function render_ogp() {
		$json = $this->get_json();
		if ( ! $json ) {
			return;
		}
		foreach ( $json as $j ) {
			$j['@context'] = 'http://schema.org';
			$rendered      = json_encode( $j );
			echo <<<HTML
<!-- Hametuha JSON-LD // -->
<script type="application/ld+json">
{$rendered}
</script>
<!-- // JSON-LD -->
HTML;
			echo "\n";
		}
	}

	/**
	 * JSON-LDを出力する
	 *
	 * @return array
	 */
	public function get_json() {
		if ( is_front_page() ) {
			return [ $this->site_clause( true ) ];
		}
		if ( is_singular() ) {
			return [ $this->clause_post( get_queried_object() ) ];
		}
		if ( is_doujin_profile_page() ) {
			$user = get_user_by( 'slug', is_doujin_profile_page() );
			return [ $this->author_clause( $user->ID, true ) ];
		}
	}

	/**
	 * 投稿のJSONを取得する
	 *
	 * @param \WP_Post $post 投稿
	 * @return array
	 */
	public function clause_post( $post ) {
		$json = [
			'@type'         => 'WebPage',
			'@id'           => get_permalink( $post ),
			'url'           => get_permalink( $post ),
			'datePublished' => mysql2date( \DateTime::ATOM, $post->post_date_gmt, false ),
			'dateModified'  => mysql2date( \DateTime::ATOM, $post->post_modified_gmt, false ),
			'publisher'     => $this->organization_clause(),
			'author'        => [ $this->author_clause( $post->post_author ) ],
			'headline'      => get_the_title( $post ),
		];
		// サムネイルを設定
		if ( has_post_thumbnail( $post ) ) {
			list( $url, $width, $height ) = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'full' );
			$json['image']                = [
				'@type'      => 'ImageObject',
				'contentUrl' => $url,
				'url'        => $url,
				'width'      => $width,
				'height'     => $height,
			];
		}
		// 共著者を設定
		if ( is_singular( 'series' ) ) {
			foreach ( Collaborators::get_instance()->get_collaborators( $post->ID ) as $collabolator ) {
				$json['author'][] = $this->author_clause( $collabolator->ID, false );
			}
		}
		// タイプを設定
		switch ( $post->post_type ) {
			case 'announcement':
				$json['@type'] = 'Article';
				break;
			case 'news':
				$json['@type'] = 'NewsArticle';
				break;
			case 'series':
				$status = Series::get_instance()->get_status( $post->ID );
				if ( 1 <= $status ) {
					$json['@type'] = 'Book';
					$json['name']  = get_the_title( $post );
					// 公開されていたら、登録
					$json['bookFormat'] = 'http://schema.org/EBook';
					if ( 2 <= $status ) {
						// 販売中なのでリンクする
						$json['sameAs'] = hametuha_kdp_url( $post );
					}
				} else {
					$json['@type'] = 'CreativeWorkSeries';
				}
				break;
			case 'post':
				// シリーズに属していたらリンクしておく。
				if ( $post->post_parent && is_post_publicly_viewable( $post->post_parent ) ) {
					$json['isPartOf'] = get_permalink( $post->post_parent );
				}
				$terms         = get_the_category( $post->ID );
				$json['@type'] = 'CreativeWork';
				foreach ( $terms as $term ) {
					switch ( $term->slug ) {
						case 'novel':
							$json['@type'] = 'ShortStory';
							break 2;
						case 'drama':
							$json['@type'] = 'Play';
							break 2;
						case 'etude':
							$json['@type'] = 'Thesis';
							break 2;
					}
				}
				break;
		}
		return $json;
	}

	/**
	 * ユーザー情報をPersonに変更
	 *
	 * @param int  $author_id ユーザーID
	 * @param bool $detaild   trueの場合、詳細情報を出す
	 * @return array
	 */
	public function author_clause( $author_id, $detaild = false ) {
		$json = [
			'@type' => 'Person',
			'@id'   => hametuha_author_url( $author_id ),
			'name'  => get_the_author_meta( 'display_name', $author_id ),
			'url'   => hametuha_author_url( $author_id ),
		];
		if ( $detaild ) {
			$user    = get_userdata( $author_id );
			$contact = [];
			// 顔写真
			$profile_pic_id = has_original_picture( $author_id );
			if ( $profile_pic_id ) {
				list( $img_url, $img_width, $img_height ) = wp_get_attachment_image_src( $profile_pic_id, 'full' );
				$json['image']                            = [
					'@type'      => 'ImageObject',
					'contentUrl' => $img_url,
					'url'        => $img_url,
					'width'      => $img_width,
					'height'     => $img_height,
				];
			} else {
				$gravatar = get_avatar_url( $user->user_email, [ 'size' => 600 ] );
				if ( $gravatar ) {
					$json['image'] = [
						'@type'      => 'ImageObject',
						'contentUrl' => $gravatar,
						'url'        => $gravatar,
						'width'      => 600,
						'height'     => 600,
					];
				}
			}
			if ( preg_match( '##u', $user->user_url ) ) {
				// ホームページ
				$contact[] = $user->user_url;
			}
			// SNSなど
			$twitter = get_the_author_meta( 'twitter', $author_id );
			if ( $twitter ) {
				$contact[] = 'https://twitter.com/' . $twitter;
			}
			if ( ! empty( $contact ) ) {
				$json['sameAs'] = $contact;
			}
			foreach ( [
				'location'    => 'homeLocation',
				'birth_place' => 'birthPlace',
			] as $key => $label ) {
				$meta = get_the_author_meta( $key, $author_id );
				if ( $meta ) {
					$json[ $label ] = $meta;
				}
			}
			// 所属
			$json['affiliation'] = $this->organization_clause();
			// 役職
			$json['jobTitle'] = hametuha_user_role( $author_id );
			// 募集していたら、探す
			if ( hametuha_user_allow_contact( $author_id ) ) {
				$posts = get_posts( [
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'post_author'    => $author_id,
					'posts_per_page' => 1,
				] );
				if ( $posts ) {
					$json['contactPoint'] = [
						'@type'       => 'ContactPoint',
						'contactType' => 'メールでのお問い合わせ',
						'url'         => hametuha_user_contact_url( $posts[0] ),
					];
				}
			}
		}
		return $json;
	}

	/**
	 * Web siteのJSONを取得する
	 *
	 * @param bool $with_organization trueの場合、運営者情報も入れる
	 * @return array
	 */
	public function site_clause( $with_organization = false ) {
		$json = [
			'@type'           => 'WebSite',
			'@id'             => home_url(),
			'url'             => home_url(),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		];
		if ( $with_organization ) {
			$json['publisher'] = $this->organization_clause( true );
		}
		return $json;
	}

	/**
	 * 組織のJSONを取得する
	 *
	 * @param bool $with_funder trueの場合、出資者情報も入れる
	 * @return array
	 */
	public function organization_clause( $with_funder = false ) {
		$json = [
			'@type'     => 'Organization',
			'url'       => 'https://hametuha.com',
			'logo'      => [
				'@type'  => 'ImageObject',
				'url'    => get_theme_file_uri( 'assets/img/hametuha-logo.png' ),
				'width'  => 300,
				'height' => 150,
			],
			'name'      => '破滅派',
			'telephone' => '050 5532 8327',
			'sameAs'    => [
				'https://www.facebook.com/hametuha.inc',
				'https://twitter.com/hametuha',
			],
		];
		if ( $with_funder ) {
			$json['funder'] = [
				'@type'     => 'Corporation',
				'url'       => 'https://hametuha.co.jp',
				'name'      => '株式会社破滅派',
				'address'   => [
					'@type'           => 'PostalAddress',
					'streetAddress'   => '銀座1-3-3 G1ビル7F 1211',
					'addressLocality' => '中央区',
					'addressRegion'   => '東京都',
					'postalCode'      => '104-0016',
					'addressCountry'  => 'JP',
				],
				'telephone' => '050 5532 8327',
			];
		}
		return $json;
	}
}
