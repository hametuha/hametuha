<?php
/**
 * Rewrite rules
 *
 * Must be an array of rewrite rules.
 * <code>$rewrites</code> is an array of normal rewrite rules.
 * <code>$api_rewrites</code> is an array of framework specified array.
 * Latter is an associative array. Keys are same as normal rewrite,
 * but value should be class name.
 * See detail below:
 *
 * <code>
 * // $rewrites will be prepend to rewrite rules.
 * $rewrites = [
 *     'hoge/([^\/]*)/?' => 'index.php?api_class=hoge&fuga=$matches[1]'
 * ];
 * // $api_rewrites will be key and class name.
 * // The class should be subclass of WPametu\API\Rest\RestBase
 * // and key's regular expression match will be passed.
 * // For example, hoge/fuga/do with GET method will be invoke
 * // SomeClass->fuga(do).
 * // You must capture arguments with RegExp's parentheses.
 * $api_rewrites = [
 *     'hoge/([^\/]*)/?' => SomeClass::class,
 * ];
 * </code>
 */

defined('ABSPATH') or die('Do not load directly');

$rewrites =  [

];

$api_rewrites = [
    'user-tags/(.*)/?' => \Hametuha\QueryHighJack\UserTag::class,
];

