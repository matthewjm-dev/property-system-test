<?php // Navigation Helper

ipsCore::requires_model(['navigation']);

class navigation_helper extends ipsCore_controller {

    public function __construct() {
        parent::__construct('navigation_helper', true);
    }

    public function get_nav( $name )
    {
        $this->load_model('navigation');

        $nav_items_array = [];
        $nav_items = $this->navigation->where(['navigation_groups.dbslug' => $name])
            ->where_live()
            ->join(['table' => 'navigation_groups', 'on' => ['group', 'navgrpid']])
            ->get_all();

        if ( $nav_items && !empty($nav_items)) {
            foreach ($nav_items as $nav_item) {
                if ($nav_item->page) {
                    $this->load_model('page', 'nav_page');
                    $this->nav_page->retrieve($nav_item->page, false, false);
                    if ($this->nav_page->live == 1) {
                        $href = $this->nav_page->slug;
                    } else {
                        $href = false;
                    }
                } else {
                    $href = $nav_item->url;
                }

                if ($href) {
                    $nav_items_array[] = [
                        'title' => $nav_item->title,
                        'href' => ipsCore_validate::link($href),
                        'current' => $this->is_current_nav_item($href),
                    ];
                }
            }

            return $nav_items_array;
        }
    }

    private function is_current_nav_item($href) {
        if ($href == ipsCore::$uri || ($href != '/' && strpos(ipsCore::$uri, $href) !== false)) {
            return true;
        }
        return false;
    }

}
