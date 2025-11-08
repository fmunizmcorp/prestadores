<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug - Sistema Clinfec</h1>";

echo "<h2>1. PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "ROOT_PATH: " . (defined('ROOT_PATH') ? ROOT_PATH : 'NOT DEFINED') . "<br>";

echo "<h2>2. Diretórios</h2>";
echo "Current dir: " . __DIR__ . "<br>";
echo "Exists src/: " . (file_exists(__DIR__ . '/src') ? 'YES' : 'NO') . "<br>";
echo "Exists config/: " . (file_exists(__DIR__ . '/config') ? 'YES' : 'NO') . "<br>";

echo "<h2>3. Autoload Test</h2>";
require_once __DIR__ . '/public/index.php';
