<?php

namespace Hametuha\Admin;


use Gianism\Api\Ga;

/**
 * Abstract class for Google Analytics
 *
 * @package Hametuha\gianism
 */
abstract class Gabstract extends Ga {

	/**
	 * Date diff
	 *
	 * @return float
	 */
	protected function getRange() {
		$from = $this->get( 'from' );
		$to   = $this->get( 'to' );
		$diff = strtotime( $to ) - strtotime( $from );

		return ceil( $diff / ( 60 * 60 * 24 ) );
	}

	/**
	 * Return metrics considering date range
	 *
	 * @return string
	 */
	protected function properMetrics() {
		$diff = $this->getRange();
		if ( $diff > 365 * 3 ) {
			// 3年以上なら月
			return 'ga:yearMonth';
		} elseif ( $diff > 30 * 3 ) {
			// 3ヶ月以上なら週
			return 'ga:yearWeek';
		} else {
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
	protected function properLabel( $date, $metrics ) {
		switch ( $metrics ) {
			case 'ga:yearMonth':
				if ( preg_match( '/^([0-9]{4})01$/', $date, $match ) ) {
					return sprintf( '%04d年01月', $match[1] );
				} else {
					return substr( $date, 4, 2 ) . '月';
				}
				break;
			case 'ga:yearWeek':
				$year      = substr( $date, 0, 4 );
				$week      = substr( $date, 4, 2 );
				$timestamp = strtotime( "{$year}-01-01" ) + ( ( $week - 1 ) * 60 * 60 * 24 * 7 );
				$date      = explode( '-', date( 'Y-m-d', $timestamp ) );
				if ( 7 >= intval( $date[2] ) ) {
					// This is month start
					if ( '01' == $date[1] ) {
						return sprintf( '%d年%02d月%02d日', $date[0], $date[1], $date[2] );
					} else {
						return sprintf( '%02d月%02d日', $date[1], $date[2] );
					}
				} else {
					return $date[2] . '日';
				}
				break;
			case 'ga:date':
				if ( preg_match( '/^([0-9]{4})0101/', $date, $match ) ) {
					return sprintf( '%04d年01月01日', $match[1] );
				} elseif ( preg_match( '/^([0-9]{4})([0-9]{2})01/', $date, $match ) ) {
					return sprintf( '%02d月01日', $match[2] );
				} else {
					return preg_replace( '/[0-9]{4}[0-9]{2}([0-9]{2})/', '$1日', $date );
				}
				break;
			default:
				return '';
				break;
		}
	}

	/**
	 * Function Name: HSLtoHex( Mixed(Hue), Mixed(Saturation), Mixed(Luminance) )
	 *
	 * @see https://github.com/mpbzh/PHP-RGB-HSL-Converter/blob/master/rgb_hsl_converter.inc.php
	 * @param array $hsl
	 *
	 * @return string
	 */
	protected function  hsl2rgb( $hsl ) {
		// Fill variables $h, $s, $l by array given.
		list($h, $s, $l) = $hsl;

		// If saturation is 0, the given color is grey and only
		// lightness is relevant.
		if ($s == 0 ) {
			$rgb = array($l, $l, $l);
		}

		// Else calculate r, g, b according to hue.
		// Check http://en.wikipedia.org/wiki/HSL_and_HSV#From_HSL for details
		else
		{
			$chroma = (1 - abs(2*$l - 1)) * $s;
			$h_     = $h * 6;
			$x         = $chroma * (1 - abs((fmod($h_,2)) - 1)); // Note: fmod because % (modulo) returns int value!!
			$m = $l - round($chroma/2, 10); // Bugfix for strange float behaviour (e.g. $l=0.17 and $s=1)

			if($h_ >= 0 && $h_ < 1) $rgb = array(($chroma + $m), ($x + $m), $m);
			else if($h_ >= 1 && $h_ < 2) $rgb = array(($x + $m), ($chroma + $m), $m);
			else if($h_ >= 2 && $h_ < 3) $rgb = array($m, ($chroma + $m), ($x + $m));
			else if($h_ >= 3 && $h_ < 4) $rgb = array($m, ($x + $m), ($chroma + $m));
			else if($h_ >= 4 && $h_ < 5) $rgb = array(($x + $m), $m, ($chroma + $m));
			else if($h_ >= 5 && $h_ < 6) $rgb = array(($chroma + $m), $m, ($x + $m));
		}

		return $rgb;
	}

	/**
	 * Get rgb
	 *
	 * @param array $rgb
	 *
	 * @return string
	 */
	protected function rgb2hex($rgb) {
		list($r,$g,$b) = $rgb;
		$r = round(255 * $r);
		$g = round(255 * $g);
		$b = round(255 * $b);
		return "#".sprintf("%02X",$r).sprintf("%02X",$g).sprintf("%02X",$b);
	}

	/**
	 * Converts HSL color to RGB hex code
	 * @param array $hsl
	 * @return array
	 */
	protected function hsl2hex($hsl) {
		$rgb = $this->hsl2rgb($hsl);
		return $this->rgb2hex($rgb);
	}

}