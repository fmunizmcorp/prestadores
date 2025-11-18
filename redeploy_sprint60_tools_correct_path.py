#!/usr/bin/env python3
"""
Sprint 61: Redeploy Sprint 60 Tools to Correct Path

The tools were deployed to /public_html/ but should be in /public_html/prestadores/
"""

import ftplib
import hashlib

FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"

def calculate_md5(file_path):
    md5 = hashlib.md5()
    with open(file_path, 'rb') as f:
        for chunk in iter(lambda: f.read(4096), b''):
            md5.update(chunk)
    return md5.hexdigest()

files = [
    'monitor_cache_status_sprint60.php',
    'clear_cache_manual_sprint60.php',
    'autoloader_cache_bust_sprint60.php',
    'force_opcache_reset_sprint58.php',
    'test_database_direct_sprint58.php'
]

print("Connecting to FTP...")
ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

# Change to prestadores directory
print("Changing to /public_html/prestadores...")
ftp.cwd("/public_html/prestadores")

print("\nDeploying tools to correct location...\n")

for filename in files:
    try:
        md5 = calculate_md5(filename)
        print(f"📤 {filename}")
        print(f"   MD5: {md5}")
        
        with open(filename, 'rb') as f:
            ftp.storbinary(f'STOR {filename}', f)
        
        size = ftp.size(filename)
        print(f"   ✅ Deployed ({size:,} bytes)\n")
    except FileNotFoundError:
        print(f"   ⚠️  File not found locally\n")
    except Exception as e:
        print(f"   ❌ Error: {str(e)}\n")

ftp.quit()

print("=" * 70)
print("DEPLOYMENT COMPLETE")
print("=" * 70)
print("\nTest URLs:")
print("https://clinfec.com.br/prestadores/monitor_cache_status_sprint60.php")
print("https://clinfec.com.br/prestadores/clear_cache_manual_sprint60.php")
print("https://clinfec.com.br/prestadores/force_opcache_reset_sprint58.php")
