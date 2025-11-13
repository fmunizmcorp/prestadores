<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "=== SIMPLE PATH TEST V11 ===\n\n";

echo "1. Current __DIR__: " . __DIR__ . "\n";
echo "2. Current __FILE__: " . __FILE__ . "\n\n";

echo "3. Does public/ exist? " . (is_dir(__DIR__ . '/public') ? 'YES' : 'NO') . "\n";
echo "4. Does src/ exist? " . (is_dir(__DIR__ . '/src') ? 'YES' : 'NO') . "\n";
echo "5. Does config/ exist? " . (is_dir(__DIR__ . '/config') ? 'YES' : 'NO') . "\n";
echo "6. Does vendor/ exist? " . (is_dir(__DIR__ . '/vendor') ? 'YES' : 'NO') . "\n\n";

echo "7. Does public/index.php exist? " . (file_exists(__DIR__ . '/public/index.php') ? 'YES' : 'NO') . "\n";
echo "8. Does src/Controllers/ exist? " . (is_dir(__DIR__ . '/src/Controllers') ? 'YES' : 'NO') . "\n\n";

if (file_exists(__DIR__ . '/src/Controllers/EmpresaTomadoraController.php')) {
    echo "9. EmpresaTomadoraController.php EXISTS!\n";
    echo "   Size: " . filesize(__DIR__ . '/src/Controllers/EmpresaTomadoraController.php') . " bytes\n";
} else {
    echo "9. EmpresaTomadoraController.php NOT FOUND\n";
}

echo "\n10. Testing require:\n";
try {
    require_once __DIR__ . '/public/index.php';
    echo "    ✅ public/index.php loaded!\n";
} catch (Throwable $e) {
    echo "    ❌ ERROR: " . $e->getMessage() . "\n";
    echo "    File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
