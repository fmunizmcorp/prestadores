#!/usr/bin/env python3
"""
Upload Models directly via FTP to prestadores directory
"""
import ftplib
import os

FTP_SERVER = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"

# Base path discovered earlier
BASE_PATH = "../domains/clinfec.com.br/public_html/prestadores"

files_to_upload = [
    {
        'local': 'NotaFiscal_NEW.php',
        'remote': f'{BASE_PATH}/src/Models/NotaFiscal.php'
    },
    {
        'local': 'Projeto_NEW.php',
        'remote': f'{BASE_PATH}/src/Models/Projeto.php'
    },
    {
        'local': 'Atividade_NEW.php',
        'remote': f'{BASE_PATH}/src/Models/Atividade.php'
    },
]

print("═══════════════════════════════════════════════════════════")
print("UPLOADING MODELS VIA FTP - SPRINT 14 FINAL")
print("═══════════════════════════════════════════════════════════\n")

try:
    ftp = ftplib.FTP(FTP_SERVER)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Connected to {FTP_SERVER}\n")
    
    success = 0
    failed = 0
    
    for idx, file_info in enumerate(files_to_upload, 1):
        local_file = file_info['local']
        remote_path = file_info['remote']
        
        print(f"[{idx}/3] {os.path.basename(local_file)}")
        print(f"  Local: {local_file}")
        print(f"  Remote: {remote_path}")
        
        if not os.path.exists(local_file):
            print(f"  ❌ Local file not found\n")
            failed += 1
            continue
        
        file_size = os.path.getsize(local_file)
        print(f"  Size: {file_size:,} bytes")
        
        try:
            with open(local_file, 'rb') as f:
                ftp.storbinary(f'STOR {remote_path}', f)
            print(f"  ✅ UPLOADED SUCCESSFULLY\n")
            success += 1
        except Exception as e:
            print(f"  ❌ UPLOAD FAILED: {str(e)[:100]}\n")
            failed += 1
    
    print("═══════════════════════════════════════════════════════════")
    print(f"SUMMARY: ✅ {success} successful, ❌ {failed} failed")
    print("═══════════════════════════════════════════════════════════\n")
    
    if success == 3:
        print("✅ ALL FILES UPLOADED!")
        print("\nNext: Clear OPcache and test routes")
    
    ftp.quit()
    
except Exception as e:
    print(f"\n❌ ERROR: {e}")
    import traceback
    traceback.print_exc()
