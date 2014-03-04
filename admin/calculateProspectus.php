<?php
include("../lib/databaseConnection.php");

$periodForProspectus = $_GET['period'];

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

// Get all tweet IDs
$stmtTweetIDs = $dbh->prepare('SELECT DISTINCT tw.TweetID, tw.ISIN FROM dacappa_stockProspectus.Tweets AS tw, dacappa_stockProspectus.TweetTokens AS tok WHERE tw.TweetID = tok.TweetID AND tw.ISIN = tok.ISIN AND tw.Sentiment IS NULL');

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


// Calculate evaluation before setting new prospectus
$stmtGetEvaluationProspectus = $dbh->prepare('SELECT pro.ISIN, StockIndex, Sentiment, pro.Timestamp FROM dacappa_stockprospectus.prospectus AS pro, shares WHERE pro.Period = :period AND DATE_ADD(DATE_ADD(Timestamp, INTERVAL :period HOUR), INTERVAL 10 Minute) >= NOW() AND shares.ISIN = pro.isin');
$stmtGetEvaluationProspectus->bindParam(':period', $periodForProspectus);

$stmtGetEvaluationResults = $dbh->prepare('SELECT Current.ISIN, Current.Timestamp, Current.Value, Current.SpreadH - Average.SpreadH AS RelativeH,Current.SpreadD - Average.SpreadD AS RelativeD,Current.SpreadW - Average.SpreadW AS RelativeW FROM (SELECT * FROM ShareValues WHERE ShareValues.ISIN = :isin ORDER BY ShareValues.Timestamp DESC LIMIT 1) AS Current, (SELECT AVG(SpreadH) AS SpreadH, AVG(SpreadD) AS SpreadD, AVG(SpreadW) AS SpreadW FROM(SELECT ShareValues.SpreadH AS SpreadH, ShareValues.SpreadD AS SpreadD, ShareValues.SpreadW AS SpreadW FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = :index ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS Average) AS Average');
$stmtGetEvaluationResults->bindParam(':index', $index);
$stmtGetEvaluationResults->bindParam(':isin', $isin);

$stmtWriteEvaluation = $dbh->prepare('INSERT INTO Evaluation VALUES(:isin, :prospectus, :timestmp, :period, :change, :success)');
$stmtWriteEvaluation->bindParam(':isin', $isin);
$stmtWriteEvaluation->bindParam(':prospectus', $prospectus);
$stmtWriteEvaluation->bindParam(':timestmp', $timestamp);
$stmtWriteEvaluation->bindParam(':period', $periodForProspectus);
$stmtWriteEvaluation->bindParam(':change', $change);
$stmtWriteEvaluation->bindParam(':success', $success);


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
 * Statements for prospectus optimization
 */

// Get quality ratio
$stmtGetEvaluationRatio = $dbh->prepare('SELECT Positive.Count AS Positive, Negative.Count AS Negative FROM (SELECT Count(*) AS Count FROM dacappa_stockProspectus.Evaluation WHERE ISIN = :isin AND Period = :period AND RelativeChange != 0 AND Success = 1) AS Positive,(SELECT Count(*) AS Count FROM dacappa_stockProspectus.Evaluation WHERE ISIN = :isin AND Period = :period AND RelativeChange != 0 AND Success = 0) AS Negative');
$stmtGetEvaluationRatio->bindParam(':isin', $ISIN);
$stmtGetEvaluationRatio->bindParam(':period', $periodForProspectus);

// Get direction for sentiment shift

$stmtGetEvaluationShift = $dbh->prepare('Select ToPositive.Count AS ToPositive, ToNegative.Count as ToNegative FROM (SELECT Count(*) AS Count FROM dacappa_stockProspectus.Evaluation WHERE ISIN = :isin AND Period = :period AND Success = 0 AND Sentiment < 0 AND RelativeChange > 0) AS ToNegative, (SELECT Count(*) AS Count FROM dacappa_stockProspectus.Evaluation WHERE ISIN = :isin AND Period = :period AND Success = 0 AND Sentiment > 0 AND RelativeChange < 0) AS ToPositive');
$stmtGetEvaluationShift->bindParam(':isin', $ISIN);
$stmtGetEvaluationShift->bindParam(':period', $periodForProspectus);


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
 * Calculate evaluation
 */

if ($stmtGetEvaluationProspectus->execute()){
    echo "Query ran successfully: <span>" . $stmtGetEvaluationProspectus->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($stmtGetEvaluationProspectus->errorInfo()) . " : <span>" . $stmtGetEvaluationProspectus->queryString . "</span><br>";
}

while ($row = $stmtGetEvaluationProspectus->fetch()) {
    $isin = $row['ISIN'];
    $index = $row['StockIndex'];
    $prospectus = $row['Sentiment'];
    $timestamp = $row['Timestamp'];

    if ($stmtGetEvaluationResults->execute()){
        echo "Query ran successfully: <span>" . $stmtGetEvaluationResults->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtGetEvaluationResults->errorInfo()) . " : <span>" . $stmtGetEvaluationResults->queryString . "</span><br>";
    }

    $back = $stmtGetEvaluationResults->fetch();

    if ($periodForProspectus == 1) {
        $change = $back['RelativeH'];
    } else if ($periodForProspectus == 24) {
        $change = $back['RelativeD'];
    } else if ($periodForProspectus == 168) {
        $change = $back['RelativeW'];
    } else {
        $change = 0;
    }

    if ($change > 0 && $prospectus > 0 || $change < 0 && $prospectus < 0) {
        $success = true;
    } else {
        $success = false;
    }

    if ($stmtWriteEvaluation->execute()){
        echo "Query ran successfully: <span>" . $stmtWriteEvaluation->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtWriteEvaluation->errorInfo()) . " : <span>" . $stmtWriteEvaluation->queryString . "</span><br>";
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

    /*
    * Perform single optimization
    */
    if ($stmtGetEvaluationRatio->execute()){
        echo "Query ran successfully: <span>" . $stmtGetEvaluationRatio->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtGetEvaluationRatio->errorInfo()) . " : <span>" . $stmtGetEvaluationRatio->queryString . "</span><br>";
    }

    if ($stmtGetEvaluationShift->execute()){
        echo "Query ran successfully: <span>" . $stmtGetEvaluationShift->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtGetEvaluationShift->errorInfo()) . " : <span>" . $stmtGetEvaluationShift->queryString . "</span><br>";
    }

    $RatioResult = $stmtGetEvaluationRatio->fetch();
    $ShiftResult = $stmtGetEvaluationShift->fetch();

    $factorForShift = 1;

    if($RatioResult['Positive'] != 0 && $RatioResult['Negative'] != 0) {
        // check to do not fuck up a good prospectus
        if (($RatioResult['Negative']/($RatioResult['Negative']+$RatioResult['Positive'])) >= 0.3) {
            $factorForShift = $factorForShift * ($RatioResult['Negative']/($RatioResult['Negative']+$RatioResult['Positive']));
            if ($ShiftResult['ToPositive'] != 0 && $ShiftResult['ToNegative'] != 0) {
                if (($ShiftResult['ToPositive']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive'])) > ($ShiftResult['ToNegative']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive'])))  {
                    // prospectus to positive
                    $factorForShift = $factorForShift * ($ShiftResult['ToPositive']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive'])) * -1;
                } else {
                    // prospectus to negative
                    $factorForShift = $factorForShift * ($ShiftResult['ToNegative']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive']));
                }

            } else if ($ShiftResult['ToPositive'] == 0) {
                // prospectus to negative
                $factorForShift = $factorForShift * 1;
            } else if ($ShiftResult['ToNegative'] == 0) {
                // prospectus to positive
                $factorForShift = $factorForShift * -1;
            }
            $prospectus = $prospectus + (abs($prospectus) * $factorForShift);
        }
    } else if ($RatioResult['Positive'] == 0 && $RatioResult['Negative'] != 0 && $RatioResult['Negative'] > 2) { // 2 For at least 3 wrong prospectusses before correction
        // pros totally wrong yet
        $factorForShift = $factorForShift * 1;
        if ($ShiftResult['ToPositive'] != 0 && $ShiftResult['ToNegative'] != 0) {
            if (($ShiftResult['ToPositive']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive'])) > ($ShiftResult['ToNegative']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive'])))  {
                // prospectus to positive
                $factorForShift = $factorForShift * ($ShiftResult['ToPositive']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive'])) * -1;
            } else {
                // prospectus to negative
                $factorForShift = $factorForShift * ($ShiftResult['ToNegative']/($ShiftResult['ToNegative']+$ShiftResult['ToPositive']));
            }
            $prospectus = $prospectus + (abs($prospectus) * $factorForShift);
        } else if ($ShiftResult['ToPositive'] == 0) {
            // prospectus to negative
            $factorForShift = $factorForShift * 1;
        } else if ($ShiftResult['ToNegative'] == 0) {
            // prospectus to positive
            $factorForShift = $factorForShift * -1;
        }
        $prospectus = $prospectus + (abs($prospectus) * $factorForShift);
    } else if ($RatioResult['Negative'] == 0) {
        // Perfect :D
    }


    array_push($prospectusEntries, array('ISIN' => $ISIN, 'Prospectus' => round($prospectus,3)));
}

//TODO: Filter Prospectus with to less tweets



// Write prospectus to database & perform general optimization

foreach ($prospectusEntries AS $entry) {

    $ISIN = $entry['ISIN'];
    $prospectus = $entry['Prospectus'];
    $prospectus = $prospectus - averageProspectus($prospectusEntries, substr($ISIN, 0,2)); //TODO: Value can become >1 or <-1

    if ($stmtWriteProspectus->execute()){
        echo "Query ran successfully: <span>" . $stmtWriteProspectus->queryString . ' - ' . $periodForProspectus . ' - ' . $prospectus . ' - ' . averageProspectus($prospectusEntries, substr($ISIN, 0,2)) . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmtWriteProspectus->errorInfo()) . " : <span>" . $stmtWriteProspectus->queryString . "</span><br>";
    }
}

function average($array) {
    return array_sum($array) / count($array);
}

function averageProspectus($array, $index){
    $sum = 0;
    $count = 0;
    foreach ($array as $entry) {
        if (substr($entry['ISIN'],0,2) == $index) {
            $sum += $entry['Prospectus'];
            $count++;
        }
    }
    return round($sum / $count,3);

}