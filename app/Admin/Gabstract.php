<?php

namespace Hametuha\Admin;


use Gianism\Api\Ga;

/**
 * Abstract class for Google Analytics
 *
 * @package Hametuha\gianism
 */
abstract class Gabstract extends Ga
{

	/**
	 * Date diff
	 *
	 * @return float
	 */
	protected function getRange(){
		$from = $this->get('from');
		$to = $this->get('to');
		$diff = strtotime($to) - strtotime($from);
		return ceil($diff / (60 * 60 * 24));
	}

	/**
	 * Return metrics considering date range
	 *
	 * @return string
	 */
	protected function properMetrics(){
		$diff = $this->getRange();
		if( $diff > 365 * 3 ){
			// 3年以上なら月
			return 'ga:yearMonth';
		}elseif( $diff > 30 * 3 ){
			// 3ヶ月以上なら週
			return 'ga:yearWeek';
		}else{
			return 'ga:date';
		}
	}

	/**
	 * Return Label
	 *
	 * @param string $date
	 * @param string $metrics
	 *
	 * @return string
	 */
	protected function properLabel($date, $metrics){
		switch( $metrics ){
			case 'ga:yearMonth':
				if( preg_match('/^([0-9]{4})01$/', $date, $match) ){
					return sprintf('%04d年01月', $match[1]);
				}else{
					return substr($date, 4, 2).'月';
				}
				break;
			case 'ga:yearWeek':
				$year = substr($date, 0, 4);
				$week = substr($date, 4, 2);
				$timestamp = strtotime("{$year}-01-01") + (($week - 1) * 60 * 60 * 24 * 7)  ;
				$date = explode('-', date('Y-m-d', $timestamp));
				if( 7 >= intval($date[2]) ){
					// This is month start
					if( '01' == $date[1] ){
						return sprintf('%d年%02d月%02d日', $date[0], $date[1], $date[2]);
					}else{
						return sprintf('%02d月%02d日', $date[1], $date[2]);
					}
				}else{
					return $date[2].'日';
				}
				break;
			case 'ga:date':
				if( preg_match('/^([0-9]{4})0101/', $date, $match) ){
					return sprintf('%04d年01月01日', $match[1]);
				}elseif( preg_match('/^([0-9]{4})([0-9]{2})01/', $date, $match) ){
					return sprintf('%02d月01日', $match[2]);
				}else{
					return preg_replace('/[0-9]{4}[0-9]{2}([0-9]{2})/', '$1日', $date);
				}
				break;
		}
	}

}