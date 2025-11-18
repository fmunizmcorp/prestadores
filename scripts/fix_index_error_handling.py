#!/usr/bin/env python3
"""
Fix index.php to handle cache_control.php error gracefully
Sprint 33 - Show proper error instead of HTTP 500
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

# Patch to add at the beginning of index.php (after opening PHP tag)
ERROR_HANDLER_PATCH = """
// ==================== EARLY ERROR HANDLER (SPRINT 33) ====================
// Capture fatal errors and display them
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Custom error handler to catch everything
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<pre style='background:#f00;color:#fff;padding:20px'>";
    echo "ERROR $errno: $errstr\\n";
    echo "File: $errfile\\n";
    echo "Line: $errline\\n";
    echo "</pre>";
    return false;
});

// Custom exception handler
set_exception_handler(function($e) {
    echo "<pre style='background:#f00;color:#fff;padding:20px'>";
    echo "EXCEPTION: " . $e->getMessage() . "\\n";
    echo "File: " . $e->getFile() . "\\n";
    echo "Line: " . $e->getLine() . "\\n";
    echo "Trace:\\n" . $e->getTraceAsString();
    echo "</pre>";
});

// ==================== CACHE CONTROL (SPRINT 33) ====================
// Try to load cache control, but don't die if it fails
$cache_control_path = __DIR__ . '/../config/cache_control.php';
if (file_exists($cache_control_path)) {
    try {
        require_once $cache_control_path;
    } catch (\\Throwable $e) {
        echo "<div style='background:#ff0;padding:10px'>";
        echo "WARNING: Could not load cache_control.php: " . $e->getMessage();
        echo "</div>";
    }
} else {
    echo "<div style='background:#ff0;padding:10px'>";
    echo "WARNING: cache_control.php not found at: $cache_control_path";
    echo "</div>";
}
"""

def download_file(ftp, path):
    """Download file"""
    try:
        buffer = BytesIO()
        ftp.retrbinary(f'RETR {path}', buffer.write)
        return buffer.getvalue().decode('utf-8', errors='replace')
    except Exception as e:
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
    print("FIX INDEX.PHP ERROR HANDLING")
    print("Sprint 33 - Show Errors Instead of HTTP 500")
    print("=" * 70)
    
    try:
        # Connect
        print("\n1️⃣ Connecting to FTP...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Connected")
        
        ftp.cwd('/public_html/prestadores')
        print(f"📁 Dir: {ftp.pwd()}")
        
        # Download current index.php
        print("\n2️⃣ Downloading current index.php...")
        index_content = download_file(ftp, 'index.php')
        
        if not index_content:
            print("❌ Could not download index.php")
            return 1
        
        print(f"✅ Downloaded ({len(index_content)} bytes)")
        
        # Backup
        print("\n3️⃣ Creating backup...")
        backup_name = f'index.php.backup_sprint33_{int(time.time())}'
        if upload_file(ftp, backup_name, index_content):
            print(f"✅ Backup: {backup_name}")
        
        # Check if already patched
        if 'EARLY ERROR HANDLER (SPRINT 33)' in index_content:
            print("\n⚠️ Index.php already has error handler patch!")
            print("   No changes needed.")
            ftp.quit()
            return 0
        
        # Find where to insert patch (after second <?php)
        lines = index_content.split('\n')
        php_tag_count = 0
        insert_index = 0
        
        for i, line in enumerate(lines):
            if '<?php' in line:
                php_tag_count += 1
                if php_tag_count == 2:  # After second <?php
                    insert_index = i + 1
                    break
        
        if insert_index == 0:
            print("❌ Could not find insertion point")
            return 1
        
        # Insert patch
        print("\n4️⃣ Patching index.php...")
        lines.insert(insert_index, ERROR_HANDLER_PATCH)
        
        # Remove old cache_control require line (it's now in the patch)
        new_lines = []
        skip_next = False
        for line in lines:
            if skip_next:
                skip_next = False
                continue
            if 'require_once __DIR__ . \'/../config/cache_control.php\';' in line:
                skip_next = True  # Skip comment lines above it too if any
                continue
            if '// Limpar cache durante desenvolvimento' in line:
                continue
            if '// Para produção: comentar as linhas em config/cache_control.php' in line:
                continue
            new_lines.append(line)
        
        new_content = '\n'.join(new_lines)
        
        # Upload
        print("\n5️⃣ Uploading patched index.php...")
        if upload_file(ftp, 'index.php', new_content):
            print(f"✅ Uploaded ({len(new_content)} bytes)")
        else:
            print("❌ Upload failed")
            ftp.quit()
            return 1
        
        print("\n" + "=" * 70)
        print("6️⃣ TEST NOW")
        print("=" * 70)
        
        print("\n✅ Index.php has been patched with error handler!")
        print("\n📝 Now test:")
        print("   https://clinfec.com.br/prestadores/")
        print("\n💡 You should see:")
        print("   - If error: RED box with error details")
        print("   - If warning: YELLOW box with warning")
        print("   - If success: The application!")
        
        ftp.quit()
        print("\n✅ DONE")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
