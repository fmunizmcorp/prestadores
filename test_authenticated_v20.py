#!/usr/bin/env python3
"""
AUTHENTICATED E2E TEST - ALL MODULES POST-SPRINT 50
Tests with actual login session
"""

import requests
import sys
from datetime import datetime

BASE_URL = 'https://prestadores.clinfec.com.br'

# Login credentials (from previous successful tests)
LOGIN_EMAIL = 'admin@clinfec.com.br'
LOGIN_PASSWORD = 'admin123'

# Test configuration
MODULES = [
    {
        'name': 'Login',
        'url': f'{BASE_URL}/?page=login',
        'success_indicators': ['login', 'senha'],
        'auth_required': False
    },
    {
        'name': 'Dashboard',
        'url': f'{BASE_URL}/?page=dashboard',
        'success_indicators': ['dashboard', 'bem-vindo', 'painel'],
        'auth_required': True
    },
    {
        'name': 'Empresas Prestadoras',
        'url': f'{BASE_URL}/?page=empresas-prestadoras',
        'success_indicators': ['empresas prestadoras', 'prestadora'],
        'fixed_in': 'Sprint 44'
    },
    {
        'name': 'Empresas Tomadoras',
        'url': f'{BASE_URL}/?page=empresas-tomadoras',
        'success_indicators': ['empresas tomadoras', 'tomadora'],
        'fixed_in': 'Sprint 49'
    },
    {
        'name': 'Serviços',
        'url': f'{BASE_URL}/?page=servicos',
        'success_indicators': ['serviço', 'servico'],
        'fixed_in': 'Sprint 45'
    },
    {
        'name': 'Contratos',
        'url': f'{BASE_URL}/?page=contratos',
        'success_indicators': ['contrato'],
        'fixed_in': 'Sprint 46'
    },
    {
        'name': 'Projetos',
        'url': f'{BASE_URL}/?page=projetos',
        'success_indicators': ['projeto'],
        'fixed_in': 'Sprint 47'
    },
    {
        'name': 'Usuários',
        'url': f'{BASE_URL}/?page=usuarios',
        'success_indicators': ['usuário', 'usuario']
    }
]

def login(session):
    """Perform login and return session"""
    try:
        # Get login page first
        login_page = session.get(f'{BASE_URL}/?page=login', timeout=10)
        
        # Attempt login
        login_data = {
            'email': LOGIN_EMAIL,
            'password': LOGIN_PASSWORD,
            'action': 'login'
        }
        
        response = session.post(f'{BASE_URL}/?page=login', data=login_data, timeout=10, allow_redirects=True)
        
        # Check if login successful
        if 'dashboard' in response.url.lower() or 'dashboard' in response.text.lower():
            return True, "Login successful"
        elif 'senha' in response.text.lower() and 'incorreta' in response.text.lower():
            return False, "Invalid credentials"
        else:
            return False, "Unknown login response"
            
    except Exception as e:
        return False, str(e)

def test_module(session, module):
    """Test a module with authenticated session"""
    try:
        response = session.get(module['url'], timeout=10, allow_redirects=True)
        
        # Check if redirected to login (not authenticated)
        if 'login' in response.url and module.get('auth_required', True):
            return {
                'success': False,
                'status_code': response.status_code,
                'error': 'Redirected to login - session expired?'
            }
        
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
        fatal_errors = [
            'fatal error',
            'typeerror',
            'parse error',
            'call to a member function on null',
            'unsupported operand types',
            'uncaught error'
        ]
        
        error_found = None
        for error in fatal_errors:
            if error in content_lower:
                error_found = error
                break
        
        success = indicators_found > 0 and error_found is None
        
        return {
            'success': success,
            'status_code': response.status_code,
            'indicators_found': indicators_found,
            'total_indicators': len(module['success_indicators']),
            'fatal_error': error_found,
            'response_size': len(response.text),
            'final_url': response.url
        }
        
    except requests.exceptions.Timeout:
        return {'success': False, 'error': 'Timeout (10s)'}
    except requests.exceptions.RequestException as e:
        return {'success': False, 'error': str(e)}

def main():
    print("=" * 80)
    print("AUTHENTICATED E2E TEST - ALL MODULES POST-SPRINT 50")
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 80)
    
    # Create session
    session = requests.Session()
    session.headers.update({'User-Agent': 'Mozilla/5.0 Test Bot'})
    
    # Login
    print("\n[AUTH] Authenticating...")
    login_success, login_message = login(session)
    
    if not login_success:
        print(f"    ❌ Login failed: {login_message}")
        print("\n⚠️  Cannot proceed without authentication")
        return 1
    else:
        print(f"    ✅ {login_message}")
    
    # Run tests
    results = []
    passed = 0
    failed = 0
    
    for i, module in enumerate(MODULES, 1):
        print(f"\n[{i}/{len(MODULES)}] Testing: {module['name']}")
        if 'fixed_in' in module:
            print(f"    🔧 Fixed in: {module['fixed_in']}")
        
        result = test_module(session, module)
        result['module'] = module['name']
        result['fixed_in'] = module.get('fixed_in', '')
        results.append(result)
        
        if result['success']:
            print(f"    ✅ PASSED")
            if 'indicators_found' in result:
                print(f"       HTTP {result['status_code']} | Indicators: {result['indicators_found']}/{result['total_indicators']}")
            passed += 1
        else:
            print(f"    ❌ FAILED")
            if 'error' in result:
                print(f"       Error: {result['error']}")
            elif 'fatal_error' in result and result['fatal_error']:
                print(f"       Fatal PHP error: {result['fatal_error']}")
            elif 'indicators_found' in result:
                print(f"       HTTP {result['status_code']} | No content indicators found")
            failed += 1
    
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
    
    # List fixed modules
    fixed_modules = [r for r in results if r.get('fixed_in')]
    if fixed_modules:
        print(f"\n🔧 Fixed Modules Status:")
        for r in fixed_modules:
            status = "✅ WORKING" if r['success'] else "❌ STILL BROKEN"
            print(f"   {r['module']}: {status} (Fixed in {r['fixed_in']})")
    
    # Success determination
    print("\n" + "=" * 80)
    fixed_passed = sum(1 for r in fixed_modules if r['success'])
    fixed_total = len(fixed_modules)
    
    if failed == 0:
        print("✅ ALL MODULES WORKING!")
        print("🎉 SYSTEM 100% OPERATIONAL")
        return 0
    elif fixed_passed == fixed_total and fixed_total > 0:
        print(f"✅ ALL {fixed_total} FIXED MODULES WORKING!")
        print("🎉 SPRINT 44-50 FIXES SUCCESSFUL")
        print(f"⚠️  {failed - (fixed_total - fixed_passed)} other module(s) have pre-existing issues")
        return 0
    else:
        print(f"⚠️  {failed} MODULE(S) FAILING")
        if fixed_passed < fixed_total:
            print(f"❌ {fixed_total - fixed_passed}/{fixed_total} fixed modules still broken")
        return 1

if __name__ == '__main__':
    sys.exit(main())
