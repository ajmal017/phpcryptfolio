<form id="chartform" method="post">
<select id="selectcoin">
<?php

$coins=traded_coins();
foreach($coins as $coin) {
	print "	<option value='" . $coin . "'>" . $coin . "</option>";
}
echo <<<EndOfHtml
</select>
<input id="subcoin" type=button value="submit">
</form>
<div id="dashboard_div" class="graph">  
  <div id="chart_div"> </div>
  <div id="control_div"> </div>
  <div id="table_div"> </div>
<div id="line_top_z" class="graph"></div>
</div>
EndOfHtml;
?>
