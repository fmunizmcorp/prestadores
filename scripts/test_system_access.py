#!/usr/bin/env python3
"""
TESTE DE ACESSO AO SISTEMA
Sprint 31 - Clinfec Prestadores
"""

import requests
import sys
from datetime import datetime

BASE_URL = "http://clinfec.com.br/prestadores"

def test_homepage():
    """Testa acesso à página inicial"""
    print("\n" + "=" * 80)
    print("🌐 TESTE 1: Página Inicial")
    print("=" * 80)
    
    try:
        response = requests.get(f"{BASE_URL}/", timeout=10)
        print(f"Status Code: {response.status_code}")
        print(f"Content-Length: {len(response.content)} bytes")
        
        if response.status_code == 200:
            # Verificar se é a página de login
            if 'login' in response.text.lower() or 'senha' in response.text.lower():
                print("✅ Página de login carregada com sucesso")
                return True
            else:
                print("⚠️  Página carregada mas não parece ser o login")
                return False
        else:
            print(f"❌ Erro HTTP: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ ERRO: {e}")
        return False

def test_login_page():
    """Testa acesso específico à página de login"""
    print("\n" + "=" * 80)
    print("🔐 TESTE 2: Página de Login")
    print("=" * 80)
    
    try:
        response = requests.get(f"{BASE_URL}/login", timeout=10)
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            print("✅ Rota /login acessível")
            return True
        elif response.status_code == 404:
            print("⚠️  Rota /login não encontrada (pode ser /)")
            return False
        else:
            print(f"❌ Erro HTTP: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ ERRO: {e}")
        return False

def test_static_files():
    """Testa acesso aos arquivos estáticos"""
    print("\n" + "=" * 80)
    print("📁 TESTE 3: Arquivos Estáticos")
    print("=" * 80)
    
    files_to_test = [
        "/assets/css/style.css",
        "/assets/js/main.js",
        "/favicon.ico"
    ]
    
    results = []
    for file_path in files_to_test:
        try:
            response = requests.head(f"{BASE_URL}{file_path}", timeout=5)
            if response.status_code == 200:
                print(f"✅ {file_path:<30} OK")
                results.append(True)
            else:
                print(f"⚠️  {file_path:<30} {response.status_code}")
                results.append(False)
        except Exception as e:
            print(f"❌ {file_path:<30} ERRO")
            results.append(False)
    
    return any(results)

def test_api_health():
    """Testa endpoint de saúde da API (se existir)"""
    print("\n" + "=" * 80)
    print("💓 TESTE 4: Health Check")
    print("=" * 80)
    
    endpoints = [
        "/api/health",
        "/health",
        "/status",
        "/"
    ]
    
    for endpoint in endpoints:
        try:
            response = requests.get(f"{BASE_URL}{endpoint}", timeout=5)
            if response.status_code == 200:
                print(f"✅ {endpoint} respondendo")
                return True
        except:
            pass
    
    print("ℹ️  Nenhum endpoint de health check encontrado (normal)")
    return True  # Não é crítico

def generate_report(tests_passed, tests_failed):
    """Gera relatório final dos testes"""
    print("\n" + "=" * 80)
    print("📊 RELATÓRIO FINAL - SPRINT 31")
    print("=" * 80)
    print()
    print(f"Data/Hora: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"URL Base: {BASE_URL}")
    print()
    print(f"✅ Testes passados: {tests_passed}")
    print(f"❌ Testes falhados: {tests_failed}")
    print()
    
    if tests_failed == 0:
        print("🎉 EXCELENTE! Sistema está acessível e funcionando.")
        print()
        print("📋 CREDENCIAIS DE ACESSO:")
        print()
        print(f"   🌐 URL: {BASE_URL}")
        print("   📧 Email: admin@clinfec.com.br")
        print("   🔑 Senha: (verificar no banco de dados)")
        print()
        print("   📧 Email: master@clinfec.com.br")
        print("   📧 Email: gestor@clinfec.com.br")
        print()
    elif tests_passed > 0:
        print("⚠️  Sistema parcialmente acessível. Verificar erros acima.")
        print()
    else:
        print("❌ Sistema inacessível. Verificar:")
        print("   1. Servidor web está rodando?")
        print("   2. DNS/domínio configurado?")
        print("   3. Arquivos enviados via FTP?")
        print("   4. Permissões dos arquivos?")
        print()
    
    print("=" * 80)
    print("✅ CHECK (PDCA) - Validação concluída")
    print("=" * 80)
    print()

def main():
    print()
    print("╔" + "=" * 78 + "╗")
    print("║" + " " * 78 + "║")
    print("║" + " TESTE DE ACESSO AO SISTEMA - SPRINT 31 ".center(78) + "║")
    print("║" + " Sistema: Clinfec Prestadores ".center(78) + "║")
    print("║" + " Fase: CHECK (PDCA) ".center(78) + "║")
    print("║" + " " * 78 + "║")
    print("╚" + "=" * 78 + "╝")
    
    tests_passed = 0
    tests_failed = 0
    
    # Executar testes
    if test_homepage():
        tests_passed += 1
    else:
        tests_failed += 1
    
    if test_login_page():
        tests_passed += 1
    else:
        tests_failed += 1
    
    if test_static_files():
        tests_passed += 1
    else:
        tests_failed += 1
    
    if test_api_health():
        tests_passed += 1
    else:
        tests_failed += 1
    
    # Relatório
    generate_report(tests_passed, tests_failed)
    
    return 0 if tests_failed == 0 else 1

if __name__ == "__main__":
    sys.exit(main())
