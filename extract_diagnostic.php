<?php
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "EXTRACTING DIAGNOSTIC SCRIPTS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$file = 'diagnostic_scripts.tar.gz';

if (file_exists($file)) {
    echo "✓ Found file: $file\n";
    echo "  Current directory: " . getcwd() . "\n\n";
    
    // Extract
    echo "Extracting...\n";
    exec("tar -xzf $file 2>&1", $output, $ret);
    
    if ($ret === 0) {
        echo "✓ Extraction successful!\n\n";
        
        // List extracted files
        echo "Extracted files:\n";
        echo "───────────────────────────────────────────────────────────────\n";
        exec("tar -tzf $file", $files);
        foreach ($files as $f) {
            $exists = file_exists($f) ? '✓' : '✗';
            echo "$exists $f\n";
        }
        
        echo "\n✓ Diagnostic scripts ready!\n";
        echo "\nAccess them at:\n";
        echo "  https://prestadores.clinfec.com.br/check_projetos_table.php\n";
        echo "  https://prestadores.clinfec.com.br/check_atividades_table.php\n";
        echo "  https://prestadores.clinfec.com.br/check_notas_fiscais_table.php\n";
        
    } else {
        echo "✗ Extraction failed!\n";
        echo "Output:\n";
        print_r($output);
    }
} else {
    echo "✗ File not found: $file\n";
    echo "\nFiles in current directory:\n";
    $files = scandir('.');
    foreach ($files as $f) {
        if ($f != '.' && $f != '..') {
            echo "  - $f\n";
        }
    }
}
