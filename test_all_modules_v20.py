#!/usr/bin/env python3
"""
E2E TEST - ALL 8 MODULES POST-SPRINT 50
Tests all system modules after critical bug fixes
"""

import requests
import sys
from datetime import datetime

BASE_URL = 'https://prestadores.clinfec.com.br'

# Test configuration
MODULES = [
    {
        'name': 'Login',
        'url': f'{BASE_URL}/?page=login',
        'success_indicators': ['login', 'senha', 'entrar'],
        'critical': True
    },
    {
        'name': 'Dashboard',
        'url': f'{BASE_URL}/?page=dashboard',
        'success_indicators': ['dashboard', 'bem-vindo'],
        'critical': True
    },
    {
        'name': 'Empresas Prestadoras',
        'url': f'{BASE_URL}/?page=empresas-prestadoras',
        'success_indicators': ['empresas prestadoras', 'nova empresa'],
        'critical': True,
        'fixed_in': 'Sprint 44'
    },
    {
        'name': 'Empresas Tomadoras',
        'url': f'{BASE_URL}/?page=empresas-tomadoras',
        'success_indicators': ['empresas tomadoras', 'nova empresa'],
        'critical': True,
        'fixed_in': 'Sprint 49'
    },
    {
        'name': 'Serviços',
        'url': f'{BASE_URL}/?page=servicos',
        'success_indicators': ['serviços', 'novo serviço'],
        'critical': True,
        'fixed_in': 'Sprint 45'
    },
    {
        'name': 'Contratos',
        'url': f'{BASE_URL}/?page=contratos',
        'success_indicators': ['contratos', 'novo contrato'],
        'critical': True,
        'fixed_in': 'Sprint 46'
    },
    {
        'name': 'Projetos',
        'url': f'{BASE_URL}/?page=projetos',
        'success_indicators': ['projetos', 'novo projeto'],
        'critical': True,
        'fixed_in': 'Sprint 47'
    },
    {
        'name': 'Usuários',
        'url': f'{BASE_URL}/?page=usuarios',
        'success_indicators': ['usuários', 'novo usuário'],
        'critical': False
    }
]

def test_module(module):
    """Testa um módulo específico"""
    try:
        response = requests.get(module['url'], timeout=10, allow_redirects=True)
        
        # Check HTTP status
        if response.status_code != 200:
            return {
                'success': False,
                'status_code': response.status_code,
                'error': f'HTTP {response.status_code}'
            }
        
        # Check content indicators
        content_lower = response.text.lower()
        indicators_found = sum(1 for indicator in module['success_indicators'] 
                              if indicator.lower() in content_lower)
        
        # Check for fatal errors
        has_fatal_error = any(err in content_lower for err in [
            'fatal error',
            'typeerror',
            'parse error',
            'call to a member function on null',
            'unsupported operand types'
        ])
        
        success = indicators_found > 0 and not has_fatal_error
        
        return {
            'success': success,
            'status_code': response.status_code,
            'indicators_found': indicators_found,
            'total_indicators': len(module['success_indicators']),
            'has_fatal_error': has_fatal_error,
            'response_size': len(response.text)
        }
        
    except requests.exceptions.Timeout:
        return {'success': False, 'error': 'Timeout (10s)'}
    except requests.exceptions.RequestException as e:
        return {'success': False, 'error': str(e)}

def main():
    print("=" * 80)
    print("E2E TEST - ALL MODULES POST-SPRINT 50")
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 80)
    
    results = []
    passed = 0
    failed = 0
    critical_passed = 0
    critical_failed = 0
    
    for i, module in enumerate(MODULES, 1):
        print(f"\n[{i}/{len(MODULES)}] Testing: {module['name']}")
        if 'fixed_in' in module:
            print(f"    🔧 Fixed in: {module['fixed_in']}")
        
        result = test_module(module)
        result['module'] = module['name']
        result['critical'] = module.get('critical', False)
        result['fixed_in'] = module.get('fixed_in', '')
        results.append(result)
        
        if result['success']:
            print(f"    ✅ PASSED")
            if 'status_code' in result:
                print(f"       HTTP {result['status_code']} | Indicators: {result['indicators_found']}/{result['total_indicators']}")
            passed += 1
            if result['critical']:
                critical_passed += 1
        else:
            print(f"    ❌ FAILED")
            if 'error' in result:
                print(f"       Error: {result['error']}")
            elif 'has_fatal_error' in result and result['has_fatal_error']:
                print(f"       Fatal PHP error detected!")
            failed += 1
            if result['critical']:
                critical_failed += 1
    
    # Final Report
    print("\n" + "=" * 80)
    print("FINAL RESULTS")
    print("=" * 80)
    
    total = len(MODULES)
    percentage = (passed / total) * 100 if total > 0 else 0
    
    print(f"\n📊 Overall Statistics:")
    print(f"   Total Modules: {total}")
    print(f"   Passed: {passed} ({percentage:.0f}%)")
    print(f"   Failed: {failed}")
    
    print(f"\n🎯 Critical Modules:")
    critical_total = sum(1 for m in MODULES if m.get('critical', False))
    critical_percentage = (critical_passed / critical_total * 100) if critical_total > 0 else 0
    print(f"   Critical Passed: {critical_passed}/{critical_total} ({critical_percentage:.0f}%)")
    if critical_failed > 0:
        print(f"   Critical Failed: {critical_failed}")
    
    # List fixed modules
    fixed_modules = [r for r in results if r.get('fixed_in')]
    if fixed_modules:
        print(f"\n🔧 Fixed Modules Status:")
        for r in fixed_modules:
            status = "✅ WORKING" if r['success'] else "❌ STILL BROKEN"
            print(f"   {r['module']}: {status} (Fixed in {r['fixed_in']})")
    
    # Success determination
    print("\n" + "=" * 80)
    if critical_failed == 0:
        print("✅ ALL CRITICAL MODULES WORKING!")
        print("🎉 SYSTEM IS OPERATIONAL")
        return 0
    else:
        print(f"⚠️  {critical_failed} CRITICAL MODULE(S) STILL FAILING")
        print("System requires additional fixes")
        return 1

if __name__ == '__main__':
    sys.exit(main())
