<?php

$c = 0;
$finished = 0;
$values = array();
$candles = array();


if( $_POST){
	if($_POST["coin"]) {
		$coin = $_POST["coin"];

		$candlesep = "~";
		
		//$out .= "[['', 'Price']," . $PHP_EOL;
		//$out .= "[";

		$backtest = `coin $coin test`;

		$candlegroup = strtok($backtest, $candlesep);
		while ($candlegroup !== false) {
			$candle[$c] = parsecandles($c, $candlegroup);
			if($candle[$c]['PRICE'] !== null) {
				if($candle[$c]['BOUGHT'] > 0)
					$candle[$c]['BUY'] = 'B';
				else
					$candle[$c]['BUY'] = '';

				if($candle[$c]['SOLD'] > 0)
					$candle[$c]['SELL'] = 'S';
				else
					$candle[$c]['SELL'] = '';

                                foreach($candle[$c] as $key => $value){
					if($key != 'BOUGHT' && $key != 'SOLD')
                               		$values = array_merge($values, array($key => $value));
				}
				array_push($candles, $values);
                                //$candles[] = array("candle" => $c, "direction" => $candle[$c]['DIRECTION'], "price" => $candle[$c]['PRICE'], "sma" => $candle[$c]['SMA'], "ema" => $candle[$c]['EMA'], "diff" => $candle[$c]['diff'], "roc" => $candle[$c]['ROC'], "rocdiff" => $candle[$c]['ROCDIFF'], "rsi" => $candle[$c]['RSI'], "rsidiff" => $candle[$c]['RSIDIFF'], "stochrsi" => $candle[$c]['STOCHRSI'], "adx" => $candle[$c]['ADX'], "mom" => $candle[$c]['MOM'], "buy" => $candle[$c]['buy'], "sell" => $candle[$c]['sell'], "cci" => $candle[$c]['CCI'], "ccidiff" => $candle[$c]['CCIDIFF']);
			}
			$candlegroup = strtok($candlesep);
			$c++;
		}
		echo json_encode($candles);
	}
}

function parsecandles($c, $candlegroup) {
        $determine = '/([aA-zZ]+)\: ([+-]?(\d*\.)?\d+)/';
        preg_match_all($determine, $candlegroup, $matches);
        foreach($matches[1] as $value => $key){
		if($key != 'null' && $key != 'from' && $key != 'to') {
			$candle['CANDLE'] = $c;
                	$candle[$key] = $matches[2][$value];
		}
        }
        return $candle;
}
?>
