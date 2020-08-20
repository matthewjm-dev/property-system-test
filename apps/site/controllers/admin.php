<?php // Admin controller

ipsCore::requires_controller( 'controller' );
ipsCore::requires_model( [ 'property', 'property_type', 'file' ] );
ipsCore::requires_core_helper( 'form_builder' );

class admin_controller extends site_controller
{
	public $property;

	// Construct
	public function __construct( $controller, $additional = false )
	{
		parent::__construct( $controller, $additional );

		$this->set_view_class( 'admin' );
	}

	// Methods
	public function index()
	{
		$properties = $this->property->get_all();

		$this->add_data( [
			'title'      => 'Property System Admin',
			'content'    => 'Edit Properties',
			'properties' => $properties,
			'create_url' => '/admin/create/',
			'edit_url'   => '/admin/edit/',
			'remove_url' => '/admin/remove/',
		] );

		$this->add_view_class( 'list' );
		$this->get_layout();
		$this->build_view();
	}

	private function property_form()
	{
		$form = new ipsCore_form_builder( 'property_form' );

		$form->set_action( ipsCore::$app->get_uri_slashed() . 'admin/process/' . ( $this->property ? $this->property->get_id() . '/' : '' ) );
		$form->set_classes( [ 'ajax_form', 'main_form', 'edit' ] );
		$form->start_section( 'form_main' );
	}

	public function property( $id = false )
	{
		if ( $id ) {
			$this->property->where( $id )->retrieve();
		}

		$form = $this->property_form();

		$this->add_data( [
			'title' => ($id ? 'Edit' : 'Create') . ' Property',
			'form' => $form,
		] );

		$this->add_view_class( 'property' );
		$this->get_layout();
		$this->build_view();
	}

	public function process( $id )
	{

	}

	public function remove( $id )
	{

	}
}