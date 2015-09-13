<?php

namespace Hametuha\Admin\Screens;


use Hametuha\Admin\Table\SalesReportTable;
use Hametuha\Model\Sales;
use WPametu\UI\Admin\Screen;


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
		if ( $this->slug === $this->input->get( 'page' ) ) {
			$this->prg->start_session();
			$endpoint = admin_url( $this->parent . '&page=' . $this->slug );
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
				if ( 'text/plain' !== $mime ) {
					throw new \Exception( $this->input->file_error_message( 'csv' ) );
				}
				// Validate rows
				$handle  = fopen( $csv, 'r' );
				$counter = 0;
				$values  = [ ];
				$errors  = 0;
				while ( $line = fgetcsv( $handle ) ) {
					if ( $counter > 0 ) {
						if ( count( $line ) !== 16 ) {
							$errors ++;
						} else {
							$value = array_map( 'trim', [
								'store'    => 'Amazon',
								'date'     => date_i18n( 'Y-m-d', strtotime( $line[0] ) ),
								'asin'     => $line[3],
								'place'    => $line[4],
								'type'     => $line[6],
								'unit'     => $line[9],
								'royalty'  => $line[14],
								'currency' => $line[15],
							] );
							if ( is_wp_error( $this->sales->validate( $value ) ) ) {
								$errors ++;
							} else {
								$values[] = $value;
							}
						}
					}
					$counter ++;
				}
				if ( $errors ) {
					throw new \Exception( sprintf( '合計%d行にエラーが見つかりました。', $errors ) );
				}
				$added = 0;
				foreach ( $values as $value ) {
					if ( $this->sales->add_record( $value ) ) {
						$added ++;
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
	 * Enqueue scripts
	 */
	protected function enqueueScript() {
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
			アップロード
		</h3>
		<form method="post" enctype="multipart/form-data"
			  action="<?= esc_url( admin_url( 'edit.php?post_type=series&page=' . $this->slug ) ) ?>">
			<?php wp_nonce_field( 'import_kdp_data' ) ?>
			<input type="file" name="csv" value="選択してください"/>
			<?php submit_button( '送信' ) ?>
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
