<?php
require_once('TwitterAPIExchange.php');

$oauthAccessToken = '';
$oauthAccessTokenSecret = '';
$consumerKey = '';
$consumerSecret = '';
$hasTag = '';
$slackUrl = '';

$settings = array(
	'oauth_access_token' => $oauthAccessToken,
	'oauth_access_token_secret' => $oauthAccessTokenSecret,
	'consumer_key' => $consumerKey,
	'consumer_secret' => $consumerSecret
);
$url = 'https://api.twitter.com/1.1/search/tweets.json';

$requestMethod = 'GET';
$getField = '?q='.$hasTag.'&result_type=recent';

$twitter = new TwitterAPIExchange($settings);
try {
	$string = json_decode($twitter->setGetfield($getField)
		->buildOauth($url, $requestMethod)
		->performRequest(), false);
	$file = fopen("ids.txt", 'rb');
	$ids = explode(",", fread($file,filesize("ids.txt")));
	foreach ($string->statuses as $items) {
		if(!in_array($items->id, $ids, true)){
			$ch = curl_init($slackUrl);
			$payload = json_encode( array( "text"=> $items->text) );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			$result = curl_exec($ch);
			curl_close($ch);
			file_put_contents('ids.txt', ','.$items->id , FILE_APPEND | LOCK_EX);
		}
	}
} catch (Exception $e) {
	throw new \RuntimeException($e->getMessage());

}

