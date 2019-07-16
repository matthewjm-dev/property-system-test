<?php // Add dashboard widget

class dashboard_widget extends ipsCore_helper
{

    public static $available_widgets = [
        'module_widget',
        'file_widget',
        'user_widget',
        'page_widget',
        'contact_widget',
    ];

    protected $widget_name;
    protected $widget_title;
    protected $widget_level = 4;
    protected $widget_data = [];

    // Getters

    public function get_name()
    {
        return $this->widget_name;
    }

    public function get_title()
    {
        return $this->widget_title;
    }

    public function get_level()
    {
        return $this->widget_level;
    }

    // Construct

    public function __construct($name, $title = false)
    {
        $this->widget_name = $name;
        if ($title) {
            $this->widget_title = $title;
        }
    }

    // Functions

    public function add_widget_data($data, $value = false)
    {
        if (!is_array($data)) {
            $data = [$data => $value];
        }
        $this->widget_data = array_merge($data, $this->widget_data);
    }

    public function render()
    {
        $data = array_merge([
            'widget_name' => $this->widget_name,
            'widget_title' => $this->widget_title,
        ], $this->widget_data);

        return ipsCore::get_part('widgets/' . $this->widget_name, $data);
    }

}
