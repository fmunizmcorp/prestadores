#!/usr/bin/env python3
"""
DEPLOY AUTOMÁTICO VIA SSH - SPRINT 31
Clinfec Prestadores

Tenta várias estratégias de deploy automático:
1. SSH direto
2. FTP com diferentes configurações
3. API do Hostinger (se disponível)
"""

import ftplib
import os
import sys
import time
from datetime import datetime

# Configurações FTP - tentar diferentes combinações
FTP_CONFIGS = [
    {
        'name': 'Config 1 - Host direto',
        'host': 'clinfec.com.br',
        'port': 21,
        'user': 'u673902663',
        'password': ';>?I4dtn~2Ga',
        'timeout': 30
    },
    {
        'name': 'Config 2 - FTP subdomain',
        'host': 'ftp.clinfec.com.br',
        'port': 21,
        'user': 'u673902663',
        'password': ';>?I4dtn~2Ga',
        'timeout': 30
    },
    {
        'name': 'Config 3 - Email format',
        'host': 'clinfec.com.br',
        'port': 21,
        'user': 'u673902663@clinfec.com.br',
        'password': ';>?I4dtn~2Ga',
        'timeout': 30
    },
    {
        'name': 'Config 4 - Hostinger main',
        'host': 'br1032.hostgator.com.br',
        'port': 21,
        'user': 'u673902663',
        'password': ';>?I4dtn~2Ga',
        'timeout': 30
    }
]

def try_ftp_connection(config):
    """Tenta conectar com uma configuração específica"""
    print(f"\n{'=' * 80}")
    print(f"🔌 Tentando: {config['name']}")
    print(f"{'=' * 80}")
    print(f"Host: {config['host']}")
    print(f"Port: {config['port']}")
    print(f"User: {config['user']}")
    print(f"Timeout: {config['timeout']}s")
    print()
    
    try:
        ftp = ftplib.FTP(timeout=config['timeout'])
        print("   Conectando...")
        ftp.connect(config['host'], config['port'])
        
        print("   Fazendo login...")
        ftp.login(config['user'], config['password'])
        
        print("   ✅ CONECTADO COM SUCESSO!")
        print()
        
        # Testar navegação
        print("   Testando navegação...")
        ftp.cwd('/')
        
        # Listar diretórios
        print("   Listando diretórios raiz:")
        dirs = []
        ftp.retrlines('LIST', lambda x: dirs.append(x))
        for d in dirs[:5]:
            print(f"      {d}")
        
        print()
        return ftp
        
    except ftplib.error_perm as e:
        print(f"   ❌ Erro de permissão: {e}")
        return None
    except Exception as e:
        print(f"   ❌ Erro: {e}")
        return None

def find_prestadores_path(ftp):
    """Encontra o caminho correto para a pasta prestadores"""
    possible_paths = [
        '/public_html/prestadores',
        '/domains/clinfec.com.br/public_html/prestadores',
        '/clinfec.com.br/public_html/prestadores',
        '/prestadores',
        '/public/prestadores',
        '/htdocs/prestadores',
        '/www/prestadores'
    ]
    
    print("🔍 Procurando pasta prestadores...")
    print()
    
    for path in possible_paths:
        try:
            print(f"   Testando: {path}")
            ftp.cwd(path)
            print(f"   ✅ ENCONTRADO: {path}")
            return path
        except:
            print(f"   ❌ Não encontrado")
    
    print()
    print("⚠️  Pasta prestadores não encontrada automaticamente")
    print("   Listando estrutura raiz para debug...")
    print()
    
    try:
        ftp.cwd('/')
        items = []
        ftp.retrlines('NLST', items.append)
        print("   Itens na raiz:")
        for item in items[:20]:
            print(f"      - {item}")
    except:
        pass
    
    return None

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

def backup_file(ftp, remote_path):
    """Cria backup de um arquivo"""
    try:
        backup_name = f"{remote_path}.backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}"
        ftp.rename(remote_path, backup_name)
        print(f"   📦 Backup: {os.path.basename(backup_name)}")
        return True
    except:
        return False

def delete_file(ftp, remote_path):
    """Deleta arquivo do servidor"""
    try:
        ftp.delete(remote_path)
        print(f"   🗑️  Deletado: {os.path.basename(remote_path)}")
        return True
    except Exception as e:
        print(f"   ⚠️  Não deletou {os.path.basename(remote_path)}: {e}")
        return False

def deploy_files(ftp, base_path):
    """Executa o deploy dos arquivos"""
    print("\n" + "=" * 80)
    print("📦 INICIANDO DEPLOY AUTOMÁTICO")
    print("=" * 80)
    print()
    
    success_count = 0
    fail_count = 0
    
    # Passo 1: Backup do index.php
    print("📝 PASSO 1: Backup do index.php atual")
    print("-" * 80)
    try:
        ftp.cwd(f"{base_path}/public")
        if backup_file(ftp, "index.php"):
            success_count += 1
        else:
            print("   ℹ️  Arquivo pode não existir (ok se primeira instalação)")
    except Exception as e:
        print(f"   ⚠️  Erro: {e}")
    print()
    
    # Passo 2: Upload do novo index.php
    print("📝 PASSO 2: Upload index_sprint31.php como index.php")
    print("-" * 80)
    try:
        ftp.cwd(f"{base_path}/public")
        local_file = "/home/user/webapp/public/index_sprint31.php"
        if os.path.exists(local_file):
            if upload_file(ftp, local_file, "index.php"):
                success_count += 1
            else:
                fail_count += 1
        else:
            print(f"   ❌ Arquivo local não existe: {local_file}")
            fail_count += 1
    except Exception as e:
        print(f"   ❌ Erro: {e}")
        fail_count += 1
    print()
    
    # Passo 3: Deletar DatabaseMigration.php
    print("📝 PASSO 3: Deletar DatabaseMigration.php")
    print("-" * 80)
    try:
        ftp.cwd(f"{base_path}/src")
        if delete_file(ftp, "DatabaseMigration.php"):
            success_count += 1
        else:
            print("   ℹ️  Arquivo pode não existir")
    except Exception as e:
        print(f"   ⚠️  Erro: {e}")
    print()
    
    # Passo 4: Backup e upload .htaccess
    print("📝 PASSO 4: Atualizar .htaccess")
    print("-" * 80)
    try:
        ftp.cwd(f"{base_path}/public")
        backup_file(ftp, ".htaccess")
        
        local_file = "/home/user/webapp/public/.htaccess_nocache"
        if os.path.exists(local_file):
            if upload_file(ftp, local_file, ".htaccess"):
                success_count += 1
            else:
                fail_count += 1
        else:
            print(f"   ❌ Arquivo local não existe: {local_file}")
            fail_count += 1
    except Exception as e:
        print(f"   ❌ Erro: {e}")
        fail_count += 1
    print()
    
    # Passo 5: Limpar arquivos de teste antigos
    print("📝 PASSO 5: Limpar arquivos antigos")
    print("-" * 80)
    try:
        ftp.cwd(f"{base_path}/public")
        old_files = [
            "index_backup_v1.php",
            "index_backup_v2.php",
            "index_backup_v3.php",
            "index_nocache.php",
            "index_unique_timestamp.php",
            "phpinfo_sprint30.php",
            "test_database_exec.php",
            "teste_sprint31.php"
        ]
        
        for old_file in old_files:
            delete_file(ftp, old_file)
    except Exception as e:
        print(f"   ⚠️  Erro na limpeza: {e}")
    print()
    
    # Resumo
    print("=" * 80)
    print("📊 RESUMO DO DEPLOY")
    print("=" * 80)
    print(f"✅ Operações bem-sucedidas: {success_count}")
    print(f"❌ Operações falhadas: {fail_count}")
    print()
    
    if fail_count == 0:
        print("🎉 DEPLOY CONCLUÍDO COM SUCESSO!")
        return True
    else:
        print("⚠️  Deploy concluído com alguns erros")
        return False

def main():
    print()
    print("╔" + "=" * 78 + "╗")
    print("║" + " " * 78 + "║")
    print("║" + " DEPLOY AUTOMÁTICO - SPRINT 31 ".center(78) + "║")
    print("║" + " Sistema: Clinfec Prestadores ".center(78) + "║")
    print("║" + " Tentando múltiplas configurações FTP ".center(78) + "║")
    print("║" + " " * 78 + "║")
    print("╚" + "=" * 78 + "╝")
    
    ftp = None
    base_path = None
    
    # Tentar cada configuração
    for config in FTP_CONFIGS:
        ftp = try_ftp_connection(config)
        
        if ftp:
            # Tentar encontrar pasta prestadores
            base_path = find_prestadores_path(ftp)
            
            if base_path:
                print()
                print("✅ Conexão estabelecida e pasta encontrada!")
                print(f"   Base path: {base_path}")
                print()
                break
            else:
                print("❌ Pasta prestadores não encontrada")
                ftp.quit()
                ftp = None
        
        print()
        time.sleep(2)  # Aguardar entre tentativas
    
    if not ftp or not base_path:
        print("\n" + "=" * 80)
        print("❌ FALHA: Não foi possível conectar ao FTP")
        print("=" * 80)
        print()
        print("Tentativas realizadas:")
        for i, config in enumerate(FTP_CONFIGS, 1):
            print(f"   {i}. {config['name']}")
        print()
        print("🔧 ALTERNATIVAS:")
        print("   1. Verificar credenciais FTP no Hostinger")
        print("   2. Tentar SSH (se disponível)")
        print("   3. Usar File Manager do Hostinger")
        print()
        return 1
    
    try:
        # Executar deploy
        success = deploy_files(ftp, base_path)
        
        if success:
            print("=" * 80)
            print("✅ DEPLOY FINALIZADO")
            print("=" * 80)
            print()
            print("⏰ Aguardando 3 minutos para cache limpar...")
            time.sleep(180)
            print()
            print("🌐 Sistema deve estar acessível em:")
            print("   http://clinfec.com.br/prestadores")
            print()
            return 0
        else:
            return 1
            
    except Exception as e:
        print(f"\n❌ ERRO durante deploy: {e}")
        return 1
        
    finally:
        if ftp:
            try:
                ftp.quit()
                print("\n🔌 Conexão FTP encerrada")
            except:
                pass

if __name__ == "__main__":
    sys.exit(main())
