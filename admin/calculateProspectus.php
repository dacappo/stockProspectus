<?php
include("../lib/databaseConnection.php");

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$stmtTweetIDs = $dbh->prepare('SELECT DISTINCT ID FROM dacappa_stockProspectus.Tweets AS tw, dacappa_stockProspectus.TweetTokens AS tok WHERE tw.id = tok.TweetID');
$stmtTweetTokenSentiments = $dbh->prepare('SELECT * FROM dacappa_stockProspectus.TweetTokens AS Tok, dacappa_stockProspectus.SentimentValues AS Val WHERE TweetID = :tweetID AND Tok.Token = Val.Token');
$stmtTweetTokenSentiments->bindParam(':tweetID', $tweetID);