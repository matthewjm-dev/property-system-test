<?php // Site admin config group model

ipsCore::requires_model('model');

class admin_config_group_model extends admin_model
{

    // Construct
    public function __construct($model, $table)
    {
        parent::__construct($model, $table);
    }

}
