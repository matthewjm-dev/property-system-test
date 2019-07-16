<?php // Modules Widget

ipsCore::requires_model('module_item');

class blog_widget extends dashboard_widget
{
    protected $widget_title = 'Blog Posts';
    protected $widget_level = 4;

    public function build()
    {
        $this->load_model('admin_module_item', 'module_item', 'blog');

        $blog_posts = $this->module_item->get_all();

        $count_all = count($blog_posts);
        $count_live = 0;
        $count_unlive = 0;
        $count_locked = 0;

        foreach ($blog_posts as $blog_post) {
            if ($blog_post->live) {
                $count_live++;
            } else {
                $count_unlive++;
            }

            if ($blog_post->locked) {
                $count_locked++;
            }
        }

        $this->add_widget_data([
            'url' => '/admin/module/blog/',
            'icon' => 'fas fa-clone',
            'count_all' => $count_all . " Total",
            'count_live' => $count_live . " Live",
            'count_unlive' => $count_unlive . " Un-Live",
            'count_locked' => $count_locked . " Locked",
        ]);
    }

}
