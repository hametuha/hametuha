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
		add_action( 'wp_ajax_hametuha_user_reward', [ $this, 'ajax_user_reward' ] );
	}

	/**
	 * Enqueue scripts
	 */
	protected function enqueueScript() {

	}

	/**
	 * Save user data
	 */
	public function ajax_user_reward() {
		try {
			if ( ! $this->input->verify_nonce( 'add_user_reward' ) || ! current_user_can( 'administrator' ) ) {
				throw new \Exception( 'あなたには権限がありません。', 401 );
			}
			$user_id = $this->input->post( 'user_id' );
			if ( ! ( $user = get_userdata( $user_id ) ) ) {
				throw new \Exception( '該当するユーザーは存在しません。', 404 );
			}
			if ( ! ( $created = $this->input->post( 'created' ) ) ) {
				$created = current_time( 'mysql' );
			}
			$price = $this->input->post( 'price' );
			if ( ! $price || ! is_numeric( $price ) ) {
				throw new \Exception( sprintf( '金額が不正です: %s', $price ), 500 );
			}
			$vat_excluded = (bool) $this->input->post( 'vat' );
			$unit = max( 1, $this->input->post( 'unit' ) );
			if ( ! ( $desc = $this->input->post( 'description' ) ) ) {
				throw new \Exception( '適用が入力されていません。', 500 );
			}
			if ( ! UserSales::get_instance()->add( $user_id, 'task', $price, $unit, $desc, ! $vat_excluded, true, 0, $created ) ) {
				throw new \Exception( 'データの保存に失敗しました。', 500 );
			}
			wp_redirect( admin_url( 'users.php?page=hametuha-user-reward' ) );
			exit;
		} catch ( \Exception $e ) {
			add_filter( 'wp_die_ajax_handler', function(){
				return '_default_wp_die_handler';
			} );
			wp_die( $e->getMessage(), get_status_header_desc( $e->getCode() ), [
				'response'  => $e->getCode(),
			    'back_link' => true,
			] );
		}
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
		if ( current_user_can( 'administrator' ) ) :
			?>
			<hr />
			<form id="user-reward-add-form" action="<?= admin_url( 'admin-ajax.php' ) ?>" method="post">
				<input type="hidden" name="action" value="hametuha_user_reward" />
				<?php wp_nonce_field( 'add_user_reward' ) ?>
				<h2>ユーザーの報酬を追加</h2>
				<table class="form-table">
					<tr>
						<th><label for="user_id">ユーザーID</label></th>
						<td>
							<?php hametuha_user_selector( 'user_id', 0, 'user_id', 'any', [ 'user-reward-select' ] ) ?>
						</td>
					</tr>
					<tr>
						<th><label for="description">適用</label></th>
						<td>
							<input class="regular-text" type="text" name="description" id="description" value="" />
						</td>
					</tr>
					<tr>
						<th><label for="price">金額</label></th>
						<td>
							<input class="regular-text" type="number" name="price" id="price" value="" />
						</td>
					</tr>
					<tr>
						<th><label for="unit">数量</label></th>
						<td>
							<input class="regular-text" type="number" name="unit" id="unit" value="1" />
						</td>
					</tr>
					<tr>
						<th>消費税</th>
						<td>
							<label>
								<input type="radio" name="vat" value="0" checked />
								内税
							</label>
							<br />
							<label>
								<input type="radio" name="vat" value="1" />
								外税
							</label>
						</td>
					</tr>
					<tr>
						<th><label for="created">日付</label></th>
						<td>
							<input class="regular-text" type="text" name="created" id="created" value="" />
						</td>
					</tr>
				</table>
				<?php submit_button( '追加' ) ?>
			</form>
		<?php
		endif;
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
