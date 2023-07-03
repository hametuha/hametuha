var gulp = require( 'gulp' ),
	fs = require( 'fs' ),
	$ = require( 'gulp-load-plugins' )(),
	pngquant = require( 'imagemin-pngquant' ),
	mozjpeg = require( 'imagemin-mozjpeg' ),
	mergeStream = require( 'merge-stream' ),
	webpack = require( 'webpack-stream' ),
	webpackBundle = require( 'webpack' ),
	named = require( 'vinyl-named' );


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
		.pipe( gulp.dest( './assets/css' ) );
} );


// Minify All
gulp.task( 'js', function () {
	return gulp.src( [ './assets/js/src/**/*.js', '!./assets/js/src/common/*.js' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.sourcemaps.init( {
			loadMaps: true
		} ) )
		.pipe( $.uglify( {
			output: {
				comments: /^!/
			}
		} ) )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './assets/js/dist/' ) );
} );

// Package jsx.
gulp.task( 'jsx', function () {
	return gulp.src( [ './assets/js/src/**/*.jsx', '!./assets/js/src/**/_*.jsx' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( named( ( file ) => {
			return file.relative.replace(/\.[^\.]+$/, '');
		} ) )
		.pipe( webpack( require( './webpack.config.js' ), webpackBundle ) )
		.pipe( gulp.dest( './assets/js/dist' ) );
} );

// Build app
gulp.task( 'commonjs', function () {
	return gulp.src( [ './assets/js/src/common/*.js' ] )
		.pipe( $.concat( 'common.js' ) )
		.pipe( gulp.dest( './assets/js/src/' ) );
} );

// JS Hint
gulp.task( 'jshint', function () {
	return gulp.src( [
		'./assets/js/src/**/*.js',
		'!./assets/js/src/modernizr.js',
		'!./assets/js/src/common/headroom.js',
		'!./assets/js/src/common/slick.js',
		'!./assets/js/src/common.js',
		'!./assets/js/src/common/jquery.mmenu.custom.js'
	] )
		.pipe( $.jshint( './assets/.jshintrc' ) )
		.pipe( $.jshint.reporter( 'jshint-stylish' ) );
} );

// Build modernizr
gulp.task( 'copylib', function () {
	return mergeStream(
		// Build Bootstrap
		gulp.src( [
			'./node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
			'./node_modules/bootbox/bootbox.js'
		] )
			.pipe( $.concat( 'bootstrap.js' ) )
			.pipe( $.uglify() )
			.pipe( gulp.dest( './assets/js/dist' ) ),
		// Build Angular
		gulp.src( [
			'./node_modules/angular/angular.js',
			'./node_modules/angular-i18n/angular-locale_ja-jp.js',
			'./node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js'
		] )
			.pipe( $.concat( 'angular.js' ) )
			.pipe( $.uglify() )
			.pipe( gulp.dest( './assets/js/dist' ) ),
		gulp.src( [
			'./node_modules/select2/dist/js/select2.min.js',
		] )
			.pipe( gulp.dest( './assets/js/dist/select2' ) ),
		gulp.src( [
			'./node_modules/select2/dist/js/i18n/ja.js'
		] )
			.pipe( gulp.dest( './assets/js/dist/select2/i18n' ) ),
		gulp.src( [
			'./node_modules/select2/dist/css/select2.min.css'
		] )
			.pipe( gulp.dest( './assets/css' ) ),
		gulp.src( [
			'./node_modules/prop-types/prop-types.min.js'
		] )
			.pipe( gulp.dest( './assets/js/dist' ) )
	);
} );

// Image min
gulp.task( 'imagemin', function () {
	return gulp.src( './assets/img/src/**/*' )
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
		.pipe( gulp.dest( './assets/img' ) );
} );

// watch
gulp.task( 'watch', function () {
	// Make SASS
	gulp.watch( 'assets/sass/**/*.scss', gulp.task( 'sass' ) );
	// Uglify all
	gulp.watch( [ 'assets/js/src/**/*.js', '!./assets/js/src/common/**/*.js' ], gulp.task( 'js' ) );
	// Handle JSX
	gulp.watch( [ 'assets/js/src/**/*.jsx' ], gulp.task( 'jsx' ) );
	// Check JS syntax
	gulp.watch( 'assets/js/src/**/*.js', gulp.task( 'jshint' ) );
	// Build common js
	gulp.watch( 'assets/js/src/common/**/*.js', gulp.task( 'commonjs' ) );
	// Minify Image
	gulp.watch( 'assets/img/src/**/*', gulp.task( 'imagemin' ) );
} );

// Build
gulp.task( 'build', gulp.parallel( 'copylib', 'jshint', 'commonjs', 'js', 'jsx', 'sass', 'imagemin' ) );

// Default Tasks
gulp.task( 'default', gulp.series( 'watch' ) );
