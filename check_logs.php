<?php
header('Content-Type: text/plain');

$log_files = [
    'logs/error.log',
    'logs/php_errors.log',
    'error_log',
    '../error_log',
];

foreach ($log_files as $log) {
    if (file_exists($log)) {
        echo "=== $log ===\n";
        echo tail_file($log, 50);
        echo "\n\n";
    }
}

function tail_file($file, $lines = 50) {
    $data = file($file);
    return implode("", array_slice($data, -$lines));
}
