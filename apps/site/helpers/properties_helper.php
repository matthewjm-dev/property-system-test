<?php // Properties Helper

ipsCore::requires_model( [ 'property', 'property_type', 'file' ] );
ipsCore::requires_core_helper( [ 'file_manager' ] );

class properties_helper extends ipsCore_helper
{
	public $api_properties;
	public $properties;
	public $first_page_url;
	public $last_page;
	public $last_page_url;
	public $next_page_url;
	public $total;

	public function __construct( $name )
	{
		parent::__construct( $name );
	}

	public function fetch( $page = 1, $count = 30 )
	{
		$curl = curl_init();

		$params = http_build_query( [
			'page[number]' => $page,
			'page[size]'   => $count,
			'api_key'      => ipsCore::$app->config[ 'properties-api' ][ 'api_key' ],
		] );

		curl_setopt_array( $curl, [
			CURLOPT_URL            => "http://trialapi.craig.mtcdevserver.com/api/properties?" . $params,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
			CURLOPT_HTTPHEADER     => [
				"cache-control: no-cache"
			],
		] );

		$response = json_decode( curl_exec( $curl ) );
		$err = curl_error( $curl );

		curl_close( $curl );

		if ( !$err && isset( $response->data ) ) {
			$this->api_properties = $response->data;
			$this->properties = false;
			$this->first_page_url = $response->first_page_url;
			$this->last_page = $response->last_page;
			$this->last_page_url = $response->last_page_url;
			$this->next_page_url = $response->next_page_url;
			$this->total = $response->total;

			$this->sync();

			return true;
		}

		return false;
	}

	public function sync()
	{
		if ( !empty( $this->api_properties ) ) {
			$this->load_model( 'property' );
			$this->load_model( 'property_type' );

			$property_ids = array_column( $this->api_properties, 'uuid' );
			$property_type_ids = array_column( $this->api_properties, 'property_type_id' );

			$properties = $this->property->where_in( [ 'uuid' => $property_ids ] )->get_all();
			$property_types = $this->property_type->where_in( [ 'type_id' => $property_type_ids ] )->get_all();

			foreach ( $this->api_properties as $api_property ) {
				$property_in_db = false;
				$property_type_in_db = false;

				// Find the corresponding Stored property for the API property
				if ( !empty( $properties ) ) {
					foreach ( $properties as $property ) {
						if ( $property->get_prop( 'uuid' ) == $api_property->uuid ) {
							$property_in_db = $property;
							continue;
						}
					}
				}

				// Find the corresponding Stored property Type for the API property type
				if ( !empty( $property_types ) ) {
					foreach ( $property_types as $property_type ) {
						if ( $property_type->get_prop( 'type_id' ) == $api_property->property_type_id ) {
							$property_type_in_db = $property_type;
							continue;
						}
					}
				}

				// If the property is not in the database, or exists but has never been modified, add or update it
				if ( !$property_in_db || ( !$property_in_db->get_prop( 'removed' ) && !$property_in_db->get_prop( 'admin_modified' )) ) {

					// Create / Update property Type

					if ( !$property_type_in_db ) {
						$property_type_in_db = $this->get_model( 'property_type' );
					}

					$property_type_in_db->set_prop( 'type_id', $api_property->property_type->id );
					$property_type_in_db->set_prop( 'title', $api_property->property_type->title );
					$property_type_in_db->set_prop( 'description', $api_property->property_type->description );
					$property_type_in_db->set_prop( 'api_created', $api_property->property_type->created_at );
					$property_type_in_db->set_prop( 'api_updated', $api_property->property_type->updated_at );

					$property_type_in_db->save();

					$property_types[] = $property_type_in_db; // Add the property type back to the types array so it doesnt get duplicated

					// Create / Update property

					if ( !$property_in_db ) {
						$property_in_db = $this->get_model( 'property' );
					}

					// Upload Images if new property or image has changed
					$images = [
						'image_full'      => 'image',
						'image_thumbnail' => 'thumbnail',
					];

					foreach ( $images as $image_key => $image_property ) {
						$image_details = ipsCore_file_manager::get_url_details( $api_property->{$image_key} );

						if ( ( !$property_in_db->get_relationship($image_property) || $property_in_db->get_relationship($image_property)->get_prop('source') != $api_property->{$image_key} ) && property_exists( $api_property, $image_key ) && !empty( $api_property->{$image_key} ) ) {
							if ( $image_full_uploaded = ipsCore_file_manager::upload_from_url( $api_property->{$image_key}, 'properties' ) ) {
								$image = $this->get_model( 'file' );
								$image->set_prop( 'alt', $image_details['filename'] );
								$image->set_prop( 'path', '/' . $image_full_uploaded['path'] );
								$image->set_prop( 'filename', $image_full_uploaded['filename'] );
								$image->set_prop( 'extension', $image_full_uploaded['extension'] );
								$image->set_prop( 'source', $api_property->{$image_key} );

								$image->save();
								$property_in_db->set_prop( $image_property, $image->get_id() );
							}
						}
					}

					$property_in_db->set_prop( 'slug', ipsCore::$functions->generate_slug( $api_property->address ) );
					$property_in_db->set_prop( 'uuid', $api_property->uuid );
					$property_in_db->set_prop( 'county', $api_property->county );
					$property_in_db->set_prop( 'country', $api_property->country );
					$property_in_db->set_prop( 'town', $api_property->town );
					$property_in_db->set_prop( 'description', $api_property->description );
					$property_in_db->set_prop( 'address', $api_property->address );
					$property_in_db->set_prop( 'latitude', $api_property->latitude );
					$property_in_db->set_prop( 'longitude', $api_property->longitude );
					$property_in_db->set_prop( 'num_bedrooms', $api_property->num_bedrooms );
					$property_in_db->set_prop( 'num_bathrooms', $api_property->num_bathrooms );
					$property_in_db->set_prop( 'price', $api_property->price );
					$property_in_db->set_prop( 'type', $api_property->type );
					$property_in_db->set_prop( 'api_created', $api_property->created_at );
					$property_in_db->set_prop( 'api_updated', $api_property->updated_at );
					$property_in_db->set_prop( 'property_type', $property_type_in_db->get_id() );

					$property_in_db->save();

					$property_in_db->load_relationships();
				}

				$this->properties[] = $property_in_db;
			}
		}

		return false;
	}
}