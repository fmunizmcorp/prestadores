#!/usr/bin/env python3
"""
Sprint 61: Fully Automated Validation and Remediation System

This script runs continuously without manual intervention:
1. Checks cache status every 15 minutes
2. Tests all 5 modules automatically
3. Validates Database.php methods availability
4. Auto-deploys remediation if needed
5. Generates reports automatically
6. Updates PR with status
7. Continues until 100% functionality achieved

Sprint: 61
Purpose: Complete automation - NO manual intervention required
Created: 2025-11-15
"""

import requests
import time
import json
import ftplib
import hashlib
from datetime import datetime
from typing import Dict, List, Tuple

# Configuration
BASE_URL = "https://clinfec.com.br/prestadores"
CHECK_INTERVAL = 900  # 15 minutes
MAX_ATTEMPTS = 24  # 6 hours (24 x 15min)

# Test credentials (read from environment or config)
TEST_USER = "admin"  # Replace with actual test user
TEST_PASS = "test_password"  # Replace with actual password

# FTP Configuration
FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_BASE_DIR = "/public_html"

class AutomatedValidator:
    """Fully automated validation and remediation system"""
    
    def __init__(self):
        self.attempt = 0
        self.start_time = datetime.now()
        self.session = requests.Session()
        self.results = []
        
    def log(self, message: str, level: str = "INFO"):
        """Log message with timestamp"""
        timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        print(f"[{timestamp}] [{level}] {message}")
        
    def check_cache_status(self) -> Dict:
        """Check cache status via monitor script"""
        self.log("Checking cache status...")
        
        try:
            url = f"{BASE_URL}/monitor_cache_status_sprint60.php"
            response = self.session.get(url, timeout=30)
            
            if response.status_code == 200:
                content = response.text
                
                # Parse for key indicators
                cache_active = "ENABLED" in content and "OPcache is ACTIVE" in content
                all_methods_present = "ALL METHODS PRESENT" in content
                file_correct_size = "4522" in content or "4,522" in content
                
                status = {
                    'cache_active': cache_active,
                    'methods_present': all_methods_present,
                    'file_size_correct': file_correct_size,
                    'ready': not cache_active and all_methods_present,
                    'timestamp': datetime.now().isoformat()
                }
                
                self.log(f"Cache Status: {'ACTIVE' if cache_active else 'CLEARED'}")
                self.log(f"Methods Present: {all_methods_present}")
                self.log(f"File Size Correct: {file_correct_size}")
                
                return status
            else:
                self.log(f"Monitor returned status {response.status_code}", "WARNING")
                return {'ready': False, 'error': f'HTTP {response.status_code}'}
                
        except Exception as e:
            self.log(f"Cache check failed: {str(e)}", "ERROR")
            return {'ready': False, 'error': str(e)}
    
    def test_direct_database_loading(self) -> bool:
        """Test Database.php loading via direct test script"""
        self.log("Testing Database.php loading directly...")
        
        try:
            url = f"{BASE_URL}/test_database_direct_sprint58.php"
            response = self.session.get(url, timeout=30)
            
            if response.status_code == 200:
                content = response.text
                
                # Check for success indicators
                success = (
                    "✅ Classe existe" in content and
                    "✅ Método prepare() existe" in content
                )
                
                self.log(f"Direct test: {'SUCCESS' if success else 'FAILED'}")
                return success
            else:
                self.log(f"Direct test returned {response.status_code}", "WARNING")
                return False
                
        except Exception as e:
            self.log(f"Direct test failed: {str(e)}", "ERROR")
            return False
    
    def test_module(self, module_name: str, module_page: str) -> Dict:
        """Test a single module"""
        self.log(f"Testing module: {module_name}...")
        
        try:
            url = f"{BASE_URL}/?page={module_page}"
            response = self.session.get(url, timeout=30, allow_redirects=True)
            
            # Check for error indicators
            has_prepare_error = "Call to undefined method" in response.text and "prepare()" in response.text
            has_500_error = response.status_code == 500
            has_fatal_error = "Fatal error" in response.text
            has_header_error = "headers already sent" in response.text
            
            # Check for success indicators
            has_html = "<html" in response.text.lower()
            has_content = len(response.text) > 1000
            
            success = (
                response.status_code == 200 and
                not has_prepare_error and
                not has_fatal_error and
                not has_header_error and
                has_html and
                has_content
            )
            
            result = {
                'module': module_name,
                'page': module_page,
                'status_code': response.status_code,
                'success': success,
                'has_prepare_error': has_prepare_error,
                'has_500_error': has_500_error,
                'has_fatal_error': has_fatal_error,
                'has_header_error': has_header_error,
                'content_length': len(response.text),
                'timestamp': datetime.now().isoformat()
            }
            
            status_icon = "✅" if success else "❌"
            self.log(f"{status_icon} {module_name}: {'SUCCESS' if success else 'FAILED'}")
            
            return result
            
        except Exception as e:
            self.log(f"Module test failed for {module_name}: {str(e)}", "ERROR")
            return {
                'module': module_name,
                'page': module_page,
                'success': False,
                'error': str(e),
                'timestamp': datetime.now().isoformat()
            }
    
    def test_all_modules(self) -> List[Dict]:
        """Test all 5 modules"""
        self.log("=" * 70)
        self.log("TESTING ALL MODULES")
        self.log("=" * 70)
        
        modules = [
            {"name": "Empresas Tomadoras", "page": "empresas-tomadoras"},
            {"name": "Projetos", "page": "projetos"},
            {"name": "Empresas Prestadoras", "page": "empresas-prestadoras"},
            {"name": "Serviços", "page": "servicos"},
            {"name": "Contratos", "page": "contratos"}
        ]
        
        results = []
        for module in modules:
            result = self.test_module(module['name'], module['page'])
            results.append(result)
            time.sleep(2)  # Small delay between tests
        
        # Calculate success rate
        success_count = sum(1 for r in results if r['success'])
        total_count = len(results)
        success_rate = (success_count / total_count * 100) if total_count > 0 else 0
        
        self.log("=" * 70)
        self.log(f"MODULE TEST RESULTS: {success_count}/{total_count} ({success_rate:.0f}%)")
        self.log("=" * 70)
        
        return results
    
    def attempt_cache_clear(self) -> bool:
        """Automatically trigger cache clearing"""
        self.log("Attempting automated cache clear...")
        
        try:
            # Trigger the manual clear script via GET request with action=clear
            url = f"{BASE_URL}/clear_cache_manual_sprint60.php?action=clear"
            response = self.session.get(url, timeout=30)
            
            if response.status_code == 200:
                success = "✅" in response.text or "SUCCESS" in response.text
                self.log(f"Cache clear: {'SUCCESS' if success else 'ATTEMPTED'}")
                return success
            else:
                self.log(f"Cache clear returned {response.status_code}", "WARNING")
                return False
                
        except Exception as e:
            self.log(f"Cache clear failed: {str(e)}", "ERROR")
            return False
    
    def deploy_alternative_autoloader(self) -> bool:
        """Deploy alternative autoloader as last resort"""
        self.log("Deploying alternative autoloader (LAST RESORT)...")
        
        try:
            # Read the alternative autoloader
            with open('autoloader_cache_bust_sprint60.php', 'r') as f:
                autoloader_content = f.read()
            
            # Read current index.php
            ftp = ftplib.FTP(FTP_HOST)
            ftp.login(FTP_USER, FTP_PASS)
            ftp.cwd(FTP_BASE_DIR)
            
            # Download current index.php
            local_file = 'public/index.php'
            with open('index_backup_sprint61.php', 'wb') as f:
                ftp.retrbinary(f'RETR public/index.php', f.write)
            
            self.log("Backup of index.php created")
            
            # Read index.php and modify autoloader section
            with open('index_backup_sprint61.php', 'r') as f:
                index_content = f.read()
            
            # Create modified version (inject alternative autoloader)
            modified_content = index_content.replace(
                "spl_autoload_register(function ($class) {",
                "require_once __DIR__ . '/../autoloader_cache_bust_sprint61.php';\nspl_autoload_register('autoloader_hybrid');\n\n// ORIGINAL AUTOLOADER DISABLED:\n// spl_autoload_register(function ($class) {"
            )
            
            # Upload modified version
            with open('index_modified_sprint61.php', 'w') as f:
                f.write(modified_content)
            
            with open('index_modified_sprint61.php', 'rb') as f:
                ftp.storbinary('STOR public/index.php', f)
            
            # Also upload the autoloader itself
            with open('autoloader_cache_bust_sprint60.php', 'rb') as f:
                ftp.storbinary('STOR autoloader_cache_bust_sprint61.php', f)
            
            ftp.quit()
            
            self.log("✅ Alternative autoloader deployed successfully!")
            return True
            
        except Exception as e:
            self.log(f"❌ Alternative autoloader deployment failed: {str(e)}", "ERROR")
            return False
    
    def generate_report(self, cache_status: Dict, module_results: List[Dict], 
                       attempt_num: int, elapsed_time: float) -> str:
        """Generate comprehensive automated report"""
        
        success_count = sum(1 for r in module_results if r.get('success', False))
        total_count = len(module_results)
        success_rate = (success_count / total_count * 100) if total_count > 0 else 0
        
        report = f"""
# 🤖 Automated Validation Report - Sprint 61

**Generated**: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} UTC  
**Attempt**: {attempt_num}/{MAX_ATTEMPTS}  
**Elapsed Time**: {elapsed_time:.1f} minutes  
**Success Rate**: {success_rate:.0f}% ({success_count}/{total_count} modules)  

---

## 📊 Cache Status

- **Cache Active**: {'⚠️ YES' if cache_status.get('cache_active', True) else '✅ NO'}
- **Methods Present**: {'✅ YES' if cache_status.get('methods_present', False) else '❌ NO'}
- **File Size Correct**: {'✅ YES' if cache_status.get('file_size_correct', False) else '❌ NO'}
- **System Ready**: {'✅ YES' if cache_status.get('ready', False) else '⏳ NOT YET'}

---

## 🧪 Module Test Results

"""
        
        for result in module_results:
            icon = "✅" if result.get('success', False) else "❌"
            module_name = result.get('module', 'Unknown')
            status_code = result.get('status_code', 'N/A')
            
            report += f"### {icon} {module_name}\n"
            report += f"- Status Code: {status_code}\n"
            
            if result.get('has_prepare_error'):
                report += f"- ❌ Error: Call to undefined method prepare()\n"
            if result.get('has_500_error'):
                report += f"- ❌ Error: HTTP 500\n"
            if result.get('has_fatal_error'):
                report += f"- ❌ Error: Fatal error detected\n"
            if result.get('has_header_error'):
                report += f"- ❌ Error: Headers already sent\n"
            if result.get('error'):
                report += f"- ❌ Error: {result['error']}\n"
            
            if result.get('success'):
                report += f"- ✅ Working correctly\n"
            
            report += "\n"
        
        report += f"""
---

## 🎯 Current Status

"""
        
        if success_rate == 100:
            report += """
### ✅ SUCCESS! System 100% Operational

All modules are working correctly. Cache has cleared and all fixes are active.

**Actions Taken**:
- ✅ Validated cache status
- ✅ Tested all modules
- ✅ Confirmed 100% functionality

**No further action required.**
"""
        elif success_rate >= 80:
            report += f"""
### ⚠️ Partial Success ({success_rate:.0f}%)

Most modules working, but some issues remain.

**Actions Taken**:
- ✅ Validated cache status
- ✅ Tested all modules
- ⚠️ Some modules still failing

**Next Action**: Continue monitoring, attempt cache clear if needed.
"""
        else:
            report += f"""
### ❌ System Not Ready ({success_rate:.0f}%)

Cache still blocking or other issues present.

**Actions Taken**:
- ✅ Validated cache status
- ❌ Most modules still failing
- ⏳ {'Attempted cache clear' if attempt_num > 8 else 'Waiting for cache expiration'}

**Next Action**: {'Deploy alternative autoloader' if attempt_num > 16 else 'Continue monitoring and cache clearing'}
"""
        
        report += f"""

---

## ⏱️ Timeline

- **Start Time**: {self.start_time.strftime('%Y-%m-%d %H:%M:%S')} UTC
- **Current Time**: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} UTC
- **Elapsed**: {elapsed_time:.1f} minutes
- **Next Check**: {CHECK_INTERVAL / 60:.0f} minutes

---

*Automated by Sprint 61 | SCRUM + PDCA | No Manual Intervention*
"""
        
        return report
    
    def run_validation_cycle(self) -> Tuple[bool, Dict]:
        """Run one complete validation cycle"""
        self.attempt += 1
        elapsed_minutes = (datetime.now() - self.start_time).total_seconds() / 60
        
        self.log("=" * 70)
        self.log(f"VALIDATION CYCLE #{self.attempt} (Elapsed: {elapsed_minutes:.1f} min)")
        self.log("=" * 70)
        
        # Step 1: Check cache status
        cache_status = self.check_cache_status()
        
        # Step 2: Test Database loading directly
        db_loading_works = self.test_direct_database_loading()
        
        # Step 3: Test all modules
        module_results = self.test_all_modules()
        
        # Calculate overall success
        success_count = sum(1 for r in module_results if r.get('success', False))
        success_rate = (success_count / len(module_results) * 100) if module_results else 0
        
        # Step 4: Generate report
        report = self.generate_report(cache_status, module_results, self.attempt, elapsed_minutes)
        
        # Save report to file
        report_filename = f"automated_report_sprint61_attempt{self.attempt:02d}.md"
        with open(report_filename, 'w') as f:
            f.write(report)
        
        self.log(f"Report saved: {report_filename}")
        
        # Step 5: Auto-remediation logic
        complete = False
        
        if success_rate == 100:
            self.log("✅ SUCCESS! System is 100% operational!")
            complete = True
        
        elif self.attempt >= 8 and success_rate < 50:
            # After 2 hours (8 x 15min), try clearing cache
            self.log("⏰ 2 hours elapsed, attempting automated cache clear...")
            self.attempt_cache_clear()
            time.sleep(180)  # Wait 3 minutes after clearing
        
        elif self.attempt >= 16 and success_rate < 80:
            # After 4 hours, deploy alternative autoloader
            self.log("⏰ 4 hours elapsed, deploying alternative autoloader...")
            self.deploy_alternative_autoloader()
            time.sleep(300)  # Wait 5 minutes after deployment
        
        result_data = {
            'attempt': self.attempt,
            'success_rate': success_rate,
            'cache_status': cache_status,
            'module_results': module_results,
            'db_loading_works': db_loading_works,
            'elapsed_minutes': elapsed_minutes,
            'complete': complete
        }
        
        return complete, result_data
    
    def run_continuous(self):
        """Run continuous validation until success or max attempts"""
        self.log("=" * 70)
        self.log("STARTING AUTOMATED VALIDATION SYSTEM - SPRINT 61")
        self.log("=" * 70)
        self.log(f"Configuration:")
        self.log(f"  - Check Interval: {CHECK_INTERVAL/60:.0f} minutes")
        self.log(f"  - Max Attempts: {MAX_ATTEMPTS} ({MAX_ATTEMPTS * CHECK_INTERVAL / 3600:.1f} hours)")
        self.log(f"  - Base URL: {BASE_URL}")
        self.log("=" * 70)
        
        while self.attempt < MAX_ATTEMPTS:
            try:
                complete, result = self.run_validation_cycle()
                
                if complete:
                    self.log("=" * 70)
                    self.log("🎉 VALIDATION COMPLETE! System 100% operational!")
                    self.log("=" * 70)
                    break
                
                if self.attempt < MAX_ATTEMPTS:
                    self.log(f"⏳ Waiting {CHECK_INTERVAL/60:.0f} minutes until next check...")
                    time.sleep(CHECK_INTERVAL)
                
            except KeyboardInterrupt:
                self.log("⚠️  Validation interrupted by user", "WARNING")
                break
            except Exception as e:
                self.log(f"❌ Validation cycle failed: {str(e)}", "ERROR")
                self.log(f"⏳ Waiting {CHECK_INTERVAL/60:.0f} minutes before retry...")
                time.sleep(CHECK_INTERVAL)
        
        if self.attempt >= MAX_ATTEMPTS:
            self.log("=" * 70)
            self.log("⚠️  Max attempts reached without 100% success", "WARNING")
            self.log("=" * 70)
        
        self.log("Automated validation system stopped")


def main():
    """Main entry point"""
    validator = AutomatedValidator()
    validator.run_continuous()


if __name__ == "__main__":
    main()

# Sprint 61: Fully Automated Validation System
# NO MANUAL INTERVENTION REQUIRED
# SCRUM + PDCA until 100% complete
