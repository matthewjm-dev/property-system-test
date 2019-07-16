<?php
/*ipsCore::requires_core_helper( 'remote_routes' );
$remote_routes = new ipsCore_remote_routes();

// Database Routes
$routes = $remote_routes->get_routes();
if ( !empty( $routes ) ) {
	foreach ( $routes as $route ) {
		$this->add_route( $route['uri'], new ipsCore_route( $route['controller'], $route['method'], $route['action'] ) );
	}
}*/

// Static Routes
$this->add_route( '/', 'pages', 'index' );
