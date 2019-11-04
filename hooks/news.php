<?php
/**
 * News related hooks.
 *
 * @package hametuha
 */

// Stop AMP customizer because it causes slow query.
add_filter( 'amp_customizer_is_enabled', '__return_false' );
