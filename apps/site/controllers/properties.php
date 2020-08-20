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
		if ( $slug != 'page' && !$page) {
			$this->property( $slug );
		} elseif (is_numeric($page) || $page === false) {
			if ( !$page ) {
				$page = 1;
			}
			$this->list( $page );
		} else {
			$this->error404();
		}
	}

	public function list( $page = 1 )
	{
		$this->add_view_class( 'list' );

		$properties_helper = new properties_helper( 'properties_helper' );
		$count = 1;

		if ( $properties_helper->fetch( $page, $count ) ) {

			$total_pages = (int)$properties_helper->total / $count;

			$first_page = ( $page == 1 ? false : 1 );
			$last_page = ( $page == $total_pages ? false : $total_pages );
			$next_page = ( $page == $total_pages ? false : $page + 1 );
			$previous_page = ( $page == 1 ? false : $page - 1 );

			$this->add_data( [
				'title'           => 'Property System',
				'content'         => 'Content',
				'properties_list' => $this->get_properties_list( $properties_helper->properties ),
				'pagination'      => ipsCore::get_part( 'properties/parts/pagination', [
					'current_page'  => $page,
					'first_page'    => $first_page,
					'last_page'     => $last_page,
					'next_page'     => $next_page,
					'previous_page' => $previous_page,
					'total_pages'   => $total_pages,
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
				'image'    => $property->get_relationship( 'image' )->get_prop( 'path' ),
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
					'address'   => $property->get_prop( 'address' ),
					'slug'      => $property->get_prop( 'slug' ),
					'thumbnail' => $property->get_relationship( 'thumbnail' )->get_prop( 'path' ),
				] );
			}
		}

		return ipsCore::get_part( 'properties/parts/list', [
			'properties' => $properties_items
		] );
	}
}
