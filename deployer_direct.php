<?php
/**
 * DEPLOYER DIRETO - Baixa arquivos individuais do GitHub
 * Usa file_get_contents para cada arquivo importante
 */
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300); // 5 minutos

echo "═══════════════════════════════════════════════════════════\n";
echo "DEPLOYMENT DIRETO - ARQUIVO POR ARQUIVO\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$branch = 'genspark_ai_developer';
$commit = '5e068b0';
$baseUrl = "https://raw.githubusercontent.com/fmunizmcorp/prestadores/{$commit}";

// Arquivos críticos para deploy
$files = [
    'src/Models/NotaFiscal.php',
    'src/Models/Projeto.php',
    'src/Models/Atividade.php',
];

echo "[1/3] Criando backup...\n";
$backup = 'backup_' . date('YmdHis') . '.tar.gz';
@exec("tar -czf $backup src/Models/NotaFiscal.php src/Models/Projeto.php src/Models/Atividade.php 2>&1", $out, $ret);
echo ($ret === 0 ? "✓" : "⚠") . " Backup: $backup\n\n";

echo "[2/3] Baixando e aplicando arquivos...\n";
$success = 0;
$failed = 0;

foreach ($files as $file) {
    echo "  Processando: $file ... ";
    
    $url = $baseUrl . '/' . $file;
    $content = @file_get_contents($url);
    
    if ($content && strlen($content) > 100) {
        // Criar diretório se não existir
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Salvar arquivo
        if (file_put_contents($file, $content)) {
            echo "✓ (" . number_format(strlen($content)) . " bytes)\n";
            $success++;
        } else {
            echo "✗ Erro ao salvar\n";
            $failed++;
        }
    } else {
        echo "✗ Erro ao baixar\n";
        $failed++;
    }
}

echo "\n[3/3] Ajustando permissões...\n";
@exec("chmod 644 src/Models/*.php 2>&1");
echo "✓ Permissões ajustadas\n\n";

echo "═══════════════════════════════════════════════════════════\n";
if ($failed === 0) {
    echo "✅ DEPLOYMENT COMPLETO!\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    echo "Arquivos deployados: $success\n";
    echo "Falhas: $failed\n\n";
    echo "Limpe o cache: https://prestadores.clinfec.com.br/clear_cache.php\n";
    echo "Teste as rotas: https://prestadores.clinfec.com.br/projetos\n";
} else {
    echo "⚠ DEPLOYMENT PARCIAL\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    echo "Arquivos deployados: $success\n";
    echo "Falhas: $failed\n";
}
