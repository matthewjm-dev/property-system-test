<?php // Modules Widget

ipsCore::requires_model('module_item');

class page_widget extends dashboard_widget
{
    protected $widget_title = 'Pages';

    public function build()
    {
        $this->load_model('admin_module_item', 'module_item', 'page');

        $pages = $this->module_item->get_all();

        $count_all = count($pages);
        $count_live = 0;
        $count_unlive = 0;
        $count_locked = 0;

        foreach ($pages as $page) {
            if ($page->live) {
                $count_live++;
            } else {
                $count_unlive++;
            }

            if ($page->locked) {
                $count_locked++;
            }
        }

        $this->add_widget_data([
            'url' => '/admin/module/pages/',
            'icon' => 'far fa-file',
            'count_all' => $count_all . " Total",
            'count_live' => $count_live . " Live",
            'count_unlive' => $count_unlive . " Un-Live",
            'count_locked' => $count_locked . " Locked",
        ]);
    }

}
