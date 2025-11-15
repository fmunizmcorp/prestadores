#!/usr/bin/env python3
"""
Sprint 25 - Deploy Alternative Solutions
Upload novo index_v2.php, .htaccess e php.ini
"""
import ftplib
import glob
from datetime import datetime

FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_REMOTE_DIR = "/domains/clinfec.com.br/public_html/prestadores/public"

print("\n" + "="*80)
print("SPRINT 25 - DEPLOY SOLUÇÕES ALTERNATIVAS")
print("="*80)
print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
print("Objetivo: Contornar OPcache com arquivos novos\n")

try:
    # Connect
    print("📡 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado")
    
    ftp.cwd(FTP_REMOTE_DIR)
    print(f"📂 Diretório: {FTP_REMOTE_DIR}\n")
    
    # Files to upload
    files_to_upload = [
        ('public/index_v2_1763076782.php', 'index_v2_1763076782.php'),
        ('public/.htaccess', '.htaccess'),
        ('public/php.ini', 'php.ini'),
        ('public/.user.ini', '.user.ini'),
    ]
    
    uploaded = []
    
    for local_path, remote_name in files_to_upload:
        try:
            print(f"📤 Uploading {remote_name}...")
            with open(local_path, 'rb') as f:
                ftp.storbinary(f'STOR {remote_name}', f)
            
            size = ftp.size(remote_name)
            print(f"   ✅ {size:,} bytes")
            uploaded.append(remote_name)
        except FileNotFoundError:
            print(f"   ⚠️  Arquivo local não encontrado: {local_path}")
        except Exception as e:
            print(f"   ❌ Erro: {e}")
    
    ftp.quit()
    
    print("\n" + "="*80)
    print("RESULTADO DO DEPLOY")
    print("="*80)
    print(f"\n✅ {len(uploaded)}/{len(files_to_upload)} arquivos uploaded:")
    for f in uploaded:
        print(f"   - {f}")
    
    print("\n" + "="*80)
    print("PRÓXIMOS PASSOS")
    print("="*80)
    print("\n1. Aguardar 30 segundos para .htaccess e php.ini serem processados")
    print("2. Testar: https://clinfec.com.br/prestadores/")
    print("3. O sistema deve usar index_v2.php (novo arquivo, sem cache)")
    print("4. Se ainda der erro, significa que OPcache é ainda mais profundo\n")
    
    print("="*80)
    
except Exception as e:
    print(f"\n❌ ERRO: {e}")
    import traceback
    traceback.print_exc()
