<?php

/**
 * Remove any double slashes from a URL.
 *
 * @param string $string
 *
 * @return string
 **/
if (!function_exists('rds')) {
    function rds($string)
    {
        return preg_replace('#/+#', '/', $string);
    }
}
