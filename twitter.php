<?php

include("lib/twitteroauth/twitteroauth/OAuth.php");
include("lib/twitteroauth/twitteroauth/twitteroauth.php");


//header('Content-type: application/json');

function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) {
    $connection = new TwitterOAuth('MYaByPa0tiAkeNcZkN0AQ', 'eEgLGrEprAp85EEeZHDvVpIfsZXkVRLSU7UA4div10', $oauth_token, $oauth_token_secret);
    return $connection;
}

$connection = getConnectionWithAccessToken("490776506-tyVem0FNqnJ3BRl6IHjdtgZYMTVMdZofKDnttZ2l", "1iulNNeINEKv1QGsGqTgKLPIRfo0nPb4tD68ziWsZjnbH");

$start = microtime(true);
$content = $connection->get('search/tweets', array('q' => '@sap-filter:retweets','lang' => 'en', 'result_type' => 'recent', 'count' => '100'));

echo microtime(true)-$start . "<br><br>";

foreach ($content->{'statuses'} AS $status) {
    echo preg_replace('#http:[^ \t]*#',' ',str_replace('#',' ',str_replace('@',' ',strtolower($status->{'text'})))) . '<br>';
}
/*
$connection = getConnectionWithAccessToken("490776506-tyVem0FNqnJ3BRl6IHjdtgZYMTVMdZofKDnttZ2l", "1iulNNeINEKv1QGsGqTgKLPIRfo0nPb4tD68ziWsZjnbH");
$content = $connection->get('search/tweets', array('q' => '@bmw-filter:retweets','lang' => 'en', 'result_type' => 'popular', 'count' => '100'));

foreach ($content->{'statuses'} AS $status) {
    echo $status->{'text'} . '<br>';
}

$connection = getConnectionWithAccessToken("490776506-tyVem0FNqnJ3BRl6IHjdtgZYMTVMdZofKDnttZ2l", "1iulNNeINEKv1QGsGqTgKLPIRfo0nPb4tD68ziWsZjnbH");
$content = $connection->get('search/tweets', array('q' => '@ibm-filter:retweets','lang' => 'en', 'result_type' => 'recent', 'count' => '100'));

foreach ($content->{'statuses'} AS $status) {
    echo $status->{'text'} . '<br>';
}

*/