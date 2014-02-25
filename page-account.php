<?php
/**
 * Template Name: アカウント
 */

$action = isset($_GET['action']) ? $_GET['action'] : '';
switch($action){
	case "profile":
		get_template_part('page');
		break;
	default:
		get_template_part('page-login');
		break;
}