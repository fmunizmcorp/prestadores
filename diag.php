<?php
/**
 * Simple Diagnostic Wrapper
 * This file bypasses .htaccess routing
 */

// Force OPcache reset
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Set ROOT_PATH
define('ROOT_PATH', dirname(__FILE__));

// Include the diagnostic
require ROOT_PATH . '/diagnostic_complete_v7.php';
