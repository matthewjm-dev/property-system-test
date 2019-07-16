<?php // Site Static Routes

//$this->add_route( 'product/*', 'product', 'index' );
//$this->add_route( 'category/*', 'category', 'index' );
$this->add_route( 'blog', 'blog', 'index' );
$this->add_route( 'services', 'services', 'index' );
$this->add_route( 'portfolio', 'portfolio', 'index' );
$this->add_route( '*', 'pages', 'page', ipsCore::$uri_parts );

/*$this->add_route( 'product/*', new ipsCore_route( 'product', 'index' ) );
$this->add_route( 'category/*', new ipsCore_route( 'category', 'index' ) );
$this->add_route( 'blog', new ipsCore_route( 'blog', 'index' ) );
$this->add_route( 'blog/*', new ipsCore_route( 'blog', 'post' ) );
$this->add_route( '/*', new ipsCore_route( 'pages', 'page' ) );*/
