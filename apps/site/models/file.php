<?php // Site file model

ipsCore::requires_model( 'model' );

class file_model extends site_model
{
	public function __construct( $name, $table = ' ' )
	{
		$this->set_default_fields();

		parent::__construct( $name, $table );

		$this->set_pkey( 'fid' );
	}

	public function set_default_fields()
	{
		$this->default_fields = [
			'fid'        => $this->get_pkey_args(),
			'created'    => [ 'type' => 'int', 'length' => 11 ],
			'modified'   => [ 'type' => 'int', 'length' => 11 ],
			'removed'    => [ 'type' => 'tinyint', 'length' => 1 ],
			'alt'        => [ 'type' => 'varchar', 'length' => 255 ],
			'path'       => [ 'type' => 'varchar', 'length' => 255 ],
			'path_thumb' => [ 'type' => 'varchar', 'length' => 255 ],
		];
	}
}
