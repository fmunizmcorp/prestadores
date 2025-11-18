#!/usr/bin/env python3
"""
FTP Explorer - Navegar estrutura FTP Hostinger
"""

import ftplib
import sys

def explore_ftp():
    try:
        # Conectar
        ftp = ftplib.FTP('ftp.clinfec.com.br')
        ftp.login('u673902663.genspark1', 'Genspark1@')
        
        print("=== CONECTADO COM SUCESSO ===")
        print(f"Diretório inicial: {ftp.pwd()}\n")
        
        # Tentar ir para domains/clinfec.com.br
        paths_to_try = [
            '/',
            '/domains',
            '/domains/clinfec.com.br',
            '/domains/clinfec.com.br/public_html',
            '/domains/clinfec.com.br/public_html/prestadores',
            '../domains/clinfec.com.br/public_html/prestadores',
            '../../domains/clinfec.com.br/public_html/prestadores',
        ]
        
        for path in paths_to_try:
            try:
                print(f"\n{'='*60}")
                print(f"Tentando: {path}")
                print(f"{'='*60}")
                
                ftp.cwd(path)
                current = ftp.pwd()
                print(f"✅ SUCESSO! Path atual: {current}")
                
                # Listar conteúdo
                print("\nArquivos/pastas:")
                items = []
                ftp.retrlines('LIST', items.append)
                
                for item in items[:20]:  # Mostrar primeiros 20
                    print(f"  {item}")
                
                if len(items) > 20:
                    print(f"\n  ... e mais {len(items) - 20} itens")
                
            except ftplib.error_perm as e:
                print(f"❌ Acesso negado: {e}")
            except Exception as e:
                print(f"❌ Erro: {e}")
        
        # Tentar voltar para raiz e listar tudo
        print(f"\n{'='*60}")
        print("LISTAGEM COMPLETA DA RAIZ")
        print(f"{'='*60}")
        
        try:
            ftp.cwd('/')
            items = []
            ftp.retrlines('LIST', items.append)
            
            for item in items:
                print(f"  {item}")
                
        except Exception as e:
            print(f"Erro ao listar raiz: {e}")
        
        ftp.quit()
        
    except Exception as e:
        print(f"ERRO FATAL: {e}")
        return 1
    
    return 0

if __name__ == '__main__':
    sys.exit(explore_ftp())
