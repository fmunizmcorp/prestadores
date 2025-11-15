#!/usr/bin/env python3
"""
Upload dos arquivos corrigidos para o servidor correto
Sprint 28 - Fix definitivo
"""

import ftplib
import sys
import os

def upload_file(local_path, remote_path):
    try:
        ftp = ftplib.FTP('ftp.clinfec.com.br')
        ftp.login('u673902663.genspark1', 'Genspark1@')
        
        print(f"\n{'='*60}")
        print(f"Uploading: {local_path}")
        print(f"To: {remote_path}")
        print(f"{'='*60}")
        
        # Get file size
        file_size = os.path.getsize(local_path)
        print(f"File size: {file_size} bytes")
        
        # Upload
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        
        # Verify
        try:
            size = ftp.size(remote_path)
            print(f"Uploaded size: {size} bytes")
            
            if size == file_size:
                print(f"✅ SUCCESS! File uploaded and verified")
            else:
                print(f"⚠️  WARNING: Size mismatch! Local={file_size}, Remote={size}")
                
        except Exception as e:
            print(f"✅ File uploaded (verification failed: {e})")
        
        ftp.quit()
        return 0
        
    except Exception as e:
        print(f"❌ ERROR: {e}")
        import traceback
        traceback.print_exc()
        return 1

def main():
    # Base paths
    remote_base = "/domains/clinfec.com.br/public_html/prestadores"
    
    # Files to upload
    files = [
        {
            'local': 'src/Database.php',
            'remote': f'{remote_base}/src/Database.php',
            'description': 'Database.php com métodos proxy (Sprint 26)'
        },
        {
            'local': 'src/DatabaseMigration.php',
            'remote': f'{remote_base}/src/DatabaseMigration.php',
            'description': 'DatabaseMigration.php corrigido (linha 17)'
        },
        {
            'local': 'public/index.php',
            'remote': f'{remote_base}/public/index.php',
            'description': 'index.php com auto-clear OPcache (Sprint 27)'
        }
    ]
    
    print("="*60)
    print("SPRINT 28 - UPLOAD DEFINITIVO DE CORREÇÕES")
    print("="*60)
    print(f"\nServidor: clinfec.com.br")
    print(f"Diretório: {remote_base}")
    print(f"Arquivos: {len(files)}")
    print()
    
    success_count = 0
    error_count = 0
    
    for file_info in files:
        print(f"\n{'='*60}")
        print(f"📁 {file_info['description']}")
        print(f"{'='*60}")
        
        if not os.path.exists(file_info['local']):
            print(f"❌ Local file not found: {file_info['local']}")
            error_count += 1
            continue
        
        result = upload_file(file_info['local'], file_info['remote'])
        
        if result == 0:
            success_count += 1
        else:
            error_count += 1
    
    print(f"\n{'='*60}")
    print(f"RESULTADO FINAL")
    print(f"{'='*60}")
    print(f"✅ Sucesso: {success_count}/{len(files)}")
    print(f"❌ Erros: {error_count}/{len(files)}")
    print(f"{'='*60}\n")
    
    return 0 if error_count == 0 else 1

if __name__ == '__main__':
    sys.exit(main())
