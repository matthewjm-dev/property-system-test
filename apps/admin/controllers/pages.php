<?php // Site admin pages controller

ipsCore::requires_controller('controller');
ipsCore::requires_model('model');

class pages_controller extends admin_controller
{
    // Construct
    public function __construct($controller)
    {
        parent::__construct($controller);

        $this->load_model('admin', 'model', false);

        $this->set_page_title('Admin 404');
    }

    // Methods
    public function index()
    {
        $this->get_layout();

        $this->set_page_title('Page');

        $this->add_data([
            'title' => 'Page Title',
            'content' => 'Page Content',
        ]);

        $this->build_view();
    }

    public function developer_information() {
        if (!$this->currentuser->is_developer()) {
            ipsCore::$functions->redirect('admin');
        }

        $title = 'Developer Information';
        $this->get_layout();
        $this->set_breadcrumbs(['Dashboard' => '/admin', $title => false]);
        $this->set_page_title($title);

        $apps = [];
        $datas = [];
        $paths = [];

        foreach(ipsCore::$apps as $app) {
            $apps[] = [
                'title' => $app->get_name(),
                'directory' => $app->get_directory(),
                'uri' => $app->get_uri(),
                'core_version' => $app->get_core_version(),
                'mailer' => $app->mailer['type'],
            ];
        }

        $datas[] = ['Site Base', 'ipsCore::$site_base', ipsCore::$site_base];
        $datas[] = ['Environment', 'ipsCore::$environment', ipsCore::$environment];

        $paths[] = ['Path Base', 'ipsCore::$path_base', ipsCore::$path_base];
        $paths[] = ['Path Core', 'ipsCore::$path_core', ipsCore::$path_core];
        $paths[] = ['Path Core Includes', 'ipsCore::$path_core_includes', ipsCore::$path_core_includes];
        $paths[] = ['Path Core Helpers', 'ipsCore::$path_core_helpers', ipsCore::$path_core_helpers];
        $paths[] = ['Path Libraries', 'ipsCore::$path_libraries', ipsCore::$path_libraries];
        $paths[] = ['Path Apps', 'ipsCore::$path_apps', ipsCore::$path_apps];
        $paths[] = ['Path App', 'ipsCore::$path_app', ipsCore::$path_app];
        $paths[] = ['Path App Helpers', 'ipsCore::$path_app_helpers', ipsCore::$path_app_helpers];
        $paths[] = ['Path Public', 'ipsCore::$path_public', ipsCore::$path_public];
        $paths[] = ['Path Public CSS', 'ipsCore::$path_public_css', ipsCore::$path_public_css];
        $paths[] = ['Path Public JS', 'ipsCore::$path_public_js', ipsCore::$path_public_js];
        $paths[] = ['Path Public IMG', 'ipsCore::$path_public_img', ipsCore::$path_public_img];

        ob_start();
        phpinfo(1);
        phpinfo(4);
        phpinfo(16);
        phpinfo(32);
        phpinfo(8);
        $phpinfo = ob_get_clean();
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        $phpinfo = "
        <style type='text/css'>
            #phpinfo {}
            #phpinfo pre {margin: 0; font-family: monospace;}
            #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
            #phpinfo a:hover {text-decoration: underline;}
            #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
            #phpinfo .center {text-align: center;}
            #phpinfo .center table {margin: 1em auto; text-align: left;}
            #phpinfo .center th {text-align: center !important;}
            #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
            #phpinfo h1 {font-size: 150%;}
            #phpinfo h2 {font-size: 125%;}
            #phpinfo .p {text-align: left;}
            #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
            #phpinfo .h {background-color: #99c; font-weight: bold;}
            #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
            #phpinfo .v i {color: #999;}
            #phpinfo img {float: right; border: 0;}
            #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
        </style>
        <div id='phpinfo'>" . $phpinfo . "</div>";

        $this->add_data([
            'title' => $title,
            'content' => 'This page contains various sections of information showing the internal variables of ipsCore and PHP, for debugging purposes.',
            'apps' => $apps,
            'apps_info' => 'Currently registered apps on this ipsCore installation',
            'paths' => $paths,
            'paths_info' => 'ipsCore Path variables showing the directory structure of the system',
            'datas' => $datas,
            'datas_info' => 'Other general data included within ipsCore',
            'phpinfo' => $phpinfo,
            'phpinfo_info' => 'PHP and server information provided by phpinfo()',
        ]);

        $this->set_view('dev_info');
        $this->build_view();
    }

    public function help_center($page = 'general') {
        if (!$page) {
            $page = 'general';
        }
        $title = 'Help Center';
        $this->get_layout();
        $this->set_breadcrumbs(['Dashboard' => '/admin', $title => false]);
        $this->set_page_title($title);

        $help_pages = [
            'general' => [
                'title' => 'General Help',
                'icon' => '',
                'content' => [
                    'overview' => [
                        'title' => false,
                        'content' => 'The help center provides basic information and instructions on using the In-Phase Solutions CMS. If something you are looking for isn\'t here, please get in touch for further assistance.',
                    ],
                ],
            ],
            'navigation' => [
                'title' => 'Navigation',
                'icon' => '',
                'content' => [
                    'overview' => [
                        'title' => false,
                        'content' => 'Navigation of the IPS CMS has been designed to be as user friendly as possible. However due to the nature of a system engineered to provide maximum content edibility and functionality it\'s easy to become overwhelmed',
                    ],
                    'adding' => [
                        'title' => 'Dashboard',
                        'content' => 'The first like found on the left hand side of the header is the "Dashboard", clicking this will return you to the main Dashboard landing page of the CMS.',
                    ],
                    'editing' => [
                        'title' => 'Modules',
                        'content' => 'After the Dashboard link, the following items are the "Modules" specific to your application. These Modules are the main data stores for the editable areas of your application, allowing you to add, edit and delete content items.',
                    ],
                    'removing' => [
                        'title' => 'Other',
                        'content' => 'The links found at the top right of the header are useful links to other areas of the CMS which you may need, including user management, file management / upload, configuration settings, this help center and the logout button, allowing you to sign yourself out of the CMS.',
                    ],
                ],
            ],
            'users' => [
                'title' => 'User Management',
                'icon' => '',
                'content' => [
                    'overview' => [
                        'title' => false,
                        'content' => '',
                    ],
                    'adding' => [
                        'title' => 'Adding a new User',
                        'content' => '',
                    ],
                    'editing' => [
                        'title' => 'Editing a User',
                        'content' => '',
                    ],
                    'removing' => [
                        'title' => 'Deleting a User',
                        'content' => '',
                    ],
                ],
            ],
            'dashboard' => [
                'title' => 'Your Dashboard',
                'icon' => '',
                'content' => [
                    'overview' => [
                        'title' => false,
                        'content' => 'Your dashboard is the main landing page of the CMS. Here you can see an overview / statistics of your content as well as quick links to manage content throughout the CMS.',
                    ],
                    'widgets' => [
                        'title' => 'Widgets',
                        'content' => 'The widgets shown on the dashboard give you a brief overview of specific modules or other data stores in your Application. These can be added / removed in request, so please get in touch with us if you\'d like to discuss adding or removing modules that you think would help your efficiency when using the CMS.',
                    ],
                ],
            ],
            'modules' => [
                'title' => 'Modules / Content',
                'icon' => '',
                'content' => [
                    'overview' => [
                        'title' => false,
                        'content' => '',
                    ],
                    'adding' => [
                        'title' => '',
                        'content' => '',
                    ],
                    'editing' => [
                        'title' => '',
                        'content' => '',
                    ],
                    'removing' => [
                        'title' => '',
                        'content' => '',
                    ],
                    'live' => [
                        'title' => '',
                        'content' => '',
                    ],
                    'locked' => [
                        'title' => '',
                        'content' => '',
                    ],
                ],
            ],
            'configuration' => [
                'title' => 'Configuration Settings',
                'icon' => '',
                'content' => [
                    'overview' => [
                        'title' => false,
                        'content' => 'Configuration settings are global options that are not related to a specific area, or can be applied to multiple places. Most of these will not regularly need to be changed, but are in place for editing if required.',
                    ],
                    'groups' => [
                        'title' => '',
                        'content' => '',
                    ],
                    'change_setting' => [
                        'title' => 'Changing a setting',
                        'content' => '',
                    ],
                ],
            ],
        ];

        if (array_key_exists($page, $help_pages)) {
            $navigation = [];
            foreach ($help_pages as $help_page_key => $help_page) {
                $navigation[] = [
                    'title' => $help_page['title'],
                    'icon' => $help_page['icon'],
                    'href' => $help_page_key,
                    'current' => ($help_page_key == $page ? true : false),
                ];
            }

            $this->add_data([
                'navigations' => $navigation,
                'page_url' => '/admin/pages/help-center/',
                'title' => $title . ' - ' . $help_pages[$page]['title'],
                'contents' => $help_pages[$page]['content'],
            ]);

            $this->set_view('help_center');
            $this->build_view();
        } else {
            $this->error404();
        }
    }
}
