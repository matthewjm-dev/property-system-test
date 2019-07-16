<?php // Modules Widget

ipsCore::requires_model('module');

class module_widget extends dashboard_widget
{
    protected $widget_title = 'Modules';
    protected $widget_level = 1;

    public function build()
    {
        $this->load_model('admin_module', 'module');

        $this->add_widget_data([
            'url' => '/admin/modules/',
            'icon' => 'fas fa-toolbox',
            'count_all' => $this->module->count() . " Modules",
        ]);
    }

}
