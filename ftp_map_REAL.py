#!/usr/bin/env python3
"""
FTP MAPPING - ESTRUTURA REAL
Sem invenções, só o que existe
"""
import ftplib

ftp = ftplib.FTP("ftp.clinfec.com.br")
ftp.login("u673902663.genspark1", "Genspark1@")

print("=== FTP ROOT REAL ===\n")
print(f"PWD: {ftp.pwd()}\n")

print("=== ARQUIVOS NA RAIZ ===")
files = []
ftp.dir(files.append)
for line in files[:30]:
    print(line)

print("\n=== PROCURANDO src/Models ===")

# Tentar encontrar src
try:
    ftp.cwd('src')
    print(f"✅ src/ existe - PWD: {ftp.pwd()}")
    
    try:
        ftp.cwd('Models')
        print(f"✅ src/Models/ existe - PWD: {ftp.pwd()}")
        
        print("\n=== MODELS ATUAIS ===")
        models = []
        ftp.dir(models.append)
        
        for line in models:
            if any(x in line for x in ['NotaFiscal', 'Projeto', 'Atividade']):
                print(line)
    except:
        print("❌ src/Models/ não existe")
        ftp.cwd('..')
except:
    print("❌ src/ não existe na raiz")

ftp.quit()
