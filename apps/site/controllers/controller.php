<?php // Site main controller

ipsCore::requires_model(['model', 'setting', 'page', 'file']);
ipsCore::requires_core_helper('validate');
ipsCore::requires_helper('navigation');

class site_controller extends ipsCore_controller
{

    // Construct
    public function __construct($controller, $additional = false)
    {
        if ( !$additional ) {
            $this->load_model('setting', 'setting', 'admin_config_field');
            $this->load_model('file', 'file', 'admin_file');

            $this->add_library(['jquery' => 'min.js', 'https://fonts.googleapis.com/css?family=Orbitron:400,700|Raleway:400,700&display=swap' => false]);
            $this->add_stylesheet(['site-lib', 'site']);
            $this->add_script(['site-lib', 'site']);
        }

        parent::__construct($controller, $additional);
    }

    // Methods
    public function get_layout()
    {
        $this->navigation = new navigation_helper();

        $this->get_header_data();
        $this->get_footer_data();
    }

    public function get_header_data()
    {
        $this->add_data([
            'site_title' => 'Test CMS',
            'header_image' => $this->get_image($this->get_setting('site_logo')),
            'header_links' => $this->navigation->get_nav( 'header_navigation' ),
        ]);
    }

    public function get_footer_data()
    {
        $this->add_data([
            'footer_contact_email' => $this->get_setting('contact_email'),
            'footer_links' => $this->navigation->get_nav( 'footer_navigation' ),
            'footer_copy_text' => '&copy; 2016 In-Phase Solutions',
        ]);
    }

    public function get_setting($dbslug)
    {
        if ($setting = $this->setting->reset()->get(['dbslug' => $dbslug])) {
            return $setting->value;
        }

        return false;
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

    public function error404()
    {
        $this->set_page_title('404 Error!');
        $this->add_data([
            'title' => '404 Page not found',
            'content' => 'The requested page could not be found.',
        ]);

        $this->get_layout();
        $this->set_view('pages/404');
        $this->build_view();
    }

}
