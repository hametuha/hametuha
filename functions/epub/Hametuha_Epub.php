<?php
/**
 * WordPressの投稿からePubを作成するクラス
 * @since 3.2.21
 */

class Hametuha_Epub{
	
	/**
	 * ePubのテンプレートが入っているディレクトリ
	 * @var string
	 */
	protected $template_dir = '';
	
	/**
	 * コンストラクタ 
	 */
	public function __construct() {
		$this->template_dir = dirname(__FILE__).DIRECTORY_SEPARATOR."template";
	}
	
	/**
	 * 一時ディレクトリを作成し、パスを返す
	 * @return boolean 
	 */
	protected function create_temp_dir(){
		$dir = DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR."hametuha-epub".DIRECTORY_SEPARATOR.uniqid();
		if(!file_exists($dir)){
			if(!@mkdir($dir, '0777', true)){
				$dir = false;
			}
		}
		return $dir;
	}
	
	/**
	 * 一時ファイルを作成し、パスを返す 
	 */
	protected function create_temp_file(){
		
	}
	
	/**
	 * ePubファイルにウォーターマークを挿入する
	 * @param string $mail
	 * @param string $user_name 
	 */
	protected function put_watermark($mail, $user_name = ''){
		
	}
}