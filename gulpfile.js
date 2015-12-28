var gulp = require('gulp'),
    fs   = require('fs'),
    $ = require('gulp-load-plugins')(),
    browserSync = require('browser-sync'),
    pngquant = require('imagemin-pngquant');


// Sassのタスク
gulp.task('sass',function(){

    var filter = $.filter('**/*.css');

    return gulp.src(['./assets/sass/**/*.scss'])
        .pipe($.plumber())
        .pipe($.sassBulkImport())
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


// Minify All
gulp.task('js', function(){
    return gulp.src(['./assets/js/src/**/*.js', '!./assets/js/src/common/*.js'])
        .pipe($.sourcemaps.init({
            loadMaps: true
        }))
        .pipe($.uglify())
        .on('error', $.util.log)
        .pipe($.sourcemaps.write('./map'))
        .pipe(gulp.dest('./assets/js/dist/'));
});

// Build app
gulp.task('commonjs', function(){
    return gulp.src(['./assets/js/src/common/*.js'])
        .pipe($.concat('common.js'))
        .pipe(gulp.dest('./assets/js/src/'));
});

// JS Hint
gulp.task('jshint', function(){
    return gulp.src(['./assets/js/src/**/*.js'])
        .pipe($.jshint('./assets/.jshintrc'))
        .pipe($.jshint.reporter('jshint-stylish'));
});

// Jade
gulp.task('jade', function(){

    var list = fs.readdirSync('./assets/jade')
        .filter(function(file) {
            return /^[^_].*\.jade$/.test(file);
        }).map(function(f){
            return f.replace('.jade', '.html');
        });

    return gulp.src(['./assets/jade/**/*.jade', '!./assets/jade/**/_*.jade'])
        .pipe($.plumber())
        .pipe($.jade({
            pretty: true,
            locals: {
                list: list,
                scripts: [
                    'https://code.jquery.com/jquery-1.11.3.min.js',
                    './js/dist/bootstrap.js',
                    './js/dist/common.js',
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
                        "strong":"登録成功！",
                        "body":"これであなたは大金持ちになりました。"
                    },
                    "info": {
                        "strong":"お知らせ",
                        "body":"これはみても見なくてもどっちでもいいお知らせです。"
                    },
                    "warning": {
                        "strong":"注意！",
                        "body":"なにかおかしなことが起きたのでこのメッセージが表示されています。"
                    },
                    "danger": {
                        "strong":"警告！",
                        "body":"あなたはなにかとんでもないことをしてしまったので、メッセージが出ています。。"
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
        }))
        .pipe(gulp.dest('./assets/'));
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
    // Build Bootstrap
    gulp.src([
        './bower_components/bootstrap-sass/assets/javascripts/bootstrap.js',
        './bower_components/bootbox.js/bootbox.js'
    ])
        .pipe($.concat('bootstrap.js'))
        .pipe($.uglify())
        .pipe(gulp.dest('./assets/js/dist'));
    // Build unpacked Libraries.
    return gulp.src([
        './bower_components/modernizr/modernizr.js',
        './bower_components/html5shiv/dist/html5shiv.js',
        './bower_components/respond/dest/respond.src.js',
        './bower_components/angular/angular.min.js',
        './bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js',
    ])
        .pipe($.uglify())
        .pipe(gulp.dest('./assets/js/dist/'));
});

// watch
gulp.task('watch',function(){
    // Make SASS
    gulp.watch('./assets/sass/**/*.scss',['sass']);
    // Uglify all
    gulp.watch(['./assets/js/src/**/*.js', '!./assets/js/src/common/**/*.js'],['js']);
    // Check JS syntax
    gulp.watch('./assets/js/src/**/*.js',['jshint']);
    // Build common js
    gulp.watch('./assets/js/src/common/**/*.js',['commonjs']);
    // Minify Image
    gulp.watch('./assets/img/src/**/*',['imagemin']);
    // Build Jasde
    gulp.watch('./assets/jade/**/*.jade', ['jade']);
});

gulp.task('bs-watch', function(){
    gulp.watch([
        './assets/css/**/*.css',
        './assets/js/dist/**/*.js',
        './assets/img/**/*', '!./assets/img/src/**/*',
        './assets/*.html'
    ], ['bs-reload'])
});

// BrowserSync
gulp.task('browser-sync', function() {
    browserSync({
        server: {
            baseDir: "./assets/"       //対象ディレクトリ
            ,index  : "index.html"      //インデックスファイル
        }
    });
});

gulp.task('bs-reload', function(){
    browserSync.reload();
});

// Build
gulp.task('build', ['copylib', 'jshint', 'commonjs', 'js', 'sass', 'imagemin', 'jade']);

// Default Tasks
gulp.task('default', ['watch']);

// Browser sync( not working?)
gulp.task('bs', ['browser-sync', 'bs-watch']);