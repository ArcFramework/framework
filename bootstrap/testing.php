<?php

/**
* PHPUnit bootstrap file
*
* @package arc/framework
*/

// First we need to load the composer autoloader so we can use WP Mock
require_once __DIR__ . '/../vendor/autoload.php';

// // Now call the bootstrap method of WP Mock
WP_Mock::bootstrap();

$_SERVER['PLUGIN_URI'] = 'test.dev/wp-content/plugins/test/';
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_COLLATE', '');

// Define essential wordpress functions
function plugins_url()
{
    return __DIR__;
}

