<?php // IPS startup

$uri_get_parts = explode('?', $_SERVER['REQUEST_URI']);
$uri = $uri_get_parts[0];

$uri_parts = explode('/', trim($uri, '/'));
$path_base = str_replace('core', '', __DIR__);
$path_apps = $path_base . 'apps/';

$apps = glob($path_apps . '*/config.ini');
$current_app_key = false;

$apps_configs = [];

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

$core_version = 'core/CoreV' . $apps_configs[$current_app_key]['core']['version'] . '/';
$core_file = $path_base . $core_version . 'ips_core.php';

require_once($path_base . 'vendor/autoload.php');
require_once($core_file);

ipsCore::$path_base = $path_base;
ipsCore::init();

foreach ($apps_configs as $app_key => $app_config) {
    ipsCore::$apps[$app_key] = new ipsCore_app($app_config);
}
ipsCore::$app = ipsCore::$apps[$current_app_key];

ipsCore::$uri                  = $uri;
ipsCore::$uri_parts            = $uri_parts;
ipsCore::$uri_get              = (isset($uri_get_parts[1]) ? $uri_get_parts[1] : false);

ipsCore::$path_core            = ipsCore::$path_base . $core_version;
ipsCore::$path_core_includes   = ipsCore::$path_core . 'includes/';
ipsCore::$path_core_helpers    = ipsCore::$path_core . 'helpers/';
ipsCore::$path_apps            = $path_apps;
ipsCore::$path_app             = ipsCore::$path_apps . ipsCore::$app->get_directory();
ipsCore::$path_app_helpers     = ipsCore::$path_app . '/helpers/';
ipsCore::$path_libraries       = ipsCore::$path_base . 'libraries/';
ipsCore::$path_public          = ipsCore::$path_base . 'public/';
ipsCore::$path_public_css      = ipsCore::$path_public . 'css/';
ipsCore::$path_public_js       = ipsCore::$path_public . 'js/';
ipsCore::$path_public_img      = ipsCore::$path_public . 'img/';

ipsCore::$site_protocol        = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
ipsCore::$site_url             = $_SERVER['SERVER_NAME'];
ipsCore::$site_base            = ipsCore::$site_protocol . '://' . ipsCore::$site_url . '/';

ipsCore::setup();
ipsCore::build();
ipsCore::display_errors();
ipsCore::render();
