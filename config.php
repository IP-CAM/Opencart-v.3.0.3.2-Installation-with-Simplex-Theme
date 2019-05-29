<?php
// HTTP
define('HTTP_SERVER', 'http://simplex2.dev.it-lab.md/');

// HTTPS
define('HTTPS_SERVER', 'http://simplex2.dev.it-lab.md/');

// DIR
define('DIR_APPLICATION', '/var/www/simplex2/data/www/simplex2.dev.it-lab.md/catalog/');
define('DIR_SYSTEM', '/var/www/simplex2/data/www/simplex2.dev.it-lab.md/system/');
define('DIR_IMAGE', '/var/www/simplex2/data/www/simplex2.dev.it-lab.md/image/');
define('DIR_STORAGE', '/var/www/simplex2/data/www/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'simplex');
define('DB_PASSWORD', '1W7f7K9j');
define('DB_DATABASE', 'simplex');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');