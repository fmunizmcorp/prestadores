#!/usr/bin/env python3
"""
Criar test_direct.txt para testar sem WordPress interferindo
Sprint 33 - Bypass WordPress completely
"""

import ftplib
import sys
from io import BytesIO

# Configurações FTP
FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'timeout': 60
}

# Arquivo de teste que NÃO é .php
TEST_HTML = b"""<!DOCTYPE html>
<html>
<head>
    <title>Test Direct Access</title>
</head>
<body>
    <h1>SUCCESS!</h1>
    <p>If you see this, the /prestadores directory is accessible!</p>
    <p>This proves WordPress is NOT blocking static files.</p>
    <hr>
    <p>Now testing PHP execution:</p>
    <script>
        fetch('test_ajax.php')
            .then(r => r.text())
            .then(text => {
                document.getElementById('php-test').innerHTML = 'PHP Result: ' + text;
            })
            .catch(err => {
                document.getElementById('php-test').innerHTML = 'PHP Error: ' + err;
            });
    </script>
    <div id="php-test">Loading PHP test...</div>
</body>
</html>
"""

# PHP test via AJAX
TEST_PHP_AJAX = b"""<?php
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
echo "PHP WORKS! Version: " . PHP_VERSION;
"""

def upload_file(ftp, remote_path, content):
    """Envia arquivo"""
    try:
        buffer = BytesIO(content)
        ftp.storbinary(f'STOR {remote_path}', buffer)
        return True
    except Exception as e:
        print(f"❌ Erro: {e}")
        return False

def main():
    print("=" * 70)
    print("CRIAR TESTE DIRETO (HTML + PHP via AJAX)")
    print("Sprint 33 - Bypass WordPress")
    print("=" * 70)
    
    try:
        # Conectar
        print("\n1️⃣ Conectando...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado")
        
        ftp.cwd('/public_html/prestadores')
        print(f"📁 Dir: {ftp.pwd()}")
        
        # Upload
        print("\n2️⃣ Enviando arquivos...")
        
        if upload_file(ftp, 'test_direct.html', TEST_HTML):
            print(f"✅ test_direct.html ({len(TEST_HTML)} bytes)")
        
        if upload_file(ftp, 'test_ajax.php', TEST_PHP_AJAX):
            print(f"✅ test_ajax.php ({len(TEST_PHP_AJAX)} bytes)")
        
        print("\n" + "=" * 70)
        print("3️⃣ TESTE AGORA")
        print("=" * 70)
        
        print("\n📝 Acesse:")
        print("   https://clinfec.com.br/prestadores/test_direct.html")
        print("\n💡 Se ver 'SUCCESS!', o diretório está acessível!")
        print("   Se ver 'PHP WORKS!', PHP está funcionando via AJAX!")
        
        ftp.quit()
        print("\n✅ CONCLUÍDO")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
