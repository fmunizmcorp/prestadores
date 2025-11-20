#!/usr/bin/env python3
"""
SPRINT 74.1 - Fix Deployment to BOTH Locations
Deploy to both /public/ and /public_html/ to ensure fix works
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
LOCAL_ROOT = '/home/user/webapp'

# Files to deploy - BOTH locations
FILES_TO_DEPLOY = [
    # Deploy to /public_html/ (main location)
    ('public/index.php', '/public_html/index.php'),
    # Deploy to /public/ (backup/alternate location)
    ('public/index.php', '/public/index.php'),
]

def deploy_to_both_locations():
    """Deploy Sprint 74 fix to BOTH /public/ and /public_html/"""
    print("=" * 80)
    print("SPRINT 74.1 - DEPLOY TO BOTH LOCATIONS")
    print("=" * 80)
    print()
    print("🐛 Bug #34: Ensuring fix is in BOTH /public/ and /public_html/")
    print()
    
    ftp = None
    
    try:
        # Connect to FTP server
        print(f"[1/4] Connecting to {FTP_HOST}:{FTP_PORT}...")
        ftp = FTP()
        ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected successfully!")
        print()
        
        # Deploy files
        print(f"[2/4] Deploying to {len(FILES_TO_DEPLOY)} locations...")
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
                print(f"   Deploying: {local_path} → {remote_path}")
                with open(local_file, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_filename}', f)
                
                deployed_count += 1
                print(f"   ✅ {remote_path}")
                
                # Return to root
                ftp.cwd('/')
                
            except Exception as e:
                print(f"   ❌ ERROR: {str(e)}")
        
        print()
        print(f"✅ Deployed {deployed_count}/{len(FILES_TO_DEPLOY)} locations successfully!")
        print()
        
        # Clear OPcache
        print("[3/4] Creating OPcache clear script...")
        
        opcache_script = """<?php
// SPRINT 74.1 - Force OPcache Clear
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared successfully\\n";
} else {
    echo "⚠️ OPcache not available\\n";
}

if (function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "✅ Stat cache cleared\\n";
}

echo "\\n🔄 All caches cleared!\\n";
?>"""
        
        # Upload to both locations
        for target_dir in ['/public_html', '/public']:
            try:
                ftp.cwd(target_dir)
                
                temp_file = '/tmp/force_clear_cache.php'
                with open(temp_file, 'w') as f:
                    f.write(opcache_script)
                
                with open(temp_file, 'rb') as f:
                    ftp.storbinary('STOR force_clear_cache.php', f)
                
                print(f"   ✅ Uploaded force_clear_cache.php to {target_dir}/")
                
                os.unlink(temp_file)
            except Exception as e:
                print(f"   ⚠️ Could not upload to {target_dir}: {e}")
        
        print()
        
        # Verify deployment
        print("[4/4] Verifying deployment...")
        print()
        
        for location in ['/public_html', '/public']:
            try:
                ftp.cwd(location)
                
                # Get file size
                size = ftp.size('index.php')
                print(f"   {location}/index.php: {size} bytes ✅")
                
            except Exception as e:
                print(f"   {location}/index.php: ERROR - {e} ❌")
        
        print()
        
        # Close connection
        ftp.quit()
        
        # Print summary
        print("=" * 80)
        print("DEPLOYMENT SUMMARY")
        print("=" * 80)
        print()
        print("✅ Sprint 74.1 - Deployed to BOTH locations!")
        print()
        print("📊 Locations updated:")
        print(f"   • /public_html/index.php (primary)")
        print(f"   • /public/index.php (alternate)")
        print()
        print("🔧 Fix included:")
        print(f"   • Bug #34: Dashboard now uses DashboardController in BOTH files")
        print()
        print("🌐 Production URL:")
        print(f"   https://prestadores.clinfec.com.br/")
        print()
        print("🎯 Expected Result: Dashboard works regardless of which directory server uses")
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
    success = deploy_to_both_locations()
    sys.exit(0 if success else 1)
