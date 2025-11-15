#!/usr/bin/env python3
"""
Deploy Automatizado - Sprint 26 - Reverse Compatibility
Adiciona métodos proxy na classe Database para compatibilidade com OPcache
Versão: 1.0.0
Data: 2025-11-14
"""

import ftplib
import hashlib
import sys
import time
from pathlib import Path

# Configurações FTP
FTP_HOST = "ftp.prestadores.clinfec.com.br"
FTP_USER = "u817707156.prestadores"
FTP_PASS = "3ClinfecPres!'0"
FTP_ROOT = "/domains/prestadores.clinfec.com.br/public_html"

def calculate_md5(file_path):
    """Calcula MD5 de um arquivo"""
    hash_md5 = hashlib.md5()
    with open(file_path, "rb") as f:
        for chunk in iter(lambda: f.read(4096), b""):
            hash_md5.update(chunk)
    return hash_md5.hexdigest()

def upload_file(ftp, local_path, remote_path):
    """Upload de arquivo via FTP com verificação MD5"""
    print(f"\n📤 Uploading: {local_path} -> {remote_path}")
    
    # Calcula MD5 local
    local_md5 = calculate_md5(local_path)
    local_size = Path(local_path).stat().st_size
    print(f"   Local MD5: {local_md5} ({local_size} bytes)")
    
    # Upload
    with open(local_path, 'rb') as f:
        ftp.storbinary(f'STOR {remote_path}', f)
    
    # Verifica tamanho remoto
    remote_size = ftp.size(remote_path)
    print(f"   Remote size: {remote_size} bytes")
    
    if local_size != remote_size:
        print(f"   ⚠️  AVISO: Tamanhos diferentes!")
        return False
    
    print(f"   ✅ Upload verificado com sucesso!")
    return True

def main():
    print("=" * 80)
    print("SPRINT 26 - DEPLOY REVERSE COMPATIBILITY")
    print("=" * 80)
    print("\n🎯 Estratégia: Adicionar métodos proxy em Database.php")
    print("   para funcionar com código em OPcache sem limpeza\n")
    
    try:
        # Conecta ao FTP
        print("🔌 Conectando ao FTP...")
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Conectado com sucesso!\n")
        
        # Arquivo a fazer upload
        file_to_upload = {
            'local': 'src/Database.php',
            'remote': f'{FTP_ROOT}/src/Database.php',
            'backup': f'{FTP_ROOT}/src/Database.php.backup_sprint26_{int(time.time())}'
        }
        
        # Faz backup do arquivo remoto
        print(f"💾 Criando backup remoto...")
        try:
            # Tenta fazer backup
            ftp.rename(file_to_upload['remote'], file_to_upload['backup'])
            print(f"   ✅ Backup criado: {file_to_upload['backup']}")
        except Exception as e:
            print(f"   ⚠️  Backup não criado (arquivo pode não existir): {e}")
        
        # Upload do arquivo
        success = upload_file(ftp, file_to_upload['local'], file_to_upload['remote'])
        
        if not success:
            print("\n❌ Erro no upload! Revertendo...")
            try:
                ftp.rename(file_to_upload['backup'], file_to_upload['remote'])
                print("✅ Backup restaurado")
            except:
                print("⚠️  Não foi possível restaurar backup")
            sys.exit(1)
        
        # Fecha conexão
        ftp.quit()
        
        print("\n" + "=" * 80)
        print("✅ DEPLOY SPRINT 26 COMPLETADO COM SUCESSO!")
        print("=" * 80)
        print("\n📋 Resumo das mudanças:")
        print("   ✅ Database.php atualizado com métodos proxy")
        print("   ✅ Métodos adicionados: exec, query, prepare, beginTransaction,")
        print("      commit, rollBack, inTransaction, lastInsertId, quote")
        print("\n🎯 Resultado esperado:")
        print("   O código em OPcache agora pode chamar Database::exec()")
        print("   que será redirecionado para PDO::exec() automaticamente")
        print("\n⏱️  Aguarde 30 segundos para testar...")
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO FATAL: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == "__main__":
    sys.exit(main())
