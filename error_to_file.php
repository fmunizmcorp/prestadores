<?php
/**
 * Captura erro e grava em arquivo
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $log = date('Y-m-d H:i:s') . " | $errstr | $errfile:$errline\n";
    file_put_contents(__DIR__ . '/error_log.txt', $log, FILE_APPEND);
});

set_exception_handler(function($e) {
    $log = date('Y-m-d H:i:s') . " | EXCEPTION: " . $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine() . "\n";
    $log .= "Trace: " . $e->getTraceAsString() . "\n\n";
    file_put_contents(__DIR__ . '/error_log.txt', $log, FILE_APPEND);
});

header('Content-Type: text/plain');
echo "Testing with error logging to file...\n\n";

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing Projeto Model...\n";
try {
    $projeto = new \App\Models\Projeto();
    $result = $projeto->all([], 1, 1);
    echo "✅ SUCCESS: " . count($result) . " results\n";
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/error_log.txt', 
        "PROJETO ERROR: " . $e->getMessage() . "\n" .
        "File: " . $e->getFile() . ":" . $e->getLine() . "\n" .
        "Trace: " . $e->getTraceAsString() . "\n\n",
        FILE_APPEND
    );
}

echo "\nCheck error_log.txt for details\n";
