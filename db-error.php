<?php
$message = <<<EOS
<strong>現在データベースに接続できなくなっています。</strong>
負荷が高い状態のため、復旧作業を行なっています。

しばらく時間を置いてからまたご利用ください。
ご迷惑をおかけいたしますが、ご理解のほどよろしくお願いいたします。

<strong>破滅派編集部　拝</strong>
EOS;
$message = '<p>'.nl2br($message, true).'</p>';
header( 'HTTP/1.1 503 Service Unavailable' );
header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
header( 'Pragma: no-cache' );
header( 'Content-Type: text/html; charset=utf-8' );
require_once __DIR__ . '/themes/hametuha/503.php';
