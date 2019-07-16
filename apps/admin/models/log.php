<?php // Admin log model

ipsCore::requires_model( 'model' );

class admin_log_model extends admin_model {

    // Construct
    public function __construct( $model, $table ) {
        parent::__construct( $model, $table );
    }

}
