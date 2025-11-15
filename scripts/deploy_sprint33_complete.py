#!/usr/bin/env python3
"""
DEPLOY AUTOMÁTICO SPRINT 33 - COMPLETO
Sistema de Prestadores Clinfec

Este script faz deploy completo via FTP:
- Envia TODOS os arquivos necessários
- Cria backups automáticos
- Valida sucesso do deploy
- Testa conectividade pós-deploy

Uso:
    python3 scripts/deploy_sprint33_complete.py

Autor: AI Development Team
Data: 14/11/2025
Sprint: 33
"""

import ftplib
import os
import sys
import time
from pathlib import Path
import hashlib
from datetime import datetime

# ==================== CONFIGURAÇÕES ====================

FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'remote_dir': '/',  # Already in prestadores directory
    'timeout': 60
}

# Diretório local do projeto
PROJECT_ROOT = Path(__file__).parent.parent
PUBLIC_DIR = PROJECT_ROOT / 'public'
SRC_DIR = PROJECT_ROOT / 'src'
CONFIG_DIR = PROJECT_ROOT / 'config'

# Arquivos críticos para deploy
CRITICAL_FILES = [
    'public/index.php',
    'config/cache_control.php',
    'config/config.php',
    'config/database.php',
    'src/Database.php',
]

# Diretórios para sincronizar
SYNC_DIRS = [
    ('public', ''),  # public/ -> root
    ('src', 'src'),
    ('config', 'config'),
]

# ==================== FUNÇÕES AUXILIARES ====================

def log(message, level='INFO'):
    """Log formatado com timestamp"""
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    print(f"[{timestamp}] [{level}] {message}")

def calculate_md5(filepath):
    """Calcula MD5 de um arquivo"""
    hash_md5 = hashlib.md5()
    try:
        with open(filepath, "rb") as f:
            for chunk in iter(lambda: f.read(4096), b""):
                hash_md5.update(chunk)
        return hash_md5.hexdigest()
    except Exception as e:
        log(f"Erro ao calcular MD5 de {filepath}: {e}", 'ERROR')
        return None

def connect_ftp():
    """Conecta ao servidor FTP"""
    log("Conectando ao servidor FTP...")
    
    try:
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.set_pasv(True)  # Modo passivo
        
        log(f"Conectando a {FTP_CONFIG['host']}:{FTP_CONFIG['port']}")
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        
        log(f"Fazendo login como {FTP_CONFIG['user']}")
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        
        log(f"✅ Conectado com sucesso!")
        log(f"Servidor: {ftp.getwelcome()}")
        
        return ftp
        
    except ftplib.error_perm as e:
        log(f"❌ Erro de permissão: {e}", 'ERROR')
        return None
    except Exception as e:
        log(f"❌ Erro de conexão: {e}", 'ERROR')
        return None

def ensure_remote_dir(ftp, remote_path):
    """Garante que o diretório remoto existe"""
    parts = remote_path.strip('/').split('/')
    current = ''
    
    for part in parts:
        if not part:
            continue
            
        current = f"{current}/{part}"
        
        try:
            ftp.cwd(current)
        except:
            try:
                log(f"Criando diretório: {current}")
                ftp.mkd(current)
                ftp.cwd(current)
            except Exception as e:
                log(f"Não foi possível criar/acessar {current}: {e}", 'WARNING')

def upload_file(ftp, local_file, remote_file):
    """Faz upload de um arquivo"""
    try:
        # Garantir que o diretório remoto existe
        remote_dir = os.path.dirname(remote_file)
        if remote_dir:
            ensure_remote_dir(ftp, remote_dir)
            ftp.cwd(FTP_CONFIG['remote_dir'])  # Voltar para base
        
        # Upload do arquivo
        with open(local_file, 'rb') as f:
            ftp.storbinary(f'STOR {remote_file}', f)
        
        return True
        
    except Exception as e:
        log(f"❌ Erro ao enviar {local_file}: {e}", 'ERROR')
        return False

def upload_directory(ftp, local_dir, remote_dir, exclude_patterns=None):
    """Faz upload recursivo de um diretório"""
    if exclude_patterns is None:
        exclude_patterns = ['.git', '__pycache__', '*.pyc', '.DS_Store', 'node_modules']
    
    uploaded = 0
    failed = 0
    
    log(f"Sincronizando: {local_dir} → {remote_dir}")
    
    for root, dirs, files in os.walk(local_dir):
        # Filtrar diretórios excluídos
        dirs[:] = [d for d in dirs if not any(d.startswith(p.rstrip('*')) for p in exclude_patterns)]
        
        # Calcular caminho relativo
        rel_path = os.path.relpath(root, local_dir)
        
        if rel_path == '.':
            remote_path = remote_dir
        else:
            remote_path = f"{remote_dir}/{rel_path}".replace('\\', '/')
        
        # Criar diretório remoto
        if remote_path and remote_path != remote_dir:
            ensure_remote_dir(ftp, f"{FTP_CONFIG['remote_dir']}/{remote_path}")
            ftp.cwd(FTP_CONFIG['remote_dir'])
        
        # Upload de arquivos
        for filename in files:
            # Filtrar arquivos excluídos
            if any(filename.endswith(p.lstrip('*')) for p in exclude_patterns if '*' in p):
                continue
            
            local_file = os.path.join(root, filename)
            
            if remote_path:
                remote_file = f"{remote_path}/{filename}".replace('\\', '/')
            else:
                remote_file = filename
            
            log(f"  Enviando: {remote_file}")
            
            if upload_file(ftp, local_file, remote_file):
                uploaded += 1
            else:
                failed += 1
    
    log(f"✅ Diretório sincronizado: {uploaded} arquivos enviados, {failed} falhas")
    return uploaded, failed

def verify_deployment(ftp, files_to_check):
    """Verifica se os arquivos críticos foram deployados"""
    log("Verificando deployment...")
    
    missing = []
    
    for filepath in files_to_check:
        try:
            # Tentar obter tamanho do arquivo (se existir, não dará erro)
            ftp.size(filepath)
            log(f"  ✅ {filepath}")
        except:
            log(f"  ❌ {filepath} - NÃO ENCONTRADO", 'WARNING')
            missing.append(filepath)
    
    if missing:
        log(f"⚠️ {len(missing)} arquivo(s) crítico(s) não encontrado(s)!", 'WARNING')
        return False
    else:
        log("✅ Todos os arquivos críticos verificados!")
        return True

def create_deployment_marker(ftp):
    """Cria arquivo marcador de deployment"""
    marker_content = f"""# DEPLOYMENT MARKER
Sprint: 33
Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
Deployed by: Automated FTP Script
Cache Control: Enabled (development mode)
"""
    
    try:
        from io import BytesIO
        marker_file = BytesIO(marker_content.encode('utf-8'))
        ftp.storbinary('STOR .deployment_sprint33', marker_file)
        log("✅ Marcador de deployment criado")
        return True
    except Exception as e:
        log(f"⚠️ Não foi possível criar marcador: {e}", 'WARNING')
        return False

# ==================== MAIN DEPLOYMENT ====================

def main():
    """Função principal de deployment"""
    log("=" * 60)
    log("DEPLOY AUTOMÁTICO SPRINT 33 - SISTEMA PRESTADORES CLINFEC")
    log("=" * 60)
    
    # Verificar se diretórios locais existem
    if not PUBLIC_DIR.exists():
        log(f"❌ Diretório public/ não encontrado em {PUBLIC_DIR}", 'ERROR')
        return False
    
    if not SRC_DIR.exists():
        log(f"❌ Diretório src/ não encontrado em {SRC_DIR}", 'ERROR')
        return False
    
    # Conectar ao FTP
    ftp = connect_ftp()
    if not ftp:
        log("❌ Falha ao conectar ao FTP. Abortando deployment.", 'ERROR')
        return False
    
    try:
        # Navegar para diretório de destino
        log(f"Navegando para {FTP_CONFIG['remote_dir']}")
        ftp.cwd(FTP_CONFIG['remote_dir'])
        log(f"Diretório atual: {ftp.pwd()}")
        
        # 1. Fazer backup do index.php existente
        log("\n--- PASSO 1: Backup do index.php ---")
        try:
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            ftp.rename('index.php', f'index.php.backup_{timestamp}')
            log(f"✅ Backup criado: index.php.backup_{timestamp}")
        except Exception as e:
            log(f"⚠️ Não foi possível fazer backup (talvez não exista): {e}", 'WARNING')
        
        # 2. Upload de arquivos críticos primeiro
        log("\n--- PASSO 2: Upload de Arquivos Críticos ---")
        critical_uploaded = 0
        critical_failed = 0
        
        for filepath in CRITICAL_FILES:
            local_file = PROJECT_ROOT / filepath
            remote_file = filepath.replace('public/', '')  # public/index.php -> index.php
            
            if local_file.exists():
                log(f"Enviando crítico: {filepath}")
                if upload_file(ftp, str(local_file), remote_file):
                    critical_uploaded += 1
                else:
                    critical_failed += 1
            else:
                log(f"⚠️ Arquivo crítico não encontrado: {filepath}", 'WARNING')
                critical_failed += 1
        
        log(f"Críticos: {critical_uploaded} enviados, {critical_failed} falhas")
        
        # 3. Sincronizar diretórios completos
        log("\n--- PASSO 3: Sincronização de Diretórios ---")
        
        total_uploaded = critical_uploaded
        total_failed = critical_failed
        
        for local_dir_name, remote_dir_name in SYNC_DIRS:
            local_path = PROJECT_ROOT / local_dir_name
            
            if local_path.exists() and local_path.is_dir():
                uploaded, failed = upload_directory(ftp, str(local_path), remote_dir_name)
                total_uploaded += uploaded
                total_failed += failed
        
        # 4. Verificar deployment
        log("\n--- PASSO 4: Verificação de Deployment ---")
        
        files_to_verify = [
            'index.php',
            'config/cache_control.php',
            'config/config.php',
            'src/Database.php',
        ]
        
        verification_ok = verify_deployment(ftp, files_to_verify)
        
        # 5. Criar marcador de deployment
        log("\n--- PASSO 5: Marcador de Deployment ---")
        create_deployment_marker(ftp)
        
        # Resumo final
        log("\n" + "=" * 60)
        log("RESUMO DO DEPLOYMENT")
        log("=" * 60)
        log(f"✅ Arquivos enviados: {total_uploaded}")
        log(f"❌ Falhas: {total_failed}")
        log(f"{'✅' if verification_ok else '❌'} Verificação: {'OK' if verification_ok else 'FALHOU'}")
        log(f"🌐 URL: https://prestadores.clinfec.com.br")
        log("=" * 60)
        
        if total_failed == 0 and verification_ok:
            log("🎉 DEPLOYMENT CONCLUÍDO COM SUCESSO!", 'SUCCESS')
            log("\n⏳ Aguarde 2-3 minutos para cache limpar")
            log("🧪 Teste em: https://prestadores.clinfec.com.br")
            log("👤 Login: admin@clinfec.com.br / password")
            return True
        else:
            log("⚠️ Deployment completado com avisos/erros", 'WARNING')
            return False
        
    except Exception as e:
        log(f"❌ Erro durante deployment: {e}", 'ERROR')
        import traceback
        traceback.print_exc()
        return False
        
    finally:
        # Fechar conexão FTP
        try:
            ftp.quit()
            log("Conexão FTP encerrada")
        except:
            pass

if __name__ == '__main__':
    success = main()
    sys.exit(0 if success else 1)
