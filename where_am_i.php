<?php
header('Content-Type: text/plain; charset=utf-8');
echo "Current Working Directory: " . getcwd() . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";

echo "\nFiles in current directory:\n";
$files = scandir('.');
foreach (array_slice($files, 0, 20) as $f) {
    if ($f != '.' && $f != '..') {
        echo "  - $f\n";
    }
}
