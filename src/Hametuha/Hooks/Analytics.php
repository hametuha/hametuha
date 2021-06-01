<?php

namespace Hametuha\Hooks;


use Hametuha\Hashboard;
use Hametuha\Service\AnalyticsMesurementApi;
use Ramsey\Uuid\Uuid;
use WPametu\Pattern\Singleton;

/**
 * Analytics hooks.
 *
 * This class adds analytics related functions.
 *
 * @package hametuha
 * @property AnalyticsMesurementApi $measurement
 */
class Analytics extends Singleton {

	/**
	 * @var string Google Analytics UA name.
	 */
	protected $ua = 'UA-1766751-2';

	/**
	 * @var string Facebook pixel ID.
	 */
	protected $pixel_id = '956989844374988';

	/**
	 * @var string Unique ID for user.
	 */
	protected $user_id = '';

	/**
	 * @var string Cookie name.
	 */
	protected $cookie_name = 'hametuhauid';

	const DIMENSION_POST_TYPE = 'dimension1';

	const DIMENSION_AUTHOR = 'dimension2';

	const DIMENSION_CATEGORY = 'dimension3';

	const DIMENSION_PAGE_TYPE = 'dimension4';

	const DIMENSION_UID = 'dimension5';

	const DIMENSION_USER_TYPE = 'dimension6';

	const METRIC_CHAR_LENGTH = 'metric1';

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = [] ) {
		// Register setup script.
		add_action( 'hashboard_enqueue_scripts', [ $this, 'enqueue_script' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'enqueue_script' ] );
		// Do analytics tag.
		add_action( 'wp_head', [ $this, 'do_tracking_code' ], 19 );
		add_action( 'admin_head', [ $this, 'do_tracking_code' ], 19 );
		add_action( 'hashboard_footer', [ $this, 'do_tracking_code' ], 19 );
		add_action( 'login_head', [ $this, 'do_tracking_code' ] );
		// Facebook pixels.
		add_action( 'hametha_after_tracking_code', [ $this, 'facebook_pixel' ] );
		// Contact Form 7
		add_action( 'wp_enqueue_scripts', [ $this, 'add_inline_script' ] );
		// Add filter for Cookie Tasting.
		add_filter(
			'cookie_tasting_uuid_key',
			function() {
				return 'google_analytics_id';
			}
		);
	}

	/**
	 * Enqueue Javascript everywhere.
	 */
	public function enqueue_script() {
		wp_enqueue_script( 'cookie-tasting-heartbeat' );
	}

	/**
	 * Render tracking code.
	 */
	public function do_tracking_code() {
		// Get cookie and if set, use it.
		// If not set, generate via uuid4 and overwrite it.
		?>
		<script>
		// Adsense connection.
		window.google_analytics_uacct = "<?php echo esc_js( $this->ua ); ?>";
		// analytics.js
		(function (i, s, o, g, r, a, m) {
			i['GoogleAnalyticsObject'] = r;
			i[r] = i[r] || function () {
					(i[r].q = i[r].q || []).push(arguments)
				}, i[r].l = 1 * new Date();
			a = s.createElement(o),
				m = s.getElementsByTagName(o)[0];
			a.async = 1;
			a.src = g;
			m.parentNode.insertBefore(a, m)
		})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
		ga('create', '<?php echo $this->ua; ?>', 'auto');
		ga('require', 'displayfeatures');
		ga('require', 'linkid', 'linkid.js');
		var uid = CookieTasting.get( 'uuid' );
		ga('set', "userId", uid);
		ga('set', "<?php echo self::DIMENSION_UID; ?>", uid);
		<?php
			// Set user type.
		if ( ! is_singular( 'news' ) ) {
			if ( ! is_user_logged_in() ) {
				$role = 'anonymous';
			} elseif ( current_user_can( 'edit_others_posts' ) ) {
				$role = 'editor';
			} elseif ( current_user_can( 'edit_posts' ) ) {
				$role = 'author';
			} else {
				$role = 'subscriber';
			}
			$this->set_dimension( self::DIMENSION_USER_TYPE, $role );
		}
			// Set contents attribution.
		if ( ( is_singular() || is_page() ) && ! is_preview() ) {
			// Set page attributes.
			$this->set_dimension( self::DIMENSION_POST_TYPE, get_queried_object()->post_type );
			$this->set_dimension( self::DIMENSION_AUTHOR, get_queried_object()->post_author );
			$this->set_dimension( self::METRIC_CHAR_LENGTH, get_post_length( get_queried_object() ) );
			// Set category.
			$cat = 0;
			foreach ( [
				'post'   => 'category',
				'news'   => 'genre',
				'faq'    => 'faq_cat',
				'thread' => 'topic',
			] as $post_type => $taxonomy ) {
				if ( $post_type !== get_queried_object()->post_type ) {
					continue;
				}
				$terms = get_the_terms( get_queried_object(), $taxonomy );
				if ( ! $terms || is_wp_error( $terms ) ) {
					continue;
				}
				foreach ( $terms as $term ) {
					$cat = $term->term_id;
					break;
				}
			}
			if ( $cat ) {
				$this->set_dimension( self::DIMENSION_CATEGORY, $cat );
			}
		}
		if ( is_404() ) {
			$type = '404';
		} elseif ( is_admin() ) {
			$type = 'admin';
		} elseif ( did_action( 'hashboard_head' ) ) {
			$type = 'dashboard';
		} elseif ( is_singular( 'news' ) || is_tax( [ 'noun', 'genre' ] ) || is_post_type_archive( 'news' ) ) {
			$type = 'news';
		} else {
			$type = 'public';
		}
			$this->set_dimension( self::DIMENSION_PAGE_TYPE, $type );
			do_action( 'hametuha_before_ga_send_pageviews' );
		?>
		ga('send', 'pageview');
		</script>
		<?php
		do_action( 'hametuha_after_tracking_code' );
	}

	/**
	 * Render Facebook pixels.
	 */
	public function facebook_pixel() {
		?>
		<!-- Facebook Pixel Code -->
		<script>
		  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
			n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
			t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
			document,'script','//connect.facebook.net/en_US/fbevents.js');
		  fbq('init', '<?php echo $this->pixel_id; ?>');
		  fbq('track', "PageView");</script>
		<noscript><img height="1" width="1" style="display:none"
					   src="https://www.facebook.com/tr?id=<?php echo $this->pixel_id; ?>&ev=PageView&noscript=1"
			/></noscript>
		<!-- End Facebook Pixel Code -->
		<?php
	}

	/**
	 * Echo set dimension function.
	 *
	 * @param string $dimension
	 * @param string $value
	 * @param string $action
	 */
	protected function set_dimension( $dimension, $value, $action = 'set' ) {
		if ( is_numeric( $value ) ) {
			$str = 'ga( "%s", "%s", %d );';
		} else {
			$str = 'ga( "%s", "%s", "%s" );';
		}
		printf( $str, esc_js( $action ), esc_js( $dimension ), esc_js( $value ) );
	}


	/**
	 * Get Google Analytics ranknig.
	 *
	 * @param string $start
	 * @param string $end
	 * @param array $params
	 * @param string $metrics
	 *
	 * @return \WP_Error|array
	 */
	public function ranking( $start, $end, $params = [], $metrics = 'ga:pageviews' ) {
		try {
			if ( ! class_exists( 'Gianism\\Service\\Google' ) ) {
				throw new \Exception( 'Gianismがインストールされていません。', 500 );
			}
			$google  = \Gianism\Service\Google::get_instance();
			$ga      = $google->ga;
			$view_id = $google->ga_profile['view'];
			if ( ! $ga || ! $view_id ) {
				throw new \Exception( 'Google Analytics is not connected.', 500 );
			}
			$params = wp_parse_args(
				$params,
				[
					'max-results' => 10,
					'dimensions'  => 'ga:pageTitle',
					'sort'        => '-ga:pageviews',
				]
			);
			$result = $ga->data_ga->get( 'ga:' . $view_id, $start, $end, $metrics, $params );
			if ( $result && count( $result->rows ) > 0 ) {
				return $result->rows;
			} else {
				return new \WP_Error( 404, '該当する結果はありませんでした。' );
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Add event listener to register event.
	 */
	public function add_inline_script() {
		$data = <<<'JS'
			document.addEventListener( 'wpcf7mailsent', function( event ) {
				try {
					var action = jQuery( event.target ).find( 'form' ).attr( 'action' ).split('#')[0].split( '?' );
					action[0] = action[0].replace( /\/$/, '' ) + '/success';
			  		ga( 'send', 'pageview', action.join( '?' ) );
				} catch ( err ) {}
			}, false );
JS;
		wp_add_inline_script( 'contact-form-7', $data );
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'measurement':
				return AnalyticsMesurementApi::get_instance(
					[
						'ua' => $this->ua,
					]
				);
				break;
		}
	}
}
