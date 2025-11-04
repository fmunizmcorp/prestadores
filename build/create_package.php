<?php
/**
 * Script para criar pacote de distribuição
 * Gera um arquivo ZIP pronto para deploy
 */

echo "===========================================\n";
echo "  GERADOR DE PACOTE - Sistema Clinfec\n";
echo "===========================================\n\n";

// Caminho base
$baseDir = dirname(__DIR__);
$buildDir = __DIR__;
$outputDir = $buildDir . '/releases';

// Cria diretório de releases se não existir
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Carrega versão
$versionConfig = require $baseDir . '/config/version.php';
$version = $versionConfig['version'];
$zipName = "clinfec-prestadores-v{$version}.zip";
$zipPath = $outputDir . '/' . $zipName;

echo "Versão do sistema: $version\n";
echo "Criando pacote: $zipName\n\n";

// Remove arquivo anterior se existir
if (file_exists($zipPath)) {
    unlink($zipPath);
    echo "Arquivo anterior removido\n";
}

// Cria o ZIP
$zip = new ZipArchive();

if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    die("❌ Erro ao criar arquivo ZIP\n");
}

// Arquivos e pastas para incluir
$filesToInclude = [
    'config',
    'database',
    'docs',
    'public',
    'src',
    'README.md',
    'INSTALACAO_HOSTINGER.md',
    'GUIA_RAPIDO.md',
    'INFORMACOES_IMPORTANTES.md',
    '.gitignore'
];

// Arquivos e pastas para excluir
$exclude = [
    '.git',
    '.gitignore',
    'build',
    'logs',
    'node_modules',
    'vendor',
    '.DS_Store',
    'Thumbs.db'
];

// Função recursiva para adicionar arquivos
function addFilesToZip($zip, $dir, $baseDir, $exclude = []) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $count = 0;
    foreach ($files as $file) {
        if ($file->isDir()) continue;
        
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($baseDir) + 1);
        
        // Verifica se deve excluir
        $skip = false;
        foreach ($exclude as $pattern) {
            if (strpos($relativePath, $pattern) !== false) {
                $skip = true;
                break;
            }
        }
        
        if (!$skip) {
            $zip->addFile($filePath, $relativePath);
            $count++;
        }
    }
    
    return $count;
}

// Adiciona cada item
echo "Adicionando arquivos ao pacote...\n";
$totalFiles = 0;

foreach ($filesToInclude as $item) {
    $itemPath = $baseDir . '/' . $item;
    
    if (is_file($itemPath)) {
        $zip->addFile($itemPath, $item);
        $totalFiles++;
        echo "  ✓ Adicionado: $item\n";
    } elseif (is_dir($itemPath)) {
        $count = addFilesToZip($zip, $itemPath, $baseDir, $exclude);
        $totalFiles += $count;
        echo "  ✓ Adicionado: $item ($count arquivos)\n";
    }
}

// Cria arquivo de instalação
$installScript = "<?php
/**
 * INSTALAÇÃO AUTOMÁTICA - Sistema Clinfec v{$version}
 * 
 * 1. Extraia este ZIP no diretório do seu site
 * 2. Acesse este arquivo pelo navegador
 * 3. Configure as credenciais do banco de dados
 * 4. Clique em Instalar
 */

// Este arquivo será criado futuramente
echo 'Sistema pronto! Acesse public/index.php';
";

$zip->addFromString('INSTALACAO.txt', "Sistema de Gestão de Prestadores - Clinfec v{$version}

INSTRUÇÕES DE INSTALAÇÃO:

1. Extraia todos os arquivos deste ZIP para o diretório do seu site
   Exemplo: public_html/prestadores/

2. Configure o banco de dados em: config/database.php
   - Host: localhost
   - Database: u673902663_prestadores
   - Username: u673902663_admin
   - Password: [sua_senha]

3. Configure o reCAPTCHA em: config/app.php
   - site_key: [sua_chave]
   - secret_key: [sua_chave_secreta]

4. Acesse o sistema pelo navegador
   URL: https://seu-site.com/prestadores/

5. O sistema irá se auto-instalar na primeira execução!

6. Faça login com:
   Email: admin@clinfec.com.br
   Senha: admin

7. IMPORTANTE: Altere a senha após o primeiro acesso!

Documentação completa em: README.md

Versão: {$version}
Data: {$versionConfig['release_date']}
");

$zip->close();

$fileSize = filesize($zipPath);
$fileSizeMB = number_format($fileSize / 1024 / 1024, 2);

echo "\n===========================================\n";
echo "✅ PACOTE CRIADO COM SUCESSO!\n";
echo "===========================================\n";
echo "Arquivo: $zipName\n";
echo "Localização: $zipPath\n";
echo "Total de arquivos: $totalFiles\n";
echo "Tamanho: {$fileSizeMB} MB\n";
echo "\nO pacote está pronto para deploy!\n";
