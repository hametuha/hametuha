var gulp = require('gulp'),
  fs = require('fs'),
  $ = require('gulp-load-plugins')(),
  browserSync = require('browser-sync'),
  pngquant = require('imagemin-pngquant'),
  mozjpeg = require('imagemin-mozjpeg'),
  mergeStream = require('merge-stream'),
  webpack       = require('webpack-stream'),
  webpackBundle = require('webpack'),
  named         = require('vinyl-named');


// Sassのタスク
gulp.task('sass', function () {

  return gulp.src(['./assets/sass/**/*.scss'])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sassGlob())
    .pipe($.sourcemaps.init())
    .pipe($.sass({
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
    }))
    .pipe($.autoprefixer({
      grid: true,
      browsers: ['last 2 versions', 'ie 11']
    }))
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest('./assets/css'));
});


// Minify All
gulp.task('js', function () {
  return gulp.src(['./assets/js/src/**/*.js', '!./assets/js/src/common/*.js'])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sourcemaps.init({
      loadMaps: true
    }))
    .pipe($.uglify({
      output:{
        comments: /^!/
      }
    }))
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest('./assets/js/dist/'));
});

// Package jsx.
gulp.task( 'jsx', function() {
  var tmp = {};
  return gulp.src([ './assets/js/src/**/*.jsx', '!./assets/js/src/**/_*.jsx' ])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe(named())
    .pipe($.rename(function (path) {
      tmp[path.basename] = path.dirname;
    }))
    .pipe(webpack({
      mode: 'production',
      devtool: 'source-map',
      module: {
        rules: [
          {
            test: /\.jsx?$/,
            exclude: /(node_modules|bower_components)/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env'],
                plugins: ['@babel/plugin-transform-react-jsx']
              }
            }
          }
        ]
      }
    }, webpackBundle))
    .pipe($.rename(function (path) {
      if (tmp[path.basename]) {
        path.dirname = tmp[path.basename];
      } else if ('.map' === path.extname && tmp[path.basename.replace(/\.js$/, '')]) {
        path.dirname = tmp[path.basename.replace(/\.js$/, '')];
      }
      return path;
    }))
    .pipe(gulp.dest('./assets/js/dist'));
});

// Build app
gulp.task('commonjs', function () {
  return gulp.src(['./assets/js/src/common/*.js'])
    .pipe($.concat('common.js'))
    .pipe(gulp.dest('./assets/js/src/'));
});

// JS Hint
gulp.task('jshint', function () {
  return gulp.src([
    './assets/js/src/**/*.js',
    '!./assets/js/src/modernizr.js',
    '!./assets/js/src/common/headroom.js',
    '!./assets/js/src/common/slick.js',
    '!./assets/js/src/common.js',
    '!./assets/js/src/common/jquery.mmenu.custom.js'
  ])
    .pipe($.jshint('./assets/.jshintrc'))
    .pipe($.jshint.reporter('jshint-stylish'));
});

// Build modernizr
gulp.task('copylib', function () {
  return mergeStream(
    // Build Bootstrap
    gulp.src([
      './node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
      './node_modules/bootbox/bootbox.js'
    ])
      .pipe($.concat('bootstrap.js'))
      .pipe($.uglify())
      .pipe(gulp.dest('./assets/js/dist')),
    // Build Angular
    gulp.src([
      './node_modules/angular/angular.js',
      './node_modules/angular-i18n/angular-locale_ja-jp.js',
      './node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js'
    ])
      .pipe($.concat('angular.js'))
      .pipe($.uglify())
      .pipe(gulp.dest('./assets/js/dist')),
    // Build unpacked Libraries.
    gulp.src([
      './node_modules/html5shiv/dist/html5shiv.js',
      './node_modules/respond.js/dest/respond.src.js',
    ])
      .pipe($.uglify())
      .pipe(gulp.dest('./assets/js/dist/')),
    gulp.src([
      './node_modules/select2/dist/js/select2.min.js',
    ])
      .pipe(gulp.dest('./assets/js/dist/select2')),
    gulp.src([
      './node_modules/select2/dist/js/i18n/ja.js'
    ])
      .pipe(gulp.dest('./assets/js/dist/select2/i18n')),
    gulp.src([
      './node_modules/select2/dist/css/select2.min.css'
    ])
      .pipe(gulp.dest('./assets/css')),
    gulp.src([
      './node_modules/prop-types/prop-types.min.js'
    ])
      .pipe( gulp.dest('./assets/js/dist') )
  );
});

// Image min
gulp.task('imagemin', function () {
  return gulp.src('./assets/img/src/**/*')
    .pipe($.imagemin([
      pngquant({
        quality: '65-80',
        speed: 1,
        floyd: 0
      }),
      mozjpeg({
        quality: 85,
        progressive: true
      }),
      $.imagemin.svgo(),
      $.imagemin.optipng(),
      $.imagemin.gifsicle()
    ]))
    .pipe(gulp.dest('./assets/img'));
});

// Jade
gulp.task('jade', function () {

  var list = fs.readdirSync('./assets/jade')
    .filter(function (file) {
      return /^[^_].*\.jade$/.test(file);
    }).map(function (f) {
      return f.replace('.jade', '.html');
    });

  return gulp.src(['./assets/jade/**/*.jade', '!./assets/jade/**/_*.jade'])
    .pipe($.plumber())
    .pipe($.pug({
      pretty: true,
      locals: {
        list: list,
        scripts: [
          'https://code.jquery.com/jquery-1.11.3.min.js',
          '../js/dist/bootstrap.js',
          '../js/dist/common.js',
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
    }))
    .pipe(gulp.dest('./assets/html/'));
});

// watch
gulp.task('watch', function () {
  // Make SASS
  gulp.watch('assets/sass/**/*.scss', gulp.task('sass'));
  // Uglify all
  gulp.watch(['assets/js/src/**/*.js', '!./assets/js/src/common/**/*.js'], gulp.task('js'));
  // Handle JSX
  gulp.watch(['assets/js/src/**/*.jsx'], gulp.task('jsx'));
  // Check JS syntax
  gulp.watch('assets/js/src/**/*.js', gulp.task('jshint'));
  // Build common js
  gulp.watch('assets/js/src/common/**/*.js', gulp.task('commonjs'));
  // Minify Image
  gulp.watch('assets/img/src/**/*', gulp.task('imagemin'));
  // Build Jade
  gulp.watch('assets/jade/**/*.jade', gulp.task('jade'));
});

gulp.task('bs-watch', function () {
  return gulp.watch([
    'assets/css/**/*.css',
    'assets/js/dist/**/*.js',
    'assets/img/**/*', '!./assets/img/src/**/*',
    'assets/html/*.html'
  ], gulp.task('bs-reload'));
});

// BrowserSync
gulp.task('browser-sync', function () {
  return browserSync({
    server: {
      baseDir: "./assets/"       //対象ディレクトリ
      , index: "html/index.html"      //インデックスファイル
    },
    reloadDelay: 500
  });
});

gulp.task('bs-reload', function () {
  return browserSync.reload();
});

// Build
gulp.task('build', gulp.parallel('copylib', 'jshint', 'commonjs', 'js', 'jsx', 'sass', 'imagemin', 'jade'));

// Default Tasks
gulp.task('default', gulp.series('watch'));

// Browser sync( not working?)
gulp.task('bs', gulp.series('browser-sync', 'bs-watch'));
