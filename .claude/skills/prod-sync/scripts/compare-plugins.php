<?php
/**
 * 本番のプラグイン構成と composer.lock を突き合わせて分類する。
 *
 * 使い方:
 *   wp @production plugin list --fields=name,status,version --format=json \
 *     | grep -v '^PHP Warning' > /tmp/prod.json
 *   php .claude/skills/prod-sync/scripts/compare-plugins.php /tmp/prod.json
 *
 * 終了コード: 未解決（分類も除外もされていない）プラグインがあれば 1、無ければ 0。
 *
 * @package hametuha
 */

// CLAUDE.md の「プラグイン管理方針」で意図的に除外しているもの。
// ここを増やすときは必ず CLAUDE.md の除外表も更新すること（composer.json には痕跡が残らないため）。
const EXCLUDED = array(
	'hamecache'                        => 'キャッシュ系（Cloudflare連携・ローカルに対応インフラ無し）',
	'memcached'                        => 'キャッシュ系（object-cacheドロップイン）',
	'mo-cache'                         => 'キャッシュ系',
	'ads-txt'                          => '外部要因対応（ads.txt）',
	'robots-txt-editor'                => '外部要因対応（robots.txt）',
	'akismet'                          => '外部API依存（APIキー無しのローカルでは動作しない）',
	'gianism-mixi'                     => 'privateリポジトリ（CI制約による妥協。本番では現役）',
	'selected-post-for-contact-form-7' => 'privateリポジトリ（CI制約による妥協。本番では現役）',
);

$prod_file = $argv[1] ?? '';
if ( ! $prod_file || ! file_exists( $prod_file ) ) {
	fwrite( STDERR, "使い方: php compare-plugins.php <本番plugin listのJSON>\n" );
	exit( 2 );
}

$prod = json_decode( file_get_contents( $prod_file ), true );
if ( ! is_array( $prod ) ) {
	fwrite( STDERR, "本番JSONを読めません（PHP Warning が混ざっていないか確認）: {$prod_file}\n" );
	exit( 2 );
}

$lock = json_decode( file_get_contents( 'composer.lock' ), true );
if ( ! is_array( $lock ) ) {
	fwrite( STDERR, "composer.lock を読めません。リポジトリルートで実行すること。\n" );
	exit( 2 );
}

$local = array();
foreach ( array_merge( $lock['packages'], $lock['packages-dev'] ) as $p ) {
	if ( preg_match( '#^(wpackagist-plugin|hametuha|tarosky)/(.+)$#', $p['name'], $m ) ) {
		$local[ $m[2] ] = $p['version'];
	}
}

$match = array();
$drift = array();
$unmanaged = array();
$excluded = array();
$local_only = $local;

foreach ( $prod as $p ) {
	// ドロップインと must-use はプラグインではないので対象外。
	if ( in_array( $p['status'], array( 'must-use', 'dropin' ), true ) ) {
		continue;
	}
	$name = $p['name'];
	$pv   = $p['version'];
	unset( $local_only[ $name ] );

	if ( isset( EXCLUDED[ $name ] ) ) {
		$excluded[] = sprintf( '%-34s %-10s %s', $name, $pv, EXCLUDED[ $name ] );
		continue;
	}
	if ( ! isset( $local[ $name ] ) ) {
		$unmanaged[] = sprintf( '%-34s %-10s %s', $name, $pv, $p['status'] );
		continue;
	}
	$lv = $local[ $name ];
	if ( $lv === $pv ) {
		$match[] = $name;
		continue;
	}
	// dev-* は GitHub のリリースを追うため version_compare が意味を持たない。
	if ( 0 === strpos( $lv, 'dev-' ) ) {
		$judge = 'GitHubリリース追随（比較不能）';
	} elseif ( version_compare( $lv, $pv, '>' ) ) {
		$judge = 'ローカルが新しい ← 本番が遅れている理由を確認すること';
	} else {
		$judge = 'ローカルが古い';
	}
	$drift[] = sprintf( '%-34s local:%-12s prod:%-12s %s', $name, $lv, $pv, $judge );
}

$section = function ( $title, array $rows ) {
	printf( "\n## %s (%d)\n", $title, count( $rows ) );
	if ( ! $rows ) {
		echo "  なし\n";
		return;
	}
	foreach ( $rows as $r ) {
		echo '  ' . $r . "\n";
	}
};

printf( "本番のプラグイン: %d本 / composer管理: %d本\n", count( $match ) + count( $drift ) + count( $unmanaged ) + count( $excluded ), count( $local ) );

$section( '一致', $match );
$section( 'バージョン差分', $drift );
$section( '本番にあるが composer 未管理', $unmanaged );
$section( '意図的に除外（CLAUDE.md 記載）', $excluded );
$section( 'composer にあるが本番に無い', array_map(
	function ( $k, $v ) {
		return sprintf( '%-34s %s', $k, $v );
	},
	array_keys( $local_only ),
	$local_only
) );

exit( $unmanaged || $drift ? 1 : 0 );
