<?php // Site admin fields model

// mfid (int ai pk), slug (varchar unique), title (text), description (text), key (varchar unique), mid (int), type (varchar), type (varchar), default (text), placeholder (text), options(text), link(int), link_field(int), show_list(int), required(int)

ipsCore::requires_model( 'model' );

class admin_module_field_model extends admin_model {

	// Construct
	public function __construct( $model, $table ) {
		parent::__construct( $model, $table );
	}

	// Methods


}
