#!/usr/bin/env python3
"""
Sprint 24 - Verify Current Index.php on Server
Confirma que o arquivo em produção NÃO tem as correções do Sprint 22
"""
import ftplib
from datetime import datetime

FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_REMOTE_DIR = "/domains/clinfec.com.br/public_html/prestadores"

print("\n" + "="*80)
print("SPRINT 24 - VERIFICAÇÃO DO ARQUIVO ATUAL EM PRODUÇÃO")
print("="*80)
print(f"\nData: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
print("Objetivo: Confirmar que index.php NÃO tem correções Sprint 22\n")

try:
    # Connect to FTP
    print("📡 Conectando ao FTP...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Conectado como: {FTP_USER}")
    
    # Navigate to directory
    ftp.cwd(FTP_REMOTE_DIR)
    print(f"📂 Diretório: {FTP_REMOTE_DIR}")
    
    # Download current index.php
    remote_file = "public/index.php"
    local_file = "PRODUCTION_CURRENT_index.php"
    
    print(f"\n📥 Baixando arquivo atual de produção...")
    with open(local_file, 'wb') as f:
        ftp.retrbinary(f'RETR {remote_file}', f.write)
    
    file_size = ftp.size(remote_file)
    print(f"✅ Download completo: {file_size:,} bytes")
    
    ftp.quit()
    
    # Analyze content
    print("\n" + "="*80)
    print("ANÁLISE DO ARQUIVO DE PRODUÇÃO")
    print("="*80)
    
    with open(local_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Count occurrences
    lowercase_count = content.count("'/controllers/")
    uppercase_count = content.count("'/Controllers/")
    
    print(f"\nConteúdo do arquivo:")
    print(f"  Tamanho: {len(content):,} caracteres")
    print(f"  Linhas: {content.count(chr(10)):,}")
    
    print(f"\nOcorrências de paths:")
    print(f"  '/controllers/' (minúsculo): {lowercase_count} ocorrências")
    print(f"  '/Controllers/' (maiúsculo): {uppercase_count} ocorrências")
    
    # Check version
    lines = content.split('\n')
    for i, line in enumerate(lines[:20], 1):
        if 'Version' in line or 'Sprint' in line or 'VERSÃO' in line:
            print(f"\nLinha {i}: {line.strip()}")
    
    print("\n" + "="*80)
    print("RESULTADO DA VERIFICAÇÃO")
    print("="*80)
    
    if lowercase_count > 0:
        print(f"\n🔴 CONFIRMADO: Arquivo em produção TEM '/controllers/' minúsculo!")
        print(f"   {lowercase_count} ocorrências encontradas")
        print(f"\n   ❌ DEPLOY DO SPRINT 22 NÃO FOI APLICADO!")
        print(f"   ❌ Correções /controllers/ → /Controllers/ NÃO estão no servidor")
        
        # Show some examples
        print(f"\n📋 Exemplos de linhas com '/controllers/':")
        count = 0
        for i, line in enumerate(lines, 1):
            if "'/controllers/" in line and count < 3:
                print(f"   Linha {i}: {line.strip()[:100]}")
                count += 1
        
        print(f"\n✅ Precisamos fazer DEPLOY MANUAL!")
        result = False
    else:
        print(f"\n✅ Arquivo parece correto (sem '/controllers/' minúsculo)")
        print(f"   {uppercase_count} ocorrências de '/Controllers/' (maiúsculo)")
        print(f"\n🤔 Mas erro V14 ainda mostra '/controllers/'...")
        print(f"   Pode ser outro arquivo ou cache muito persistente")
        result = True
        
except Exception as e:
    print(f"\n❌ ERRO: {e}")
    import traceback
    traceback.print_exc()
    result = None

print("\n" + "="*80)
exit(0 if result else 1)
