const gulp = require( 'gulp' );
const fs = require( 'fs' );
const md5File = require( 'md5-file' );
const { glob } = require( 'glob' );
const $ = require( 'gulp-load-plugins' )();
const pngquant = require( 'imagemin-pngquant' );
const mozjpeg = require( 'imagemin-mozjpeg' );
const mergeStream = require( 'merge-stream' );
const webpack = require( 'webpack-stream' );
const webpackBundle = require( 'webpack' );
const named = require( 'vinyl-named' );
const sass = require( 'gulp-sass' )( require( 'sass' ) );
const { plugins } = require( "@babel/preset-env/lib/plugins-compat-data" );

let noplumber = true;

// エラーオプションを切り替える
gulp.task( 'plumber', function( done ) {
	noplumber = false;
	done();
} );

// Sassのタスク
gulp.task( 'sass', function () {
	let task = gulp.src( [ './assets/sass/**/*.scss' ] );
	if ( noplumber ) {
		task = task.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) );
	}
	return task
		.pipe( $.sassGlob() )
		.pipe( $.sourcemaps.init() )
		.pipe( sass( {
			errLogToConsole: true,
			outputStyle: 'compressed',
			sourceComments: false,
			sourcemap: true,
			includePaths: [
				'./assets/sass',
				'./vendor',
				'./node_modules',
				'./vendor/hametuha'
			]
		} ) )
		.pipe( $.autoprefixer() )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './assets/css' ) );
} );


// Minify All
gulp.task( 'js', function () {
	let task = gulp.src( [ './assets/js/src/**/*.js', '!./assets/js/src/common/*.js' ] );
	if ( noplumber ) {
		task = task.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) );
	}
	return task
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
	let task = gulp.src( [ './assets/js/src/**/*.jsx', '!./assets/js/src/**/_*.jsx' ] );
	if ( noplumber ) {
		task = task.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) );
	}
	return task
		.pipe( named( ( file ) => {
			return file.relative.replace(/\.[^\.]+$/, '');
		} ) )
		.pipe( webpack( require( './webpack.config.js' ), webpackBundle ) )
		.pipe( gulp.dest( './assets/js/dist' ) );
} );

// Build wp-dependencies.json
gulp.task( 'deps', function( done ) {
	glob( 'assets/js/dist/**/*.LICENSE.txt' ).then( res => {
		return Promise.all( res.map( path => {
			return fs.promises.readFile( path, 'utf-8' ).then( content => {
				const name = path.replace( /^assets\/js\/dist\//, '' ).replace( /\.LICENSE\.txt$/, '' );
				const deps = [];
				content.replace( /@deps(.*)$/m, ( match, p1 ) => {
					p1.split( ',' ).map( dep => deps.push( dep.trim() ) );
				} );
				let handle = 'hametuha-' + name.replace( '/', '-' ).replace( '.js', '' );
				content.replace( /@handle(.*)$/m, ( match, p1 ) => {
					handle = p1.trim();
				} );
				path = path.replace( /\.LICENSE\.txt$/, '' );
				return {
					handle,
					ext: 'js',
					path,
					deps,
					hash: md5File.sync( path ),
					footer: true,
				};
			} );
		} ) );
	} ).then( values => {
		return fs.writeFileSync( './wp-dependencies.json', JSON.stringify( values, null, 2 ) );
	} ).then( () => done() );
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
		'!./assets/js/src/common/headroom.js',
		'!./assets/js/src/common/slick.js',
		'!./assets/js/src/common.js',
		'!./assets/js/src/common/jquery.mmenu.custom.js'
	] )
		.pipe( $.jshint( './assets/.jshintrc' ) )
		.pipe( $.jshint.reporter( 'jshint-stylish' ) );
} );

// Build 3rd party libraries.
gulp.task( 'copylib', function () {
	return mergeStream(
		// Build Bootstrap 5
		gulp.src( [
			'./node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
			'./node_modules/bootbox/dist/bootbox.js'
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
	// deps.
	gulp.watch( [ 'assets/js/dist/**/*.js.LICENSE.txt' ], gulp.task( 'deps' ) );
	// Check JS syntax
	gulp.watch( 'assets/js/src/**/*.js', gulp.task( 'jshint' ) );
	// Build common js
	gulp.watch( 'assets/js/src/common/**/*.js', gulp.task( 'commonjs' ) );
	// Minify Image
	gulp.watch( 'assets/img/src/**/*', gulp.task( 'imagemin' ) );
} );

// Build
gulp.task( 'build', gulp.series( 'plumber', gulp.parallel( 'copylib', 'jshint', 'commonjs', 'js', 'jsx', 'sass', 'imagemin' ), 'deps' ) );

// Default Tasks
gulp.task( 'default', gulp.series( 'watch' ) );
