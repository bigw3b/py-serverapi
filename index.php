<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<meta name='author' content='marzus'>
	<title>Československý Roleplay</title>
	<style>
	.center {
		margin: auto;
	}
	.canvas-container {
		position: relative;
		width: 750px;
		height: 300px;
	}
	</style>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" >
</head>
<body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>

<div class='canvas-container center'>
	<canvas id='actualInfoGraph'></canvas>
</div>

<?php
// Requirements and XML loading
require "SampQueryAPI.php";
$xml = simplexml_load_file( 'servers.xml' );

// Actual info = Bar Chart

$actual_labels = array();
$actual_data = array();

foreach ( $xml->server as $server ) {
	$samp_query = new SampQueryAPI( $server->ip, ( int ) $server->port );
	$actual_labels[] = ( string ) $server->name_short;
	$actual_data[] = $samp_query->isOnline() ? count( $samp_query->getBasicPlayers() ) : 0;
}

?>

<script>
var ctx = document.getElementById( 'actualInfoGraph' ).getContext( '2d' );
var actualInfoBarChart = new Chart( ctx, {
	type: 'bar',
	data: {
		labels: <?php echo json_encode( $actual_labels ); ?>,
		datasets: [{
			label: "Počet hráčov",
			borderColor: '#0099cc',
			backgroundColor: '#0099cc',
			data: <?php echo json_encode( $actual_data); ?>,
		}]
	},

	options: {
		title: {
			display: true,
			text: 'Aktuálny stav',
			fontSize: 20
		},
		maintainAspectRatio: false,
		legend: {
			display: false
		},
		scales: {
			yAxes: [{
				gridLines: {
					drawBorder: false,
					display: false
				},
				ticks: {
					display: false,
					beginAtZero: true,
					min: 0,
					max: <?php echo max( $actual_data ) == 0 ? 10 : ceil( max( $actual_data ) / 10 ) * 10; ?>
				}
			}],
			xAxes: [{
				gridLines: {
					display: false,
				},
				barThickness: 85
			}]
		}
	}
} )
</script>

<?php

// SQL Connection
$db = include( 'db_config.php' );
$conn = new mysqli( $db['host'], $db['user'], $db['pass'], $db['name'] );

// For each server own canvas
foreach ( $xml->server as $server ) {
?>

<div class="canvas-container center" >
    <canvas class="lineChart" id="<?php echo $server->sql; ?>"></canvas>
</div>

<?php

	// Each server SQL query and graph arrays
	$graph_heading = ( string ) $server->name_long;
	$graph_labels = array();
	$graph_data = array();

	$sql = "SELECT MAX(players) players, SUBSTRING(time_stamp, 12) time_stamp FROM " . $server->sql . " GROUP BY SUBSTRING(time_stamp, 1, 13) ORDER BY id DESC LIMIT 24;";

	$result = $conn->query( $sql );

	while ( $row = $result->fetch_assoc() ) {
		$graph_labels[] = $row[ 'time_stamp' ];
		$graph_data[] = ( int ) $row[ 'players' ];
	}

	// Fix order of sql query result
	$graph_labels = array_reverse( $graph_labels );
	$graph_data = array_reverse( $graph_data );

	// Print JS scripts
	?>

<script>
var ctx = document.getElementById('<?php echo $server->sql; ?>').getContext('2d');
var chart = new Chart(ctx, {
	type: 'line',

	data: {
		labels: <?php echo json_encode( $graph_labels ); ?>,
		datasets: [{
			label: "Počet hráčov",
			borderColor: '#31698a',
			backgroundColor: '#31698a',
			data: <?php echo json_encode( $graph_data ); ?>,
			fill: false
		}]
	},

	options: {
		title: {
			display: true,
			text: "<?php echo $graph_heading; ?>"
		},
		maintainAspectRatio: false,
		legend: {
			display: false
		},
		scales: {
			yAxes: [{
				ticks: {
					beginAtZero: true,
					min: 0,
					max: <?php echo max( $graph_data ) == 0 ? 2 : ceil( max( $graph_data ) / 10 ) * 10 + 5; ?>,
					callback: function( value, index, values ) { if ( Math.floor( value ) == value ) { return value; } }
				}
			}]
		}
	}
});
</script>

<?php	
} // End of foreach

$conn->close();
?>


<?php include('includes/footer.php'); ?>
</body>
</html>
