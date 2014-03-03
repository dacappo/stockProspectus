<?php

include("../lib/twitteroauth/twitteroauth/OAuth.php");
include("../lib/twitteroauth/twitteroauth/twitteroauth.php");
include("../lib/databaseConnection.php");

/*
 * Establish connection to twitter api
 */
function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) {
    $connection = new TwitterOAuth('MYaByPa0tiAkeNcZkN0AQ', 'eEgLGrEprAp85EEeZHDvVpIfsZXkVRLSU7UA4div10', $oauth_token, $oauth_token_secret);
    return $connection;
}

$connection = getConnectionWithAccessToken("490776506-tyVem0FNqnJ3BRl6IHjdtgZYMTVMdZofKDnttZ2l", "1iulNNeINEKv1QGsGqTgKLPIRfo0nPb4tD68ziWsZjnbH");

/*
 * Establish database connection
 */
$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$stmtQueries = $dbh->prepare('SELECT Shares.ISIN, TweetSearch.Query FROM Shares, TweetSearch WHERE Shares.ISIN = TweetSearch.ISIN');

$stmtLatestTweet = $dbh->prepare('SELECT TweetID FROM Tweets WHERE ISIN = :isin ORDER BY TweetID DESC LIMIT 1;');
$stmtLatestTweet->bindParam(':isin', $isin);

$stmtTweet = $dbh->prepare('INSERT INTO Tweets VALUES(:tweetID, :isin, FROM_UNIXTIME(:createdAt), :retweets,:tweet, NULL)');
$stmtTweet->bindParam(':tweetID', $tweetID);
$stmtTweet->bindParam(':isin', $isin);
$stmtTweet->bindParam(':createdAt', $createdAt);
$stmtTweet->bindParam(':retweets', $retweets);
$stmtTweet->bindParam(':tweet', $tweet);

$stmtToken = $dbh->prepare('INSERT INTO TweetTokens VALUES (:tweetID, :isin, :token)');
$stmtToken->bindParam(':token', $token);
$stmtToken->bindParam(':isin', $isin);
$stmtToken->bindParam(':tweetID', $tweetID);


/*
 * Request for every single share
 */


if ($results = $stmtQueries->execute()){
    echo "Query ran successfully: <span>" . $stmtQueries->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($stmtQueries->errorInfo()) . " : <span>" . $stmtQueries->queryString . "</span><br>";
}

while ($row = $stmtQueries->fetch()) {
    $query = '@' . $row['Query'] . '-filter:retweets';
    $isin = $row['ISIN'];

    // Get latest tweet ID -> less load for json
    if ($stmtLatestTweet->execute()){
        echo "Query ran successfully: <span>" . $stmtLatestTweet->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtLatestTweet->errorInfo()) . " : <span>" . $stmtLatestTweet->queryString . "</span><br>";
    }

    $latestTweetArray = $stmtLatestTweet->fetch();
    $latestTweetId = $latestTweetArray['TweetID'];

    // Twitter API call
    $start = microtime(true);
    $content = $connection->get('search/tweets', array('q' => $query,'lang' => 'en', 'result_type' => 'recent', 'count' => '100' , 'since_id' => $latestTweetId));
    echo "Fetch time: " . (microtime(true)-$start) . " for @" . $row['Query'] . " <br>";
    echo "Server time: " . $content->{'search_metadata'}->{'completed_in'} . "<br><br>";

    /*
     *  Tweet loop
     */

    foreach ($content->{'statuses'} AS $status) {
        $tweetID = $status->{'id_str'}; // Take id_str, because id s are bigger than common int in php -> db (BIGINT)
        $createdAt = strtotime($status->{'created_at'});
        $retweets = $status->{'retweet_count'};
        $tweet = $status->{'text'};

        echo ("--- Tweet ". $tweetID ." ---<br>");

        if ($stmtTweet->execute()){
            echo "Query ran successfully: <span>" . $stmtTweet->queryString . "</span><br>";

            $tweet = escapeTweet($tweet);
            $tweet = filterWhiteSpaces($tweet);

            $tokens = explode(" ", $tweet);

            // Token lopp
            foreach ($tokens AS $token) {
                if (strlen($token) > 2) {
                    if ($stmtToken->execute()){
                        echo "Query ran successfully: <span>" . $stmtToken->queryString . " : " . $tweetID . " - " . $token . "</span><br>";
                    } else {
                        echo "Error running query: " . array_pop($stmtToken->errorInfo()) . " : <span>" . $stmtToken->queryString . "</span><br>";
                    }
                }
            }

        } else {
            echo "Error running query: " . array_pop($stmtTweet->errorInfo()) . " : <span>" . $stmtTweet->queryString . "</span><br>";
        }

        echo ("--- End tweet ---<br><br>");
    }
}

$dbh = null;

/*
 * Some helping functions for token extraction
 */

function escapeTweet($tweet){

    // Filter special Twitter characters
    $tweet = strtolower($tweet);
    $tweet = str_replace('@',' ',$tweet);
    $tweet = str_replace('#',' ',$tweet);

    // Replace smileys with equivalent tokens
    $tweet = str_replace(':-)',' happysmiley ',$tweet);
    $tweet = str_replace(':)',' happysmiley ',$tweet);
    $tweet = str_replace(';-)',' happysmiley ',$tweet);
    $tweet = str_replace(';)',' happysmiley ',$tweet);
    $tweet = str_replace(':-(',' sadsmiley ',$tweet);
    $tweet = str_replace(':(',' sadsmiley ',$tweet);

    // End of line encodings

    $tweet = trim($tweet); // fucking line feed is not deleted!

    // Filter Urls and special characters
    $tweet = preg_replace('#http:[^ \t]*#',' ',$tweet);
    $tweet = preg_replace('#n\'t#',' not',$tweet);
    $tweet = str_replace('?',' ',$tweet);
    $tweet = str_replace('!',' ',$tweet);
    $tweet = str_replace('\'',' ',$tweet);
    $tweet = str_replace('/',' ',$tweet);
    $tweet = str_replace('"',' ',$tweet);
    $tweet = str_replace('.',' ',$tweet);
    $tweet = str_replace(',',' ',$tweet);
    $tweet = str_replace('<',' ',$tweet);
    $tweet = str_replace('>',' ',$tweet);
    $tweet = str_replace('&',' ',$tweet);
    $tweet = str_replace(';',' ',$tweet);
    $tweet = str_replace('(',' ',$tweet);
    $tweet = str_replace(')',' ',$tweet);
    $tweet = str_replace('-',' ',$tweet);
    $tweet = str_replace('_',' ',$tweet);
    $tweet = str_replace(':',' ',$tweet);
    $tweet = str_replace('^',' ',$tweet);
    return $tweet;
}

function filterWhiteSpaces($tweet) {
    $tweet = preg_replace('#[ ]+#',' ',$tweet);
    $tweet = preg_replace('#^[ ]+#','',$tweet);
    return preg_replace('#[ ]+$#','',$tweet);
}


