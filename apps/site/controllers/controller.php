<?php // Site main controller

ipsCore::requires_model(['model', 'file', 'property_type', 'property']);
ipsCore::requires_core_helper('validate');
ipsCore::requires_helper('navigation');

class site_controller extends ipsCore_controller
{

    // Construct
    public function __construct($controller, $additional = false)
    {
        if (!$additional) {
            $this->load_model('file');
            $this->load_model('property_type');
            $this->load_model('property');

            $this->add_library(['jquery' => 'min.js']);
            $this->add_stylesheet(['site-lib', 'site']);
            $this->add_script(['site-lib', 'site', 'ipscore']);
        }

        parent::__construct($controller, $additional);
    }

    // Methods
    public function get_layout()
    {
        $this->get_header_data();
    }

    public function get_header_data()
    {
        $this->add_data([
            'site_title'   => 'Property System',
            'header_links' => [
                [
                    'title' => 'Property List',
                    'href'  => '/'
                ],
                [
                    'title' => 'Admin',
                    'href'  => '/admin'
                ]
            ]

        ]);
    }

    public function get_file($file_id, $object = false)
    {
        if ($file = $this->file->get(['afid' => $file_id, 'type' => 'file'])) {
            if ($object) {
                return $file;
            } else {
                return '<a class="file-' . $file_id . '" src="' . $file->path . '" title="' . $file->title . '">' . $file->title . '</a>';
            }
        }

        return false;
    }

    public function get_image($image_id, $object = false)
    {
        if ($file = $this->file->reset()->get(['afid' => $image_id, 'type' => 'image'])) {
            if ($object) {
                return $file;
            } else {
                return '<img src="' . $file->path . '" class="image-' . $image_id . '" alt="' . $file->title . '" />';
            }
        }

        return false;
    }

    public function error404($message = false, $title = false)
    {
        $this->set_page_title(($title ?: '404 Error!'));
        $this->add_data([
            'title'   => ($title ?: '404 Page not found'),
            'content' => ($message ?: 'The requested page could not be found.'),
        ]);

        $this->get_layout();
        $this->set_view('404');
        $this->build_view();
    }

}
