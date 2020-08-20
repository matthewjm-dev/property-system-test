<?php // Admin controller

ipsCore::requires_controller('controller');
ipsCore::requires_model(['property', 'property_type', 'file']);
ipsCore::requires_core_helper('form_builder');

class admin_controller extends site_controller
{
    public $property;

    // Construct
    public function __construct($controller, $additional = false)
    {
        parent::__construct($controller, $additional);

        $this->set_view_class('admin');
    }

    // Methods
    public function index()
    {
        $properties = $this->property->get_all();

        $this->add_data([
            'title'      => 'Property System Admin',
            'content'    => 'Edit Properties',
            'properties' => $properties,
            'create_url' => '/admin/create/',
            'edit_url'   => '/admin/edit/',
            'remove_url' => '/admin/remove/',
        ]);

        $this->add_view_class('list');
        $this->get_layout();
        $this->build_view();
    }

    private function property_form()
    {
        $form = new ipsCore_form_builder('property_form');

        $form->set_action(ipsCore::$app->get_uri_slashed() . 'admin/process/' . ($this->property ? $this->property->get_id() . '/' : ''));
        $form->set_classes(['ajax_form', 'main_form', 'edit']);
        $form->field_group('form_main', function () use ($form) {
            $form->add_text('county', 'County');
            $form->add_text('country', 'Country');
            $form->add_text('town', 'Town');
            $form->add_textarea('description', 'Description');
            $form->add_text('address', 'Address');
            $form->add_text('latitude', 'Latitude');
            $form->add_text('longitude', 'Longitude');
            $form->add_number('num_bedrooms', 'Number of Bedrooms');
            $form->add_number('num_bathrooms', 'Number of Bathrooms');
            $form->add_text('price', 'Price');
            $form->add_text('type', 'Type');
        });
        $form->add_submit('submit', );

        return $form;
    }

    public function property($id = false)
    {
        if ($id) {
            $this->property->where($id)->retrieve();
        }

        $form = $this->property_form();

        $this->add_data([
            'title' => ($id ? 'Edit' : 'Create') . ' Property',
            'form'  => $form->render(),
        ]);

        $this->add_view_class('property');
        $this->get_layout();
        $this->build_view();
    }

    public function process($id = false)
    {
        $errors = [];

        $form = $this->property_form();
        $form->populate_form();
        $form->validate_form($errors);

        if (empty($errors)) {
            if ($id) {
                $this->property->where($id)->retrieve();
            } else {
                $this->property->where($id)->retrieve();
            }


        } else {

        }
    }

    public function remove($id)
    {

    }
}