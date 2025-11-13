<?php
// Check errors and debug output
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Error Check</title></head><body>";
echo "<h1>PHP Error Diagnosis</h1>";

echo "<h2>1. PHP Version</h2>";
echo "<pre>" . phpversion() . "</pre>";

echo "<h2>2. Error Reporting</h2>";
echo "<pre>error_reporting: " . error_reporting() . "</pre>";
echo "<pre>display_errors: " . ini_get('display_errors') . "</pre>";

echo "<h2>3. Autoloader Test</h2>";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "<pre>✅ Autoloader loaded</pre>";
} else {
    echo "<pre>❌ Autoloader NOT FOUND</pre>";
}

echo "<h2>4. Config Test</h2>";
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
    echo "<pre>✅ Config loaded</pre>";
    echo "<pre>BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "</pre>";
} else {
    echo "<pre>❌ Config NOT FOUND</pre>";
}

echo "<h2>5. Database Test</h2>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        $db = Database::getInstance();
        echo "<pre>✅ Database connection OK</pre>";
    } catch (Exception $e) {
        echo "<pre>❌ Database error: " . $e->getMessage() . "</pre>";
    }
} else {
    echo "<pre>❌ Database config NOT FOUND</pre>";
}

echo "<h2>6. Controller Test</h2>";
if (class_exists('App\Controllers\AuthController')) {
    echo "<pre>✅ AuthController class exists</pre>";
} else {
    echo "<pre>❌ AuthController class NOT FOUND</pre>";
}

echo "</body></html>";
?>
