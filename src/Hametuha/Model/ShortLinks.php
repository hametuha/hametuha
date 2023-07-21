<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * TermUserRelationships
 *
 * @package Hametuha\Model
 * @property-read string $terms
 * @property-read string $term_taxonomy
 * @property-read string $users
 * @property-read string $posts
 */
class ShortLinks extends Model {


	/**
	 * リレーションテーブル名
	 *
	 * @var string
	 */
	protected $name = 'short_links';

	protected $default_placeholder = [
		'link_id' => '%d',
		'url'     => '%s',
		'host'    => '%s',
		'path'    => '%s',
		'args'    => '%s',
		'created' => '%s',
	];

	/**
	 * Detect if record exists
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @param int $term_taxonomy_id
	 * @return bool
	 */
	protected  function record_exists( $user_id, $post_id, $term_taxonomy_id ) {
		return (bool) $this->select( 'COUNT(*)' )
			->wheres([
				"{$this->table}.user_id = %d"          => $user_id,
				"{$this->table}.object_id = %d"        => $post_id,
				"{$this->table}.term_taxonomy_id = %d" => $term_taxonomy_id,
			])->get_var();
	}

	/**
	 * Return URL
	 *
	 * @param string $url
	 *
	 * @return bool|string
	 */
	public function get_shorten( $url ) {
		$shorten = (int) $this->select( 'link_id' )->where( 'url = %s', $url )->get_var();
		if ( ! $shorten ) {
			if ( ! ( $shorten = $this->build( $url ) ) ) {
				return $url;
			}
		}
		return home_url( '/l/' . $this->encode( $shorten ) . '/', 'https' );
	}

	/**
	 * Make original
	 *
	 * @param string $shorten
	 *
	 * @return null|string
	 */
	public function get_original( $shorten ) {
		$id = $this->decode( $shorten );
		return $this->select( 'url' )->where( 'link_id = %d', $id )->get_var();
	}

	/**
	 * Get Base62ed number
	 *
	 * @param int $num
	 * @param int $b base number
	 *
	 * @return string
	 */
	protected function encode( $num, $b = 62 ) {
		$base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$r    = $num % $b;
		$res  = $base[ $r ];
		$q    = floor( $num / $b );
		while ( $q ) {
			$r   = $q % $b;
			$q   = floor( $q / $b );
			$res = $base[ $r ] . $res;
		}
		return $res;
	}

	/**
	 * Return id from string
	 *
	 * @param string $string
	 *
	 * @return int
	 */
	protected function decode( $string, $b = 62 ) {
		$base  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$limit = strlen( $string );
		$res   = strpos( $base, $string[0] );
		for ( $i = 1; $i < $limit; $i ++ ) {
			$res = $b * $res + strpos( $base, $string[ $i ] );
		}
		return $res;
	}

	/**
	 * Default join
	 *
	 * @return int|false
	 */
	protected function build( $url ) {
		$components = parse_url( $url );
		if ( ! $components ) {
			return false;
		}
		$this->insert([
			'url'     => $url,
			'host'    => $components['host'],
			'path'    => $components['path'],
			'args'    => $components['query'],
			'created' => current_time( 'mysql' ),
		]);
		return (int) $this->db->insert_id;
	}

}
