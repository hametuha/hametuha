<?php

namespace Hametuha\Service;


use Hametuha\Pattern\Singleton;

/**
 * reCAPTCHA V3
 *
 * @package hametuha
 * @property-read string $site_key
 * @property-read string $secret_key
 * @property-read bool   $site_key_defined
 * @property-read bool   $secret_key_defined
 * @property-read bool   $available
 */
class RecaptchaV3 extends Singleton {

	/**
	 * Constructor
	 */
	protected function init() {
		add_action( 'admin_init', [ $this, 'add_setting_fields' ] );
		// Add in login page.
		add_action( 'login_enqueue_scripts', [ $this, 'login_head' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'login_head' ] );
		add_action( 'login_form', [ $this, 'login_form_input' ], 99999 );
		add_action( 'register_form', [ $this, 'login_form_input' ] );
		// Handle login validation.
		add_filter( 'authenticate', [ $this, 'authenticate' ], 50, 3 );
		// Handle registration validation.
		add_filter( 'registration_errors', [ $this, 'registration_errors' ], 10, 3 );
	}

	/**
	 * Verify reCAPTCHA's token.
	 *
	 * @param string $token
	 * @param string $ip    Default user's remote IP.
	 * @return array|bool|\WP_Error
	 */
	public function verify( $token, $ip = '' ) {
		if ( ! $this->available ) {
			return new \WP_Error( 'recaptcha_verification_failed', __( 'This site has no proper setting.', 'hametuha' ) );
		}
		if ( empty( $token ) ) {
			return new \WP_Error( 'recaptcha_verification_failed', __( 'アクセスチェックで異常が発生しました。やりなおしてください。', 'hametuha' ) );
		}
		$result = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
			'body' => [
				'secret'   => $this->secret_key,
				'response' => $token,
				'remoteip' => $ip ?: $_SERVER['REMOTE_ADDR'],
			],
		] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$response = json_decode( $result['body'] );
		if ( ! $response || ! $response->success ) {
			return new \WP_Error( 'recaptcha_verification_failed', __( 'Our spam filter recognize your request as spam. Please contact us if you are not spam.', 'hametuha' ) );
		}
		return true;
	}

	/**
	 * Enqueue recaptcha script.
	 *
	 * @param string $target DOM id of input.
	 * @param string $score  Default is 'homepage'. See {https://developers.google.com/recaptcha/docs/v3#interpreting_the_score}
	 */
	public function enqueue_recaptcha( $target = '', $score = 'homepage' ) {
		wp_enqueue_script( 'recaptcha-v3', add_query_arg( [
			'render' => $this->site_key,
		], 'https://www.google.com/recaptcha/api.js' ), [], null, true );
		$target = esc_js( $target );
		$score  = esc_js( $score );
		$key    = esc_js( $this->site_key );
		$js     = <<<JS
	   grecaptcha.ready(function() {
	        var target = '{$target}';
			grecaptcha.execute( '{$key}', { action: '{$score}' } ).then( function( token ) {
			  // Do something.
			  var input = document.getElementById( target );
			  if ( input ) {
			    input.value = token;
			  }
			});
       });
JS;
		wp_add_inline_script( 'recaptcha-v3', $js );
	}

	/**
	 * Enqueue login header.
	 *
	 * @return string
	 */
	public function login_head() {
		if ( $this->available ) {
			$this->enqueue_recaptcha( 'recaptcha-v3-token', 'login' );
		}
	}

	/**
	 * Render recaptcha v3 token field.
	 */
	public function login_form_input() {
		printf( '<input type="hidden" name="%1$s" id="%1$s" value="" />', 'recaptcha-v3-token' );
		echo wp_kses_post(
			sprintf(
				'<p style="color:#999">%s</p>',
				__( 'This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy">Privacy Policy</a> and<a href="https://policies.google.com/terms">Terms of Service</a> apply.', 'hametuha' )
			)
		);
	}

	/**
	 * Login filter.
	 *
	 * @param null|\WP_User|\WP_Error $user
	 * @param string                  $username
	 * @param string                  $password
	 * @return null|\WP_Error
	 */
	public function authenticate( $user, $username, $password ) {
		if ( ! $this->available ) {
			return $user;
		}
		if ( empty( $username ) || empty( $password ) ) {
			// This is not my case.
			return $user;
		}
		// Is this login try?
		$token  = filter_input( INPUT_POST, 'recaptcha-v3-token' );
		$result = $this->verify( $token );
		if ( is_wp_error( $result ) ) {
			if ( is_wp_error( $user ) ) {
				$user->add( $result->get_error_code(), $result->get_error_message() );
			} else {
				$user = $result;
			}
		}
		return $user;
	}

	/**
	 * Verify registration information.
	 *
	 * @param \WP_Error $errors
	 * @param string    $login
	 * @param string    $email
	 * @return \WP_Error
	 */
	public function registration_errors( $errors, $login, $email ) {
		if ( ! $this->available ) {
			return $errors;
		}
		$token  = filter_input( INPUT_POST, 'recaptcha-v3-token' );
		$result = $this->verify( $token );
		if ( is_wp_error( $result ) ) {
			$errors->add( $result->get_error_code(), $result->get_error_message() );
		}
		return $errors;
	}

	/**
	 * Add setting fields
	 */
	public function add_setting_fields() {
		add_settings_section( 'recaptcha', __( 'Google reCAPTCHA', 'hametuha' ), function() {
			printf( '<p class="description">%s</p>', esc_html__( 'Add Google reCAPTCHA to protect your site.', 'hametuha' ) );
		}, 'writing' );
		foreach ( [
			[ 'site_key', __( 'Site Key', 'hametuha' ), $this->site_key_defined ],
			[ 'secret_key', __( 'Secret Key', 'hametuha' ), $this->secret_key_defined ],
		] as list( $name, $title, $is_defined ) ) {
			$option_name = 'recaptcha_v3_' . $name;
			add_settings_field( $option_name, $title, function() use ( $name, $is_defined, $option_name ) {
				$value = $this->{$name};
				?>
				<input name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>"
					   type="text" class="regular-text"
					   value="<?php echo esc_attr( $value ); ?>" <?php echo $is_defined ? 'readonly="readonly"' : ''; ?>
					   placeholder="xxxxxxxx" />
				<?php
				if ( $is_defined ) {
					printf( '<p class="description">%s</p>', esc_html__( 'This value is defined in code.', 'hametuha' ) );
				}
			}, 'writing', 'recaptcha' );
			if ( ! $is_defined ) {
				register_setting( 'writing', $option_name );
			}
		}
	}

	/**
	 * Get defined constants.
	 *
	 * @param string $name
	 * @return string
	 */
	private function get_key( $name ) {
		$const = 'RECAPTCHA_V3_' . strtoupper( $name );
		if ( defined( $const ) ) {
			$constants = get_defined_constants();
			$value     = isset( $constants[ $const ] ) ? $constants[ $const ] : '';
		} else {
			$value = (string) get_option( 'recaptcha_v3_' . $name, '' );
		}
		return apply_filters( 'recaptcha_v3_' . $name, $value );
	}

	/**
	 * Getter
	 *
	 * @param $name string
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'site_key':
			case 'secret_key':
				return $this->get_key( $name );
			case 'site_key_defined':
			case 'secret_key_defined':
				return defined( 'RECAPTCHA_V3_' . strtoupper( str_replace( '_defined', '', $name ) ) );
			case 'available':
				return $this->site_key && $this->secret_key;
			default:
				return null;
		}
	}


}
