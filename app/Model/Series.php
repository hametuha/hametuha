<?php

namespace Hametuha\Model;
use WPametu\DB\Model;


/**
 * Series model
 *
 * @package Hametuha\Model
 */
class Series extends Model
{
	/**
	 * Status Label
	 *
	 * @var array
	 */
	public $status_label = [
		'未販売',
		'販売申請中',
		'販売中',
	];

	/**
	 * Return array of WP_Users
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_authors($post_id){
		$users = [];
		foreach( $this->select("{$this->db->users}.*")
					->from($this->db->users)
					->join($this->db->posts, "{$this->db->posts}.post_author = {$this->db->users}.ID")
					->where("{$this->db->posts}.post_parent = %d", $post_id)
					->where("{$this->db->posts}.post_type = %s", 'post')
					->group_by("{$this->db->users}.ID")->result() as $user
		){
			$users[] = new \WP_User($user);
		}
		return $users;
	}

	/**
	 * Get selling status
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function get_status($post_id){
		return (int) get_post_meta($post_id, '_kdp_status', true);
	}


	/**
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_asin($post_id){
		return (string)get_post_meta($post_id, '_asin', true);
	}

	/**
	 * Get direction
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_direction($post_id){
		return 'vertical' == (string)get_post_meta($post_id, 'orientation', true) ? 'rtl' : 'ltr';
	}

	/**
	 * Get visibility of title
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function get_title_visibility($post_id){
		return (int)get_post_meta( $post_id, '_show_title', true );
	}

	/**
	 * Get subtitle
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_subtitle($post_id){
		return (string) get_post_meta( $post_id, 'subtitle', true );
	}

	/**
	 * Get preface
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_preface($post_id){
		return (string) get_post_meta( $post_id, '_preface', true );
	}

	/**
	 * Is finished
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_finished($post_id){
		return (bool) get_post_meta($post_id, '_series_finished', true);
	}

	/**
	 * Get visibility of series
	 *
	 * @param int $series_id
	 *
	 * @return int
	 */
	public function get_visibiity($series_id) {
		return (int) get_post_meta( $series_id, '_visibility', true );
	}

	/**
	 * Should hide?
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return bool
	 */
	public function should_hide( $post = null ){
		$post = get_post($post);
		$index = $this->get_visibiity($post->post_parent);
		if( !$index ){
			return false;
		}
		$where = <<<SQL
			post_parent = %d
			AND (
				( menu_order > %d )
				OR
				( post_date < %s  )
			)
SQL;

		$count = (int) $this->select('COUNT(ID)')
			->from($this->db->posts)
			->where($where, [$post->post_parent, $post->menu_order, $post->post_date])->get_var();
		return $count >= $index;
	}

	/**
	 * Get external URL
	 *
	 * @param int $series_id
	 *
	 * @return string
	 */
	public function get_external( $series_id ){
		return (string) get_post_meta($series_id, '_external_url', true);
	}

	/**
	 * Get total count
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return int
	 */
	public function get_total($post = null){
		$post = get_post($post);
		return (int) $this->select('COUNT(ID)')->from($this->db->posts)
			->where('post_type = %s', 'post')
			->where("post_status = %s",'publish')
			->where("post_parent = %d", $post->ID)
			->get_var();
	}

	/**
	 * Get current index
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return int
	 */
	public function get_index($post = null){
		$post = get_post($post);
		$query = <<<SQL
			post_type = 'post' AND post_status = 'publish'
			AND post_parent = %d
			AND (
				menu_order > %d
				OR
				post_date < %s
			)
SQL;
		return 1 + (int)$this->select('COUNT(ID)')->from($this->db->posts)
		                   ->where($query, [$post->post_parent, $post->menu_order, $post->post_date])->get_var();
	}

	/**
	 * Return index label
	 *
	 * @param null|\WP_Post|int $post
	 *
	 * @return string
	 */
	public function index_label($post = null){
		$post = get_post($post);
		$index = $this->get_index($post);
		$total = $this->get_total($post->post_parent);
		if( $total == $index ){
			return $this->is_finished($post->post_parent) ? '最終話' : '最新話';
		}else{
			return sprintf('第%s話', $index);
		}
	}

	/**
	 * Get Sibling post
	 *
	 * @param int $offset
	 * @param null|\WP_Post|int $post
	 *
	 * @return mixed|null
	 */
	public function get_sibling($offset = 0, $post = null){
		$post = get_post($post);
		return $this->select('*')->from($this->db->posts)
			->where("post_type = 'post' AND post_status = 'publish' AND post_parent = %d", $post->post_parent)
			->order_by('menu_order', 'DESC')
			->order_by('post_date')
			->limit(1, $offset)
			->get_row();
	}

	/**
	 * Get next link
	 *
	 * @param string $before
	 * @param string $after
	 * @param null|\WP_Post|int $post
	 *
	 * @return string
	 */
	public function next($before = '<li>', $after = '</li>', $post = null){
		return $this->prev($before, $after, $post, true);

	}

	/**
	 * Get previous link
	 *
	 * @param string $before
	 * @param string $after
	 * @param null|\WP_Post|int $post
	 * @param bool $next
	 *
	 * @return string
	 */
	public function prev($before = '<li>', $after = '</li>', $post = null, $next = false){
		$index = $this->get_index($post);
		if( $next ){
			$link = '<a href="%s">第%s話 &raquo;</a>';
		}else{
			$link = '<a href="%s">&laquo; 第%s話</a>';
		}
		$operand = $next ? 1 : -1;
		if( !$index || ($index < 2 && !$next) || !($prev = $this->get_sibling($index - 1 + $operand, $post)) ){
			return '';
		}
		return sprintf('%s'.$link.'%s', $before, get_permalink($prev->ID), $index + $operand, $after);
	}
}
