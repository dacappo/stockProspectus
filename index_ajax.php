<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Stock prospectus based on social media">
    <meta name="author" content="Patrick Spiegel">

    <title>Stock Prospectus</title>
    <link rel="shortcut icon" href="favicon.png" type="image/png"/>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

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

            <a class="navbar-brand" href="#">Share Prospectus</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>

        </div><!--/.nav-collapse -->
    </div>
</div>

<div class="container">
    <div class="starter-template">
        <h1>Twitter Based Stock Analysis</h1>
        <p class="lead">Making use of current Twitter feeds, a general sentiment for each share can be measured.<br></p>
    </div>

    <div class="row">
        <div class="my_navigation col-md-3">

            <h4>Indexes</h4>
            <ul class="nav nav-pills nav-stacked">
                <li class="active" ><a onclick="updateIndex('dax')">DAX</a></li>
                <li><a onclick="updateIndex('dj')" >Dow Jones</a></li>
            </ul>

            <h4>Lists</h4>
            <ul class="nav nav-pills nav-stacked">
                <li><a href="#up">Tending Upwards</a></li>
                <li><a href="#down" >Tending Downwards</a></li>
            </ul>

        </div>
        <div class="col-md-9">
            <div id="main_panel" class="panel panel-default">


            </div>
        </div>
    </div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<script>

    function initializeIndex() {

        if (window.location.hash == "" | window.location.hash == "#")
            window.location.hash = '#'+ 'dax';

        getIndex();

    }


    function updateIndex(index) {

        window.location.hash = '#'+ index;

        $('#main_panel').toggle("fade","slow",function() {getIndex()});

        $('#main_panel').toggle("fade","slow");
    }

    function getIndex() {

        var panel = document.getElementById('main_panel');

        var request = $.ajax({
            type: "POST",
            url: "ajax/getShareValues.php?index=" + location.hash.slice(1),
            dataType: "json"
        });

        panel.innerHTML = '<!-- Default panel contents --><div id="time_of_update" class="panel-heading"></div><!-- Table --><table id="share-table" class="table"><tr><th>Name</th><th>Value</th><th>Hourly Spread</th><th class="hidden-phone">Daily Spread</th><th class=" hidden-phone">Weekly Spread</th></tr></table>';

        request.done(function( json ) {
            json.shares.forEach(function(share) {
                var record = document.createElement('tr');
                var record_name = document.createElement('td');
                var record_isin = document.createElement('td');
                var record_value = document.createElement('td');
                var record_spreadH = document.createElement('td');
                var record_spreadD = document.createElement('td');
                var record_spreadW = document.createElement('td');
                record_value.setAttribute('class', 'price');
                record_spreadH.setAttribute('class', 'price');
                record_spreadD.setAttribute('class', 'price hidden-phone');
                record_spreadW.setAttribute('class', 'price hidden-phone');

                record_name.innerText = share.Name;
                record_isin.innerText = share.ISIN;
                record_value.innerText = share.Value + share.Currency;

                if (share.SpreadH != share.Value) {
                    record_spreadH.innerText =share.SpreadH + share.Currency;
                } else {
                    record_spreadH.innerText = "-";
                }

                if (share.SpreadD != share.Value) {
                    record_spreadD.innerText =share.SpreadD + share.Currency;
                } else {
                    record_spreadD.innerText = "-";
                }

                if (share.SpreadW != share.Value) {
                    record_spreadW.innerText = share.SpreadW + share.Currency;
                } else {
                    record_spreadW.innerText = "-";
                }

                record.appendChild(record_name);
                //record.appendChild(record_isin);
                record.appendChild(record_value);
                record.appendChild(record_spreadH);
                record.appendChild(record_spreadD);
                record.appendChild(record_spreadW);

                document.getElementById('share-table').children[0].appendChild(record);
            });

            document.getElementById('time_of_update').innerHTML = 'Timestamp: ' + json.Timestamp;
        });

    }

    initializeIndex();

    // Setting navigation event handlers
    $('.my_navigation li').click(function(e) {
        $('.my_navigation li.active').removeClass('active');
        var $this = $(this);
        if (!$this.hasClass('active')) {
            $this.addClass('active');
        }
        e.preventDefault();
    });

</script>


</body>
</html>





