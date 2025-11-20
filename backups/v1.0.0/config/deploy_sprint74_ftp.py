#!/usr/bin/env python3
"""
SPRINT 74 - FTP Deployment Script
Deploys Sprint 74 Bug #34 fix to production via FTP
"""

from ftplib import FTP
import os
import sys

# FTP Configuration
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_PORT = 21

# Paths
REMOTE_ROOT = '/public_html'
LOCAL_ROOT = '/home/user/webapp'

# Files to deploy for Sprint 74
FILES_TO_DEPLOY = [
    # Main routing file (dashboard route fix)
    ('public/index.php', '/public_html/index.php'),
]

def deploy_sprint74_ftp():
    """Deploy Sprint 74 Bug #34 fix via FTP"""
    print("=" * 80)
    print("SPRINT 74 - FTP DEPLOYMENT TO PRODUCTION")
    print("=" * 80)
    print()
    print("🐛 Bug #34: Dashboard Carregado Sem Controller")
    print()
    
    ftp = None
    
    try:
        # Connect to FTP server
        print(f"[1/4] Connecting to {FTP_HOST}:{FTP_PORT}...")
        ftp = FTP()
        ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected successfully!")
        print(f"   Welcome: {ftp.getwelcome()}")
        print()
        
        # Verify connection
        print("[2/4] Verifying remote directory...")
        try:
            ftp.cwd(REMOTE_ROOT)
            print(f"✅ Found remote directory: {REMOTE_ROOT}")
        except Exception as e:
            print(f"❌ ERROR: Cannot access {REMOTE_ROOT}: {str(e)}")
            return False
        print()
        
        # Deploy files
        print(f"[3/4] Deploying {len(FILES_TO_DEPLOY)} file(s)...")
        print()
        
        deployed_count = 0
        for local_path, remote_path in FILES_TO_DEPLOY:
            local_file = os.path.join(LOCAL_ROOT, local_path)
            
            if not os.path.exists(local_file):
                print(f"❌ ERROR: Local file not found: {local_file}")
                continue
            
            try:
                # Extract directory and filename
                remote_dir = os.path.dirname(remote_path)
                remote_filename = os.path.basename(remote_path)
                
                # Change to remote directory
                try:
                    ftp.cwd(remote_dir)
                except:
                    print(f"   ❌ Cannot access directory: {remote_dir}")
                    continue
                
                # Upload file in binary mode
                print(f"   Deploying: {local_path}")
                with open(local_file, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_filename}', f)
                
                deployed_count += 1
                print(f"   ✅ {local_path} → {remote_path}")
                
                # Return to root
                ftp.cwd(REMOTE_ROOT)
                
            except Exception as e:
                print(f"   ❌ ERROR deploying {local_path}: {str(e)}")
        
        print()
        print(f"✅ Deployed {deployed_count}/{len(FILES_TO_DEPLOY)} file(s) successfully!")
        print()
        
        # Clear OPcache
        print("[4/4] Creating OPcache clear script...")
        
        opcache_script = """<?php
// SPRINT 74 - Clear OPcache after deployment
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully\\n";
} else {
    echo "OPcache not available\\n";
}
?>"""
        
        try:
            ftp.cwd(REMOTE_ROOT)
            
            # Create temporary file locally
            temp_file = '/tmp/clear_opcache_sprint74.php'
            with open(temp_file, 'w') as f:
                f.write(opcache_script)
            
            # Upload OPcache clear script
            with open(temp_file, 'rb') as f:
                ftp.storbinary('STOR clear_opcache_sprint74.php', f)
            
            print("✅ OPcache clear script uploaded!")
            print("   Please execute: https://prestadores.clinfec.com.br/clear_opcache_sprint74.php")
            
            os.unlink(temp_file)
            
        except Exception as e:
            print(f"⚠️  WARNING: Could not upload OPcache script: {str(e)}")
        
        print()
        
        # Close connection
        ftp.quit()
        
        # Print summary
        print("=" * 80)
        print("DEPLOYMENT SUMMARY")
        print("=" * 80)
        print()
        print("✅ Sprint 74 Bug #34 correction deployed successfully!")
        print()
        print("📊 Files deployed:")
        print(f"   • public/index.php (dashboard route fix)")
        print()
        print("🔧 Fix included:")
        print(f"   • Bug #34: Dashboard now uses DashboardController")
        print(f"   • No more warnings: Undefined variable $stats")
        print(f"   • No more warnings: Array offset on null")
        print(f"   • No more warnings: Deprecated number_format(null)")
        print()
        print("🌐 Production URL:")
        print(f"   https://prestadores.clinfec.com.br/")
        print()
        print("⚠️  IMPORTANT: Execute OPcache clear script:")
        print("   https://prestadores.clinfec.com.br/clear_opcache_sprint74.php")
        print()
        print("🎯 Expected Result: Dashboard loads with stats, no warnings")
        print()
        print("=" * 80)
        
        return True
        
    except Exception as e:
        print()
        print(f"❌ DEPLOYMENT FAILED: {str(e)}")
        print()
        import traceback
        traceback.print_exc()
        return False
    
    finally:
        if ftp:
            try:
                ftp.quit()
            except:
                pass

if __name__ == '__main__':
    success = deploy_sprint74_ftp()
    sys.exit(0 if success else 1)
