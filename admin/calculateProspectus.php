<?php
include("../lib/databaseConnection.php");

$periodForProspectus = $_GET['period'];

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

// Get all tweet IDs
$stmtTweetIDs = $dbh->prepare('SELECT DISTINCT tw.TweetID, tw.ISIN FROM dacappa_stockProspectus.Tweets AS tw, dacappa_stockProspectus.TweetTokens AS tok WHERE tw.TweetID = tok.TweetID AND tw.ISIN = tok.ISIN');

// Get sentiments of tokens contained in tweet
$stmtTweetTokenSentiments = $dbh->prepare('SELECT AVG(Sentiment) AS Sentiment FROM dacappa_stockProspectus.TweetTokens AS Tok, dacappa_stockProspectus.SentimentValues AS Val WHERE TweetID = :tweetID AND ISIN = :isin AND Tok.Token = Val.Token');
$stmtTweetTokenSentiments->bindParam(':tweetID', $tweetID);
$stmtTweetTokenSentiments->bindParam(':isin', $isin);

// Get changing sentiment (not)
$stmtTweetTokenChanger = $dbh->prepare('SELECT COUNT(Token) AS SumOfNot FROM TweetTokens WHERE (Token="not" OR Token="never") AND TweetID = :tweetID AND ISIN = :isin ');
$stmtTweetTokenChanger->bindParam(':tweetID', $tweetID);
$stmtTweetTokenChanger->bindParam(':isin', $isin);

/// Write sentiment of tweet
$stmtSetTweetSentiment = $dbh->prepare('UPDATE Tweets SET Sentiment = :tweetSentiment WHERE TweetID = :tweetID AND ISIN = :isin');
$stmtSetTweetSentiment->bindParam(':tweetID', $tweetID);
$stmtSetTweetSentiment->bindParam(':isin', $isin);
$stmtSetTweetSentiment->bindParam(':tweetSentiment', $tweetSentiment);


// Get all ISINs with tweets
$stmtGetISINs = $dbh->prepare('SELECT Distinct(Sha.ISIN) FROM dacappa_stockProspectus.Shares AS Sha, dacappa_stockProspectus.Tweets AS Twe WHERE Sha.ISIN = Twe.ISIN AND DATE_ADD(Timestamp, INTERVAL :period HOUR) >= NOW()');
$stmtGetISINs->bindParam(':period', $periodForProspectus);

// Get sentiments of tweets for every single ISIN
$stmtGetShareTweets = $dbh->prepare('SELECT Sentiment, Retweets FROM dacappa_stockProspectus.Tweets WHERE Tweets.ISIN = :isin AND DATE_ADD(Timestamp, INTERVAL :period HOUR) >= NOW()');
$stmtGetShareTweets->bindParam(':isin', $ISIN);
$stmtGetShareTweets->bindParam(':period', $periodForProspectus);

// Write prospectus for share
$stmtWriteProspectus = $dbh->prepare('INSERT INTO dacappa_stockProspectus.Prospectus VALUES(:isin, :prospectus, FROM_UNIXTIME(' . time() . '), :period)');
$stmtWriteProspectus->bindParam(':isin', $ISIN);
$stmtWriteProspectus->bindParam(':prospectus', $prospectus);
$stmtWriteProspectus->bindParam(':period', $periodForProspectus);


/*
 * Calculate tweet sentiments
 */
// TODO: Tweet sentiment calculation outsourcing -> extra script
if ($stmtTweetIDs->execute()){
    echo "Query ran successfully: <span>" . $stmtTweetIDs->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($stmtTweetIDs->errorInfo()) . " : <span>" . $stmtTweetIDs->queryString . "</span><br>";
}

while ($row = $stmtTweetIDs->fetch()) {
    $tweetID = $row['TweetID'];
    $isin = $row['ISIN'];

    if ($stmtTweetTokenSentiments->execute()){
        echo "Query ran successfully: <span>" . $stmtTweetTokenSentiments->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtTweetTokenSentiments->errorInfo()) . " : <span>" . $stmtTweetTokenSentiments->queryString . "</span><br>";
    }

    // Calc sentiment for tweet
    $tweetSentiment = 0;
    while ($rowToken = $stmtTweetTokenSentiments->fetch()) {
        $tweetSentiment = $rowToken['Sentiment'];
    }

    // Get "not" s
    if ($stmtTweetTokenChanger->execute()){
        echo "Query ran successfully: <span>" . $stmtTweetTokenChanger->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtTweetTokenChanger->errorInfo()) . " : <span>" . $stmtTweetTokenChanger->queryString . "</span><br>";
    }

    $countNots = 0;
    while ($rowTokenChanger = $stmtTweetTokenChanger->fetch()) {
        $countNots = $rowTokenChanger['SumOfNot'];
    }
    if(($countNots % 2) == 1) $tweetSentiment =  -1 * $tweetSentiment ;

    if ($stmtSetTweetSentiment->execute()){
        echo "Query ran successfully: <span>" . $stmtSetTweetSentiment->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtSetTweetSentiment->errorInfo()) . " : <span>" . $stmtSetTweetSentiment->queryString . "</span><br>";
    }
}

/*
 * Calculate share prospectus out of tweet sentiments
 */
if ($stmtGetISINs->execute()){
    echo "Query ran successfully: <span>" . $stmtGetISINs->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($stmtGetISINs->errorInfo()) . " : <span>" . $stmtGetISINs->queryString . "</span><br>";
}

$prospectusEntries = array();

while ($row = $stmtGetISINs->fetch()) {
    $ISIN = $row['ISIN'];
    echo $ISIN . "<br>";
    if ($stmtGetShareTweets->execute()){
        echo "Query ran successfully: <span>" . $stmtGetShareTweets->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtGetShareTweets->errorInfo()) . " : <span>" . $stmtGetShareTweets->queryString . "</span><br>";
    }

    // calculate number of retweets
    $prospectus = 0;
    $divider = 0;
    while ($rowTweet = $stmtGetShareTweets->fetch()) {
        $prospectus += ($rowTweet['Sentiment'] * ( 1 + $rowTweet['Retweets']));
        $divider += 1 + $rowTweet['Retweets'];
    }

    $prospectus = round(($prospectus/$divider),3);

    array_push($prospectusEntries, array('ISIN' => $ISIN, 'Prospectus' => $prospectus));
}

//TODO: Filter Prospectus with to less tweets
//TODO: Prospectus calculation in relation to overall sentiment of share tweets -> sometime general positive sentiment

// Write prospectus to database & perform some optimization

foreach ($prospectusEntries AS $entry) {

    $ISIN = $entry['ISIN'];
    $prospectus = $entry['Prospectus'];
    $prospectus = $prospectus - averageProspectus($prospectusEntries); //TODO: Value can become >1 or <-1

    if ($stmtWriteProspectus->execute()){
        echo "Query ran successfully: <span>" . $stmtWriteProspectus->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtWriteProspectus->errorInfo()) . " : <span>" . $stmtWriteProspectus->queryString . "</span><br>";
    }
}

function average($array) {
    return array_sum($array) / count($array);
}

function averageProspectus($array){
    $sum = 0;
    foreach ($array as $entry) {
        $sum += $entry['Prospectus'];
    }
    return round($sum / count($array),3);
}