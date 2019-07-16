<?php // Admin Static Routes
$pre = '/admin/';

$this->add_route( $pre, 'dashboard', 'index' );
$this->add_route( $pre . 'login', 'login', 'index' );
$this->add_route( $pre . 'login/process', 'login', 'process' );

/*$this->add_route( $pre, new ipsCore_route( 'home', 'index' ) );
$this->add_route( $pre . 'login', new ipsCore_route( 'login', 'index', [] ) );
$this->add_route( $pre . 'login/process', new ipsCore_route( 'login', 'process', [] ) );*/

/*$this->add_route( $pre . 'logout', new ipsCore_route( 'login', 'logout', [] ) );
$this->add_route( $pre . 'stats', new ipsCore_route( 'statistics', 'index', [] ) );
$this->add_route( $pre . 'posts', new ipsCore_route( 'posts', 'index', [] ) );
$this->add_route( $pre . 'config', new ipsCore_route( 'configuration', 'index', [] ) );*/
