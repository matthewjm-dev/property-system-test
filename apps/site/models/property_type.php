<?php // Site file model

ipsCore::requires_model( 'model' );

class property_type_model extends site_model
{

	// Construct
	public function __construct( $name, $table = ' ' )
	{
		$this->set_default_fields();

		parent::__construct( $name, $table );

		$this->set_pkey( 'ptid' );
	}

	public function set_default_fields()
	{
		$this->default_fields = [
			'ptid'             => $this->get_pkey_args(),
			'created'          => [ 'type' => 'int', 'length' => 11 ],
			'modified'         => [ 'type' => 'int', 'length' => 11 ],
			'removed'          => [ 'type' => 'tinyint', 'length' => 1 ],
			'slug'             => [ 'type' => 'varchar', 'length' => 255 ],
			'type_id'          => [ 'type' => 'varchar', 'length' => 255 ],
			'title'            => [ 'type' => 'varchar', 'length' => 255 ],
			'description'      => [ 'type' => 'text' ],
			'api_created'      => [ 'type' => 'varchar', 'length' => 255 ],
			'api_updated'      => [ 'type' => 'varchar', 'length' => 255 ],
		];
	}

}
