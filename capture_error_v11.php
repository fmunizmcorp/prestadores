<?php
// Force error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Capture output
ob_start();

echo "Starting test...\n";

try {
    // Set up environment
    $_GET['page'] = 'empresas-tomadoras';
    $_GET['action'] = 'index';
    
    echo "Including public/index.php...\n";
    
    // Include the main index
    require __DIR__ . '/public/index.php';
    
} catch (Throwable $e) {
    echo "\n\nERROR CAUGHT:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

$output = ob_get_clean();

// Save to file
file_put_contents(__DIR__ . '/error_capture_v11.txt', $output);

// Output
header('Content-Type: text/plain');
echo $output;
?>
