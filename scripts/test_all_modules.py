#!/usr/bin/env python3
"""
Test All Modules E2E
Verifica se todos os módulos estão respondendo corretamente
"""

import requests
import time
from datetime import datetime

BASE_URL = 'https://prestadores.clinfec.com.br'

# Páginas a testar
PAGES_TO_TEST = [
    {
        'name': 'Login',
        'url': '/?page=login',
        'success_indicators': ['<!DOCTYPE html>', 'Sistema Clinfec', 'E-mail', 'Senha'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
    {
        'name': 'Dashboard',
        'url': '/?page=dashboard',
        'success_indicators': ['<!DOCTYPE html>'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
    {
        'name': 'Empresas Tomadoras',
        'url': '/?page=empresas-tomadoras',
        'success_indicators': ['<!DOCTYPE html>'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
    {
        'name': 'Empresas Prestadoras',
        'url': '/?page=empresas-prestadoras',
        'success_indicators': ['<!DOCTYPE html>'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
    {
        'name': 'Contratos',
        'url': '/?page=contratos',
        'success_indicators': ['<!DOCTYPE html>'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
    {
        'name': 'Projetos',
        'url': '/?page=projetos',
        'success_indicators': ['<!DOCTYPE html>'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
    {
        'name': 'Atividades',
        'url': '/?page=atividades',
        'success_indicators': ['<!DOCTYPE html>'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
    {
        'name': 'Serviços',
        'url': '/?page=servicos',
        'success_indicators': ['<!DOCTYPE html>'],
        'fail_indicators': ['Fatal error', 'Class', 'not found', 'Parse error']
    },
]

def test_page(page):
    """Test a single page"""
    url = BASE_URL + page['url']
    
    try:
        response = requests.get(url, timeout=10, allow_redirects=True)
        content = response.text
        
        # Check for errors
        for fail_indicator in page['fail_indicators']:
            if fail_indicator in content:
                return {
                    'status': 'FAIL',
                    'reason': f'Found error indicator: {fail_indicator}',
                    'status_code': response.status_code,
                    'snippet': content[:500]
                }
        
        # Check for success indicators
        found_indicators = []
        for success_indicator in page['success_indicators']:
            if success_indicator in content:
                found_indicators.append(success_indicator)
        
        if len(found_indicators) >= 1:  # At least one success indicator
            return {
                'status': 'PASS',
                'found': found_indicators,
                'status_code': response.status_code,
                'content_length': len(content)
            }
        else:
            return {
                'status': 'WARN',
                'reason': 'No success indicators found (might be redirect)',
                'status_code': response.status_code,
                'snippet': content[:300]
            }
            
    except Exception as e:
        return {
            'status': 'ERROR',
            'reason': str(e)
        }

def main():
    """Run all tests"""
    
    print("🧪 TESTE E2E - Todos os Módulos")
    print(f"Base URL: {BASE_URL}")
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 70)
    print()
    
    results = []
    pass_count = 0
    fail_count = 0
    warn_count = 0
    
    for page in PAGES_TO_TEST:
        print(f"📄 Testando: {page['name']}")
        print(f"   URL: {page['url']}")
        
        result = test_page(page)
        results.append({
            'page': page['name'],
            'result': result
        })
        
        if result['status'] == 'PASS':
            print(f"   ✅ PASS - Status: {result['status_code']}, Tamanho: {result['content_length']} bytes")
            if 'found' in result:
                print(f"      Indicadores encontrados: {len(result['found'])}")
            pass_count += 1
        elif result['status'] == 'FAIL':
            print(f"   ❌ FAIL - {result['reason']}")
            print(f"      Snippet: {result.get('snippet', '')[:200]}...")
            fail_count += 1
        elif result['status'] == 'WARN':
            print(f"   ⚠️  WARN - {result['reason']}")
            warn_count += 1
        else:
            print(f"   ❌ ERROR - {result['reason']}")
            fail_count += 1
        
        print()
        time.sleep(0.5)  # Small delay between requests
    
    # Summary
    print("=" * 70)
    print("📊 RESUMO DOS TESTES")
    print(f"   ✅ PASS:  {pass_count}")
    print(f"   ⚠️  WARN:  {warn_count}")
    print(f"   ❌ FAIL:  {fail_count}")
    print(f"   📝 TOTAL: {len(PAGES_TO_TEST)}")
    print()
    
    if fail_count == 0:
        print("🎉 TODOS OS TESTES PASSARAM!")
        return 0
    else:
        print(f"⚠️  {fail_count} teste(s) falharam")
        return 1

if __name__ == '__main__':
    exit(main())
