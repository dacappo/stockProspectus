<?php
include("../lib/databaseConnection.php");

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

// Get all tweet IDs
$stmtTweetIDs = $dbh->prepare('SELECT DISTINCT ID FROM dacappa_stockProspectus.Tweets AS tw, dacappa_stockProspectus.TweetTokens AS tok WHERE tw.id = tok.TweetID');

// Get sentiments of tokens contained in tweet
$stmtTweetTokenSentiments = $dbh->prepare('SELECT Sentiment FROM dacappa_stockProspectus.TweetTokens AS Tok, dacappa_stockProspectus.SentimentValues AS Val WHERE TweetID = :tweetID AND Tok.Token = Val.Token');
$stmtTweetTokenSentiments->bindParam(':tweetID', $tweetID);

/// Write sentiment of tweet
$stmtSetTweetSentiment = $dbh->prepare('UPDATE Tweets SET Sentiment = :tweetSentiment WHERE ID = :tweetID');
$stmtSetTweetSentiment->bindParam(':tweetID', $tweetID);
$stmtSetTweetSentiment->bindParam(':tweetSentiment', $tweetSentiment);

// Get all ISINs with tweets
$stmtGetISINs = $dbh->prepare('SELECT Distinct(Sha.ISIN) FROM dacappa_stockProspectus.Shares AS Sha, dacappa_stockProspectus.Tweets AS Twe WHERE Sha.ISIN = Twe.ISIN');

// Get sentiments of tweets for every single ISIN
$stmtGetShareTweets = $dbh->prepare('SELECT Sentiment FROM dacappa_stockProspectus.Tweets WHERE Tweets.ISIN = :isin');
$stmtGetShareTweets->bindParam(':isin', $ISIN);

// Write prospectus for share
$stmtWriteProspectus = $dbh->prepare('INSERT INTO dacappa_stockProspectus.Prospectus VALUES(:isin, :prospectus)');
$stmtWriteProspectus->bindParam(':isin', $ISIN);
$stmtWriteProspectus->bindParam(':prospectus', $prospectus);


/*
 * Calculate tweet sentiments
 */
if ($stmtTweetIDs->execute()){
    echo "Query ran successfully: <span>" . $stmtTweetIDs->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($stmtTweetIDs->errorInfo()) . " : <span>" . $stmtTweetIDs->queryString . "</span><br>";
}

while ($row = $stmtTweetIDs->fetch()) {
    $tweetID = $row['ID'];

    if ($stmtTweetTokenSentiments->execute()){
        echo "Query ran successfully: <span>" . $stmtTweetTokenSentiments->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtTweetTokenSentiments->errorInfo()) . " : <span>" . $stmtTweetTokenSentiments->queryString . "</span><br>";
    }

    // Calc sentiment for tweet
    $tweetSentiment = 0;
    while ($rowToken = $stmtTweetTokenSentiments->fetch()) {
        $tweetSentiment += $rowToken['Sentiment'];
    }

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

while ($row = $stmtGetISINs->fetch()) {
    $ISIN = $row['ISIN'];
    echo $ISIN . "<br>";
    if ($stmtGetShareTweets->execute()){
        echo "Query ran successfully: <span>" . $stmtGetShareTweets->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtGetShareTweets->errorInfo()) . " : <span>" . $stmtGetShareTweets->queryString . "</span><br>";
    }

    $prospectus = 0;
    while ($rowTweet = $stmtGetShareTweets->fetch()) {
        $prospectus += $rowTweet['Sentiment'];
    }

    if ($stmtWriteProspectus->execute()){
        echo "Query ran successfully: <span>" . $stmtWriteProspectus->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtWriteProspectus->errorInfo()) . " : <span>" . $stmtWriteProspectus->queryString . "</span><br>";
    }

}