<?php
// Script para descompactar via comandos shell
header('Content-Type: text/plain');

echo "=== EXTRACTION VIA SHELL ===\n\n";

$tarballs = [
    'config.tar.gz' => 'config/',
    'src.tar.gz' => 'src/',  
    'public.tar.gz' => 'public/',
    'database.tar.gz' => 'database/'
];

foreach ($tarballs as $tarball => $dest) {
    $filepath = __DIR__ . '/' . $tarball;
    
    if (file_exists($filepath)) {
        echo "Found: $tarball\n";
        echo "Extracting to: $dest\n";
        
        // Usar tar command
        $cmd = "cd " . __DIR__ . " && tar -xzf $tarball 2>&1";
        $output = shell_exec($cmd);
        
        if ($output) {
            echo "Output: $output\n";
        } else {
            echo "✅ Extracted successfully\n";
        }
        
        // Deletar tarball
        unlink($filepath);
        echo "✅ Deleted $tarball\n\n";
    } else {
        echo "❌ Not found: $tarball\n\n";
    }
}

echo "=== EXTRACTION COMPLETE ===\n";
