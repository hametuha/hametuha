var gulp = require('gulp'),
    $ = require('gulp-load-plugins')(),
    browserSync = require('browser-sync'),
    pngquant = require('imagemin-pngquant');


// Sassのタスク
gulp.task('sass',function(){

    var filter = $.filter('**/*.css');

    return gulp.src(['./assets/sass/**/*.scss'])
        .pipe($.plumber())
        .pipe($.sourcemaps.init())
        .pipe($.sass({
            errLogToConsole: true,
            outputStyle: 'compressed',
            sourceComments: 'normal',
            sourcemap: true,
            includePaths: [
                './assets/sass',
                './vendor',
                './bower_components/bootstrap-sass/assets/stylesheets',
                './vendor/hametuha'
            ]
        }))
        .pipe($.sourcemaps.write('./map'))
        //.pipe($.pleeease({
        //    browsers: ['last 4 versions', 'ie 8']
        //}))
        //.pipe(filter)
        //.pipe(filter.restore())
        .pipe(gulp.dest('./assets/css'));
});


// Minify
gulp.task('js', function(){
    return gulp.src(['./assets/js/src/**/*.js'])
        .pipe($.sourcemaps.init({
            loadMaps: true
        }))
        .pipe($.uglify())
        .on('error', $.util.log)
        .pipe($.sourcemaps.write('./map'))
        .pipe(gulp.dest('./assets/js/dist/'));
});

// JS Hint
gulp.task('jshint', function(){
    return gulp.src(['./assets/js/src/**/*.js'])
        .pipe($.jshint('./assets/.jshintrc'))
        .pipe($.jshint.reporter('jshint-stylish'));
});

// Image min
gulp.task('imagemin', function(){
    return gulp.src('./assets/img/src/**/*')
        .pipe($.imagemin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngquant()]
        }))
        .pipe(gulp.dest('./assets/img'));
});

// Build modernizr
gulp.task('copylib', function(){
    return gulp.src(['./bower_components/modernizr/modernizr.js', './bower_components/bootbox.js/bootbox.js'])
        .pipe($.uglify())
        .pipe(gulp.dest('./assets/js/dist/'));
});

// watch
gulp.task('watch',function(){
    gulp.watch('./assets/sass/**/*.scss',['sass']);
    gulp.watch('./assets/js/src/**/*.js',['js', 'jshint']);
    gulp.watch('./assets/img/src/**/*',['imagemin']);
});

gulp.task('bs-watch', function(){
    gulp.watch([
        './assets/css/**/*.css',
        './assets/js/dist/**/*.js', '!./assets/js/src/**/*',
        './assets/img/**/*', '!./assets/img/src/**/*'
    ], ['bs-reload'])
});

// BrowserSync
gulp.task('browser-sync', function() {
    browserSync({
        proxy: "hametuha.info"
    });
});

gulp.task('bs-reload', function(){
    browserSync.reload({
        stream: true
    });
});

// Build
gulp.task('build', ['copylib', 'jshint', 'js', 'sass', 'imagemin']);

// Default Tasks
gulp.task('default', ['watch']);

// Browser sync( not working?)
gulp.task('bs', ['browser-sync', 'bs-watch']);