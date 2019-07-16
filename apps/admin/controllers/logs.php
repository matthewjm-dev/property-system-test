<?php // Site admin home controller

ipsCore::requires_controller('controller');
ipsCore::requires_model(['log']);

class logs_controller extends admin_controller
{
    protected static $permission_level = 2;
    protected $per_page = 30;

    // Construct
    public function __construct($controller, $additional = false)
    {
        parent::__construct($controller, $additional);

        $this->load_model('admin_log', 'log');
        $this->load_model('admin_user', 'user');

        $this->set_page_title('View Logs');
    }

    // Methods
    public function index($current_page)
    {
        $this->get_layout();

        $this->set_breadcrumbs(['Dashboard' => '/admin', 'Logs' . ($current_page > 1 ? ' ( Page ' . $current_page . ' )' : '') => false]);

        $logs_data = $this->get_filtered_list(['model' => $this->log, 'current_page' => $current_page, 'where' => false, 'per_page' => $this->per_page]);
        $logs = [];

        if (!empty($logs_data)) {
            foreach ($logs_data as $log_data) {
                $user = $this->user->reset()->get($log_data->user);

                $logs[] = [
                    'alid' => $log_data->alid,
                    'created' => date('d/m/Y h:i:s', $log_data->created),
                    'user' => ($user ? $user->username : 'Invalid User'),
                    'title' => $log_data->title,
                ];
            }
        }

        $this->add_data([
            'title' => 'View Logs',
            'content' => 'View Admin Logs',
            'view_log_href' => '/admin/logs/view/',
            'view_log_text' => 'View Log',
            'logs' => $logs,
        ]);

        $this->build_view();

    }

    public function view($alid = false)
    {
        if ($alid && $this->log->retrieve($alid)) {
            $this->get_layout();

            $this->set_breadcrumbs(['Dashboard' => '/admin', 'Logs' => '/admin/logs/']);

            $user = $this->currentuser->get($this->log->user);

            $this->add_data([
                'title' => 'View Logs',
                'content' => 'View Admin Logs',
                'created' => date('d/m/Y h:i:s', $this->log->created),
                'user' => ($user ? $user->username : 'Invalid User'),
                'title' => $this->log->title,
                'content' => $this->log->content,
            ]);

            $this->build_view();
        } else {
            $this->index();
        }
    }

}
