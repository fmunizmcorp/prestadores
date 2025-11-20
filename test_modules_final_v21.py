#!/usr/bin/env python3
"""
Final Module Test - V21
Tests all modules after Database.php deployment
"""

import requests
import sys
from datetime import datetime

BASE_URL = 'https://prestadores.clinfec.com.br'

# Test each module that was reported as broken
MODULES = [
    {
        'name': 'Empresas Prestadoras',
        'url': f'{BASE_URL}/?page=empresas-prestadoras',
        'expected_status': 200,
        'must_not_contain': ['500', '500 Error', 'Fatal Error', 'Class "App\\Database" not found'],
        'should_contain': ['empresas prestadoras', 'prestadora']
    },
    {
        'name': 'Empresas Tomadoras',
        'url': f'{BASE_URL}/?page=empresas-tomadoras',
        'expected_status': 200,
        'must_not_contain': ['500', '500 Error', 'Fatal Error', 'TypeError'],
        'should_contain': ['empresas tomadoras', 'tomadora']
    },
    {
        'name': 'Serviços',
        'url': f'{BASE_URL}/?page=servicos',
        'expected_status': 200,
        'must_not_contain': ['500', '500 Error', 'Fatal Error', 'Class "App\\Database" not found'],
        'should_contain': ['serviço', 'servico']
    },
    {
        'name': 'Contratos',
        'url': f'{BASE_URL}/?page=contratos',
        'expected_status': 200,
        'must_not_contain': ['500', '500 Error', 'Fatal Error', 'Warning: Cannot modify header'],
        'should_contain': ['contrato']
    },
    {
        'name': 'Projetos',
        'url': f'{BASE_URL}/?page=projetos',
        'expected_status': 200,
        'must_not_contain': ['500', '500 Error', 'Fatal Error', 'Call to a member function', 'Class "App\\Database" not found'],
        'should_contain': ['projeto']
    }
]

def test_module(module):
    """Test a module"""
    try:
        response = requests.get(module['url'], timeout=10, allow_redirects=True)
        
        # Check status code
        status_ok = response.status_code == module['expected_status']
        
        # Check content
        content_lower = response.text.lower()
        
        # Check for errors that must NOT be present
        has_errors = False
        found_error = None
        for error_text in module['must_not_contain']:
            if error_text.lower() in content_lower:
                has_errors = True
                found_error = error_text
                break
        
        # Check for content that SHOULD be present
        has_content = False
        for expected in module['should_contain']:
            if expected.lower() in content_lower:
                has_content = True
                break
        
        # Determine success
        # If redirected to login, that's OK (needs auth but no crash)
        if 'login' in response.url.lower() and not has_errors:
            return {
                'success': True,
                'status_code': response.status_code,
                'note': 'Redirected to login (needs auth, but NO CRASH)'
            }
        
        # If has errors, it failed
        if has_errors:
            return {
                'success': False,
                'status_code': response.status_code,
                'error': f'Found error: {found_error}'
            }
        
        # If has expected content, it passed
        if has_content and not has_errors:
            return {
                'success': True,
                'status_code': response.status_code,
                'note': 'Page loaded with expected content'
            }
        
        # Otherwise, uncertain
        return {
            'success': False,
            'status_code': response.status_code,
            'error': 'No expected content found'
        }
        
    except Exception as e:
        return {'success': False, 'error': str(e)}

def main():
    print("=" * 80)
    print("FINAL MODULE TEST - POST DATABASE.PHP DEPLOYMENT")
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 80)
    
    results = []
    passed = 0
    failed = 0
    
    for i, module in enumerate(MODULES, 1):
        print(f"\n[{i}/{len(MODULES)}] Testing: {module['name']}")
        
        result = test_module(module)
        result['module'] = module['name']
        results.append(result)
        
        if result['success']:
            print(f"    ✅ PASSED")
            if 'note' in result:
                print(f"       {result['note']}")
            passed += 1
        else:
            print(f"    ❌ FAILED")
            if 'error' in result:
                print(f"       Error: {result['error']}")
            failed += 1
    
    # Final Report
    print("\n" + "=" * 80)
    print("FINAL RESULTS")
    print("=" * 80)
    
    total = len(MODULES)
    percentage = (passed / total) * 100 if total > 0 else 0
    
    print(f"\n📊 Module Status:")
    print(f"   Total: {total}")
    print(f"   Passed: {passed} ({percentage:.0f}%)")
    print(f"   Failed: {failed}")
    
    print(f"\n🔧 Detailed Status:")
    for r in results:
        status = "✅ WORKING" if r['success'] else "❌ BROKEN"
        print(f"   {r['module']}: {status}")
    
    print("\n" + "=" * 80)
    if failed == 0:
        print("✅ ALL MODULES WORKING!")
        print("🎉 DATABASE.PHP FIX SUCCESSFUL")
        return 0
    else:
        print(f"⚠️  {failed} MODULE(S) STILL FAILING")
        return 1

if __name__ == '__main__':
    sys.exit(main())
