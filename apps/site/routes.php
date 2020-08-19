<?php // Site Static Routes

$this->add_route( 'property', 'properties', 'property', ipsCore::$uri_parts );
$this->add_route( '*', 'properties', 'index', ipsCore::$uri_parts );

$this->add_route_group('admin', false, function() {
	$this->add_route( 'properties', 'properties', 'index' );
	$this->add_route( 'property/create', 'properties', 'create' );
	$this->add_route( 'property/edit', 'properties', 'edit' );
	$this->add_route( 'property/process', 'properties', 'process' );
	$this->add_route( 'property/remove', 'properties', 'remove' );
	$this->add_route( '.', 'dashboard', 'index' );
});
