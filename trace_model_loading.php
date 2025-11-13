<?php
/**
 * Trace Model Loading - Descobre de onde os Models são carregados
 */

// Forçar exibição de erros
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Limpar OPcache primeiro
if (function_exists('opcache_reset')) {
    opcache_reset();
}

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "TRACE MODEL LOADING - Descobrir origem dos Models\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Informações básicas
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Path: " . __FILE__ . "\n";
echo "Working Dir: " . getcwd() . "\n\n";

// Carregar autoloader
echo "─────────────────────────────────────\n";
echo "Loading Autoloader\n";
echo "─────────────────────────────────────\n";

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "✅ Found: $autoloadPath\n";
    require_once $autoloadPath;
    echo "✅ Loaded\n\n";
} else {
    echo "❌ Not found: $autoloadPath\n";
    die();
}

// Tentar carregar Projeto e mostrar o arquivo REAL que foi carregado
echo "─────────────────────────────────────\n";
echo "Tracing Projeto Model Loading\n";
echo "─────────────────────────────────────\n";

try {
    // Antes de instanciar, verificar se classe existe
    if (class_exists('App\\Models\\Projeto', true)) {
        echo "✅ Class App\\Models\\Projeto exists\n";
        
        // Usar Reflection para descobrir o arquivo REAL
        $reflection = new ReflectionClass('App\\Models\\Projeto');
        $filename = $reflection->getFileName();
        
        echo "📄 Loaded from: $filename\n";
        echo "   File size: " . filesize($filename) . " bytes\n";
        echo "   Modified: " . date('Y-m-d H:i:s', filemtime($filename)) . "\n";
        
        // Verificar se extende BaseModel
        $parent = $reflection->getParentClass();
        if ($parent) {
            echo "⚠️  PROBLEM: Extends " . $parent->getName() . "\n";
            echo "   Parent file: " . $parent->getFileName() . "\n";
        } else {
            echo "✅ No parent class (correct)\n";
        }
        
        // Mostrar primeiras linhas do arquivo
        echo "\n   First 15 lines of loaded file:\n";
        $lines = file($filename);
        for ($i = 0; $i < min(15, count($lines)); $i++) {
            echo "   " . ($i + 1) . ": " . rtrim($lines[$i]) . "\n";
        }
        
    } else {
        echo "❌ Class App\\Models\\Projeto does not exist\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n─────────────────────────────────────\n";
echo "Tracing Atividade Model Loading\n";
echo "─────────────────────────────────────\n";

try {
    if (class_exists('App\\Models\\Atividade', true)) {
        echo "✅ Class App\\Models\\Atividade exists\n";
        
        $reflection = new ReflectionClass('App\\Models\\Atividade');
        $filename = $reflection->getFileName();
        
        echo "📄 Loaded from: $filename\n";
        echo "   File size: " . filesize($filename) . " bytes\n";
        echo "   Modified: " . date('Y-m-d H:i:s', filemtime($filename)) . "\n";
        
        $parent = $reflection->getParentClass();
        if ($parent) {
            echo "⚠️  PROBLEM: Extends " . $parent->getName() . "\n";
        } else {
            echo "✅ No parent class (correct)\n";
        }
    } else {
        echo "❌ Class does not exist\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n─────────────────────────────────────\n";
echo "Checking for BaseModel\n";
echo "─────────────────────────────────────\n";

if (class_exists('App\\Models\\BaseModel', false)) {
    echo "⚠️  WARNING: BaseModel EXISTS (should not!)\n";
    $reflection = new ReflectionClass('App\\Models\\BaseModel');
    echo "   Loaded from: " . $reflection->getFileName() . "\n";
} else {
    echo "✅ BaseModel does not exist (correct)\n";
}

echo "\n─────────────────────────────────────\n";
echo "Listing /src/Models directory\n";
echo "─────────────────────────────────────\n";

$modelsDir = __DIR__ . '/src/Models';
if (is_dir($modelsDir)) {
    $files = scandir($modelsDir);
    echo "Files in $modelsDir:\n";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $fullPath = $modelsDir . '/' . $file;
            $size = filesize($fullPath);
            $mtime = date('Y-m-d H:i:s', filemtime($fullPath));
            echo "  - $file ($size bytes, modified: $mtime)\n";
        }
    }
} else {
    echo "❌ Directory not found: $modelsDir\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "END OF TRACE\n";
echo "═══════════════════════════════════════════════════════════\n";
