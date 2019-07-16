<?php // Site pages controller

ipsCore::requires_controller('controller');
ipsCore::requires_model('page');

class pages_controller extends site_controller
{

    // Construct
    public function __construct($controller, $additional = false)
    {
        $this->load_model('page');

        parent::__construct($controller, $additional);
    }

    // Methods
    public function index()
    {
        $home_page = $this->get_setting('home_page_id');
        $this->page->retrieve($home_page);

        $this->set_page_title($this->page->title);

        ipsCore::requires_model('service');
        $this->load_model('service');

        $service_items = [];
        $services = $this->service->get_all();
        foreach($services as $service) {
            $service_items[] = [
                'title' => $service->title,
                'snippet' => $service->snippet,
                'href' => $href = '/services/' . $service->slug,
            ];
        }

        if ($background = $this->get_setting('homepage_areas_background')) {
            $background = $this->get_image($this->get_setting('homepage_areas_background'), true)->path;
        } else {
            $background = '';
        }

        $this->add_data([
            'title' => $this->page->title,
            'content' => $this->page->content,
            'homepage_areas_bg' => $background,
            'homepage_areas' => $service_items,
        ]);

        $this->get_layout();
        $this->build_view();
    }

    public function page($slug, $part = false)
    {
        $func_args = func_get_args();
        $slug = ipsCore_validate::stripSlug($func_args[0]);

        $args = array_merge(['slug' => $slug], $this->where_live());

        $method = 'page_' . $slug;
        if (method_exists($this, $method)) {
            $this->$method($func_args);
        } else {
            if ($this->page->retrieve($args)) {

                $this->set_page_title($this->page->title);

                $this->add_data([
                    'class' => $slug,
                    'title' => $this->page->title,
                    'content' => $this->page->get_prop('content'),
                ]);

                $this->get_layout();
                $this->build_view();
            } else {
                $this->error404();
            }
        }
    }

    public function page_contact($func_args) {
        $slug = ipsCore_validate::stripSlug($func_args[0]);

        if ($this->page->retrieve(['slug' => $slug])) {

            ipsCore::requires_helper('submission_form');
            $contact_form = new submission_form('contact_form_submissions');

            if (isset($func_args[1]) && $func_args[1] == 'process') {
                //$send_to = $this->get_setting('contact_form_recipients');
                $send_to = false;
                $success_message = $this->get_setting('contact_form_success');
                $contact_form->process_form($success_message, $send_to);
            } else {
                $this->set_page_title($this->page->title);

                $this->add_data([
                    'class' => $slug,
                    'title' => $this->page->title,
                    'content' => $this->page->get_prop('content'),
                    'contact_email_text' => $this->page->get_prop('contact_email_text'),
                    'contact_email' => $this->get_setting('contact_email'),
                    'contact_phone_text' => $this->page->get_prop('contact_phone_text'),
                    'contact_phone' => $this->get_setting('contact_phone'),
                    'contact_form' => $contact_form->render(),
                ]);

                $this->get_layout();
                $this->set_view('pages/contact');
                $this->build_view();
            }
        } else {
            $this->error404();
        }
    }

}
