#!/usr/bin/env python3
"""
Verify files were actually deployed to production server
Downloads and compares with local versions
"""

import ftplib
import os
import sys
import hashlib

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

FILES_TO_VERIFY = [
    'src/Models/EmpresaPrestadora.php',
    'src/Models/Servico.php',
    'src/Models/Contrato.php',
    'src/Controllers/ProjetoController.php',
    'src/Models/Projeto.php',
    'src/Models/EmpresaTomadora.php'
]

def get_file_hash(filepath):
    """Get MD5 hash of file"""
    if not os.path.exists(filepath):
        return None
    with open(filepath, 'rb') as f:
        return hashlib.md5(f.read()).hexdigest()

def download_ftp_file(ftp, remote_path, local_temp_path):
    """Download file from FTP"""
    try:
        with open(local_temp_path, 'wb') as f:
            ftp.retrbinary(f'RETR /{remote_path}', f.write)
        return True
    except Exception as e:
        print(f"  ✗ Error downloading: {e}")
        return False

def main():
    print("=" * 80)
    print("DEPLOYMENT VERIFICATION - Sprint 44-50 Files")
    print("=" * 80)
    
    # Connect to FTP
    print(f"\n🔌 Connecting to {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("✓ Connected")
    except Exception as e:
        print(f"✗ Connection failed: {e}")
        return 1
    
    # Verify each file
    mismatches = 0
    matches = 0
    missing = 0
    
    for file_path in FILES_TO_VERIFY:
        print(f"\n📄 Verifying: {file_path}")
        
        # Get local hash
        local_hash = get_file_hash(file_path)
        if not local_hash:
            print(f"  ✗ Local file not found!")
            missing += 1
            continue
        
        print(f"  Local MD5:  {local_hash[:16]}...")
        
        # Download remote file
        temp_file = f"/tmp/{os.path.basename(file_path)}.remote"
        if not download_ftp_file(ftp, file_path, temp_file):
            print(f"  ✗ Remote file not found or download failed!")
            missing += 1
            continue
        
        # Get remote hash
        remote_hash = get_file_hash(temp_file)
        print(f"  Remote MD5: {remote_hash[:16]}...")
        
        # Compare
        if local_hash == remote_hash:
            print(f"  ✅ MATCH - File deployed correctly")
            matches += 1
        else:
            print(f"  ❌ MISMATCH - File on server is DIFFERENT!")
            print(f"     Local file might not have been deployed!")
            mismatches += 1
        
        # Cleanup
        os.remove(temp_file)
    
    ftp.quit()
    
    # Results
    print("\n" + "=" * 80)
    print("VERIFICATION RESULTS")
    print("=" * 80)
    total = len(FILES_TO_VERIFY)
    print(f"\nFiles Verified: {total}")
    print(f"  ✅ Matches: {matches}")
    print(f"  ❌ Mismatches: {mismatches}")
    print(f"  ⚠️  Missing: {missing}")
    
    if mismatches > 0 or missing > 0:
        print(f"\n🔴 DEPLOYMENT FAILED - {mismatches + missing} files not properly deployed!")
        return 1
    else:
        print(f"\n✅ ALL FILES DEPLOYED CORRECTLY")
        return 0

if __name__ == '__main__':
    sys.exit(main())
