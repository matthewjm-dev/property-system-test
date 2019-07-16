<?php // Users Widget

ipsCore::requires_model('user');

class user_widget extends dashboard_widget
{
    protected $widget_title = 'Users';

    public function build()
    {
        $this->load_model('admin_user', 'user');

        $this->add_widget_data([
            'url' => '/admin/users/',
            'icon' => 'fas fa-users',
            'count_all' => $this->user->count() . " Users",
        ]);
    }
}
