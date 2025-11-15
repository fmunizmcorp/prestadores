#!/usr/bin/env python3
"""
Replace index.php temporarily with diagnostic version
Sprint 33 - Find the exact error
"""

import ftplib
import sys
from io import BytesIO
import time

# Configurações FTP
FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'timeout': 60
}

# Diagnostic index.php that tests everything step by step
DIAGNOSTIC_INDEX = """<?php
// DIAGNOSTIC MODE - Sprint 33
// This version tests each component individually

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Diagnostic</title></head><body>";
echo "<h1>System Diagnostic - Sprint 33</h1>";
echo "<pre>";

// Test 1: Basic PHP
echo "\\n[1] PHP Version: " . PHP_VERSION . " ✓\\n";

// Test 2: Paths
echo "\\n[2] Paths:\\n";
echo "    __DIR__: " . __DIR__ . "\\n";
echo "    __FILE__: " . __FILE__ . "\\n";
echo "    Parent: " . dirname(__DIR__) . "\\n";

// Test 3: Directories
echo "\\n[3] Directory Check:\\n";
$dirs = [
    '../config' => dirname(__DIR__) . '/config',
    '../src' => dirname(__DIR__) . '/src',
    '../public' => dirname(__DIR__) . '/public',
];

foreach ($dirs as $name => $path) {
    $exists = is_dir($path);
    echo "    $name: " . ($exists ? '✓ EXISTS' : '✗ MISSING') . "\\n";
    if ($exists) {
        $files = scandir($path);
        echo "      Files: " . count($files) . "\\n";
    }
}

// Test 4: cache_control.php
echo "\\n[4] cache_control.php:\\n";
$cache_control_path = dirname(__DIR__) . '/config/cache_control.php';
echo "    Path: $cache_control_path\\n";
$exists = file_exists($cache_control_path);
echo "    Exists: " . ($exists ? 'YES' : 'NO') . "\\n";

if ($exists) {
    echo "    Size: " . filesize($cache_control_path) . " bytes\\n";
    echo "    Attempting to load...\\n";
    try {
        require_once $cache_control_path;
        echo "    ✓ LOADED SUCCESSFULLY!\\n";
    } catch (\\Throwable $e) {
        echo "    ✗ ERROR: " . $e->getMessage() . "\\n";
        echo "    File: " . $e->getFile() . " Line: " . $e->getLine() . "\\n";
    }
}

// Test 5: OPcache
echo "\\n[5] OPcache:\\n";
echo "    function_exists('opcache_reset'): " . (function_exists('opcache_reset') ? 'YES' : 'NO') . "\\n";
if (function_exists('opcache_reset')) {
    echo "    Calling opcache_reset()...\\n";
    try {
        $result = opcache_reset();
        echo "    Result: " . ($result ? 'TRUE' : 'FALSE') . "\\n";
    } catch (\\Throwable $e) {
        echo "    ERROR: " . $e->getMessage() . "\\n";
    }
}

// Test 6: Session
echo "\\n[6] Session:\\n";
try {
    session_start();
    echo "    ✓ Session started\\n";
    echo "    Session ID: " . session_id() . "\\n";
} catch (\\Throwable $e) {
    echo "    ✗ ERROR: " . $e->getMessage() . "\\n";
}

// Test 7: config.php
echo "\\n[7] config.php:\\n";
$config_path = dirname(__DIR__) . '/config/config.php';
$exists = file_exists($config_path);
echo "    Exists: " . ($exists ? 'YES' : 'NO') . "\\n";
if ($exists) {
    try {
        $config = require $config_path;
        echo "    ✓ LOADED SUCCESSFULLY!\\n";
        echo "    Type: " . gettype($config) . "\\n";
        if (is_array($config)) {
            echo "    Keys: " . count($config) . "\\n";
        }
    } catch (\\Throwable $e) {
        echo "    ✗ ERROR: " . $e->getMessage() . "\\n";
    }
}

// Test 8: Database.php
echo "\\n[8] Database.php:\\n";
$db_path = dirname(__DIR__) . '/src/Database.php';
$exists = file_exists($db_path);
echo "    Exists: " . ($exists ? 'YES' : 'NO') . "\\n";
if ($exists) {
    echo "    Size: " . filesize($db_path) . " bytes\\n";
    echo "    Attempting to load...\\n";
    try {
        require_once $db_path;
        echo "    ✓ LOADED SUCCESSFULLY!\\n";
    } catch (\\Throwable $e) {
        echo "    ✗ ERROR: " . $e->getMessage() . "\\n";
        echo "    File: " . $e->getFile() . " Line: " . $e->getLine() . "\\n";
    }
}

echo "\\n\\n=== DIAGNOSTIC COMPLETE ===\\n";
echo "\\n</pre></body></html>";
"""

def download_file(ftp, path):
    """Download file"""
    try:
        buffer = BytesIO()
        ftp.retrbinary(f'RETR {path}', buffer.write)
        return buffer.getvalue().decode('utf-8', errors='replace')
    except:
        return None

def upload_file(ftp, path, content):
    """Upload file"""
    try:
        buffer = BytesIO(content.encode('utf-8'))
        ftp.storbinary(f'STOR {path}', buffer)
        return True
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def main():
    print("=" * 70)
    print("REPLACE INDEX.PHP WITH DIAGNOSTIC VERSION")
    print("Sprint 33 - Find Exact Error")
    print("=" * 70)
    
    try:
        # Connect
        print("\n1️⃣ Connecting...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        ftp.cwd('/public_html/prestadores')
        print(f"✅ Connected to {ftp.pwd()}")
        
        # Download and backup current index.php
        print("\n2️⃣ Backing up current index.php...")
        current_index = download_file(ftp, 'index.php')
        if current_index:
            backup_name = f'index.php.backup_before_diagnostic_{int(time.time())}'
            if upload_file(ftp, backup_name, current_index):
                print(f"✅ Backup: {backup_name}")
        
        # Upload diagnostic version
        print("\n3️⃣ Uploading diagnostic index.php...")
        if upload_file(ftp, 'index.php', DIAGNOSTIC_INDEX):
            print(f"✅ Uploaded ({len(DIAGNOSTIC_INDEX)} bytes)")
        else:
            print("❌ Upload failed!")
            ftp.quit()
            return 1
        
        ftp.quit()
        
        print("\n" + "=" * 70)
        print("4️⃣ TEST NOW")
        print("=" * 70)
        print("\n📝 Access: https://clinfec.com.br/prestadores/")
        print("\n💡 You should see detailed diagnostic output")
        print("   showing EXACTLY where the error occurs!")
        print("\n⚠️ After diagnosis, we'll restore the original index.php")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
