<?php
// @see http://qiita.com/srea/items/58ba0f7d870a6ee3da2a
// @see https://developers.line.me/bot-api/api-reference#receiving_messages
nocache_headers();
$json_string  = file_get_contents( 'php://input' );
$jsonObj      = json_decode( $json_string );
$to           = $jsonObj->result[0]->content->from;
$message      = $jsonObj->result[0]->content->text;
$content_type = $jsonObj->result[0]->content->contentType;
switch ( $content_type ) {
	case 1:
	case 10:
		$ch = curl_init( 'https://chatbot-api.userlocal.jp/api/chat' );
		curl_setopt_array( $ch, [
			CURLOPT_POST => true,
		    CURLOPT_POSTFIELDS => [
			    'key' => USER_LOCAL_KEY,
			    'message' => $message,
			    'bot_name' => 'めつかれ！',
			    'platform' => 'line',
			    'user_id'  => $to,
		    ],
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_SSL_VERIFYPEER => false,
		] );
		$res = json_decode( curl_exec( $ch ) );
		if ( $res && 'success' == $res->status ) {
			$response = $res->result;
		} else {
			$response = sprintf( 'すいません、エラーです……（%s）', curl_errno( $ch ) );
		}
		curl_close( $ch );

		$response_format_text = [
			'contentType' => 1,
			"toType" => 1,
			"text" => $response,
		];
		break;
	default:
		$response_format_text = [
			'contentType' => 1,
			"toType" => 1,
			"text" => "すいません、まだそれは理解できません……",
		];
		break;
}

// toChannelとeventTypeは固定値なので、変更不要。
$post_data = [
	"to"        => [ $to ],
	"toChannel" => "1383378250",
	"eventType" => "138311608800106203",
	"content"   => $response_format_text,
];

$ch = curl_init( "https://trialbot-api.line.me/v1/events" );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post_data ) );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
	'Content-Type: application/json; charser=UTF-8',
	'X-Line-ChannelID: ' . LINE_CHANNEL_ID,
	'X-Line-ChannelSecret: ' . LINE_CHANNEL_SECRET,
	'X-Line-Trusted-User-With-ACL: ' . LINE_CHANNEL_MID
) );
$result = curl_exec( $ch );
curl_close( $ch );
exit;
