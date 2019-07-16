<?php // Site admin module item model
// for items in dynamically created tables for modules

ipsCore::requires_model('model');

class admin_module_item_model extends admin_model
{

    // Construct
    public function __construct($name, $table)
    {
        parent::__construct($name, $table);
    }
}
