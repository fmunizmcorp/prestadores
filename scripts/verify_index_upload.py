#!/usr/bin/env python3
"""
Verify what's actually in index.php on the server
Sprint 33 - Check file integrity
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

def main():
    print("=" * 70)
    print("VERIFY INDEX.PHP ON SERVER")
    print("=" * 70)
    
    try:
        # Connect
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        ftp.cwd('/public_html/prestadores')
        
        # Download index.php
        buffer = BytesIO()
        ftp.retrbinary('RETR index.php', buffer.write)
        content = buffer.getvalue().decode('utf-8', errors='replace')
        
        print(f"\n✅ Downloaded index.php ({len(content)} bytes)")
        print("\n📄 First 100 lines:")
        print("-" * 70)
        
        lines = content.split('\n')
        for i, line in enumerate(lines[:100], 1):
            print(f"{i:3d}: {line}")
        
        if len(lines) > 100:
            print(f"\n... ({len(lines) - 100} more lines)")
        
        # Check for syntax issues
        print("\n" + "=" * 70)
        print("ANALYSIS:")
        print("=" * 70)
        
        issues = []
        
        # Check for PHP opening tag
        if not content.startswith('<?php'):
            issues.append("File doesn't start with <?php")
        
        # Check for BOM
        if content.startswith('\ufeff'):
            issues.append("File has UTF-8 BOM (may cause issues)")
        
        # Check file size
        if len(content) < 100:
            issues.append(f"File is suspiciously small ({len(content)} bytes)")
        
        if issues:
            print("\n⚠️ ISSUES FOUND:")
            for issue in issues:
                print(f"   - {issue}")
        else:
            print("\n✅ No obvious issues found")
            print("   File structure looks OK")
        
        ftp.quit()
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
