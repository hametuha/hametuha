<?php
/**
 * Text analyzer using MeCab.
 *
 * @package hametuha
 * @see https://github.com/youaoi/php-mecab
 * @since 8.1.0
 */

use Youaoi\MeCab\MeCab;

/**
 * Tokenize text
 *
 * @param string $text String to split.
 * @return string[]|WP_Error
 */
function hametuha_text_split( $text ) {
	hametuha_mecab_setup();
	try {
		$tokens = hametuha_text_tokenize( $text );
		$phrases = [];
		$slot = [];
		$cur_index = 0;
		foreach ( $tokens as $index => $token ) {
			$slot[] = $token->text;
			if ( isset( $tokens[ $index + 1 ] ) && ! hametuha_is_splittable( $token, $tokens[ $index + 1 ] ) ) {
				continue 1;
			}
			// This is last.
			$phrases[ $cur_index ] = implode( '', $slot );
			$cur_index++;
			$slot = [];
		}
		return $phrases;
	} catch ( \Exception $e ) {
		return new WP_Error( 'mecab_error', $e->getMessage(), [
			'code' => $e->getCode(),
		] );
	}
}

/**
 * Is text can be split?
 *
 * @param \Youaoi\MeCab\MeCabWord $token      Token.
 * @param \Youaoi\MeCab\MeCabWord $next_token Token.
 * @return boolean
 */
function hametuha_is_splittable( $token, $next_token ) {
	if ( in_array( $token->speech, [ '記号' ], true ) ) {
		return ! in_array( $token->text, [ '「', '『', '［', '（' ], true );
	}
	if ( in_array( $token->speech, [ '連体詞' ], true ) ) {
		return false;
	}
	if ( in_array( $next_token->speech, [ '記号', '助詞', '助動詞' ], true ) ) {
		return false;
	}
	if ( '動詞' === $token->speech && '動詞' === $next_token->speech ) {
		return false;
	}
	return true;
}

/**
 * Get text to token.
 *
 * @param string $text String to parse.
 * @return \Youaoi\MeCab\MeCabWord[]|null|WP_Error
 */
function hametuha_text_tokenize( $text ) {
	hametuha_mecab_setup();
	try {
		return MeCab::parse( $text );
	} catch ( \Exception $e ) {
		return new WP_Error( 'mecab_error', $e->getMessage(), [
			'code' => $e->getCode(),
		] );
	}
}

/**
 * Setup MeCab config.
 *
 * @return void
 */
function hametuha_mecab_setup() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$config = [];
	if ( defined( 'MECAB_EXEC' ) ) {
		// PATHが通っていないmecabを起動させる時に設定(default: mecab)
		$config['command'] = MECAB_EXEC;
	}
	if ( defined( 'MECAB_DIC_DIR' ) ) {
		// 独自の辞書ディレクトリを利用する場合に設定(default: null)
		$config['dictionaryDir'] = MECAB_DIC_DIR;
	}
	if ( defined( 'MECAB_DICTIONARIES' ) ) {
		// 指定辞書を利用し解析。複数利用時はカンマ区切り(default: null)
		$config['dictionary'] = MECAB_DICTIONARIES;
	}
	$config = apply_filters( 'hametuha_mecab_config', $config );
	if ( ! empty( $config ) ) {
		return;
	}
	MeCab::setDefaults( $config );
}
