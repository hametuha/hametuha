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
				'./node_modules/bootstrap/scss',
				'./vendor'
			]
		} ) )
		.pipe( $.autoprefixer() )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './dist/css' ) );
} );

// Style lint.
const stylelintFunction = ( src ) => {
	let task = gulp.src( src );
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
};

// Lint all.
gulp.task( 'stylelint', function () {
	return stylelintFunction( [ './assets/sass/**/*.scss' ] );
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
const eslintFunction = ( src ) => {
	let task = gulp.src( src );
	if ( plumber ) {
		task = task.pipe( $.plumber() );
	}
	return task.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() );
};

// Register as task.
gulp.task( 'eslint', function () {
	return eslintFunction( [
		'./assets/js/**/*.{js,jsx}',
		'!./assets/js/vendor/**/*.js',
	] );
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
gulp.task( 'pug', function () {
	const list = fs.readdirSync( './assets/pug' )
		.filter( function ( file ) {
			return /^[^_].*\.pug$/.test( file );
		} ).map( function ( f ) {
			return f.replace( '.pug', '.html' );
		} );
	const json = require( './assets/pug/setting.json' );
	json.list = list;
	return gulp.src( [
		'./assets/pug/**/*.pug',
		'!./assets/pug/**/_*.pug'
	] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.pug( {
			pretty: true,
			locals: json,
		} ) )
		.pipe( gulp.dest( './dist/' ) );
} );

// watch
gulp.task( 'watch', ( done ) => {
	// Make SASS
	const stylelintWatcher = gulp.watch( 'assets/sass/**/*.scss', gulp.task( 'sass' ) );
	const stylelintHandler = ( path ) => {
		return stylelintFunction( path );
	};
	stylelintWatcher.on( 'change', stylelintHandler );
	stylelintWatcher.on( 'add', stylelintHandler );

	// Bundle JS and Lint only changed.
	const eslintWatcher = gulp.watch( [ 'assets/js/**/*.{js,jsx}' ], gulp.task( 'jsx' ) );
	const eslintHandler = ( path ) => {
		return eslintFunction( path );
	};
	eslintWatcher.on( 'change', eslintHandler );
	eslintWatcher.on( 'add', eslintHandler );

	// Minify Image
	gulp.watch( 'assets/img/**/*', gulp.task( 'imagemin' ) );

	// Dump JSON
	gulp.watch( [
		'dist/js/**/*.js',
		'dist/css/**/*.css',
	], gulp.task( 'dump' ) );

	// Build pug
	gulp.watch( [
		'assets/pug/**/*.pug',
		'assets/pug/setting.json',
	], gulp.task( 'pug' ) );

	done();
} );

// Watch browsersync changes.
gulp.task( 'bs-watch', ( done ) => {
	gulp.watch( [ 'dist/**/*' ], gulp.task( 'bs-reload' ) );
	done();
} );

// BrowserSync
gulp.task( 'bs-init', function ( done ) {
	browserSync( {
		server: {
			baseDir: "./dist/"       //対象ディレクトリ
			, index: "index.html"      //インデックスファイル
		},
		reloadDelay: 500
	} );
	done();
} );

gulp.task( 'bs-reload', function ( done ) {
	browserSync.reload();
	done();
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
gulp.task( 'bs', gulp.series( 'bs-init', 'bs-watch' ) );

// Static assets development.
gulp.task( 'statics', gulp.parallel( 'watch', 'bs' ) );

// Lint
gulp.task( 'lint', gulp.series( 'noplumber', gulp.parallel( 'stylelint', 'eslint' ) ) );
