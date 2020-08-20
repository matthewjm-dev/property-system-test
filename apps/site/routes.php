<?php // Site Static Routes

$this->add_route_group('admin', false, function() {
	$this->add_route( 'create', 'admin', 'property' );
	$this->add_route( 'edit/*', 'admin', 'property' );
	$this->add_route( 'process', 'admin', 'process' );
	$this->add_route( 'remove/*', 'admin', 'remove' );
	$this->add_route( '.', 'admin', 'index' );
});

$this->add_route( 'property', 'properties', 'property' );
$this->add_route( 'page', 'properties', 'list' );
$this->add_route( 'paginate', 'properties', 'paginate' );
$this->add_route( '*', 'properties', 'list', ipsCore::$uri_parts );
