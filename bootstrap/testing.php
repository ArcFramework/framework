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

// Define essential wordpress functions
function plugins_url()
{
    return __DIR__;
}

