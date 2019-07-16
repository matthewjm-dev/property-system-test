<?php // Admin main controller

ipsCore::requires_model(['model', 'user', 'module', 'log']);
ipsCore::requires_core_helper(['form_builder', 'file_manager']);
ipsCore::requires_helper(['extend_form_builder', 'filter_bar']);

class admin_controller extends ipsCore_controller
{
    // Construct
    public function __construct($controller, $additional = false)
    {
        parent::__construct($controller, $additional);

        if (!$additional) {
            $this->load_model('admin_user', 'currentuser');

            if (!$this->currentuser->is_logged_in() && !ipsCore::$functions->is_page('login')) {
                $redir = '';
                if (isset(ipsCore::$uri) && !in_array(ipsCore::$uri, ['/admin', '/admin/login'])) {
                    $redir = '?redir=' . ipsCore::$uri;
                }
                ipsCore::$functions->redirect('admin/login' . $redir);
            } else {
                $this->currentuser->where(ipsCore::$session->read('user_details')['uid'])->retrieve();
            }

            $this->add_library(['jquery' => 'min.js', 'jquery-ui' => ['min.js', 'min.css'], 'ckeditor' => 'js']);
            $this->add_stylesheet(['admin-lib', 'admin']);
            $this->add_script(['admin-lib', 'admin']);

            $this->extend_form_builder();
        }

        if ($this->has_controller_permission_level()) {
            $this->set_error404('error_nopermission');
        }
    }

    // Methods
    public function get_layout()
    {
        $this->get_header();
        $this->get_nav();
        $this->get_flash();
        $this->get_modules_nav();
        $this->get_footer();
    }

    public function get_header_logo() {
        $this->add_data([
            'header_logo_src' => '/img/admin/logo-alt-small.png',
            'header_logo_title' => 'In-Phase Solutions Content Management System',
            'header_logo_cmsver' => 'CMS v1.00',
        ]);
    }

    public function get_header()
    {
        $header_items_dev = [];

        if ($this->currentuser->is_developer()) {
            $header_items_dev = [
                $this->get_nav_item( 'Modules', '/admin/modules', 'fas fa-toolbox'),
                $this->get_nav_item( 'Logs', '/admin/logs', 'fas fa-list-alt'),
                $this->get_nav_item( 'Dev Info', '/admin/pages/developer-information', 'fas fa-info-circle'),
            ];
        }

        if ($this->currentuser->is_user_level(2)) {
            $header_items_dev[] = $this->get_nav_item( 'Configuration', '/admin/configuration', 'fas fa-cog');
        }

        $header_items = [
            $this->get_nav_item('Files', '/admin/files', 'far fa-file-image'),
            $this->get_nav_item('Users', '/admin/users', 'fas fa-users'),
            $this->get_nav_item('Help Center', '/admin/pages/help-center', 'fas fa-question-circle'),
            $this->get_nav_item('Logout', '/admin/login/logout', 'fas fa-sign-in-alt'),
        ];

        /*$header_items = [
            $this->get_nav_item_new( 'Modules', 'admin', 'modules', false, false,'fas fa-toolbox'),
            $this->get_nav_item_new( 'Logs', 'admin', 'logs', false, false, 'fas fa-list-alt'),
            $this->get_nav_item_new( 'Dev Info', 'admin', 'pages', 'developer-information', false, 'fas fa-info-circle'),
            $this->get_nav_item_new( 'Configuration', 'admin', 'configuration', false, false, 'fas fa-cog'),
            $this->get_nav_item_new('Files', 'admin', 'files', false, false, 'far fa-file-image'),
            $this->get_nav_item_new('Users', 'admin', 'users', false, false, 'fas fa-users'),
            $this->get_nav_item_new('Help Center', 'admin', 'pages', 'help-center', false, 'fas fa-question-circle'),
            $this->get_nav_item_new('Logout', 'admin', 'login', 'logout', false, 'fas fa-sign-in-alt'),
        ];*/

        $this->get_header_logo();

        $this->add_data([
            'site_title' => 'Test CMS',
            'header_items' => array_merge($header_items_dev, $header_items),
        ]);
    }

    public function get_nav()
    {
        $nav_items = [
            $this->get_nav_item('Dashboard', '/admin',  'fas fa-home'),
        ];

        $this->add_data(['nav_items' => $nav_items]);
    }

    private function get_nav_item($name, $href, $icon = false, array $classes = [])
    {
        if ($this->is_current_nav_item($href)) {
            $classes[] = 'current';
        }

        return [
            'name' => $name,
            'icon' => $icon,
            'href' => $href,
            'classes' => implode(" ", $classes),
        ];
    }

    private function is_current_nav_item($href) {
        if ($href == ipsCore::$uri || strpos(ipsCore::$uri, $href) !== false) {
            return true;
        }
        return false;
    }

    private function get_nav_item_new($name, $app, $controller, $method, $args, $icon = false, array $classes = [])
    {
        $href = '/' . ($app && $app != '' && $app != '/' ? $app . '/' : '')
                . $controller
                . ($method && $method != '' && $method != '/' ? $method . '/' : '')
                . ($args && $args != '' && $args != '/' ? $args . '/' : '');

        if ($this->is_current_nav_item($href)) {
            $classes[] = 'current';
        }

        //if ($this->has_controller_permission_level($controller)) {
            return [
                'name' => $name,
                'icon' => $icon,
                'href' => $href,
                'classes' => implode(" ", $classes),
            ];
        //}
    }

    public function get_flash()
    {
        $flash_messages = (ipsCore::$session->read('flash_messages') ?: false);
        $this->add_data(['flash_message' => ipsCore::get_part('parts/flash', ['flash_messages' => $flash_messages])]);
        $this->clear_flash();
    }

    public function add_flash($content, $type = 'success')
    {
        $flash_messages = (ipsCore::$session->read('flash_messages') ?: false);

        if (!is_array($flash_messages)) {
            $flash_messages = [];
        }

        $flash_messages[] = ['message' => $content, 'type' => $type];

        ipsCore::$session->write('flash_messages', $flash_messages);
    }

    public function clear_flash()
    {
        ipsCore::$session->write('flash_messages', false);
    }

	public function error404( $message = false) {
		$this->get_layout();
		$this->set_page_title( '404 Error!' );
		$this->set_view('404');

		$this->add_data( [
			'title' => '404 Error!',
			'content' => ($message ?: 'The requested page could not be found.' ),
		] );

		$this->build_view();
	}

    public function error_nopermission() {
        $this->get_layout();
        $this->set_page_title( 'No Permission!' );
        $this->set_view('404');

        $this->add_data( [
            'title' => 'No Permission Error!',
            'content' => 'Sorry, you do not have permission to access that page. If this error is incorrect please contact an administrator for assistance.',
        ] );

        $this->build_view();
    }

    public function has_controller_permission_level($controller = false) {
        if ($controller) {
            $controller .= '_controller';
        } else {
            $controller = get_class($this);
        }

        if (isset($controller::$permission_level)) {
            $controller_level = $controller::$permission_level;

            if (((int)$this->currentuser->level > $controller_level)) {
                return true;
            }
        }
        return false;
    }

    public function get_modules_nav()
    {
        $this->load_model('admin_module', 'nav_modules');

        $modules = $this->nav_modules->where_has_permission()->get_all();
        $nav = '';

        if (!empty($modules)) {
            $nav = $this->get_nav_recursive($modules);
        }

        $this->add_data(['nav_modules' => $nav]);
    }

    private function get_nav_recursive($modules, $current_id = 0)
    {
        $nav = '';

        if (!empty($modules)) {
            if ($current_id == 0) {
                $nav .= '<ul id="' . $modules[0]->get_model_name() . '">';
            }
            foreach ($modules as $module) {
                if ($module->parent == $current_id) {
                    $href = '/admin/module/' . $module->slug;
                    $classes = ($this->is_current_nav_item($href) ? ' class="current"' : '');

                    $nav .= '<li' . $classes . '><a href="' . $href . '">' . (isset($module->icon) ? '<i class="' . $module->icon . '"></i>' : '') . $module->title . '</a>';
                    $sub_nav = $this->get_nav_recursive($modules, $module->mid);
                    if (!empty($sub_nav)) {
                        $nav .= '<div class="menu-sub-item open"><i class="fas fa-bars open"></i><i class="fas fa-times close"></i></div>';
                        $nav .= '<ul>' . $sub_nav . '</ul>';
                    }
                    $nav .= '</li>';
                }
            }
            if ($current_id == 0) {
                $nav .= '</ul>';
            }
        }

        return $nav;
    }

    public function get_footer()
    {
        $footer_text = 'Contact support on: enquiries@inphasesolutions.com';

        $this->add_data(['footer_text' => $footer_text]);
    }

    public function set_breadcrumbs(array $breadcrumbs = [])
    {
        $this->add_data(['breadcrumbs' => ipsCore::get_part('parts/breadcrumbs', ['breadcrumbs' => $breadcrumbs])]);
    }

    public function log($title, $content, $user = false)
    {
        $log = $this->get_model('admin_log', 'log');
        if ($user) {
            $log->user = $user;
        } else {
            if ($this->currentuser->is_logged_in()) {
                $log->user = $this->currentuser->get_id();
            } else {
                $log->user = 0;
            }
        }
        $log->title = $title;
        $log->content = $content;
        $log->created = time();
        if (!$log->save()) {
            ipsCore::add_error('Failed to save log: ' . $title);
        }
    }

    public function sort($type)
    {
        if (isset($_POST['position']) && !empty($_POST['position'])) {
            $name = $type . '_sorting';
            $this->load_model($type, $type . '_sorting');
            foreach ($_POST['position'] as $position => $item) {
                $item = $this->{$name}->get($item);
                $item->position = $position;
                $item->save();
            }
        }
    }

    protected function extend_form_builder()
    {
        ipsCore_form_builder::add_field_type('submodule', [
            'title' => 'Sub Module',
            'type' => 'int',
            'length' => '11',
            'link' => true,
        ]);

        ipsCore_form_builder::add_field_type('image_picker', [
            'title' => 'Image Picker',
            'type' => 'int',
            'length' => '11',
        ]);

        ipsCore_form_builder::add_field_type('image_picker_multi', [
            'title' => 'Multiple Image Picker',
            'type' => 'varchar',
            'length' => '255',
        ]);

        ipsCore_form_builder::add_field_type('file_picker', [
            'title' => 'File Picker',
            'type' => 'int',
            'length' => '11',
        ]);

        ipsCore_form_builder::add_field_type('file_picker_multi', [
            'title' => 'Multiple File Picker',
            'type' => 'varchar',
            'length' => '255',
        ]);

        ipsCore_form_builder::$field_types['file']['unselectable'] = true;
        ipsCore_form_builder::$field_types['image']['unselectable'] = true;
    }

    public function get_list_items($args)
    {
        $filters = $this->filter_bar_init($args['module_slug'], $args['current_page'], $args['submit_location']);

        $args['orderby'] = $filters->get_filter_orderby();
        $args['order'] = $filters->get_filter_order();
        $args['per_page'] = $filters->get_filter_perpage();

        $search = $filters->get_filter_search();
        if ($search && $search != '') {
            $args['model']->where([$args['searchfield'] => ['value' => $search, 'like' => true]]);
        }

        $items = $this->get_filtered_list($args);

        return $items;
    }

    protected function filter_bar_init($module_slug, $current_page = false, $submit_location = false)
    {
        return new filter_bar($module_slug, $current_page, $submit_location);
    }

    public function filter_bar_submit($module_slug)
    {
        $filters = $this->filter_bar_init($module_slug);

        $json_data = ['redirect' => '/admin/module/' . $module_slug . '/' . ($filters->get_current_page() == "1" ? '' : $filters->get_current_page() . '/' )];

        $this->add_data(['json' => $json_data]);

        $this->build_view([/*'layout' => false, */'json' => true]);
    }
}
