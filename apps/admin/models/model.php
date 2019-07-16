<?php // Site admin main model

//ipsCore::requires_model( 'model', 'site' );

class admin_model extends ipsCore_model {

	// Construct
	public function __construct( $name, $table = false ) {
		parent::__construct( $name, $table );
	}

	public function get_user_level() {
		if ( $this->is_logged_in() ) {
			return ipsCore::$session->read( 'user_level' );
		} return 0;
	}

	public function get_module_from_item() {

    }

    // QUERY FUNCTIONS

    public function where_has_permission($level = false) {
	    if (!$level) {
	        $level = ipsCore::$controller->currentuser->level;
        }

        $this->where(['level' => ['value' => $level, 'operator' => '>=']]);

        return $this;
    }

}
