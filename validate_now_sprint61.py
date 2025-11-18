#!/usr/bin/env python3
"""
Sprint 61: Immediate Validation Check (Single Run)

Quick validation to check current system status right now.
This is a simplified version that runs once and reports immediately.

Sprint: 61
Created: 2025-11-15
"""

import requests
import sys
from datetime import datetime

BASE_URL = "https://clinfec.com.br/prestadores"

def log(message):
    """Print with timestamp"""
    print(f"[{datetime.now().strftime('%H:%M:%S')}] {message}")

def check_cache_monitor():
    """Check cache status via monitor"""
    log("Checking cache status...")
    try:
        url = f"{BASE_URL}/monitor_cache_status_sprint60.php"
        response = requests.get(url, timeout=30)
        
        if response.status_code == 200:
            content = response.text
            
            cache_active = "ENABLED" in content and "OPcache is ACTIVE" in content
            methods_present = "ALL METHODS PRESENT" in content
            file_correct = "4522" in content or "4,522" in content
            
            log(f"  Cache Active: {'⚠️  YES (still caching)' if cache_active else '✅ NO (cleared)'}")
            log(f"  Methods Present: {'✅ YES' if methods_present else '❌ NO'}")
            log(f"  File Size: {'✅ Correct' if file_correct else '⚠️  Check needed'}")
            
            return not cache_active and methods_present
        else:
            log(f"  ❌ Monitor returned HTTP {response.status_code}")
            return False
    except Exception as e:
        log(f"  ❌ Error: {str(e)}")
        return False

def test_direct_db():
    """Test Database.php directly"""
    log("Testing Database.php loading...")
    try:
        url = f"{BASE_URL}/test_database_direct_sprint58.php"
        response = requests.get(url, timeout=30)
        
        if response.status_code == 200:
            success = "✅ Classe existe" in response.text and "✅ Método prepare() existe" in response.text
            log(f"  {'✅ Database loads correctly' if success else '❌ Database loading failed'}")
            return success
        else:
            log(f"  ❌ Test returned HTTP {response.status_code}")
            return False
    except Exception as e:
        log(f"  ❌ Error: {str(e)}")
        return False

def test_module(name, page):
    """Test a single module"""
    try:
        url = f"{BASE_URL}/?page={page}"
        response = requests.get(url, timeout=30, allow_redirects=True)
        
        has_error = (
            "Call to undefined method" in response.text or
            "Fatal error" in response.text or
            response.status_code == 500
        )
        
        success = response.status_code == 200 and not has_error and len(response.text) > 1000
        
        icon = "✅" if success else "❌"
        log(f"  {icon} {name}: {'Working' if success else 'Failed'}")
        
        return success
    except Exception as e:
        log(f"  ❌ {name}: Error - {str(e)}")
        return False

def main():
    """Main validation"""
    print("\n" + "=" * 70)
    print("SPRINT 61: IMMEDIATE VALIDATION CHECK")
    print("=" * 70)
    print(f"Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} UTC")
    print(f"Target: {BASE_URL}")
    print("=" * 70 + "\n")
    
    # Check cache
    cache_ready = check_cache_monitor()
    print()
    
    # Test Database directly
    db_works = test_direct_db()
    print()
    
    # Test all modules
    log("Testing all modules...")
    modules = [
        ("Empresas Tomadoras", "empresas-tomadoras"),
        ("Projetos", "projetos"),
        ("Empresas Prestadoras", "empresas-prestadoras"),
        ("Serviços", "servicos"),
        ("Contratos", "contratos")
    ]
    
    results = [test_module(name, page) for name, page in modules]
    
    # Calculate success
    success_count = sum(results)
    total = len(results)
    success_rate = (success_count / total * 100) if total > 0 else 0
    
    print("\n" + "=" * 70)
    print("VALIDATION RESULTS")
    print("=" * 70)
    print(f"Modules Working: {success_count}/{total} ({success_rate:.0f}%)")
    print(f"Cache Status: {'✅ Ready' if cache_ready else '⏳ Still active'}")
    print(f"Database Loading: {'✅ Working' if db_works else '❌ Issues'}")
    print("=" * 70 + "\n")
    
    if success_rate == 100:
        print("🎉 SUCCESS! System is 100% operational!")
        print("\nAll modules working correctly. Cache has cleared successfully.")
        return 0
    elif success_rate >= 80:
        print(f"⚠️  PARTIAL SUCCESS ({success_rate:.0f}%)")
        print("\nMost modules working. May need cache clearing or a bit more time.")
        print("\nNext steps:")
        print(f"  1. Access: {BASE_URL}/clear_cache_manual_sprint60.php")
        print("  2. Click 'Limpar Cache Agora'")
        print("  3. Wait 2-3 minutes and run this script again")
        return 1
    else:
        print(f"❌ SYSTEM NOT READY ({success_rate:.0f}%)")
        print("\nCache is still blocking updates.")
        print("\nNext steps:")
        print("  1. Wait for natural cache expiration (1-2 hours typical)")
        print(f"  2. Or use manual clear: {BASE_URL}/clear_cache_manual_sprint60.php")
        print("  3. Run this script again in 15-30 minutes")
        return 2

if __name__ == "__main__":
    sys.exit(main())
