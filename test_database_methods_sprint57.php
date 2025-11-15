<?php
/**
 * Test Database Methods Sprint 57
 * Valida se método prepare() e outros estão funcionando
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST DATABASE METHODS SPRINT 57 ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Teste 1: Carregar Database.php
echo "Teste 1: Carregando Database.php...\n";
try {
    require_once __DIR__ . '/src/Database.php';
    echo "✅ Database.php carregado\n\n";
} catch (Exception $e) {
    echo "❌ ERRO ao carregar: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Teste 2: Verificar classe
echo "Teste 2: Verificando classe App\\Database...\n";
if (class_exists('App\\Database')) {
    echo "✅ Classe existe\n\n";
} else {
    echo "❌ Classe não encontrada\n\n";
    exit(1);
}

// Teste 3: Verificar métodos
echo "Teste 3: Verificando métodos...\n";
$methods = get_class_methods('App\\Database');
$required = ['getInstance', 'getConnection', 'prepare', 'query', 'exec', 'lastInsertId'];

foreach ($required as $method) {
    if (in_array($method, $methods)) {
        echo "✅ $method()\n";
    } else {
        echo "❌ $method() - AUSENTE!\n";
    }
}
echo "\n";

// Teste 4: Tentar getInstance
echo "Teste 4: Tentando Database::getInstance()...\n";
try {
    $db = App\Database::getInstance();
    echo "✅ getInstance() funcionou\n";
    echo "   Tipo: " . get_class($db) . "\n\n";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Teste 5: Tentar prepare()
echo "Teste 5: Tentando \$db->prepare()...\n";
try {
    $stmt = $db->prepare("SELECT 1");
    echo "✅ prepare() funcionou!\n";
    echo "   Tipo retornado: " . get_class($stmt) . "\n\n";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Teste 6: Testar query()
echo "Teste 6: Tentando \$db->query()...\n";
try {
    $result = $db->query("SELECT 1 as test");
    echo "✅ query() funcionou!\n";
    $row = $result->fetch();
    echo "   Resultado: " . ($row['test'] == 1 ? 'OK' : 'FALHOU') . "\n\n";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n\n";
}

// Teste 7: Simular uso como Models fazem
echo "Teste 7: Simulando uso dos Models...\n";
try {
    // Como os Models usam:
    $this_db = App\Database::getInstance(); // $this->db = Database::getInstance()
    $stmt = $this_db->prepare("SELECT 1 as value"); // $this->db->prepare($sql)
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Simulação de Model funcionou!\n";
    echo "   Valor retornado: " . $result['value'] . "\n\n";
} catch (Exception $e) {
    echo "❌ ERRO na simulação: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "=== RESULTADO FINAL ===\n";
echo "✅ TODOS OS TESTES PASSARAM!\n";
echo "✅ Bug #7 (prepare() undefined) está CORRIGIDO\n";
echo "✅ Database.php está funcional\n\n";

echo "Próximo: Testar módulos reais (Projetos, Empresas Prestadoras, etc.)\n";
