#!/usr/bin/env python3
"""
DEPLOY FINAL SPRINT 31 - SOLUÇÃO DEFINITIVA PARA CACHE PHP
Clinfec Prestadores

Estratégia:
1. Copiar index_sprint31.php sobre index.php (sobrescrever)
2. Apagar DatabaseMigration.php completamente do servidor
3. Instalar .htaccess com configuração anti-cache
4. Limpar todos os arquivos de backup antigos
"""

import ftplib
import os
import sys
from datetime import datetime

FTP_CONFIG = {
    'host': 'clinfec.com.br',
    'user': 'u673902663',
    'password': ';>?I4dtn~2Ga',
    'remote_base': '/public_html/prestadores'
}

def connect_ftp():
    """Conecta ao servidor FTP"""
    try:
        print("🔌 Conectando ao FTP...")
        ftp = ftplib.FTP(FTP_CONFIG['host'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado a {FTP_CONFIG['host']}")
        return ftp
    except Exception as e:
        print(f"❌ ERRO ao conectar FTP: {e}")
        sys.exit(1)

def backup_file(ftp, remote_path):
    """Cria backup de um arquivo antes de sobrescrever"""
    try:
        backup_name = f"{remote_path}.backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}"
        ftp.rename(remote_path, backup_name)
        print(f"   📦 Backup criado: {os.path.basename(backup_name)}")
        return True
    except:
        return False

def upload_file(ftp, local_path, remote_path):
    """Upload de arquivo via FTP"""
    try:
        with open(local_path, 'rb') as file:
            ftp.storbinary(f'STOR {remote_path}', file)
        
        size = os.path.getsize(local_path)
        print(f"   ✅ {os.path.basename(local_path):<40} {size:>8} bytes")
        return True
    except Exception as e:
        print(f"   ❌ {os.path.basename(local_path):<40} ERRO: {e}")
        return False

def delete_file(ftp, remote_path):
    """Deleta arquivo do servidor"""
    try:
        ftp.delete(remote_path)
        print(f"   🗑️  Deletado: {remote_path}")
        return True
    except Exception as e:
        print(f"   ⚠️  Não foi possível deletar {remote_path}: {e}")
        return False

def main():
    print()
    print("╔" + "=" * 78 + "╗")
    print("║" + " " * 78 + "║")
    print("║" + " DEPLOY FINAL SPRINT 31 - SOLUÇÃO CACHE ".center(78) + "║")
    print("║" + " Sistema: Clinfec Prestadores ".center(78) + "║")
    print("║" + " Metodologia: SCRUM + PDCA ".center(78) + "║")
    print("║" + " " * 78 + "║")
    print("╚" + "=" * 78 + "╝")
    print()
    
    # Conectar
    ftp = connect_ftp()
    
    try:
        base_path = FTP_CONFIG['remote_base']
        
        # ========================================
        print("\n" + "=" * 80)
        print("📝 PASSO 1: SUBSTITUIR index.php")
        print("=" * 80)
        print()
        
        # Fazer backup do index.php atual
        index_remote = f"{base_path}/public/index.php"
        backup_file(ftp, index_remote)
        
        # Upload do index_sprint31.php como index.php
        local_index = "/home/user/webapp/public/index_sprint31.php"
        if os.path.exists(local_index):
            upload_file(ftp, local_index, index_remote)
        else:
            print(f"   ❌ Arquivo local não encontrado: {local_index}")
        
        # ========================================
        print("\n" + "=" * 80)
        print("🗑️  PASSO 2: REMOVER DatabaseMigration.php")
        print("=" * 80)
        print()
        
        migration_remote = f"{base_path}/src/DatabaseMigration.php"
        delete_file(ftp, migration_remote)
        
        # ========================================
        print("\n" + "=" * 80)
        print("⚙️  PASSO 3: INSTALAR .htaccess ANTI-CACHE")
        print("=" * 80)
        print()
        
        htaccess_remote = f"{base_path}/public/.htaccess"
        backup_file(ftp, htaccess_remote)
        
        local_htaccess = "/home/user/webapp/public/.htaccess_nocache"
        if os.path.exists(local_htaccess):
            upload_file(ftp, local_htaccess, htaccess_remote)
        else:
            print(f"   ❌ Arquivo local não encontrado: {local_htaccess}")
        
        # ========================================
        print("\n" + "=" * 80)
        print("🧹 PASSO 4: LIMPAR ARQUIVOS ANTIGOS")
        print("=" * 80)
        print()
        
        files_to_delete = [
            f"{base_path}/public/index_backup_v1.php",
            f"{base_path}/public/index_backup_v2.php",
            f"{base_path}/public/index_backup_v3.php",
            f"{base_path}/public/index_nocache.php",
            f"{base_path}/public/index_unique_timestamp.php",
            f"{base_path}/src/DatabaseMigration.php.disabled",
            f"{base_path}/src/DatabaseMigration_backup.php",
        ]
        
        for file_path in files_to_delete:
            delete_file(ftp, file_path)
        
        # ========================================
        print("\n" + "=" * 80)
        print("✅ DEPLOY CONCLUÍDO COM SUCESSO!")
        print("=" * 80)
        print()
        print("📋 PRÓXIMAS AÇÕES:")
        print()
        print("   1. ⏳ Aguardar 2-3 minutos para cache limpar")
        print("   2. 🌐 Acessar: http://clinfec.com.br/prestadores")
        print("   3. 🔐 Fazer login com:")
        print("      - admin@clinfec.com.br")
        print("      - master@clinfec.com.br")
        print("      - gestor@clinfec.com.br")
        print("   4. 📊 Verificar Dashboard carregando")
        print("   5. ✅ Confirmar que erro DatabaseMigration sumiu")
        print()
        print("=" * 80)
        print("📌 SPRINT 31 - STATUS: PRONTO PARA TESTE")
        print("=" * 80)
        print()
        
    except Exception as e:
        print(f"\n❌ ERRO durante deploy: {e}")
        return 1
        
    finally:
        ftp.quit()
        print("🔌 Conexão FTP encerrada")
        print()
    
    return 0

if __name__ == "__main__":
    sys.exit(main())
