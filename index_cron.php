<?php
// Version
define('VERSION', '3.0.3.2');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Configuration
if (is_file(__DIR__ . '/config.php')) {
	require_once(__DIR__ . '/config.php');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');
$registry = new Registry();

// loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Event
$event = new Event($registry);
$registry->set('event', $event);

// Config
$config = new Config();
$registry->set('config', $config);
$config->load('default');
$config->load('catalog');
// DB
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

// Settings
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting`  ORDER BY store_id ASC");

foreach ($query->rows as $result) {
	if (!$result['serialized']) {
		$config->set($result['key'], $result['value']);
	} else {
		$config->set($result['key'], json_decode($result['value'], true));
	}
}
// Cache
$cache = new Cache('apc');
$registry->set('cache', $cache);
// Language
$language = new Language($config->get('language_directory'));
$registry->set('language', $language);
// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		foreach ($value as $priority => $action) {
			$event->register($key, new Action($action), $priority);
		}
	}
}

$registry->get('load')->model('setting/event');

$results = $registry->get('model_setting_event')->getEvents();

foreach ($results as $result) {
	$event->register(substr($result['trigger'], strpos($result['trigger'], '/') + 1), new Action($result['action']), $result['sort_order']);
}
$registry->set('currency', new Cart\Currency($registry));
// Tax
$registry->set('tax', new Cart\Tax($registry));

if ($config->get('config_tax_default') == 'shipping') {
	$registry->get('tax')->setShippingAddress($config->get('config_country_id'), $config->get('config_zone_id'));
}

if ($config->get('config_tax_default') == 'payment') {
	$registry->get('tax')->setPaymentAddress($config->get('config_country_id'), $config->get('config_zone_id'));
}

$registry->get('tax')->setStoreAddress($config->get('config_country_id'), $config->get('config_zone_id'));

$loader->language('account/account');


// If the default theme is selected we need to know which directory its pointing to
if ($config->get('config_theme') == 'default') {
	$theme = $config->get('theme_default_directory');
} else {
	$theme = $config->get('config_theme');
}

// If there is a theme override we should get it
$registry->get('load')->model('design/theme');

$theme_info = $registry->get('model_design_theme')->getTheme('', $theme);

$config->set('template_engine', 'twig');
// Document
$registry->set('document', new Document());

// Config Autoload
if ($config->has('config_autoload')) {
	foreach ($config->get('config_autoload') as $value) {
		$loader->config($value);
	}
}
// Customer
$customer = new Cart\Customer($registry);
$registry->set('customer', $customer);
