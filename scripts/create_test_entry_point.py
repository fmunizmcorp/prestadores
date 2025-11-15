#!/usr/bin/env python3
"""
Create a NEW entry point with a different name
Sprint 33 - Bypass cache completely
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

# Super simple test that MUST work
SIMPLE_TEST = f"""<?php
// SIMPLE TEST - {int(time.time())}
echo "HELLO FROM PHP!<br>";
echo "Time: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "File: " . __FILE__ . "<br>";
echo "Directory: " . __DIR__ . "<br>";
"""

# .htaccess that routes EVERYTHING to our new file
HTACCESS_ROUTE_TO_TEST = """# Route all requests to test_entry.php
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /prestadores/
    
    # Route everything to test_entry.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ test_entry.php [QSA,L]
</IfModule>
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
    print("CREATE NEW ENTRY POINT - BYPASS ALL CACHE")
    print("Sprint 33")
    print("=" * 70)
    
    try:
        # Connect
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        ftp.cwd('/public_html/prestadores')
        print(f"✅ Connected to {ftp.pwd()}")
        
        # Upload test_entry.php
        print("\n1️⃣ Creating test_entry.php...")
        if upload_file(ftp, 'test_entry.php', SIMPLE_TEST):
            print(f"✅ Uploaded test_entry.php")
        
        # Update .htaccess to route to it
        print("\n2️⃣ Updating .htaccess...")
        if upload_file(ftp, '.htaccess', HTACCESS_ROUTE_TO_TEST):
            print(f"✅ Updated .htaccess")
        
        ftp.quit()
        
        print("\n" + "=" * 70)
        print("3️⃣ TEST")
        print("=" * 70)
        
        print("\n📝 Access:")
        print("   https://clinfec.com.br/prestadores/test_entry.php")
        print("   https://clinfec.com.br/prestadores/")
        print("\n💡 Both should show 'HELLO FROM PHP!'")
        print("   If not, there's a deeper server issue")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        return 1

if __name__ == '__main__':
    sys.exit(main())
