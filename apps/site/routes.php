<?php // Site Static Routes

$this->add_route_group('admin', false, function() {
	$this->add_route( 'create', 'admin', 'property' );
	$this->add_route( 'edit/*', 'admin', 'property' );
	$this->add_route( 'process', 'admin', 'process' );
	$this->add_route( 'remove/*', 'admin', 'remove' );
	$this->add_route( '.', 'admin', 'index' );
});

$this->add_route( 'property', 'properties', 'property', ipsCore::$uri_parts );
$this->add_route( '*', 'properties', 'index', ipsCore::$uri_parts );
