<?php // Site admin home controller

ipsCore::requires_controller('controller');
ipsCore::requires_model('dashboard');
ipsCore::requires_helper('dashboard_widget');

class dashboard_controller extends admin_controller
{
    // Construct
    public function __construct($controller)
    {
        $this->load_model('admin_dashboard', 'model');
        $this->set_page_title('Admin Dashboard');
        $this->set_view('dashboard');

        parent::__construct($controller);
    }

    // Methods
    public function index()
    {
        $this->get_layout();

        $widgets_html = '';
        $widget_items = dashboard_widget::$available_widgets;

        foreach ( $widget_items as $widget_item ) {
            ipsCore::requires_helper(['widgets/' . $widget_item]);
            $widget = new $widget_item($widget_item);
            if ($this->currentuser->is_user_level($widget->get_level())) {
                $widget->build();
                $widgets_html .= $widget->render();
            }
        }

        $this->add_data([
            'content' => 'Welcome to the admin Dashboard, ' . ipsCore::$session->read('user_details')['username'] . '.<br />From the admin section you can manage the content throughout your website.',
            'widgets_html' => $widgets_html,
        ]);

        $this->build_view();
    }
}
