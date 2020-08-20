<?php // Site properties controller

ipsCore::requires_controller('controller');
ipsCore::requires_helper('properties_helper');

class properties_controller extends site_controller
{
    public $apiError = 'Property information was not received from the API';

    // Construct
    public function __construct($controller, $additional = false)
    {
        parent::__construct($controller, $additional);

        $this->set_page_title('Property List');

        $this->set_view_class('properties');
    }

    // Methods
    public function list($page = 1)
    {
        $this->add_view_class('list');

        if ($data = $this->get_list($page)) {
            $this->add_data($data);

            $this->get_layout();
            $this->build_view();
        } else {
            $this->error404( $this->apiError, 'API Failure' );
        }
    }

    public function paginate($page = 1) {
        if ($data = $this->get_list($page)) {
            $this->add_json([
                'fragments' => [
                    '.property-list' => $data['properties_list'],
                    '#pagination' => $data['pagination'],
                ]
            ]);

            $this->set_view('list');
            $this->build_json();
        } else {
            $this->add_json_failure([$this->apiError]);
        }
    }

    private function get_list($page)
    {
        if (!$page || !is_numeric($page)) {
            $page = 1;
        }

        $properties_helper = new properties_helper('properties_helper');

        if ($properties_helper->fetch($page)) {

            $total_pages = (int)$properties_helper->total / $properties_helper->count;

            $first_page = ($page == 1 ? false : 1);
            $last_page = ($page == $total_pages ? false : $total_pages);
            $next_page = ($page == $total_pages ? false : $page + 1);
            $previous_page = ($page == 1 ? false : $page - 1);

            $data = [
                'title'           => 'Property System',
                'content'         => 'Content',
                'properties_list' => $this->get_properties_list($properties_helper->properties),
                'pagination'      => ipsCore::get_part('properties/parts/pagination', [
                    'current_page'  => $page,
                    'first_page'    => $first_page,
                    'last_page'     => $last_page,
                    'next_page'     => $next_page,
                    'previous_page' => $previous_page,
                    'total_pages'   => $total_pages,
                ]),
            ];

            return $data;
        }

        return false;
    }

    public function property($slug)
    {
        $this->add_view_class('item');

        $this->set_page_title('Property System');

        if ($property = $this->property->where(['slug' => $slug])->get()) {
            $this->add_data([
                'title'    => 'Viewing Property - ' . $property->get_prop('address'),
                'property' => $property,
                'image'    => $property->get_relationship('image')->get_prop('path'),
            ]);

            $this->get_layout();
            $this->build_view();
        } else {
            $this->error404();
        }
    }

    public function get_properties_list($properties)
    {
        $properties_items = [];

        if (!empty($properties)) {
            foreach ($properties as $property) {
                $properties_items[] = ipsCore::get_part('properties/parts/item', [
                    'address'   => $property->get_prop('address'),
                    'slug'      => $property->get_prop('slug'),
                    'thumbnail' => $property->get_relationship('thumbnail')->get_prop('path'),
                ]);
            }
        }

        return ipsCore::get_part('properties/parts/list', [
            'properties' => $properties_items
        ]);
    }
}
