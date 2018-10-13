<?php header( "Refresh:90" ); ?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Zeintar toto je len debug</title>
</head>
<style>
.canvas-container {
	width: 550px;
	height: 150px;
	position: relative;
}


.lineChart {
	left: 25%;
	top: 25%;
}
</style>
<body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<?php

// SQL Connection & XML loading
$db = include( 'db_config.php' );
$conn = new mysqli( $db['host'], $db['user'], $db['pass'], $db['name'] );
$xml = simplexml_load_file( 'servers.xml' );

// For each server own canvas
foreach ( $xml->server as $server ) {
	echo "<strong>" . $server->name_long . "</strong>"; 
?>

<div class="canvas-container" >
    <canvas class="lineChart" id="<?php echo $server->sql; ?>"></canvas>
</div>

<?php

	// Each server SQL query and graph arrays
	$graph_labels = array();
	$graph_data = array();

	$sql = "SELECT players, time_stamp FROM (SELECT id, players, SUBSTRING(time_stamp, 12) as time_stamp FROM " . $server->sql . " ORDER BY id DESC LIMIT 10) q1 ORDER BY id;";
	$result = $conn->query( $sql );

	while ( $row = $result->fetch_assoc() ) {
		$graph_labels[] = $row[ 'time_stamp' ];
		$graph_data[] = ( int ) $row[ 'players' ];
	}

	?>

<script>
var ctx = document.getElementById('<?php echo $server->sql; ?>').getContext('2d');
var chart = new Chart(ctx, {
	type: 'line',

	data: {
		labels: <?php echo json_encode( $graph_labels ); ?>,
		datasets: [{
			label: "Počet hráčov",
			borderColor: 'grey',
			backgroundColor: 'grey',
			data: <?php echo json_encode( $graph_data ); ?>,
			fill: false
		}]
	},

	options: {
		responsive: true,
		maintainAspectRatio: false,
		legend: {
			display: false
		},
		scales: {
			yAxes: [{
				ticks: {
					beginAtZero: true,
					max: <?php echo max( $graph_data ) == 0 ? 5 : ceil( max( $graph_data ) / 10 ) * 10 + 5; ?>
				}
			}]
		}
	}
});
</script>

<?php	
}
?>

</body>
</html>