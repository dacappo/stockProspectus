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

$stmtTweet = $dbh->prepare('INSERT INTO Tweets VALUES(:tweetID, :isin,:retweets, 0)');
$stmtTweet->bindParam(':tweetID', $tweetID);
$stmtTweet->bindParam(':isin', $isin);
$stmtTweet->bindParam(':retweets', $retweets);

$stmtToken = $dbh->prepare('INSERT INTO TweetTokens VALUES (:tweetID, :token)');
$stmtToken->bindParam(':token', $token);
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
    $query = '@' .$row['Query'] . '-filter:retweet';
    $isin = $row['ISIN'];

    // Twitter API call
    $start = microtime(true);
    $content = $connection->get('search/tweets', array('q' => $query,'lang' => 'en', 'result_type' => 'recent', 'count' => '100'));
    echo "Fetch time: " . (microtime(true)-$start) . "<br>";
    echo "Server time: " . $content->{'search_metadata'}->{'completed_in'} . "<br><br>";

    /*
     *  Tweet loop
     */
    foreach ($content->{'statuses'} AS $status) {
        $tweetID = $status->{'id'};
        $retweets = $status->{'retweet_count'};

        if ($stmtTweet->execute()){
            echo "Query ran successfully: <span>" . $stmtTweet->queryString . "</span><br>";
        } else {
            echo "Error running query: " . array_pop($stmtTweet->errorInfo()) . " : <span>" . $stmtTweet->queryString . "</span><br>";
        }

        $tweet = escapeTweet($status->{'text'});
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


