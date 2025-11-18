<?php
// Sprint 33 Deployment
$packagePath = '/home/u673902663/domains/clinfec.com.br/public_html/deploy_sprint33_complete.tar.gz';
$extractTo = '/home/u673902663/domains/clinfec.com.br/public_html/prestadores';

if (file_exists($packagePath)) {
    try {
        $phar = new PharData($packagePath);
        $phar->extractTo($extractTo, null, true);
        
        // Apply Sprint 31 fixes
        $oldIndex = $extractTo . '/public/index.php';
        if (file_exists($oldIndex)) {
            rename($oldIndex, $oldIndex . '.OLD');
        }
        
        $newIndex = $extractTo . '/public/index_sprint31.php';
        if (file_exists($newIndex)) {
            copy($newIndex, $extractTo . '/public/index.php');
        }
        
        $migration = $extractTo . '/src/DatabaseMigration.php';
        if (file_exists($migration)) {
            rename($migration, $migration . '.DISABLED');
        }
        
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        header("Location: https://prestadores.clinfec.com.br");
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Package not found at: $packagePath");
}
