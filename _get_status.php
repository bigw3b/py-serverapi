<?php
//-------------------------
require 'SampQueryAPI.php';
//-------------------------

$servers_xml = simplexml_load_file( 'servers.xml' );

foreach ( $servers_xml->server as $server ) {
	$query = new SampQueryAPI( $server->ip, ( int ) $server->port );
	echo $server->name_long;
	echo $query->isOnline() ? ': Online' : ': Offline'; 
	echo '<hr />';
}

?>