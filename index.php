<?php
session_start();
$_SESSION['login_user'] = $_SERVER['REMOTE_USER'];

require_once('functions.php');
require_once('class.db.php');
require_once('class.polapi.php');

$dbaxx = new DBAXX();
$polapi = new POLAPI( $dbaxx, $_SESSION['login_user'] );

//$test=$polapi->get_completebalances();
$polbalance=$polapi->get_balances('ALL');
$polorders=$polapi->get_open_orders('ALL');
$polticker = $polapi->get_ticker($dbaxx, 'usdt_btc');


//print_r($test);
//exit;

//$pattern = "/\'([A-Za-z0-9]+)\'/";
//$patterno = "/^([A-Za-z0-9]+)/";

$div = "";

$return = show_balances(var_export($polbalance, true),$polbalance,$polorders,$polticker,$polapi);

$graphpoint = $dbaxx->db_select("SELECT `datetime`, `btc` FROM `graph_" . $_SESSION['login_user'] . "` WHERE `datetime` > '" . date('Y-m-d', strtotime('-1 month')) . "'");
$balancegraph = graphBalance($graphpoint, $return);

if (isset($_GET['action'])) {
   if ($_GET['action'] == 'short') {
	if (isset($_GET['coin'])) {
            if (isset($_GET['price'])) {
		if (isset($_GET['amount'])) {
		  $coin = $_GET['coin'];
		  $amount = $_GET['amount'];
//echo $polbalance['BTC_' . $coin];
		  //$amount = $polbalance['BTC_' . $coin];
//echo $amount;
		  $price = $_GET['price'];
		echo $price . " - " . $amount;
		  $sellresponse = $polapi->sell('BTC_' . $coin, $price, $amount);
   		  var_dump($sellresponse);
		  //$market = 1;
		}
	    }
	}
   } else if ($_GET['action'] == 'long') {
	if (isset($_GET['coin'])) {
	    $coin = $_GET['coin'];
	    if (isset($_GET['price'])) {
	    	$price = $_GET['price'];
	    } else {
	    	$orderbook = $polapi->get_order_book('BTC_' . $coin);
		foreach($orderbook['bids'] as $order) {
//var_dump($order);
			$tbtc += ($order[0] * $order[1]);
//echo $tbtc . "<br>";
			if ($tbtc > 1)
				break;
			$lastprice = $order[0];
		}
		$price = $lastprice;
			echo var_dump($order) . "<br><br>";
	    }
	    //$price = ($polapi->get_ticker('BTC_' . $coin)['highestBid'] + 0.000001);
echo $polbalance['BTC'] . "<br>";
echo $price . "<br>";

	    $amount = ($polbalance['BTC'] / $price);
	    $buyresponse = $polapi->buy('BTC_' . $coin, $price, $amount);
	    var_dump($buyresponse);
	    //$market = 1;
        }
   } else if ($_GET['action'] == 'cancel') {
	if (isset($_GET['order'])) {
	    $order = $_GET['order'];
	    $coin = $_GET['coin'];
	    $cancelresponse = $polapi->cancel_order($coin, $order);
	    var_dump($cancelresponse);
	    $market = 1;
	}
   }
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
  <link rel="stylesheet" type="text/css" href="stylesheet.css">

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/lodash/4.16.3/lodash.min.js"></script>
    <script>
        $(document).ready(function(){
                $("#subcoin").click(function(){
			$('#line_top_z').html('<img src="https://preloaders.net/preloaders/287/Filling%20broken%20ring.gif">');
			//$('#dashboard_div').html('<img src="https://preloaders.net/preloaders/287/Filling%20broken%20ring.gif">');
			var vcoin = $("#selectcoin").val();
			$.post("ajax.php", //Required URL of the page on server
			{ // Data Sending With Request To Server
				coin:vcoin
			},
			function(response,status){ // Required Callback Function
      $('#dashboard_div').fadeOut('slow', function(){
	  $("#line_top_z").css("display", "none");
          $('#dashboard_div').fadeIn('slow').html(drawIndicatorChart(response));
        });
			});
		});
	});

<?php if($market){ echo 'window.setTimeout(function(){ window.location = "https://gekko.jonbeck.info/balance/"; },3000); $("#line_top_z").css("display", "block");'; } ?>

    </script>

<script type="text/javascript">

google.charts.load('visualization', '1', {packages: ['controls', 'charteditor']});
//google.setOnLoadCallback(drawIndicatorChart);
<!-- google.charts.load('current', {'packages':['corechart']}); -->

<!-- google.charts.setOnLoadCallback(drawIndicatorChart('[{"candle":1,"price":0}]')); -->

function drawIndicatorChart(response) {
    //console.log("*----Received Data----*\n\nResponse : " + response);
    var data = new google.visualization.DataTable();

    data.addColumn('number', 'Candle');
    data.addColumn('number', 'Price');
    data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
    data.addColumn({type: 'string', role:'annotation'});
    data.addColumn({type: 'string', role:'annotation'});
    //data.addColumn('number', 'SMA');
    data.addColumn('number', 'EMA');
    data.addColumn('number', 'BandUp');
    data.addColumn('number', 'BandMid');
    data.addColumn('number', 'BandLow');

    var candles = JSON.parse(response);
    var candlecount = candles.length;
    var candlehist = candlecount - (candlecount / 12);
    $.each(candles, function(i,candles)
    {

    var CANDLE=parseInt(candles.CANDLE);
    var PRICE=parseFloat(candles.PRICE);
    var EMA=parseFloat(candles.EMA);
    var BANDUP=parseFloat(candles.BANDUP);
    var BANDMID=parseFloat(candles.BANDMID);
    var BANDLOW=parseFloat(candles.BANDLOW);

    var i = 0;
    var tooltip = '<div style="text-align: left; padding: 5px;">'; // + propertyName + ': ' + candles['PRICE'];
    for(var propertyName in candles) {
      if(propertyName != '' && candles[propertyName] != '' && propertyName != 'CANDLE' && propertyName != 'BUY' && propertyName != 'SELL') {
        if(i > 1)
          tooltip += "<p>";
        tooltip += propertyName + ': ' + candles[propertyName];
	if(i > 1)
	  tooltip += "</p>";
      }
      i++;
    }
    tooltip += "</div>";
      //console.log('[' + candle + ', ' + price + ', ' + tooltip + ', ' + buy + ', ' + sell + ']');

    data.addRows([ [candles.CANDLE, PRICE, tooltip, candles.BUY, candles.SELL, EMA, BANDUP, BANDMID, BANDLOW] ]);
    //data.addRows([ [candle, price, 'Price: ' + price + '\nRSI: ' + response.rsi, buy, sell] ]);
    });

    var dash = new google.visualization.Dashboard(document.getElementById('dashboard'));
  
    var chart = new google.visualization.ChartWrapper({
        chartType: 'ComboChart',
        containerId: 'chart_div',
        options: {
	  //chartArea: {'width': '100%', 'height': '100%'},
          //chartArea:{left:0,top:200,width:"50%",height:"50%"},
	  height: 250,
          tooltip: {trigger: 'selection', isHtml: true},
          crosshair: { trigger: 'both' },
          legend: { 
            position: 'bottom', 
            alignment: 'center', 
            textStyle: {
              fontSize: 12
            }
          },
          lineWidth: 1,
          pointSize: 1,
          annotations: {
              color: 'red',
              stemColor : 'none'
          },
          explorer: {
              actions: ['dragToZoom', 'rightClickToReset'],
              axis: 'both',
              keepInBounds: true
          },
          hAxis: {
              title: 'Candle',
              gridlines: {
                color: 'transparent'
              },
          },
          vAxis: {
              title: 'Price',
    	      gridlines: {
                color: 'transparent'
              },
          },
	  //trendlines: { 0: { color: '78909c', tooltip : true, enableInteractivity: 'true', pointSize: 0, lineWidth: 1, visibleInLegend: false } },
	  trendlines: { 0: { color: '78909c', tooltip : true, enableInteractivity: 'true', pointSize: 0, lineWidth: 1, type: 'exponential', visibleInLegend: false } },
          series: {
            legend: 'none',
            0: { color: '43a047', tooltip : true, enableInteractivity: 'true', pointSize: 1 }, //green price
            1: { color: 'peru', enableInteractivity: 'false', pointSize: 0 }, //orange ema
            2: { color: '01579b', tooltip : true, enableInteractivity: 'true', pointSize: 1 }, //blue bandmid
            3: { color: '01579b', tooltip : true, enableInteractivity: 'true', enableInteractivity: 'false', pointSize: 0 },
            4: { color: '01579b', tooltip : true, enableInteractivity: 'true', pointSize: 1 },
            5: { color: 'purple' },
          },
          //animation:{
          //    duration: 500,
          //    easing: 'out',
          //    startup: false
          //}
      }
    });
    
    var control = new google.visualization.ControlWrapper({
        controlType: 'ChartRangeFilter',
        containerId: 'control_div',
        options: {
            filterColumnIndex: 0,
            ui: {
                chartOptions: {
                    height: 50,
                    chartArea: {
                        width: '75%'
                    },
                    annotations: {
                      stemColor : 'none'
                    },
                    lineWidth: 1,
                    series: {
                      legend: 'none',
                      0: { color: 'green' },
                      1: { color: 'peru' },
                      2: { color: 'blue' },
                    },
                }
            }
        },
        state: {
          range: {
            start: candlehist
          }
        }        
    });
    
    dash.bind([control], [chart]);
    
    dash.draw(data);
    
    // example of a new date set up
    setTimeout(function () {
      control.setState({range: {
      		start: candlehist,
          	end: candlecount
      }});
      control.draw();
    }, 500);
}
</script>


	<?php echo $balancegraph; ?>

</head>
<body>
<div id="mySidenav" class="sidenav">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <a class="balance" onclick="closeNav()">Balance</a>
  <a class="charts" onclick="closeNav()">Charts</a>
</div>

<label class="hamburger-icon" onclick="openNav()">
    <span>&nbsp;</span>
    <span>&nbsp;</span>
    <span>&nbsp;</span>
</label>

<div class="balance" id="balance">

<main>

  <div>

<?php

print '$' . number_format($return['btcvalue'], 2, '.', ',') . '<hr style="width: 120px;"/>';
print "<p>" . $return['totalbtc'] . "</p>";

if(isset($_GET["action"]) && $_GET["action"] == 'dbentry' && isset($_SESSION['login_user']) && $return['totalbtc'] > 0 && $return['vested']) {
	$dbaxx->db_insert("INSERT INTO `coinx`.`graph_" . $_SESSION['login_user'] . "` (`datetime`, `btc`) VALUES (CURRENT_TIMESTAMP, '" . $return['totalbtc'] . "');");
}
//echo $return['selectcoins'];
//if($return['selectcoins']) {
//	print "<p><select id='selectcoins'>" . $return['selectcoins'] . "</select></p>";
//}

if($return['portfolio']) {
	print $return['portfolio'];
} else {
	print "Currently not invested!";
}
?>
  </div>
</main>
<div class="graph" id="line_top_x"></div>
<!-- <div class="graphic" id="png"><img src='https://preloaders.net/preloaders/287/Filling%20broken%20ring.gif'></div> -->

<div class="totalusd" id="tot">$<?php print $return['totalusd']; ?></div>


</div> <!-- end of balance page -->


<div class="charts" id="charts">
	<?php include('charts.php'); ?>
</div> <!-- end charts page -->

<script>
        var y = document.getElementById('tot');

        window.onload = function () { y.style.display = 'block';}
</script>

<script>
function openNav() {
    document.getElementById("mySidenav").style.width = "250px";
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
}
</script>

<script>

$(document).ready(function(){
    $('a.balance').click(function(){
        $("#charts").css("display", "none");
        $("#balance").css("display", "block");
    });
    $('a.charts').click(function(){
	$("#balance").css("display", "none");
        $("#charts").css("display", "block");
    });
});
</script>
</body>
</html>
