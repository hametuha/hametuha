#!/usr/bin/env node
/*!
 * assets/img の画像をその場で圧縮する。
 *
 * 画像はビルドのたびに圧縮せず、追加・差し替えたときに一度だけこれを実行して、
 * 圧縮後のファイルをコミットする。
 *
 *   npm run compress-images                     # Git上で追加・変更された画像だけ
 *   npm run compress-images -- assets/img/a.png # ファイルやディレクトリを指定
 *
 * 圧縮して小さくならなかったファイルは書き換えない。
 * GIF・ICO は対象外（そのままコミットする）。
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { execSync } = require( 'child_process' );
const sharp = require( 'sharp' );

const ROOT = path.resolve( __dirname, '..' );
const IMG_DIR = 'assets/img';

// 旧 gulp-imagemin（mozjpeg 85 / pngquant 65-80）と同程度の設定
const encoders = {
	'.jpg': ( img ) => img.jpeg( { quality: 85, progressive: true, mozjpeg: true } ),
	'.jpeg': ( img ) => img.jpeg( { quality: 85, progressive: true, mozjpeg: true } ),
	'.png': ( img ) => img.png( { palette: true, quality: 80, effort: 10, compressionLevel: 9 } ),
};

/**
 * Git 上で追加・変更されたファイル（未追跡を含む）。
 *
 * @return {string[]}
 */
function changedFiles() {
	const git = ( cmd ) => execSync( `git ${ cmd }`, { cwd: ROOT, encoding: 'utf-8' } );
	// porcelain のパスはリポジトリのトップからの相対パス
	const top = git( 'rev-parse --show-toplevel' ).trim();
	return git( `status --porcelain --untracked-files=all -- ${ IMG_DIR }` )
		.split( '\n' )
		.filter( ( line ) => line && ! line.slice( 0, 2 ).includes( 'D' ) )
		.map( ( line ) => path.resolve( top, line.slice( 3 ).split( ' -> ' ).pop() ) );
}

/**
 * ディレクトリなら中のファイルを再帰的に展開する。
 *
 * @param {string} target
 * @return {string[]}
 */
function expand( target ) {
	const abs = path.resolve( process.cwd(), target );
	if ( ! fs.statSync( abs ).isDirectory() ) {
		return [ abs ];
	}
	return fs.readdirSync( abs ).flatMap( ( name ) => expand( path.join( abs, name ) ) );
}

async function compress( file ) {
	const encode = encoders[ path.extname( file ).toLowerCase() ];
	const rel = path.relative( ROOT, file );
	if ( ! encode ) {
		return console.log( `skip   ${ rel }` );
	}
	const before = fs.statSync( file ).size;
	// rotate() で EXIF の向きを画素に反映してからメタデータを落とす
	const buffer = await encode( sharp( file ).rotate() ).toBuffer();
	if ( buffer.length >= before ) {
		return console.log( `keep   ${ rel } (${ before } bytes)` );
	}
	fs.writeFileSync( file, buffer );
	const rate = Math.round( ( 1 - buffer.length / before ) * 100 );
	console.log( `shrink ${ rel } ${ before } -> ${ buffer.length } bytes (-${ rate }%)` );
}

( async () => {
	const args = process.argv.slice( 2 );
	const files = args.length ? args.flatMap( expand ) : changedFiles();
	if ( ! files.length ) {
		console.log( `${ IMG_DIR } に追加・変更された画像はありません。` );
		return;
	}
	for ( const file of files ) {
		await compress( file );
	}
} )().catch( ( err ) => {
	console.error( err );
	process.exit( 1 );
} );
