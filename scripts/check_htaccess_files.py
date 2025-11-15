#!/usr/bin/env python3
"""
Script para verificar e corrigir arquivos .htaccess
Sprint 33 - Resolver problema de roteamento WordPress
"""

import ftplib
import sys
from io import BytesIO

# Configurações FTP
FTP_CONFIG = {
    'host': 'ftp.clinfec.com.br',
    'user': 'u673902663.genspark1',
    'password': 'Genspark1@',
    'port': 21,
    'timeout': 60
}

def download_file(ftp, remote_path):
    """Baixa arquivo do servidor"""
    try:
        buffer = BytesIO()
        ftp.retrbinary(f'RETR {remote_path}', buffer.write)
        content = buffer.getvalue().decode('utf-8', errors='replace')
        return content
    except Exception as e:
        return None

def upload_file(ftp, remote_path, content):
    """Envia arquivo para o servidor"""
    try:
        buffer = BytesIO(content.encode('utf-8'))
        ftp.storbinary(f'STOR {remote_path}', buffer)
        return True
    except Exception as e:
        print(f"❌ Erro ao enviar {remote_path}: {e}")
        return False

def backup_file(ftp, remote_path):
    """Cria backup de arquivo"""
    try:
        backup_path = remote_path + '.backup_sprint33'
        content = download_file(ftp, remote_path)
        if content:
            return upload_file(ftp, backup_path, content)
        return False
    except Exception as e:
        print(f"⚠️ Erro ao fazer backup de {remote_path}: {e}")
        return False

def main():
    print("=" * 70)
    print("VERIFICAÇÃO E CORREÇÃO DE .htaccess FILES")
    print("Sprint 33 - Resolver Roteamento WordPress")
    print("=" * 70)
    
    try:
        # Conectar FTP
        print("\n1️⃣ Conectando ao FTP...")
        ftp = ftplib.FTP(timeout=FTP_CONFIG['timeout'])
        ftp.connect(FTP_CONFIG['host'], FTP_CONFIG['port'])
        ftp.login(FTP_CONFIG['user'], FTP_CONFIG['password'])
        print(f"✅ Conectado: {ftp.getwelcome()}")
        
        # Verificar diretório atual
        current_dir = ftp.pwd()
        print(f"📁 Diretório atual: {current_dir}")
        
        # ARQUIVO 1: .htaccess da raiz do WordPress
        print("\n" + "=" * 70)
        print("2️⃣ VERIFICANDO: /public_html/.htaccess (WordPress root)")
        print("=" * 70)
        
        root_htaccess_path = '/public_html/.htaccess'
        root_content = download_file(ftp, root_htaccess_path)
        
        if root_content:
            print(f"✅ Arquivo encontrado ({len(root_content)} bytes)")
            print("\n📄 CONTEÚDO ATUAL:")
            print("-" * 70)
            print(root_content)
            print("-" * 70)
            
            # Verificar se tem regra para excluir /prestadores
            if 'prestadores' in root_content.lower():
                print("\n✅ Contém menção a 'prestadores'")
            else:
                print("\n⚠️ NÃO contém menção a 'prestadores'")
                print("   WordPress pode estar interceptando /prestadores/")
            
            # Verificar RewriteCond
            if 'RewriteCond' in root_content and 'prestadores' in root_content.lower():
                print("✅ Contém RewriteCond relacionado a prestadores")
            else:
                print("⚠️ Falta RewriteCond para excluir /prestadores do WordPress")
                
        else:
            print("⚠️ Arquivo não encontrado ou vazio")
        
        # ARQUIVO 2: .htaccess da aplicação prestadores
        print("\n" + "=" * 70)
        print("3️⃣ VERIFICANDO: /public_html/prestadores/.htaccess")
        print("=" * 70)
        
        prestadores_htaccess_path = '/public_html/prestadores/.htaccess'
        prestadores_content = download_file(ftp, prestadores_htaccess_path)
        
        if prestadores_content:
            print(f"✅ Arquivo encontrado ({len(prestadores_content)} bytes)")
            print("\n📄 CONTEÚDO ATUAL:")
            print("-" * 70)
            print(prestadores_content)
            print("-" * 70)
        else:
            print("⚠️ Arquivo não encontrado ou vazio")
        
        # ANÁLISE E RECOMENDAÇÕES
        print("\n" + "=" * 70)
        print("4️⃣ ANÁLISE E RECOMENDAÇÕES")
        print("=" * 70)
        
        issues = []
        recommendations = []
        
        # Verificar problema no root
        if root_content:
            if 'prestadores' not in root_content.lower():
                issues.append("WordPress root .htaccess NÃO exclui /prestadores/")
                recommendations.append("Adicionar RewriteCond para excluir /prestadores/")
            
            if 'RewriteCond %{REQUEST_URI} !^/prestadores' not in root_content:
                issues.append("Falta RewriteCond específico: !^/prestadores")
                recommendations.append("Adicionar: RewriteCond %{REQUEST_URI} !^/prestadores")
        
        # Verificar problema no prestadores
        if not prestadores_content or len(prestadores_content) < 50:
            issues.append("Prestadores .htaccess muito simples ou vazio")
            recommendations.append("Adicionar regras de rewrite para o front controller")
        
        if issues:
            print("\n⚠️ PROBLEMAS IDENTIFICADOS:")
            for i, issue in enumerate(issues, 1):
                print(f"   {i}. {issue}")
            
            print("\n💡 RECOMENDAÇÕES:")
            for i, rec in enumerate(recommendations, 1):
                print(f"   {i}. {rec}")
        else:
            print("\n✅ Nenhum problema óbvio identificado")
        
        # PROPOR CORREÇÃO
        print("\n" + "=" * 70)
        print("5️⃣ CORREÇÃO PROPOSTA")
        print("=" * 70)
        
        if issues:
            print("\n📝 Vou criar arquivos .htaccess corrigidos:")
            print("   - /public_html/.htaccess (WordPress root)")
            print("   - /public_html/prestadores/.htaccess (aplicação)")
            print("\n⚠️ ATENÇÃO: Backups serão criados com sufixo .backup_sprint33")
            print("\n⏳ Aguarde confirmação para aplicar correções...")
        else:
            print("\n✅ Não é necessário correção de .htaccess")
            print("   O problema pode ser outro (PHP, permissões, etc.)")
        
        # Fechar conexão
        ftp.quit()
        print("\n" + "=" * 70)
        print("✅ VERIFICAÇÃO CONCLUÍDA")
        print("=" * 70)
        
        return 0
        
    except Exception as e:
        print(f"\n❌ ERRO: {e}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(main())
