<?php
// TODO(eric): remove this once we upgrade to PHP 8
if (!function_exists('str_contains')) {
    /**
     * Check if substring is contained in string
     *
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    function str_contains($haystack, $needle)
    {
        return (strpos($haystack, $needle) !== false);
    }
}