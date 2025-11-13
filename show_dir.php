<?php
header('Content-Type: text/plain');
echo "Current directory: " . __DIR__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "\nDirectory listing:\n";
print_r(scandir(__DIR__));
