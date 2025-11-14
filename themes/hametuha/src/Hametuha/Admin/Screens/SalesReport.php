<?php

namespace Hametuha\Admin\Screens;


use Hametuha\Admin\Table\SalesReportTable;
use Hametuha\Model\Sales;
use WPametu\UI\Admin\Screen;
use WPametu\Utility\StringHelper;


/**
 * Sales report screen
 *
 * @package Hametuha\Admin\Screens
 * @property-read Sales $sales
 */
class SalesReport extends Screen {

	protected $menu_title = '売上一覧';

	protected $title = '売り上げ';

	protected $slug = 'hamepub-sales';

	protected $parent = 'edit.php?post_type=series';

	protected $caps = 'edit_others_posts';

	protected $icon = 'dashicons-money';

	/**
	 * Executed on admin_init
	 */
	public function adminInit() {
		$endpoint = admin_url( $this->parent . '&page=' . $this->slug );
		if ( $this->slug === $this->input->get( 'page' ) ) {
			$this->prg->start_session();
		}
		if ( $this->input->verify_nonce( 'import_kdp_data' ) ) {
			try {
				// If file uploaded?
				$file = $this->input->file_info( 'csv' );
				if ( ! $file ) {
					throw new \Exception( $this->input->file_error_message( 'csv' ) );
				}
				// Check mime
				$csv    = $file['tmp_name'];
				$handle = finfo_open( FILEINFO_MIME_TYPE );
				$mime   = finfo_file( $handle, $csv );
				finfo_close( $handle );
				if ( ! in_array( $mime, [ 'text/plain', 'text/csv' ], true ) ) {
					throw new \Exception( $this->input->file_error_message( 'csv' ) );
				}
				// Validate rows
				$csv_obj = new \SplFileObject( $csv, 'r' );
				$csv_obj->setFlags( \SplFileObject::READ_CSV );
				$counter = 0;
				$values  = [];
				$errors  = 0;
				while ( $line = $csv_obj->fgetcsv() ) {
					if ( ! $counter ) {
						++$counter;
						continue;
					}
					switch ( ( $type = $this->input->post( 'type' ) ) ) {
						case 'kdp':
							if ( count( $line ) !== 16 ) {
								++$errors;
							} else {
								$line  = array_map( 'trim', $line );
								$value = [
									'store'    => 'Amazon',
									'date'     => date_i18n( 'Y-m-d', strtotime( $line[0] ) ),
									'asin'     => $line[3],
									'place'    => $line[4],
									'type'     => $line[6],
									'unit'     => $line[7],
									'royalty'  => $line[14],
									'currency' => $line[15],
								];
								if ( is_wp_error( $this->sales->validate( $value ) ) ) {
									++$errors;
								} else {
									$values[] = $value;
								}
							}
							break;
						case 'kenp':
							// TODO: 直す
							$date = $this->input->post( 'date' );
							if ( ! StringHelper::get_instance()->is_date( $date ) ) {
								throw new \Exception( "{$date} は不正な日付です。" );
							}
							if ( count( $line ) !== 7 ) {
								++$errors;
							} else {
								$value = array_map( 'trim', [
									'store'    => 'KENP',
									'date'     => $date,
									'asin'     => $line[2],
									'place'    => $line[3],
									'type'     => '読み放題',
									'unit'     => $line[4],
									'royalty'  => $line[5],
									'currency' => $line[6],
								] );
								if ( is_wp_error( $this->sales->validate( $value ) ) ) {
									++$errors;
								} else {
									$values[] = $value;
								}
							}
							break;
						default:
							throw new \Exception( "{$type}は不正なオペレーションです。" );
							break;
					}
					++$counter;
				}
				if ( $errors ) {
					throw new \Exception( sprintf( '合計%d行にエラーが見つかりました。', $errors ) );
				}
				$added = 0;
				foreach ( $values as $value ) {
					if ( $this->sales->add_record( $value ) ) {
						++$added;
					}
				}
				$this->prg->addMessage( sprintf( '%d件のデータを挿入しました', $added ) );
			} catch ( \Exception $e ) {
				$this->prg->addErrorMessage( $e->getMessage() );
			} finally {
				wp_redirect( $endpoint );
				exit;
			}
		}
	}

	/**
	 * Load templates
	 */
	protected function content() {
		printf( '<form action="%s" method="get">', admin_url( 'edit.php' ) );
		echo <<<HTML
			<input type="hidden" name="post_type" value="series" />
			<input type="hidden" name="page" value="{$this->slug}" />
HTML;

		$table = new SalesReportTable();
		$table->prepare_items();
		$table->views();
		$table->search_box( '検索', 's' );
		ob_start();
		$table->display();
		$content = preg_replace( '/<input[^>]+_wp_http_referer[^>]+>/u', '', ob_get_contents() );
		ob_end_clean();
		echo $content;
		echo '</form>';
		$this->upload_form();
	}

	/**
	 * File Import form
	 */
	protected function upload_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<h3>
			<span class="dashicons dashicons-upload"></span>
			KDP売り上げアップロード
		</h3>
		<form method="post" enctype="multipart/form-data"
				action="<?php echo esc_url( admin_url( 'edit.php?post_type=series&page=' . $this->slug ) ); ?>">
			<?php wp_nonce_field( 'import_kdp_data' ); ?>
			<input type="hidden" name="type" value="kdp" />
			<input type="file" name="csv" value="選択してください"/>
			<?php submit_button( '送信' ); ?>
		</form>
		<h3>
			<span class="dashicons dashicons-upload"></span>
			KENPアップロード
		</h3>
		<form method="post" enctype="multipart/form-data"
				action="<?php echo esc_url( admin_url( 'edit.php?post_type=series&page=' . $this->slug ) ); ?>">
			<?php wp_nonce_field( 'import_kdp_data' ); ?>
			<input type="hidden" name="type" value="kenp" />
			<p>
				<label>
					年月
					<input type="text" name="date" value="" class="datepicker" placeholder="2017-01-15" />
				</label>
			</p>
			<p>
				<label>
					ファイル
					<input type="file" name="csv" value="選択してください"/>
				</label>
			</p>
			<?php submit_button( '送信' ); ?>
		</form>
		<?php
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
			case 'sales':
				return Sales::get_instance();
				break;
			default:
				return parent::__get( $name ); // TODO: Change the autogenerated stub
				break;
		}
	}
}
