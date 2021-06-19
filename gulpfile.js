const gulp = require( 'gulp' );
const fs = require( 'fs' );
const $ = require( 'gulp-load-plugins' )();
const browserSync = require( 'browser-sync' );
const pngquant = require( 'imagemin-pngquant' );
const mozjpeg = require( 'imagemin-mozjpeg' );
const mergeStream = require( 'merge-stream' );
const webpack = require( 'webpack-stream' );
const webpackBundle = require( 'webpack' );
const named = require( 'vinyl-named' );
const { dumpSetting } = require('@kunoichi/grab-deps');

let plumber = true;

// Sassのタスク
gulp.task( 'sass', function () {

	return gulp.src( [ './assets/sass/**/*.scss' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.sassGlob() )
		.pipe( $.sourcemaps.init() )
		.pipe( $.sass( {
			errLogToConsole: true,
			outputStyle: 'compressed',
			sourceComments: false,
			sourcemap: true,
			includePaths: [
				'./assets/sass',
				'./vendor',
				'./node_modules/bootstrap-sass/assets/stylesheets',
				'./vendor/hametuha'
			]
		} ) )
		.pipe( $.autoprefixer() )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './dist/css' ) );
} );

// Style lint.
gulp.task( 'stylelint', function () {
	let task = gulp.src( [ './assets/sass/**/*.scss' ] );
	if ( plumber ) {
		task = task.pipe( $.plumber() );
	}
	return task.pipe( $.stylelint( {
		reporters: [
			{
				formatter: 'string',
				console: true,
			},
		],
	} ) );
} );

// Package jsx.
gulp.task( 'jsx', function () {
	return gulp.src( [
		'./assets/js/**/*.{jsx,js}',
		'!./assets/js/**/_*.{jsx,js}',
		'!./assets/js/vendor/**/*.{jsx,js}',
	] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( named( (file) =>  {
			return file.relative.replace(/\.[^\.]+$/, '');
		} ) )
		.pipe( webpack( require( './webpack.config.js' ), webpackBundle ) )
		.pipe( gulp.dest( './dist/js' ) );
} );

// ESLint
gulp.task( 'eslint', function () {
	let task = gulp.src( [
		'./assets/js/**/*.{js,jsx}',
		'!./assets/js/vendor/**/*.js',
	] );
	if ( plumber ) {
		task = task.pipe( $.plumber() );
	}
	return task.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() );
} );

// Build modernizr
gulp.task( 'copylib', function () {
	return mergeStream(
		// Bootbox
		gulp.src( [
			'./node_modules/bootbox/dist/bootbox.all.min.js',
		] )
			.pipe( gulp.dest( './dist/vendor/bootbox' ) ),
		// Build Angular
		gulp.src( [
			'./node_modules/angular/angular.js',
			'./node_modules/angular-i18n/angular-locale_ja-jp.js',
			'./node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js',
		] )
			.pipe( $.concat( 'angular.js' ) )
			.pipe( $.uglify() )
			.pipe( gulp.dest( './dist/vendor/angular' ) ),
		gulp.src( [
			'./node_modules/select2/dist/js/select2.min.js',
			'./node_modules/select2/dist/css/select2.min.css',
		] )
			.pipe( gulp.dest( './dist/vendor/select2' ) ),
		gulp.src( [
			'./node_modules/select2/dist/js/i18n/ja.js'
		] )
			.pipe( gulp.dest( './dist/vendor/select2/i18n' ) ),
		gulp.src( [
			'./node_modules/slick-carousel/slick/slick.min.js'
		] )
			.pipe( gulp.dest( './dist/vendor/slick' ) ),
		gulp.src( [
			'./assets/js/vendor/jquery.mmenu.custom.js'
		] )
			.pipe( gulp.dest( './dist/vendor/mmenu' ) ),
		gulp.src( [
			'./node_modules/headroom.js/dist/headroom.min.js'
		] )
			.pipe( gulp.dest( './dist/vendor/headroom' ) ),
		gulp.src( [
			'./node_modules/prop-types/prop-types.min.js'
		] )
			.pipe( gulp.dest( './dist/vendor/prop-types' ) )
	);
} );

// Image min
gulp.task( 'imagemin', function () {
	return gulp.src( './assets/img/**/*' )
		.pipe( $.imagemin( [
			pngquant( {
				quality: '65-80',
				speed: 1,
				floyd: 0
			} ),
			mozjpeg( {
				quality: 85,
				progressive: true
			} ),
			$.imagemin.svgo(),
			$.imagemin.optipng(),
			$.imagemin.gifsicle()
		] ) )
		.pipe( gulp.dest( './dist/img' ) );
} );

// Jade
gulp.task( 'jade', function () {
	const list = fs.readdirSync( './assets/jade' )
		.filter( function ( file ) {
			return /^[^_].*\.jade$/.test( file );
		} ).map( function ( f ) {
			return f.replace( '.jade', '.html' );
		} );

	return gulp.src( [ './assets/jade/**/*.jade', '!./assets/jade/**/_*.jade' ] )
		.pipe( $.plumber() )
		.pipe( $.pug( {
			pretty: true,
			locals: {
				list: list,
				scripts: [
					'https://code.jquery.com/jquery-1.11.3.min.js',
					'../js/bootstrap.js',
					'../js/common.js',
				],

				labels: {
					"default": "デフォルト",
					"primary": "重要",
					"success": "成功",
					"info": "お知らせ",
					"warning": "警告",
					"danger": "危険"
				},
				"msgs": {
					"success": {
						"strong": "登録成功！",
						"body": "これであなたは大金持ちになりました。"
					},
					"info": {
						"strong": "お知らせ",
						"body": "これはみても見なくてもどっちでもいいお知らせです。"
					},
					"warning": {
						"strong": "注意！",
						"body": "なにかおかしなことが起きたのでこのメッセージが表示されています。"
					},
					"danger": {
						"strong": "警告！",
						"body": "あなたはなにかとんでもないことをしてしまったので、メッセージが出ています。。"
					}
				},
				carousels: [
					{
						"active": "active",
						"url": "./img/photo1.jpg",
						"alt": "最初のスライド",
						"caption": "これは日向山の山頂付近です。"
					},
					{
						"active": "",
						"url": "./img/photo2.jpg",
						"alt": "二番目のスライド",
						"caption": "これは八ヶ岳の山中です。"
					},
					{
						"active": "",
						"url": "./img/photo3.jpg",
						"alt": "三番目のスライド",
						"caption": "富山県の日本海に沈む夕日です。"
					}
				],
				lists: [
					{
						"title": "その他雑記",
						"body": "どうでもいいことが書いてあります。当サイトの人気コンテンツ。",
						"active": true
					},
					{
						"title": "Twitter",
						"body": "Twitterのコンテンツをただコピーしただけのページですが、二番目に人気があります。",
						"active": false
					},
					{
						"title": "料理と狩猟",
						"body": "私の趣味である料理と狩猟について書いています。同じ趣味を持っている方は共有してください",
						"active": false
					},
					{
						"title": "読書記録",
						"body": "私が読んだ本の感想について記してあります。ただし、一度も本を読んだことはありません。",
						"active": false
					}
				]
			}
		} ) )
		.pipe( gulp.dest( './dist/' ) );
} );

// watch
gulp.task( 'watch', function () {
	// Make SASS
	gulp.watch( 'assets/sass/**/*.scss', gulp.parallel( 'sass', 'stylelint' ) );
	// Bundle JS
	gulp.watch( [ 'assets/js/**/*.{js,jsx}' ], gulp.parallel( 'jsx', 'eslint' ) );
	// Minify Image
	gulp.watch( 'assets/img/**/*', gulp.task( 'imagemin' ) );
	// Dump JSON
	gulp.watch( [
		'dist/js/**/*.js',
		'dist/css/**/*.css',
	], gulp.task( 'dump' ) );
	// Build Jade
	gulp.watch( 'assets/jade/**/*.jade', gulp.task( 'jade' ) );
} );

// Watch browsersync changes.
gulp.task( 'bs-watch', function () {
	return gulp.watch( [
		'dist/**/*',
	], gulp.task( 'bs-reload' ) );
} );

// BrowserSync
gulp.task( 'browser-sync', function () {
	return browserSync( {
		server: {
			baseDir: "./dist/"       //対象ディレクトリ
			, index: "index.html"      //インデックスファイル
		},
		reloadDelay: 500
	} );
} );

gulp.task( 'bs-reload', function () {
	return browserSync.reload();
} );

// Toggle plumber.
gulp.task( 'noplumber', ( done ) => {
	plumber = false;
	done();
} );

// Dump task.
gulp.task( 'dump', function( done ) {
	dumpSetting( [ 'dist/js', 'dist/css' ] );
	done();
} );

// Build
gulp.task( 'build', gulp.series( gulp.parallel( 'copylib', 'jsx', 'sass', 'imagemin' ), 'dump' ) );

// Default Tasks
gulp.task( 'default', gulp.series( 'watch' ) );

// Browser sync( not working?)
gulp.task( 'bs', gulp.series( 'browser-sync', 'bs-watch' ) );


// Lint
gulp.task( 'lint', gulp.series( 'noplumber', gulp.parallel( 'stylelint', 'eslint' ) ) );
