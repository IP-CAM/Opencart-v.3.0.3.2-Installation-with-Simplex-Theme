<?php
ini_set("memory_limit", "1536M");
// Version
define('VERSION', '3.0.3.2');
unset($_COOKIE['currency']);

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');