#!/usr/bin/env python3
"""
Sprint 60: Deploy Advanced Cache Tools

Deploys 3 new cache monitoring and clearing tools to production:
1. monitor_cache_status_sprint60.php - Advanced cache status monitor
2. autoloader_cache_bust_sprint60.php - Alternative autoloader with cache-busting
3. clear_cache_manual_sprint60.php - User-friendly manual cache clearing tool

Sprint: 60
Created: 2025-11-15
Purpose: Provide user with comprehensive cache management tools
"""

import ftplib
import os
import hashlib
from datetime import datetime

# FTP Configuration
FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_BASE_DIR = "/public_html"

def calculate_md5(file_path):
    """Calculate MD5 hash of a file"""
    md5 = hashlib.md5()
    with open(file_path, 'rb') as f:
        for chunk in iter(lambda: f.read(4096), b''):
            md5.update(chunk)
    return md5.hexdigest()

def deploy_file(ftp, local_path, remote_path, description):
    """Deploy a single file via FTP"""
    print(f"\n📤 Deploying: {description}")
    print(f"   Local:  {local_path}")
    print(f"   Remote: {remote_path}")
    
    # Check local file
    if not os.path.exists(local_path):
        print(f"   ❌ ERROR: Local file not found!")
        return False
    
    file_size = os.path.getsize(local_path)
    md5_hash = calculate_md5(local_path)
    
    print(f"   Size:   {file_size:,} bytes")
    print(f"   MD5:    {md5_hash}")
    
    try:
        # Upload file
        with open(local_path, 'rb') as file:
            ftp.storbinary(f'STOR {remote_path}', file)
        
        # Verify upload
        remote_size = ftp.size(remote_path)
        
        if remote_size == file_size:
            print(f"   ✅ SUCCESS: Uploaded and verified ({remote_size:,} bytes)")
            return True
        else:
            print(f"   ⚠️  WARNING: Size mismatch (expected {file_size}, got {remote_size})")
            return False
            
    except Exception as e:
        print(f"   ❌ ERROR: {str(e)}")
        return False

def main():
    """Main deployment function"""
    print("=" * 70)
    print("SPRINT 60: DEPLOY ADVANCED CACHE TOOLS")
    print("=" * 70)
    print(f"Timestamp: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Target: {FTP_HOST}")
    print()
    
    # Files to deploy
    files_to_deploy = [
        {
            'local': 'monitor_cache_status_sprint60.php',
            'remote': 'monitor_cache_status_sprint60.php',
            'description': 'Advanced Cache Status Monitor (HTML UI)'
        },
        {
            'local': 'autoloader_cache_bust_sprint60.php',
            'remote': 'autoloader_cache_bust_sprint60.php',
            'description': 'Alternative Cache-Busting Autoloader'
        },
        {
            'local': 'clear_cache_manual_sprint60.php',
            'remote': 'clear_cache_manual_sprint60.php',
            'description': 'User-Friendly Manual Cache Clear Tool'
        }
    ]
    
    try:
        # Connect to FTP
        print("🔗 Connecting to FTP server...")
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Connected successfully as {FTP_USER}")
        
        # Change to base directory
        ftp.cwd(FTP_BASE_DIR)
        print(f"📁 Changed to directory: {FTP_BASE_DIR}")
        
        # Deploy each file
        success_count = 0
        fail_count = 0
        
        for file_info in files_to_deploy:
            result = deploy_file(
                ftp, 
                file_info['local'], 
                file_info['remote'], 
                file_info['description']
            )
            
            if result:
                success_count += 1
            else:
                fail_count += 1
        
        # Summary
        print("\n" + "=" * 70)
        print("DEPLOYMENT SUMMARY")
        print("=" * 70)
        print(f"✅ Successful: {success_count}/{len(files_to_deploy)}")
        print(f"❌ Failed:     {fail_count}/{len(files_to_deploy)}")
        
        if fail_count == 0:
            print("\n🎉 ALL FILES DEPLOYED SUCCESSFULLY!")
        else:
            print(f"\n⚠️  {fail_count} file(s) failed to deploy")
        
        # Access URLs
        print("\n" + "=" * 70)
        print("ACCESS URLS")
        print("=" * 70)
        print("📊 Cache Monitor:")
        print("   https://clinfec.com.br/prestadores/monitor_cache_status_sprint60.php")
        print()
        print("🧹 Manual Cache Clear:")
        print("   https://clinfec.com.br/prestadores/clear_cache_manual_sprint60.php")
        print()
        print("📚 Autoloader (use if cache persists):")
        print("   Available at: autoloader_cache_bust_sprint60.php")
        print("   Instructions included in file comments")
        
        # Next steps
        print("\n" + "=" * 70)
        print("NEXT STEPS FOR USER")
        print("=" * 70)
        print("1. Access Cache Monitor to check current status")
        print("2. If issues persist, use Manual Cache Clear tool")
        print("3. If still not resolved after 4+ hours, deploy alternative autoloader")
        print("4. Test all modules after cache clearing")
        print("5. Report results to development team")
        
        # Close FTP
        ftp.quit()
        print("\n✅ FTP connection closed")
        
    except Exception as e:
        print(f"\n❌ DEPLOYMENT FAILED: {str(e)}")
        return 1
    
    return 0 if fail_count == 0 else 1

if __name__ == "__main__":
    exit(main())

# Sprint 60 Deployment Script
# SCRUM + PDCA | Advanced Cache Management Tools
# GenSpark AI Developer
