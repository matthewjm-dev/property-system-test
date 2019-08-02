<?php // IPS startup

$uri_get_parts = explode('?', $_SERVER['REQUEST_URI']);
$uri = $uri_get_parts[0];

$uri_parts = explode('/', trim($uri, '/'));
$path_base = str_replace('core', '', __DIR__);
$path_apps = $path_base . 'apps/';

$apps = glob($path_apps . '*/app.ini');
$current_app_key = false;

$apps_configs = [];

/* Find Apps */
if ($apps) {
    $i = 1;
    foreach ($apps as $app_path) {
        $app = parse_ini_file($app_path, true);

        if ($app['app']['uri'] == '') {
            $key = 0;
        } else {
            $key = $i;
            $i++;
        }
        $apps_configs[$key] = $app;
    }
}

/* Find Current App */
if (!empty($apps_configs)) {
    foreach ($apps_configs as $app_key => $apps_config) {
        if ($apps_config['app']['uri'] == $uri_parts[0]) {
            $current_app_key = $app_key;
            break;
        }
    }
} else {
    die('App Config missing');
}
if (!$current_app_key) {
    $current_app_key = 0;
}

/* Get core version */
$core_version = 'core/core_v' . $apps_configs[$current_app_key]['core']['version'] . '/';
$core_file = $path_base . $core_version . 'ips_core.php';

/* Autoload */
require_once($path_base . 'vendor/autoload.php');
require_once($core_file);

/* Statics - Base path */
ipsCore::$path_base = $path_base;
ipsCore::init();

/* Statics - Core path */
ipsCore::$path_core            = ipsCore::$path_base . $core_version;
ipsCore::$path_core_includes   = ipsCore::$path_core . 'includes/';
ipsCore::$path_core_helpers    = ipsCore::$path_core . 'helpers/';

/* Statics - App paths */
ipsCore::$path_apps            = $path_apps;
foreach ($apps_configs as $app_key => $app_config) {
    ipsCore::$apps[$app_key] = new ipsCore_app($app_config);
}
ipsCore::$app = ipsCore::$apps[$current_app_key];
ipsCore::$path_app             = ipsCore::$path_apps . ipsCore::$app->get_directory();
ipsCore::$path_app_helpers     = ipsCore::$path_app . '/helpers/';

/* Statics - URI */
ipsCore::$uri                  = $uri;
ipsCore::$uri_parts            = $uri_parts;
ipsCore::$uri_get              = (isset($uri_get_parts[1]) ? $uri_get_parts[1] : false);

/* Statics - Lib */
ipsCore::$path_libraries       = ipsCore::$path_base . 'libraries/';

/* Statics - Public */
ipsCore::$path_public          = ipsCore::$path_base . 'public/';
ipsCore::$path_public_css      = ipsCore::$path_public . 'css/';
ipsCore::$path_public_js       = ipsCore::$path_public . 'js/';
ipsCore::$path_public_img      = ipsCore::$path_public . 'img/';

/* Statics - Site */
ipsCore::$site_protocol        = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
ipsCore::$site_url             = $_SERVER['SERVER_NAME'];
ipsCore::$site_base            = ipsCore::$site_protocol . '://' . ipsCore::$site_url . '/';

/* Build site */
ipsCore::setup();
ipsCore::build();
ipsCore::display_errors();
ipsCore::render();
