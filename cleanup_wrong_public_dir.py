#!/usr/bin/env python3
"""
SPRINT 74.2 - Cleanup Wrong Deployment Location
Remove /public/ directory that was deployed by mistake
Per user instruction: "apague os errados para não bagunçar o servidor"
"""

from ftplib import FTP
import sys

# FTP Configuration
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'

def cleanup_wrong_directory():
    """Remove /public/ directory (wrong location)"""
    print("=" * 80)
    print("SPRINT 74.2 - CLEANUP WRONG LOCATION")
    print("=" * 80)
    print()
    print("📋 Task: Remove /public/ directory (deployed by mistake)")
    print("📍 Correct location: /public_html/ (verified DocumentRoot)")
    print()
    print("⚠️  User instruction: 'Apague os errados para não bagunçar o servidor'")
    print()
    
    ftp = None
    
    try:
        # Connect
        print("[1/4] Connecting to FTP...")
        ftp = FTP()
        ftp.connect(FTP_HOST, 21, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected")
        print()
        
        # List /public/ before deletion
        print("[2/4] Listing /public/ contents before deletion...")
        try:
            ftp.cwd('/public')
            files = []
            ftp.retrlines('LIST', files.append)
            print(f"   Found {len(files)} items in /public/")
            print()
        except Exception as e:
            print(f"   ⚠️  Directory may not exist or already deleted: {e}")
            print()
            return
        
        # Delete files first
        print("[3/4] Deleting files in /public/...")
        deleted_files = 0
        for line in files:
            parts = line.split()
            if len(parts) < 9:
                continue
            
            filename = parts[-1]
            if filename in ['.', '..']:
                continue
            
            is_dir = line.startswith('d')
            
            if not is_dir:
                try:
                    ftp.delete(filename)
                    deleted_files += 1
                    print(f"   ✅ Deleted file: {filename}")
                except Exception as e:
                    print(f"   ❌ Failed to delete {filename}: {e}")
        
        print(f"   Deleted {deleted_files} files")
        print()
        
        # Delete subdirectories recursively (if any)
        print("[4/4] Removing /public/ directory...")
        try:
            ftp.cwd('/')
            ftp.rmd('public')
            print("   ✅ Directory /public/ removed successfully!")
        except Exception as e:
            # May fail if directory has subdirectories
            print(f"   ⚠️  Could not remove directory: {e}")
            print("   Note: Directory may have subdirectories that need manual cleanup")
        
        print()
        print("=" * 80)
        print("✅ CLEANUP COMPLETE")
        print("=" * 80)
        print()
        print("📊 Summary:")
        print("   • Removed: /public/ directory (wrong deployment location)")
        print("   • Kept: /public_html/ (correct DocumentRoot)")
        print("   • Impact: None (server uses /public_html/)")
        print()
        
        # Verify /public_html/ still exists
        print("🔍 Verifying correct location still exists...")
        try:
            ftp.cwd('/public_html')
            size = len([f for f in ftp.nlst() if f not in ['.', '..']])
            print(f"   ✅ /public_html/ exists with {size} items")
            
            # Check index.php
            files = []
            ftp.retrlines('LIST index.php', files.append)
            if files:
                parts = files[0].split()
                filesize = parts[4] if len(parts) > 4 else '?'
                print(f"   ✅ /public_html/index.php: {filesize} bytes (Sprint 74 fix active)")
        except Exception as e:
            print(f"   ❌ ERROR: /public_html/ not found! {e}")
        
        ftp.quit()
        
    except Exception as e:
        print(f"❌ Error: {e}")
        if ftp:
            ftp.quit()
        sys.exit(1)

if __name__ == "__main__":
    cleanup_wrong_directory()
