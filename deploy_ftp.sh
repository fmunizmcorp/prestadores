#!/bin/bash

# Script de Deploy FTP - Clinfec Prestadores
# Data: 2025-11-08

FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"
FTP_DIR="/"

echo "========================================="
echo "DEPLOY FTP - CLINFEC PRESTADORES"
echo "========================================="
echo ""

# Fazer backup do servidor primeiro
echo "[1/4] Fazendo backup do servidor..."
BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S).tar.gz"
curl --user "$FTP_USER:$FTP_PASS" "ftp://$FTP_HOST/" --list-only 2>/dev/null > /tmp/server_files.txt
echo "Arquivos no servidor: $(wc -l < /tmp/server_files.txt)"
echo ""

# Upload do arquivo tar.gz
echo "[2/4] Enviando pacote deploy..."
DEPLOY_FILE="deploy_20251108_202440.tar.gz"
curl --user "$FTP_USER:$FTP_PASS" \
     --upload-file "$DEPLOY_FILE" \
     "ftp://$FTP_HOST/$DEPLOY_FILE" \
     --ftp-create-dirs \
     --progress-bar

if [ $? -eq 0 ]; then
    echo "✅ Upload do pacote completo!"
else
    echo "❌ Erro no upload"
    exit 1
fi
echo ""

# Enviar script de descompactação
echo "[3/4] Criando script de descompactação no servidor..."
cat > /tmp/extract.php << 'EOF'
<?php
// Script de descompactação automática
$file = 'deploy_20251108_202440.tar.gz';

if (file_exists($file)) {
    echo "Descompactando $file...\n";
    
    // Criar backup antes
    $backup = 'backup_before_deploy_' . date('YmdHis') . '.tar.gz';
    system("tar -czf $backup --exclude='*.tar.gz' .", $ret1);
    echo "Backup criado: $backup\n";
    
    // Extrair novo código
    system("tar -xzf $file", $ret2);
    
    if ($ret2 === 0) {
        echo "✅ Descompactação completa!\n";
        echo "✅ Deploy realizado com sucesso!\n";
        
        // Ajustar permissões
        system("chmod -R 755 .");
        system("chmod -R 777 uploads/ logs/ 2>/dev/null");
        echo "✅ Permissões ajustadas!\n";
        
        // Limpar arquivo
        unlink($file);
        echo "✅ Arquivo temporário removido!\n";
    } else {
        echo "❌ Erro na descompactação\n";
    }
} else {
    echo "❌ Arquivo $file não encontrado\n";
}
?>
EOF

curl --user "$FTP_USER:$FTP_PASS" \
     --upload-file /tmp/extract.php \
     "ftp://$FTP_HOST/extract.php" \
     --progress-bar

echo ""
echo "[4/4] Para completar o deploy, execute:"
echo "  https://prestadores.clinfec.com.br/extract.php"
echo ""
echo "========================================="
echo "DEPLOY PREPARADO COM SUCESSO!"
echo "========================================="
