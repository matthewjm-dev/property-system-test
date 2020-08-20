<?php // Site file model

ipsCore::requires_model( 'model' );

class property_model extends site_model
{

	// Construct
	public function __construct( $name, $table = ' ' )
	{
		$this->set_default_fields();

		parent::__construct( $name, $table );

		$this->set_pkey( 'pid' );

		$this->add_relationship( 'property_type', 'property_type', 'ptid', 'property_type' );
		$this->add_relationship( 'file', 'image', 'fid', 'image' );
		$this->add_relationship( 'file', 'thumbnail', 'fid', 'thumbnail' );
	}

	public function set_default_fields()
	{
		$this->default_fields = [
			'pid'            => $this->get_pkey_args(),
			'created'        => [ 'type' => 'int', 'length' => 11 ],
			'modified'       => [ 'type' => 'int', 'length' => 11 ],
			'removed'        => [ 'type' => 'tinyint', 'length' => 1 ],
			'slug'           => [ 'type' => 'varchar', 'length' => 255 ],
			'title'          => [ 'type' => 'varchar', 'length' => 255 ],
			'uuid'           => [ 'type' => 'varchar', 'length' => 255 ],
			'property_type'  => [ 'type' => 'int', 'length' => 11 ],
			'county'         => [ 'type' => 'varchar', 'length' => 255 ],
			'country'        => [ 'type' => 'varchar', 'length' => 255 ],
			'town'           => [ 'type' => 'varchar', 'length' => 255 ],
			'description'    => [ 'type' => 'text' ],
			'address'        => [ 'type' => 'text' ],
			'image'          => [ 'type' => 'int', 'length' => 11 ],
			'thumbnail'      => [ 'type' => 'int', 'length' => 11 ],
			'latitude'       => [ 'type' => 'varchar', 'length' => 255 ],
			'longitude'      => [ 'type' => 'varchar', 'length' => 255 ],
			'num_bedrooms'   => [ 'type' => 'varchar', 'length' => 255 ],
			'num_bathrooms'  => [ 'type' => 'varchar', 'length' => 255 ],
			'price'          => [ 'type' => 'varchar', 'length' => 11 ],
			'type'           => [ 'type' => 'varchar', 'length' => 255 ],
			'api_created'    => [ 'type' => 'varchar', 'length' => 255 ],
			'api_updated'    => [ 'type' => 'varchar', 'length' => 255 ],
			'admin_modified' => [ 'type' => 'tinyint', 'length' => 1 ],
		];
	}

}
