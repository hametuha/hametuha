/**
 * Lighthouse CI の結果（.lighthouseci/lhr-*.json）を、URL ごとの中央値の表（Markdown）にする。
 *
 * しきい値は .lighthouserc.json の assertions と揃えること。
 *
 * 使い方: node .github/scripts/lighthouse-summary.mjs [.lighthouseci]
 */
import { readdirSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

const dir = process.argv[ 2 ] || '.lighthouseci';

// 表に出す指標。limit は「悪い（poor）」の境界で、超えたら ❌ を付ける
const METRICS = [
	{ id: 'largest-contentful-paint', label: 'LCP', unit: 'ms', limit: 4000 },
	{ id: 'cumulative-layout-shift', label: 'CLS', unit: '', limit: 0.25 },
	{ id: 'total-blocking-time', label: 'TBT', unit: 'ms', limit: 600 },
	{ id: 'first-contentful-paint', label: 'FCP', unit: 'ms' },
	{ id: 'server-response-time', label: 'TTFB', unit: 'ms' },
];

const median = ( values ) => {
	const sorted = values.filter( ( v ) => typeof v === 'number' ).sort( ( a, b ) => a - b );
	if ( ! sorted.length ) {
		return null;
	}
	return sorted[ Math.floor( sorted.length / 2 ) ];
};

const format = ( value, unit ) => {
	if ( value === null ) {
		return '-';
	}
	return unit === 'ms' ? `${ Math.round( value ) }ms` : value.toFixed( 3 );
};

// URL ごとに、各回の値を集める
const byUrl = new Map();
for ( const file of readdirSync( dir ).filter( ( f ) => /^lhr-.*\.json$/.test( f ) ) ) {
	const lhr = JSON.parse( readFileSync( join( dir, file ), 'utf8' ) );
	const url = lhr.requestedUrl;
	if ( ! byUrl.has( url ) ) {
		byUrl.set( url, [] );
	}
	byUrl.get( url ).push( lhr );
}

const lines = [
	'| URL | ' + METRICS.map( ( m ) => m.label ).join( ' | ' ) + ' |',
	'|---|' + METRICS.map( () => '---' ).join( '|' ) + '|',
];
for ( const [ url, runs ] of [ ...byUrl ].sort() ) {
	const cells = METRICS.map( ( m ) => {
		const value = median( runs.map( ( lhr ) => lhr.audits[ m.id ]?.numericValue ) );
		const over = m.limit !== undefined && value !== null && value > m.limit;
		return ( over ? '❌ ' : '' ) + format( value, m.unit );
	} );
	lines.push( `| ${ decodeURI( url ) } | ${ cells.join( ' | ' ) } |` );
}
lines.push( '' );
lines.push( `各 URL ${ Math.max( ...[ ...byUrl.values() ].map( ( r ) => r.length ) ) } 回の中央値（モバイル、Lighthouse のシミュレーション）。❌ は「悪い（poor）」の境界を超えたもの。` );

process.stdout.write( lines.join( '\n' ) + '\n' );
