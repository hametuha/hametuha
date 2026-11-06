/**
 * ESLint の設定
 *
 * wp-scripts の既定（@wordpress/eslint-plugin の recommended）に、テーマ固有の設定を足す。
 */
const globals = require( 'globals' );
const defaultConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...defaultConfig,
	{
		ignores: [
			// ビルドの生成物
			'assets/**',
			// 同梱しているサードパーティのライブラリ（手を入れない）
			'assets-src/js/common/slick.js',
			'assets-src/js/common/headroom.js',
		],
	},
	{
		files: [ 'assets-src/**/*.js' ],
		languageOptions: {
			globals: {
				...globals.browser,
				...globals.jquery,
				// テーマが PHP（wp_localize_script）や別ハンドルで出しているグローバル
				Hametuha: 'readonly',
				HametuhaComponents: 'readonly',
				wpApiSettings: 'readonly',
				// 外部スクリプト
				gtag: 'readonly',
				bootstrap: 'readonly',
				bootbox: 'readonly',
				moment: 'readonly',
				Recharts: 'readonly',
			},
		},
		settings: {
			// @wordpress/* は WordPress が読み込むもの（wp-scripts が外部化する）。npm には入れない
			'import/internal-regex': '^@wordpress/',
		},
		rules: {
			'import/no-unresolved': [ 'error', { ignore: [ '^@wordpress/' ] } ],
			// 全角スペースを判定する正規表現（/[ 　]/ など）で使っている
			'no-irregular-whitespace': [ 'error', { skipRegExps: true } ],
		},
	},
];
