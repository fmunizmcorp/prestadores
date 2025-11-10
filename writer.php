<?php
/**
 * FILE WRITER - Escreve arquivos diretamente baixando do GitHub
 */
header('Content-Type: text/plain; charset=utf-8');

$branch = 'genspark_ai_developer';
$commit = 'c01e495';
$baseUrl = "https://raw.githubusercontent.com/fmunizmcorp/prestadores/{$commit}";

$files = [
    'src/Models/NotaFiscal.php',
    'src/Models/Projeto.php',
    'src/Models/Atividade.php',
];

echo "FILE WRITER - DEPLOYMENT\n";
echo "════════════════════════════════════════════\n\n";
echo "Current Dir: " . getcwd() . "\n\n";

$success = 0;
$failed = 0;

foreach ($files as $file) {
    echo "[$file]\n";
    
    $url = $baseUrl . '/' . $file;
    echo "  Downloading from GitHub... ";
    
    $content = @file_get_contents($url);
    
    if ($content && strlen($content) > 100) {
        echo "OK (" . number_format(strlen($content)) . " bytes)\n";
        
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "  Created dir: $dir\n";
        }
        
        echo "  Writing... ";
        if (file_put_contents($file, $content)) {
            echo "OK\n";
            chmod($file, 0644);
            $success++;
        } else {
            echo "FAILED\n";
            $failed++;
        }
    } else {
        echo "FAILED (download)\n";
        $failed++;
    }
    echo "\n";
}

echo "════════════════════════════════════════════\n";
echo "SUCCESS: $success / FAILED: $failed\n";

if ($failed === 0) {
    echo "\n✅ ALL FILES DEPLOYED!\n";
    echo "\nClear cache: /clear_cache.php\n";
} else {
    echo "\n⚠ SOME FILES FAILED\n";
}
