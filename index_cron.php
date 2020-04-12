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