#!/usr/bin/env python3
"""
Deploy arquivo de teste diagnóstico super simples
Sprint 33 - Diagnosticar PHP fatal error
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

def upload_file(ftp, remote_path, content):
    """Envia arquivo para o servidor"""
    try:
        buffer = BytesIO(content)
        ftp.storbinary(f'STOR {remote_path}', buffer)
        return True
    except Exception as e:
        print(f"❌ Erro ao enviar {remote_path}: {e}")
        return False

def main():
    print("=" * 70)
    print("DEPLOY DE ARQUIVOS DE DIAGNÓSTICO")
    print("Sprint 33 - Testar PHP Básico")
    print("=" * 70)
    
    # Arquivos de teste progressivos
    tests = {
        'test_basic.php': b"""<?php
echo "OK";
""",
        'test_phpinfo_mini.php': b"""<?php
phpinfo();
""",
        'test_error_display.php': b"""<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Dir: " . __DIR__ . "<br>";
echo "Script: " . __FILE__ . "<br>";

echo "<hr><h3>Testing require_once...</h3>";

try {
    $config_path = __DIR__ . '/../config/cache_control.php';
    echo "Trying to load: $config_path<br>";
    
    if (file_exists($config_path)) {
        echo "File exists: YES<br>";
        require_once $config_path;
        echo "Loaded successfully!<br>";
    } else {
        echo "File exists: NO<br>";
        echo "Looking in: " . dirname($config_path) . "<br>";
        if (is_dir(dirname($config_path))) {
            echo "Directory exists: YES<br>";
            $files = scandir(dirname($config_path));
            echo "Files in config/: " . implode(', ', $files) . "<br>";
        } else {
            echo "Directory exists: NO<br>";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
}

echo "<h3>All checks done!</h3>";
""",
        'test_index_error.php': b"""<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. PHP Works<br>";
echo "2. Current directory: " . __DIR__ . "<br>";

$cache_control_path = __DIR__ . '/../config/cache_control.php';
echo "3. Cache control path: $cache_control_path<br>";

if (file_exists($cache_control_path)) {
    echo "4. File exists: YES<br>";
    try {
        require_once $cache_control_path;
        echo "5. Loaded successfully!<br>";
    } catch (Throwable $e) {
        echo "5. ERROR: " . $e->getMessage() . "<br>";
        echo "   File: " . $e->getFile() . "<br>";
        echo "   Line: " . $e->getLine() . "<br>";
    }
} else {
    echo "4. File exists: NO<br>";
}

echo "6. Test complete<br>";
"""
    }
    
    try:
        # Conectar FTP
        print("\n1️⃣ Conectando ao FTP...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado: {ftp.getwelcome()}")
        
        # Mudar para diretório prestadores
        ftp.cwd('/public_html/prestadores')
        print(f"📁 Diretório: {ftp.pwd()}")
        
        # Upload dos testes
        print("\n2️⃣ Enviando arquivos de teste...")
        
        for filename, content in tests.items():
            if upload_file(ftp, filename, content):
                print(f"✅ {filename} ({len(content)} bytes)")
            else:
                print(f"❌ {filename} FALHOU")
        
        # Mostrar URLs para teste
        print("\n" + "=" * 70)
        print("3️⃣ URLs PARA TESTAR")
        print("=" * 70)
        
        print("\n📝 Teste cada URL em ordem:")
        for i, filename in enumerate(tests.keys(), 1):
            url = f"https://prestadores.clinfec.com.br/{filename}"
            print(f"\n{i}. {filename}")
            print(f"   {url}")
        
        print("\n💡 ESPERADO:")
        print("   test_basic.php: Deve mostrar 'OK'")
        print("   test_phpinfo_mini.php: Deve mostrar página phpinfo()")
        print("   test_error_display.php: Deve mostrar informações detalhadas")
        print("   test_index_error.php: Deve mostrar se o problema está no require")
        
        # Fechar conexão
        ftp.quit()
        print("\n" + "=" * 70)
        print("✅ DEPLOY CONCLUÍDO")
        print("=" * 70)
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
