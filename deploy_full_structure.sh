#!/bin/bash
USER="u673902663.genspark1:Genspark1@"
FTP="ftp://ftp.clinfec.com.br"

echo "=== DEPLOY COMPLETO DA ESTRUTURA ==="
echo

# Upload index.php principal
echo "[1/10] Uploading index.php..."
curl -T "index.php" --ftp-pasv --user "$USER" "$FTP/./index.php" 2>&1 | tail -1

# Upload .htaccess
echo "[2/10] Uploading .htaccess..."
curl -T ".htaccess" --ftp-pasv --user "$USER" "$FTP/./.htaccess" 2>&1 | tail -1

# Upload cadastroinicial.php
echo "[3/10] Uploading cadastroinicial.php..."
curl -T "cadastroinicial.php" --ftp-pasv --user "$USER" "$FTP/./cadastroinicial.php" 2>&1 | tail -1

# Upload config completo (tar para preservar estrutura)
echo "[4/10] Creating config tarball..."
tar -czf config.tar.gz config/
echo "Uploading config.tar.gz..."
curl -T "config.tar.gz" --ftp-pasv --user "$USER" "$FTP/./config.tar.gz" 2>&1 | tail -1

# Upload src completo
echo "[5/10] Creating src tarball..."
tar -czf src.tar.gz src/
echo "Uploading src.tar.gz..."
curl -T "src.tar.gz" --ftp-pasv --user "$USER" "$FTP/./src.tar.gz" 2>&1 | tail -1

# Upload public completo
echo "[6/10] Creating public tarball..."
tar -czf public.tar.gz public/
echo "Uploading public.tar.gz..."
curl -T "public.tar.gz" --ftp-pasv --user "$USER" "$FTP/./public.tar.gz" 2>&1 | tail -1

# Upload database completo
echo "[7/10] Creating database tarball..."
tar -czf database.tar.gz database/
echo "Uploading database.tar.gz..."
curl -T "database.tar.gz" --ftp-pasv --user "$USER" "$FTP/./database.tar.gz" 2>&1 | tail -1

# Upload extractor script
echo "[8/10] Creating extractor..."
cat > extract_all.php << 'EOFEXTRACT'
<?php
echo "Extracting tarballs...\n";

$files = ['config.tar.gz', 'src.tar.gz', 'public.tar.gz', 'database.tar.gz'];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "Extracting $file...\n";
        $phar = new PharData(__DIR__ . '/' . $file);
        $phar->extractTo(__DIR__, null, true);
        echo "✅ $file extracted\n";
        unlink(__DIR__ . '/' . $file);
    }
}

echo "\n✅ All files extracted!\n";
EOFEXTRACT

echo "Uploading extractor..."
curl -T "extract_all.php" --ftp-pasv --user "$USER" "$FTP/./extract_all.php" 2>&1 | tail -1

echo
echo "[9/10] Executar extrator via web..."
echo "Acesse: https://prestadores.clinfec.com.br/extract_all.php"
echo
echo "[10/10] Aguardando 5 segundos para extração..."
sleep 5

echo "✅ DEPLOY COMPLETO FINALIZADO!"
echo "Teste: bash test_all_routes.sh"
