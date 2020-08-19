<?php // Site properties controller

ipsCore::requires_controller( 'controller' );
ipsCore::requires_helper( 'properties_helper' );

class properties_controller extends site_controller
{

	// Construct
	public function __construct( $controller, $additional = false )
	{
		parent::__construct( $controller, $additional );

		$this->set_page_title( 'Property List' );

		$this->set_view_class( 'properties' );
	}

	// Methods
	public function index( $slug = false, $page = false )
	{
		if ( $slug && $slug != 'property' ) {
			$this->property( $slug );
		} else {
			if ( !$page ) {
				$page = 1;
			}
			$this->list( $page );
		}
	}

	public function list( $page )
	{
		$this->add_view_class( 'list' );

		$properties_helper = new properties_helper( 'properties_helper' );
		if ( $properties_helper->fetch( $page ) ) {

			$this->add_data( [
				'title'           => 'Property System',
				'content'         => 'Content',
				'properties_list' => $this->get_properties_list( $properties_helper->properties ),
				'pagination'      => $this->get_properties_pagination( [
					'current_page' => $page,
					'',
					'',
					'',
					'',
				] ),
			] );

			$this->get_layout();
			$this->build_view();
		} else {
			$this->error404( 'Property information was not received from the API', 'API Failure' );
		}
	}

	public function property( $slug )
	{
		$this->add_view_class( 'item' );

		$this->set_page_title( 'Property System' );

		if ( $property = $this->property->where( [ 'slug' => $slug ] )->get() ) {
			$this->add_data( [
				'title'    => 'Viewing Property - ' . $property->get_prop( 'address' ),
				'property' => $property,
			] );

			$this->get_layout();
			$this->build_view();
		} else {
			$this->error404();
		}
	}

	public function get_properties_list( $properties )
	{
		$properties_items = [];

		if ( !empty( $properties ) ) {
			foreach ( $properties as $property ) {
				$properties_items[] = ipsCore::get_part( 'properties/parts/item', [
					'property' => $property
				] );
			}
		}

		return ipsCore::get_part( 'properties/parts/list', [
			'properties' => $properties_items
		] );
	}

	public function get_properties_pagination( $args )
	{

	}
}
