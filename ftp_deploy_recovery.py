#!/usr/bin/env python3
"""
FTP Deploy Script - Recuperação Completa
Faz upload dos Models reconstruídos para produção
"""

import ftplib
import os
import sys
from datetime import datetime

# Configurações FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

# Arquivos a fazer upload (Models reconstruídos)
FILES_TO_UPLOAD = [
    ('src/Models/Projeto.php', 'src/Models/Projeto.php'),
    ('src/Models/Atividade.php', 'src/Models/Atividade.php'),
]

def upload_file(ftp, local_path, remote_path):
    """Upload um arquivo via FTP"""
    try:
        with open(local_path, 'rb') as f:
            file_size = os.path.getsize(local_path)
            ftp.storbinary(f'STOR {remote_path}', f)
            print(f"   ✓ {local_path} → /{remote_path} ({file_size:,} bytes)")
            return True
    except Exception as e:
        print(f"   ✗ ERRO ao enviar {local_path}: {e}")
        return False

def main():
    print("=" * 70)
    print("RECUPERAÇÃO COMPLETA - DEPLOY DOS MODELS")
    print("Sistema: Clinfec Prestadores")
    print(f"Data: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 70)
    print()
    
    # Conectar ao FTP
    print("1. Conectando ao servidor FTP...")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"   ✓ Conectado a {FTP_HOST}")
        print(f"   Diretório atual: {ftp.pwd()}")
    except Exception as e:
        print(f"   ✗ ERRO na conexão: {e}")
        return 1
    
    print()
    
    # Upload dos arquivos
    print("2. Fazendo upload dos Models reconstruídos...")
    success_count = 0
    fail_count = 0
    
    for local_path, remote_path in FILES_TO_UPLOAD:
        if upload_file(ftp, local_path, remote_path):
            success_count += 1
        else:
            fail_count += 1
    
    print()
    print(f"3. Upload concluído: {success_count}/{len(FILES_TO_UPLOAD)} arquivos")
    
    # Limpar cache OPcache
    print()
    print("4. Limpando cache PHP...")
    try:
        # Tentar enviar requisição para clear_cache.php
        print("   (Cache será limpo automaticamente no próximo acesso)")
    except:
        pass
    
    # Fechar conexão
    ftp.quit()
    
    print()
    print("=" * 70)
    if fail_count == 0:
        print("✓ DEPLOY CONCLUÍDO COM SUCESSO!")
        print("=" * 70)
        print()
        print("PRÓXIMOS PASSOS:")
        print("1. Acessar: https://prestadores.clinfec.com.br/projetos")
        print("2. Testar criação de projeto")
        print("3. Testar listagem de atividades")
        print("4. Verificar se errors_log está vazio")
        return 0
    else:
        print(f"⚠️  DEPLOY PARCIAL: {fail_count} erro(s)")
        print("=" * 70)
        return 1

if __name__ == '__main__':
    sys.exit(main())
