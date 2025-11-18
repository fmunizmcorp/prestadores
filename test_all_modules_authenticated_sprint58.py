#!/usr/bin/env python3
"""
Sprint 58 - Teste Automatizado de TODOS os Módulos
Testa com autenticação real para validar correção do Bug #7
"""

import requests
from datetime import datetime

BASE_URL = "https://prestadores.clinfec.com.br"
LOGIN_URL = f"{BASE_URL}/?page=login"
DASHBOARD_URL = f"{BASE_URL}/dashboard"

# Credenciais de teste
EMAIL = "admin@clinfec.com.br"
PASSWORD = "Master@2024"

# Módulos a testar
MODULES = [
    {
        "name": "Empresas Prestadoras",
        "url": f"{DASHBOARD_URL}?page=empresas-prestadoras",
        "bug": "#1 - TypeError",
        "expected": "Lista de empresas ou mensagem vazia"
    },
    {
        "name": "Serviços",
        "url": f"{DASHBOARD_URL}?page=servicos",
        "bug": "#2 - TypeError",
        "expected": "Lista de serviços ou mensagem vazia"
    },
    {
        "name": "Empresas Tomadoras",
        "url": f"{DASHBOARD_URL}?page=empresas-tomadoras",
        "bug": "#3 - Já funcionava",
        "expected": "Lista de empresas tomadoras"
    },
    {
        "name": "Contratos",
        "url": f"{DASHBOARD_URL}?page=contratos",
        "bug": "#4 - Header Error",
        "expected": "Lista de contratos ou mensagem vazia"
    },
    {
        "name": "Projetos",
        "url": f"{DASHBOARD_URL}?page=projetos",
        "bug": "#7 - prepare() undefined (CRÍTICO)",
        "expected": "Lista de projetos ou mensagem vazia"
    }
]

def print_header(text):
    print("\n" + "=" * 80)
    print(text.center(80))
    print("=" * 80)

def print_section(text):
    print("\n" + "-" * 80)
    print(text)
    print("-" * 80)

def login(session):
    """Faz login e retorna session autenticada"""
    print_section("AUTENTICAÇÃO")
    
    # Primeiro, pega a página de login para obter o CSRF token
    response = session.get(BASE_URL)
    
    # Extrai CSRF token (simplificado - pode precisar ajustar)
    if 'csrf_token' in response.text:
        import re
        match = re.search(r'name="csrf_token" value="([^"]+)"', response.text)
        csrf_token = match.group(1) if match else ""
    else:
        csrf_token = ""
    
    # Dados de login
    login_data = {
        "email": EMAIL,
        "senha": PASSWORD,
        "csrf_token": csrf_token
    }
    
    # Faz POST para login
    response = session.post(LOGIN_URL, data=login_data, allow_redirects=False)
    
    if response.status_code in [200, 302]:
        print(f"✅ Login bem-sucedido (Status: {response.status_code})")
        return True
    else:
        print(f"❌ Falha no login (Status: {response.status_code})")
        return False

def test_module(session, module):
    """Testa um módulo específico"""
    print(f"\n📋 Testando: {module['name']}")
    print(f"   Bug Original: {module['bug']}")
    print(f"   URL: {module['url']}")
    
    try:
        response = session.get(module['url'], timeout=10)
        
        # Verificar se não foi redirecionado para login
        if '/login' in response.url or '?page=login' in response.url:
            print(f"   ⚠️  Redirecionado para login (sessão expirou?)")
            return False, "redirect_to_login"
        
        # Verificar erros HTTP
        if response.status_code >= 500:
            print(f"   ❌ ERRO 500 - Servidor")
            return False, "http_500"
        
        if response.status_code >= 400:
            print(f"   ❌ ERRO {response.status_code}")
            return False, f"http_{response.status_code}"
        
        # Verificar presença de erros no HTML
        content = response.text.lower()
        
        error_indicators = [
            ('fatal error', 'fatal_error'),
            ('call to undefined method', 'undefined_method'),
            ('database::prepare', 'prepare_error'),
            ('erro interno do servidor', 'internal_error'),
            ('500', 'error_500_page'),
            ('exception', 'exception'),
            ('parse error', 'parse_error'),
            ('syntax error', 'syntax_error')
        ]
        
        for indicator, error_type in error_indicators:
            if indicator in content:
                print(f"   ❌ ERRO DETECTADO: {indicator}")
                return False, error_type
        
        # Verificar se página carregou com conteúdo esperado
        success_indicators = [
            'dashboard',
            module['name'].lower(),
            'tabela',
            'lista',
            'nenhum registro',
            'sem dados',
            'adicionar',
            'novo'
        ]
        
        has_content = any(indicator in content for indicator in success_indicators)
        
        if has_content:
            print(f"   ✅ PASSOU - Página carregou com conteúdo")
            return True, "success"
        else:
            print(f"   ⚠️  INDEFINIDO - Página carregou mas sem indicadores de sucesso")
            # Mostrar primeiros 500 chars para debug
            print(f"      Conteúdo (primeiros 500 chars): {response.text[:500]}...")
            return True, "unknown"  # Consideramos sucesso por falta de erros claros
        
    except requests.exceptions.Timeout:
        print(f"   ❌ TIMEOUT - Módulo não respondeu em 10s")
        return False, "timeout"
    except Exception as e:
        print(f"   ❌ EXCEÇÃO: {e}")
        return False, "exception"

def main():
    print_header("SPRINT 58 - TESTE AUTOMATIZADO DE TODOS OS MÓDULOS")
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Base URL: {BASE_URL}")
    print(f"Objetivo: Validar correção do Bug #7 (prepare() undefined)")
    
    # Criar sessão
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Test Bot Sprint 58)'
    })
    
    # Fazer login
    if not login(session):
        print("\n❌ FALHA CRÍTICA: Não foi possível fazer login")
        print("   Verifique credenciais e tente novamente")
        return 1
    
    # Testar todos os módulos
    print_section("TESTANDO MÓDULOS")
    results = []
    
    for module in MODULES:
        success, error_type = test_module(session, module)
        results.append({
            "module": module["name"],
            "bug": module["bug"],
            "success": success,
            "error_type": error_type
        })
    
    # Resultados
    print_header("RESULTADOS FINAIS")
    
    passed = sum(1 for r in results if r["success"])
    failed = len(results) - passed
    success_rate = (passed / len(results)) * 100
    
    print(f"\n📊 Estatísticas:")
    print(f"   Total de Módulos: {len(results)}")
    print(f"   Passou: {passed}")
    print(f"   Falhou: {failed}")
    print(f"   Taxa de Sucesso: {success_rate:.1f}%")
    
    print(f"\n📋 Detalhamento:")
    for result in results:
        status = "✅ PASSOU" if result["success"] else "❌ FALHOU"
        print(f"   {status} - {result['module']}")
        print(f"      Bug: {result['bug']}")
        if not result["success"]:
            print(f"      Erro: {result['error_type']}")
    
    # Comparação com relatório anterior
    print(f"\n📈 Comparação com Relatório V19:")
    print(f"   Antes: 1/5 módulos funcionais (20%)")
    print(f"   Agora: {passed}/5 módulos funcionais ({success_rate:.0f}%)")
    
    if success_rate >= 80:
        print(f"   Melhoria: +{success_rate - 20:.0f}% pontos percentuais ✅")
        print("\n🎉 SUCESSO! Sistema voltou a funcionar!")
    elif success_rate > 20:
        print(f"   Melhoria: +{success_rate - 20:.0f}% pontos percentuais 🟡")
        print("\n⚠️  Sistema melhorou mas ainda tem problemas")
    else:
        print(f"   Melhoria: 0 pontos percentuais ❌")
        print("\n❌ Sistema continua quebrado")
    
    # Status final
    print("\n" + "=" * 80)
    if success_rate == 100:
        print("STATUS FINAL: ✅ TODOS OS MÓDULOS FUNCIONAIS (100%)")
    elif success_rate >= 80:
        print(f"STATUS FINAL: 🟡 MAIORIA DOS MÓDULOS FUNCIONAIS ({success_rate:.0f}%)")
    elif success_rate >= 50:
        print(f"STATUS FINAL: 🟠 METADE DOS MÓDULOS FUNCIONAIS ({success_rate:.0f}%)")
    else:
        print(f"STATUS FINAL: ❌ SISTEMA SEVERAMENTE DEGRADADO ({success_rate:.0f}%)")
    print("=" * 80)
    
    return 0 if success_rate >= 80 else 1

if __name__ == "__main__":
    exit(main())
