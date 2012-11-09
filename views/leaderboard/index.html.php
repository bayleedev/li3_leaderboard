<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Task', 'Unit Tests Created']
			<?php foreach($data['data'] as $name => $value) echo ",['{$name}', {$value['count']}]"; ?>
		]);

		var options = {
			title: 'Unit Tests Created'
		};

		var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
		chart.draw(data, {});
	}
</script>
<div style="width:669"
<div class="item">
	<div class="test-result test-result-notice">Table Format</div>
	<table class="test-result solution">
		<thead>
			<tr>
				<th>Name</th>
				<th>Count</th>
				<th>Percent</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($data['data'] as $name => $value) { ?>
			<tr>
				<td><?=$name?></td>
				<td><?=$value['count']?></td>
				<td><?=$value['percent']?>%</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<div class="item">
	<div class="test-result test-result-notice">Pie Chart</div>
	<div id="chart_div" style="height: 300px;" class="test-result solution"></div>
</div>