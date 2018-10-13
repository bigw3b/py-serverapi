<?php
#--------------------------
require 'SampQueryAPI.php';
#--------------------------

$xml = simplexml_load_file( 'servers.xml' );
$json = array();

foreach ( $xml->server as $server ) {
	$query = new SampQueryAPI( $server->ip, ( int ) $server->port );
	$json[ ( string ) $server->sql ] = $query->isOnline() ? count( $query->getBasicPlayers() ) : 0;
}

echo json_encode( $json );
?>
