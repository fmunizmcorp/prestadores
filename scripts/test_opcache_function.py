#!/usr/bin/env python3
"""
Test if opcache_reset() causes the error
Sprint 33 - Test OPcache function
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

# Test different scenarios
TESTS = {
    'test_opcache_exists.php': """<?php
echo "Test 1: OPcache function exists?<br>";
if (function_exists('opcache_reset')) {
    echo "YES - opcache_reset EXISTS<br>";
} else {
    echo "NO - opcache_reset DOES NOT EXIST<br>";
}

echo "<br>Test 2: Trying to call it...<br>";
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "Result: " . ($result ? 'TRUE' : 'FALSE') . "<br>";
} else {
    echo "SKIPPED - function doesn't exist<br>";
}

echo "<br>Test 3: clearstatcache...<br>";
clearstatcache(true);
echo "SUCCESS!<br>";
""",
    'test_cache_control_file.php': """<?php
echo "Test: Loading cache_control.php<br>";

$path = __DIR__ . '/../config/cache_control.php';
echo "Path: $path<br>";
echo "Exists: " . (file_exists($path) ? 'YES' : 'NO') . "<br>";

if (file_exists($path)) {
    echo "Trying to load...<br>";
    try {
        require_once $path;
        echo "SUCCESS!<br>";
    } catch (\\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "<br>";
    }
}
""",
    'test_simple_require.php': """<?php
echo "Test: Simple require<br>";

// Try to require a file that should exist
$config_path = __DIR__ . '/../config/config.php';
echo "Config path: $config_path<br>";
echo "Exists: " . (file_exists($config_path) ? 'YES' : 'NO') . "<br>";

if (file_exists($config_path)) {
    echo "Trying to load...<br>";
    try {
        $config = require $config_path;
        echo "SUCCESS! Type: " . gettype($config) . "<br>";
    } catch (\\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "<br>";
    }
}
"""
}

def upload_file(ftp, path, content):
    """Upload file"""
    try:
        buffer = BytesIO(content.encode('utf-8'))
        ftp.storbinary(f'STOR {path}', buffer)
        return True
    except Exception as e:
        return False

def main():
    print("=" * 70)
    print("TEST OPCACHE AND REQUIRE FUNCTIONS")
    print("Sprint 33 - Find What's Causing HTTP 500")
    print("=" * 70)
    
    try:
        # Connect
        print("\n1️⃣ Connecting...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        ftp.cwd('/public_html/prestadores')
        print(f"✅ Connected to {ftp.pwd()}")
        
        # Upload test files
        print("\n2️⃣ Uploading test files...")
        for filename, content in TESTS.items():
            if upload_file(ftp, filename, content):
                print(f"✅ {filename}")
        
        ftp.quit()
        
        # Test via index.php (since other .php files are blocked)
        print("\n" + "=" * 70)
        print("3️⃣ IMPORTANT:")
        print("=" * 70)
        print("\n⚠️ These test files CANNOT be accessed directly!")
        print("   WordPress blocks all .php files except index.php")
        print("\n💡 We need to:")
        print("   1. Copy one test into index.php temporarily")
        print("   2. OR create an include mechanism in index.php")
        print("\n📝 Let me create a wrapper index...")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        return 1

if __name__ == '__main__':
    sys.exit(main())
