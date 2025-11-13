<?php
/**
 * Lê os logs de erro do PHP para diagnóstico
 */
header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "PHP ERROR LOGS - ÚLTIMAS ENTRADAS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Possíveis localizações de log
$possible_logs = [
    ini_get('error_log'),
    '/var/log/php_errors.log',
    '/var/log/php-fpm/error.log',
    __DIR__ . '/php_errors.log',
    __DIR__ . '/error_log',
    __DIR__ . '/../logs/php_errors.log',
    __DIR__ . '/../error_log',
    '/home/u673902663/logs/php_errors.log',
    '/home/u673902663/domains/clinfec.com.br/logs/php_errors.log',
    '/opt/alt/php83/var/log/php-fpm/error.log',
];

echo "Configuração PHP:\n";
echo "- error_log: " . ini_get('error_log') . "\n";
echo "- log_errors: " . (ini_get('log_errors') ? 'Sim' : 'Não') . "\n";
echo "- display_errors: " . (ini_get('display_errors') ? 'Sim' : 'Não') . "\n";
echo "- error_reporting: " . ini_get('error_reporting') . "\n\n";

echo "Procurando por arquivos de log:\n";
$found_logs = [];
foreach ($possible_logs as $log_path) {
    if ($log_path && file_exists($log_path) && is_readable($log_path)) {
        $found_logs[] = $log_path;
        echo "✅ Encontrado: $log_path\n";
    }
}

if (empty($found_logs)) {
    echo "\n❌ Nenhum arquivo de log acessível encontrado\n";
    echo "\nTentando criar erro intencional para descobrir localização...\n";
    
    // Gerar erro intencional
    @trigger_error("TEST ERROR FROM read_php_errors.php", E_USER_WARNING);
    
    echo "Erro de teste gerado. Verifique a localização do log na sua hospedagem.\n";
} else {
    echo "\n" . count($found_logs) . " arquivo(s) de log encontrado(s)\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    foreach ($found_logs as $log_path) {
        echo "\n📄 LOG: $log_path\n";
        echo "─────────────────────────────────────\n";
        echo "Tamanho: " . filesize($log_path) . " bytes\n";
        echo "Última modificação: " . date('Y-m-d H:i:s', filemtime($log_path)) . "\n\n";
        
        // Ler últimas 50 linhas
        $lines = file($log_path);
        if ($lines === false) {
            echo "❌ Erro ao ler arquivo\n";
            continue;
        }
        
        $total_lines = count($lines);
        $start = max(0, $total_lines - 100); // Últimas 100 linhas
        
        echo "Exibindo últimas " . ($total_lines - $start) . " linhas de $total_lines totais:\n\n";
        
        for ($i = $start; $i < $total_lines; $i++) {
            // Filtrar apenas erros relevantes (Projetos, Atividades, NotaFiscal)
            $line = $lines[$i];
            if (stripos($line, 'projeto') !== false || 
                stripos($line, 'atividade') !== false || 
                stripos($line, 'notafiscal') !== false ||
                stripos($line, 'database') !== false ||
                stripos($line, 'fatal') !== false ||
                stripos($line, 'error') !== false) {
                echo $line;
            }
        }
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "FIM DOS LOGS\n";
echo "═══════════════════════════════════════════════════════════\n";
