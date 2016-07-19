<?php

namespace Hametuha\Admin\Screens;


use Hametuha\Admin\Table\UserRewardTable;
use Hametuha\Model\Sales;
use Hametuha\Model\UserSales;
use WPametu\UI\Admin\Screen;


/**
 * Sales report screen
 *
 * @package Hametuha\Admin\Screens
 * @property-read UserSales $user_sales
 */
class UserReward extends Screen {

	protected $menu_title = '報酬';

	protected $title = 'ユーザー報酬';

	protected $slug = 'hametuha-user-reward';

	protected $parent = 'users.php';

	protected $caps = 'edit_others_posts';

	/**
	 * Executed on admin_init
	 */
	public function adminInit() {
	}

	/**
	 * Enqueue scripts
	 */
	protected function enqueueScript() {

	}

	/**
	 * Load templates
	 */
	protected function content() {
		printf( '<form action="%s" method="get">', admin_url( 'users.php' ) );
		echo <<<HTML
			<input type="hidden" name="page" value="{$this->slug}" />
HTML;

		$table = new UserRewardTable();
		$table->prepare_items();
		$table->views();
		$table->search_box( '検索', 's' );
		ob_start();
		$table->display();
		$content = preg_replace( '/<input[^>]+_wp_http_referer[^>]+>/u', '', ob_get_contents() );
		ob_end_clean();
		echo $content;
		echo '</form>';
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'user_sales':
				return UserSales::get_instance();
				break;
			default:
				return parent::__get( $name ); // TODO: Change the autogenerated stub
				break;
		}
	}


}
