#!/usr/bin/env node
/**
 * node_modules のライブラリを assets/vendor にコピーする（ビルドしないもの）
 *
 * - Bootstrap 5（bundle 版）と bootbox を連結して assets/vendor/bootstrap.js にする
 * - select2 と日本語ファイル・CSS を assets/vendor/select2 にコピーする
 *
 * assets/vendor は grab-deps dump の対象外（wp-dependencies.json に載せない）。
 *
 * 登録は hooks/assets.php で行っている。
 */

const fs = require( 'fs' );
const path = require( 'path' );

const root = path.resolve( __dirname, '..' );
const nodeModules = path.join( root, 'node_modules' );

/**
 * ファイルを書き出す（ディレクトリが無ければ作る）
 *
 * @param {string} dest    出力先（テーマからの相対パス）
 * @param {string} content 中身
 */
const write = ( dest, content ) => {
	const file = path.join( root, dest );
	fs.mkdirSync( path.dirname( file ), { recursive: true } );
	fs.writeFileSync( file, content );
	// eslint-disable-next-line no-console
	console.log( `  ${ dest }` );
};

const read = ( file ) => fs.readFileSync( path.join( nodeModules, file ), 'utf8' );

// eslint-disable-next-line no-console
console.log( 'Copying libraries:' );

write(
	'assets/vendor/bootstrap.js',
	[
		read( 'bootstrap/dist/js/bootstrap.bundle.min.js' ),
		read( 'bootbox/dist/bootbox.min.js' ),
	].join( '\n;\n' )
);
write( 'assets/vendor/select2/select2.min.js', read( 'select2/dist/js/select2.min.js' ) );
write( 'assets/vendor/select2/i18n/ja.js', read( 'select2/dist/js/i18n/ja.js' ) );
write( 'assets/vendor/select2/select2.min.css', read( 'select2/dist/css/select2.min.css' ) );
