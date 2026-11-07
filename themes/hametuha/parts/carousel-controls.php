<?php
/**
 * カルーセル（Swiper）の前後ボタンとページ送りのドット
 *
 * .carousel の中、.swiper の後ろに置く。動かしているのは assets-src/js/components/carousel.js。
 *
 * @feature-group carousel
 */

// 本文の途中で読み込んでも、フッターで出力される
wp_enqueue_script( 'hametuha-carousel' );
?>
<button type="button" class="carousel__prev" aria-label="<?php esc_attr_e( '前へ', 'hametuha' ); ?>"></button>
<button type="button" class="carousel__next" aria-label="<?php esc_attr_e( '次へ', 'hametuha' ); ?>"></button>
<div class="carousel__dots"></div>
