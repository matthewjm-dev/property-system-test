<?php // Site admin config field model

ipsCore::requires_model( 'model' );

class admin_config_field_model extends admin_model {

	// Construct
	public function __construct( $model, $table ) {
		parent::__construct( $model, $table );
	}

}