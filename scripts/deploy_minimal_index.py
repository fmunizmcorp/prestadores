#!/usr/bin/env python3
"""
Deploy absolutely minimal index.php to test if PHP works
Sprint 33 - Isolate the problem
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

# Absolutely minimal PHP that just works
MINIMAL_INDEX = """<?php
echo "PHP WORKS!";
echo "<br>PHP Version: " . PHP_VERSION;
echo "<br>Current Time: " . date('Y-m-d H:i:s');
echo "<br>Directory: " . __DIR__;
"""

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
    print("DEPLOY MINIMAL INDEX.PHP")
    print("Sprint 33 - Test Basic PHP Execution")
    print("=" * 70)
    
    try:
        # Connect
        print("\n1️⃣ Connecting...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Connected")
        
        ftp.cwd('/public_html/prestadores')
        print(f"📁 Dir: {ftp.pwd()}")
        
        # Upload as different name first
        print("\n2️⃣ Uploading minimal_index.php...")
        if upload_file(ftp, 'minimal_index.php', MINIMAL_INDEX):
            print(f"✅ Uploaded ({len(MINIMAL_INDEX)} bytes)")
        else:
            print("❌ Upload failed")
            ftp.quit()
            return 1
        
        print("\n" + "=" * 70)
        print("3️⃣ TEST NOW")
        print("=" * 70)
        
        print("\n📝 Test URL:")
        print("   https://clinfec.com.br/prestadores/minimal_index.php")
        print("\n💡 Expected:")
        print("   - Should show: PHP WORKS!")
        print("   - Should show PHP version")
        print("   - Should show current time")
        print("\n⚠️ If this doesn't work, PHP itself is broken or WordPress is intercepting")
        
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
