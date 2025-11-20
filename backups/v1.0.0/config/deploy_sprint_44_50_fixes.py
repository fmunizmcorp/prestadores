#!/usr/bin/env python3
"""
DEPLOY SPRINT 44-50 FIXES
Uploads only the 6 files modified to fix critical bugs
"""

import ftplib
import os
import sys
from pathlib import Path

FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Exact files modified in Sprints 44-50
FILES_TO_UPLOAD = [
    'src/Models/EmpresaPrestadora.php',
    'src/Models/Servico.php',
    'src/Models/Contrato.php',
    'src/Controllers/ProjetoController.php',
    'src/Models/Projeto.php',
    'src/Models/EmpresaTomadora.php'
]

def ensure_dir(ftp, path):
    """Garante que diretório existe no servidor FTP"""
    dirs = []
    while path:
        dirs.append(path)
        path = os.path.dirname(path)
    
    dirs.reverse()
    
    for directory in dirs:
        if directory and directory != '/':
            try:
                ftp.cwd(f'/{directory}')
            except:
                try:
                    ftp.mkd(f'/{directory}')
                except:
                    pass

def upload_file(ftp, local_path, remote_path):
    """Upload de arquivo individual"""
    # Garante que diretório existe
    remote_dir = os.path.dirname(remote_path)
    if remote_dir:
        ensure_dir(ftp, remote_dir)
    
    # Upload
    with open(local_path, 'rb') as f:
        ftp.storbinary(f'STOR /{remote_path}', f)

def main():
    print("=" * 70)
    print("DEPLOY SPRINT 44-50: CRITICAL BUG FIXES")
    print("=" * 70)
    print(f"\n📁 Arquivos a enviar: {len(FILES_TO_UPLOAD)}")
    for f in FILES_TO_UPLOAD:
        print(f"   • {f}")
    
    # Conectar FTP
    print(f"\n🔌 Conectando ao servidor FTP: {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("✓ Conexão estabelecida!")
    except Exception as e:
        print(f"✗ Erro ao conectar: {e}")
        sys.exit(1)
    
    # Upload dos arquivos
    print(f"\n📤 Iniciando upload...")
    uploaded = 0
    failed = 0
    
    for file_path in FILES_TO_UPLOAD:
        if not os.path.exists(file_path):
            print(f"   ✗ Arquivo não encontrado: {file_path}")
            failed += 1
            continue
        
        try:
            upload_file(ftp, file_path, file_path)
            print(f"   ✓ {file_path}")
            uploaded += 1
        except Exception as e:
            print(f"   ✗ Erro em {file_path}: {e}")
            failed += 1
    
    # Resultado
    print("\n" + "=" * 70)
    print(f"✅ UPLOAD CONCLUÍDO")
    print(f"   Enviados: {uploaded}/{len(FILES_TO_UPLOAD)}")
    if failed > 0:
        print(f"   Falhas: {failed}")
    print("=" * 70)
    
    ftp.quit()
    
    if failed == 0:
        print("\n🎉 DEPLOY 100% SUCESSO!")
        return 0
    else:
        print(f"\n⚠️  DEPLOY PARCIAL - {failed} falha(s)")
        return 1

if __name__ == '__main__':
    sys.exit(main())
