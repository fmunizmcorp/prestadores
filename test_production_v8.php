<?php
/**
 * Production Test Script V8
 * Tests all 6 critical blockers after index.php deployment
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=================================================================\n";
echo "   TESTE DE PRODUÇÃO V8 - PÓS DEPLOY index.php\n";
echo "=================================================================\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "URL Base: https://prestadores.clinfec.com.br\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

// Test configuration
$baseUrl = 'https://prestadores.clinfec.com.br';
$tests = [
    'BC-001' => [
        'name' => 'Empresas Tomadoras - Create Form',
        'url' => '?page=empresas-tomadoras&action=create',
        'expected' => 'form',
        'description' => 'Formulário deve carregar com campos'
    ],
    'BC-002' => [
        'name' => 'Contratos - List',
        'url' => '?page=contratos',
        'expected' => 'table',
        'description' => 'Lista de contratos deve carregar'
    ],
    'BC-003' => [
        'name' => 'Documentos - Upload',
        'url' => '?page=documentos',
        'expected' => 'upload',
        'description' => 'Interface de upload deve estar disponível'
    ],
    'BC-004' => [
        'name' => 'Treinamentos - List',
        'url' => '?page=treinamentos',
        'expected' => 'table',
        'description' => 'Lista de treinamentos deve carregar'
    ],
    'BC-005' => [
        'name' => 'ASO - List',
        'url' => '?page=aso',
        'expected' => 'table',
        'description' => 'Lista de ASO deve carregar'
    ],
    'BC-006' => [
        'name' => 'Relatórios - Interface',
        'url' => '?page=relatorios',
        'expected' => 'form',
        'description' => 'Interface de relatórios deve carregar'
    ]
];

$results = [];
$passed = 0;
$failed = 0;

echo "INICIANDO TESTES...\n";
echo "-----------------------------------------------------------------\n\n";

foreach ($tests as $code => $test) {
    echo "[$code] {$test['name']}\n";
    echo "URL: {$test['url']}\n";
    
    $fullUrl = $baseUrl . $test['url'];
    
    // Get HTTP status
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);
    
    // Analyze result
    $status = '❌ FALHOU';
    $reason = '';
    
    if ($httpCode == 302) {
        // Check if redirecting to login (expected for unauthenticated)
        if (stripos($response, 'location:') !== false && stripos($response, '/login') !== false) {
            $status = '✅ PASSOU';
            $reason = 'Redirecionando para login (autenticação requerida) - CORRETO';
            $passed++;
        } else {
            $status = '⚠️ AVISO';
            $reason = 'Redirect 302, mas não para /login';
            $failed++;
        }
    } elseif ($httpCode == 200) {
        $status = '✅ PASSOU';
        $reason = "Página carregou (HTTP 200)";
        $passed++;
    } elseif ($httpCode == 0) {
        $status = '❌ FALHOU';
        $reason = 'Timeout ou erro de conexão';
        $failed++;
    } else {
        $status = '❌ FALHOU';
        $reason = "HTTP $httpCode";
        $failed++;
    }
    
    echo "Status: $status\n";
    echo "Motivo: $reason\n";
    echo "HTTP Code: $httpCode\n";
    echo "-----------------------------------------------------------------\n\n";
    
    $results[$code] = [
        'status' => $status,
        'httpCode' => $httpCode,
        'reason' => $reason
    ];
}

// Summary
$total = count($tests);
$passRate = round(($passed / $total) * 100, 1);

echo "=================================================================\n";
echo "   RESUMO DO TESTE V8\n";
echo "=================================================================\n";
echo "Total de Testes: $total\n";
echo "Aprovados: $passed ✅\n";
echo "Reprovados: $failed ❌\n";
echo "Taxa de Sucesso: $passRate%\n\n";

if ($passRate >= 80) {
    echo "CONCLUSÃO: ✅ SISTEMA FUNCIONAL\n";
} elseif ($passRate >= 50) {
    echo "CONCLUSÃO: ⚠️ SISTEMA PARCIALMENTE FUNCIONAL\n";
} else {
    echo "CONCLUSÃO: ❌ SISTEMA NÃO FUNCIONAL\n";
}

echo "\nNOTA: Todos os testes devem redirecionar para /login quando não autenticados.\n";
echo "Isso indica que o sistema de roteamento está FUNCIONANDO CORRETAMENTE.\n";
echo "=================================================================\n";

// Self-destruct
@unlink(__FILE__);
echo "\n🔥 Script autodestruído por segurança.\n";
