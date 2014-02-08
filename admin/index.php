
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Stock prospectus based on social media">
    <meta name="author" content="Patrick Spiegel">

    <title>Stock Prospectus</title>
    <link rel="shortcut icon" href="../favicon.png" type="image/png"/>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.css" rel="stylesheet">

    <style>
body {
    padding-top: 50px;
        }
        .starter-template {
    padding: 40px 15px;
            text-align: center;
        }

        .price {
    text-align: right;
        }

        .positive {
    color: #4cae4c;
}

        .negative {
    color: #c12e2a;
}

        .sum_up_row {
    background-color: #f5f5f5;
            font-weight: 800;
        }

    </style>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand" href="#"><span class="glyphicon glyphicon-stats"></span> Stock Prospectus</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="../dax">Home</a></li>
               <li class="active"><a href="config.php">Settings</a></li>
               <li><a href="#contact">Contact</a></li>
            </ul>

       </div><!--/.nav-collapse -->
   </div>
</div>

<div class="container">
    <div class="starter-template">
        <h1>Configuration of application and database</h1>
        <p class="lead">Within this page, the secured scripts for application and database resets can be called. Attention, this can cause errors!<br></p>
    </div>
        <div class="col-md-12">
            <!-- Table -->
            <table id="options-table" class="table table-hover">
                <tr><th>Action</th><th>Link</th></tr>
                <tr><td>Reset share tables</td><td><a href="initializeShareDatabase.php" ><span class="label label-danger">initializeShareDatabase.php</span></a></td></tr>
                <tr><td>Parse current share values</td><td><a href="parseShareValues.php" ><span class="label label-danger">parseShareValues.php</span></a></td></tr>
                <tr><td>Reset tweet tables</td><td><a href="initializeTweetDatabase.php" ><span class="label label-danger">initializeTweetDatabase.php</span></a></td></tr>
                <tr><td>Request current tweets</td><td><a href="requestTweets.php" ><span class="label label-danger">requestTweets.php</span></a></td></tr>
                <tr><td>Reset sentiment tables</td><td><a href="initializeSentimentDatabase.php" ><span class="label label-danger">initializeSentimentDatabase.php</span></a></td></tr>
                <tr><td>Parse sentiment ranking</td><td><a href="parseSentimentRanking.php" ><span class="label label-danger">parseSentimentRanking.php</span></a></td></tr>
                <tr><td>Calculate prospectus</td><td><a href="" ><span class="label label-danger">calcProspectus.php</span></a></td></tr>
            </table>
            </div></div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="../js/bootstrap.min.js"></script>

</body>
</html>





