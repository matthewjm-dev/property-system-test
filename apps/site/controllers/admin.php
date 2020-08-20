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

		$type_options = [
			[
				'text'  => 'For Sale',
				'value' => 'sale',
			], [
				'text'  => 'For Rent',
				'value' => 'rent',
			]
		];

		$existing = false;
		if ( $this->property->get_id() ) {
			$existing = $this->property;
		}

		$form->set_action( ipsCore::$app->get_uri_slashed() . 'admin/process/' . ( $existing ? $this->property->get_id() . '/' : '' ) );
		$form->set_classes( [ 'ajax_form', 'main_form', 'edit' ] );
		$form->field_group( 'form_main', function () use ( $form, $existing, $type_options ) {
			$form->add_text( 'county', 'County', [ 'required' => true ] );
			$form->add_text( 'country', 'Country', [ 'required' => true ] );
			$form->add_text( 'town', 'Town', [ 'required' => true ] );
			$form->add_textarea( 'description', 'Description' );
			$form->add_text( 'address', 'Address', [ 'required' => true ] );
			$form->add_text( 'latitude', 'Latitude' );
			$form->add_text( 'longitude', 'Longitude' );
			$form->add_number( 'num_bedrooms', 'Number of Bedrooms', [ 'required' => true ] );
			$form->add_number( 'num_bathrooms', 'Number of Bathrooms', [ 'required' => true ] );
			$form->add_text( 'price', 'Price', [ 'required' => true ] );
			$form->add_radio( 'type', 'Type', [ 'required' => true, 'options' => $type_options ] );
			if ( $existing ) {
				$form->add_html( 'image_preview', '<img src="' . $existing->get_relationship( 'image' )->get_prop( 'path' ) . '" alt="' . $existing->get_relationship( 'image' )->get_prop( 'alt' ) . '" />' );
			}
			$form->add_image( 'image', 'Image', [ 'required' => ( $existing ? false : true ) ] );
			if ( $existing ) {
				$form->add_html( 'image_thumbnail_preview', '<img src="' . $existing->get_relationship( 'thumbnail' )->get_prop( 'path' ) . '" alt="' . $existing->get_relationship( 'thumbnail' )->get_prop( 'alt' ) . '" />' );
			}
			$form->add_image( 'thumbnail', 'Thumbnail', [ 'required' => ( $existing ? false : true ) ] );
		} );
		$form->add_submit( 'submit', ( $existing ? 'Save' : 'Create' ) . ' Property' );

		return $form;
	}

	public function property( $id = false )
	{
		if ( $id ) {
			$this->property->where( $id )->retrieve();
		}

		$form = $this->property_form();

		if ( $this->property->get_id() ) {
			$form->populate_form( $this->property );
		}

		$this->add_data( [
			'title' => 'Admin - ' . ( $id ? 'Edit' : 'Create' ) . ' Property',
			'form'  => $form->render(),
		] );

		$this->add_view_class( 'property' );
		$this->get_layout();
		$this->build_view();
	}

	public function process( $id = false )
	{
		$errors = [];

		if ( $id ) {
			$this->property->where( $id )->retrieve();
			$this->property->set_prop( 'admin_modified', 1 );
		}

		$form = $this->property_form();
		$form->populate_form();
		$form->validate_form( $errors );

		if ( empty( $errors ) ) {
			$this->property->set_prop( 'county', $form->get_field_value( 'county' ) );
			$this->property->set_prop( 'country', $form->get_field_value( 'country' ) );
			$this->property->set_prop( 'town', $form->get_field_value( 'town' ) );
			$this->property->set_prop( 'description', $form->get_field_value( 'description' ) );
			$this->property->set_prop( 'address', $form->get_field_value( 'address' ) );
			$this->property->set_prop( 'latitude', $form->get_field_value( 'latitude' ) );
			$this->property->set_prop( 'longitude', $form->get_field_value( 'longitude' ) );
			$this->property->set_prop( 'num_bedrooms', $form->get_field_value( 'num_bedrooms' ) );
			$this->property->set_prop( 'num_bathrooms', $form->get_field_value( 'num_bathrooms' ) );
			$this->property->set_prop( 'price', $form->get_field_value( 'price' ) );
			$this->property->set_prop( 'type', $form->get_field_value( 'type' ) );

			// Images
			$images = [ 'image', 'thumbnail' ];
			foreach ( $images as $image ) {
				$file = $form->get_field_value( $image );
			}

			if ( $this->property->save() ) {
				if ( $id ) {
					$this->add_json_success( 'Property Saved' );
				} else {
					$this->add_json_redirect( ipsCore::$app->get_uri_slashed() . 'admin/edit/' . $this->property->get_id() );
				}

			} else {
				$errors[] = 'Failed to save Property';
			}
		}

		if ( !empty( $errors ) ) {
			$this->add_json_failure( $errors );
		}

		$this->build_json();
	}

	public function remove( $id )
	{

	}
}