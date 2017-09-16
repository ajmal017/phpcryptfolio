<?php

function traded_coins() {
	$i=0;
	$path = '/home/adm1n/gekko';
	$results = scandir($path);

	foreach ($results as $result) {
    		if ($result === '.' or $result === '..') continue;


    		if (is_file($path . '/' . $result)) {
			preg_match("/([a-z]+)\-trade-config.js/i", $result, $match);
			if($match[1] != ''){
			  $array[$i] = $match[1];
			  $i++;
			}
    		}
	}
	return $array;
}

function show_balances($result,$polbalance,$polorders,$polticker,$polapi) {
	$totalbtc = 0;
	$orderbalance = 0;

	if($polbalance)
	foreach($polbalance as $coin => $amount){

		$selectcoins .= "<option value='" . $coin . "'>" . $coin . "</option>";

		if ($amount > 0 && $coin !== 'BTC' && $coin !== 'USDT'){
			$vested = 1;
			$totalbtc += ($polticker['BTC_' . $coin]['last'] * $amount);
                        $portfolio .= "<p>Hold <a href='https://m.poloniex.com/#/exchange/btc_" . strtolower($coin) . "' target='_new'>BTC_" . $coin . "</a>[" . number_format($polticker['BTC_' . $coin]['last'], 8) . "] (" . number_format($amount, 2) . " coins) ";
			$portfolio .= "<a href='" . $_SERVER['PHP_SELF']. "?action=short&coin=" . $coin . "&price=" . ($polticker['BTC_' . $coin]['last'] / 1.2) . "&amount=" . $amount . "' onclick=\"return confirm('Are you sure you want to short " . $coin . "?')\">Short!</a></p>";
		}
	}
	if($polorders)
	foreach($polorders as $coin => $key){
	  foreach($key as $order) {
		if (sizeof($order) > 0){
			if ($order['type'] == 'buy') {
				$totalbtc += $order['total'];
                        	$portfolio .= "<p>" . ucfirst($order['type']) . " <a href='https://m.poloniex.com/#/exchange/" . strtolower($coin) . "' target='_new'>" . $coin . "</a>[" . number_format($polticker[$coin]['last'], 8) . "] @ " . number_format($order['rate'], 8) . " (" . number_format($order['amount'], 2) . " coins)";
				$portfolio .= "<a href='" . $_SERVER['PHP_SELF']. "?coin=" . $coin . "&action=cancel&order=" . $order['orderNumber'] . "' onclick=\"return confirm('Are you sure you want to cancel this order?')\"> Cancel</a></p>";
			}
			elseif ($order['type'] == 'sell') {
				$totalbtc += ($order['amount'] * $polticker[$coin]['last']);
                        	$portfolio .= "<p>" . ucfirst($order['type']) . " <a href='https://m.poloniex.com/#/exchange/" . strtolower($coin) . "' target='_new'>" . $coin . "(" . number_format($polticker[$coin]['last'], 8) . ")</a> @ " . number_format($order['rate'], 8) . " (" . number_format($order['amount'], 2) . " coins)";
				$portfolio .= "<a href='" . $_SERVER['PHP_SELF']. "?coin=" . $coin . "&action=cancel&order=" . $order['orderNumber'] . "' onclick=\"return confirm('Are you sure you want to cancel this order?')\"> Cancel</a></p>";
			}
		}
          }
	}

	$totalbtc += $polbalance['BTC'];
	$btcvalue = $polticker['USDT_BTC']['last'];
	$totalusd = number_format((($totalbtc * $btcvalue) + $polbalance['USDT']), 2, '.', ',');
	return array('totalbtc' => $totalbtc, 'btcvalue' => $btcvalue, 'totalusd' => $totalusd, 'portfolio' => $portfolio, 'vested' => $vested, 'selectcoins' => $selectcoins);
}

function graphBalance($graphpoint, $return){
$out = <<< HTML
    <script type="text/javascript">

      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawBalanceChart);

      function drawBalanceChart() {
        var data = google.visualization.arrayToDataTable([
HTML;
        $out .= "	['Date', 'BTC']," . PHP_EOL;

	foreach($graphpoint as $entry){
        	$date = $entry['datetime'];
        	$createDate = new DateTime($date);
        	$day = $createDate->format('m/d');
        	$time = $createDate->format('h:i');
        	$out .= "	['" . $day . " " . $time . "', " . $entry['btc'] . "]," . PHP_EOL;
	}
        $out .= "	['Current " . date('h:i') . "', " . $return['totalbtc'] . "]" . PHP_EOL;

$out .= <<< HTML

        ]);

        var options = {
	  chartArea: {top: '10', 'width': '80%', 'height': '80%'},
	  height: 320,
          title: '',
          legend: { position: 'none' },
          lineWidth: 1,
          explorer: {
              actions: ['dragToZoom', 'rightClickToReset'],
              axis: 'both',
              keepInBounds: true
          },
          //explorer: {
          //actions: ['dragToZoom', 'rightClickToReset'],
          //axis: 'both',
          //keepInBounds: true,
          //maxZoomIn: 4.0
	  //},
        };

        var chart = new google.visualization.LineChart(document.getElementById('line_top_x'));

        chart.draw(data, options);
      }
    </script>
HTML;

  return $out;
}

?>
