<?php // Site admin fields model

ipsCore::requires_model( 'model' );

class admin_module_model extends admin_model {

    // Construct
    public function __construct( $name, $table ) {
        parent::__construct( $name, $table );

        $this->set_pkey( 'mid' );
    }
}