#!/usr/bin/env python3
"""
SPRINT 62 - STEP 1: Analyze and Download from Hostinger
Downloads ONLY essential files (no cache, no logs, no temp files)
"""

import ftplib
import os
import sys
from pathlib import Path

# FTP Configuration
FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"
FTP_REMOTE_DIR = "/public_html/prestadores"

# Local directory
LOCAL_DIR = "/home/user/webapp/migration_backup"

# Essential directories to download
ESSENTIAL_DIRS = [
    'src',
    'public',
    'config',
    'vendor',
    'database'
]

# Files to EXCLUDE (patterns)
EXCLUDE_PATTERNS = [
    '.git',
    'logs/',
    'cache/',
    'temp/',
    'tmp/',
    '.DS_Store',
    'Thumbs.db',
    '__pycache__',
    '*.pyc',
    '.htaccess~',
    '*.bak',
    '*.old',
    '*.swp',
    '.env.backup',
    'node_modules/'
]

# Essential root files
ROOT_FILES = [
    '.htaccess',
    'composer.json',
    'composer.lock',
    'autoload.php',
    'bootstrap.php'
]

def should_exclude(path):
    """Check if path should be excluded"""
    for pattern in EXCLUDE_PATTERNS:
        if pattern in path:
            return True
    return False

def list_ftp_recursive(ftp, remote_dir, prefix=""):
    """List all files recursively"""
    files = []
    try:
        items = []
        ftp.retrlines(f'LIST {remote_dir}', items.append)
        
        for item in items:
            parts = item.split(None, 8)
            if len(parts) < 9:
                continue
            
            name = parts[8]
            if name in ['.', '..']:
                continue
            
            permissions = parts[0]
            full_path = f"{remote_dir}/{name}".replace("//", "/")
            display_path = f"{prefix}{name}"
            
            if should_exclude(display_path):
                print(f"  ⏭️  SKIP: {display_path}")
                continue
            
            if permissions[0] == 'd':
                # Directory
                print(f"  📁 DIR: {display_path}/")
                sub_files = list_ftp_recursive(ftp, full_path, f"{prefix}{name}/")
                files.extend(sub_files)
            else:
                # File
                size = int(parts[4]) if len(parts) > 4 else 0
                print(f"  📄 FILE: {display_path} ({size:,} bytes)")
                files.append({
                    'remote_path': full_path,
                    'local_path': display_path,
                    'size': size
                })
    except Exception as e:
        print(f"  ⚠️  Error listing {remote_dir}: {e}")
    
    return files

def download_file(ftp, remote_path, local_path):
    """Download a single file"""
    try:
        # Create local directory
        local_file = Path(LOCAL_DIR) / local_path
        local_file.parent.mkdir(parents=True, exist_ok=True)
        
        # Download
        with open(local_file, 'wb') as f:
            ftp.retrbinary(f'RETR {remote_path}', f.write)
        
        return True
    except Exception as e:
        print(f"    ❌ ERROR: {e}")
        return False

def main():
    print("🚀 SPRINT 62 - STEP 1: Analyze and Download from Hostinger")
    print("=" * 70)
    
    # Create local directory
    os.makedirs(LOCAL_DIR, exist_ok=True)
    
    # Connect to FTP
    print(f"\n📡 Connecting to {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Connected as {FTP_USER}")
    except Exception as e:
        print(f"❌ FTP Connection failed: {e}")
        return 1
    
    # Change to prestadores directory
    try:
        ftp.cwd(FTP_REMOTE_DIR)
        print(f"✅ Changed to {FTP_REMOTE_DIR}")
    except Exception as e:
        print(f"❌ Cannot change to {FTP_REMOTE_DIR}: {e}")
        return 1
    
    # List all files
    print(f"\n📋 Analyzing structure...")
    print("=" * 70)
    
    all_files = []
    
    # Download root essential files
    print(f"\n1️⃣ Essential Root Files:")
    for filename in ROOT_FILES:
        try:
            items = []
            ftp.retrlines(f'LIST {filename}', items.append)
            if items:
                parts = items[0].split(None, 8)
                size = int(parts[4]) if len(parts) > 4 else 0
                print(f"  📄 {filename} ({size:,} bytes)")
                all_files.append({
                    'remote_path': f"{FTP_REMOTE_DIR}/{filename}",
                    'local_path': filename,
                    'size': size
                })
        except:
            print(f"  ⏭️  SKIP: {filename} (not found)")
    
    # Download essential directories
    for idx, dir_name in enumerate(ESSENTIAL_DIRS, start=2):
        print(f"\n{idx}️⃣ Directory: {dir_name}/")
        try:
            dir_files = list_ftp_recursive(ftp, f"{FTP_REMOTE_DIR}/{dir_name}", f"{dir_name}/")
            all_files.extend(dir_files)
            print(f"  ✅ Found {len(dir_files)} files")
        except Exception as e:
            print(f"  ⚠️  Error scanning {dir_name}: {e}")
    
    # Summary
    total_size = sum(f['size'] for f in all_files)
    print(f"\n" + "=" * 70)
    print(f"📊 SUMMARY:")
    print(f"   Total files to download: {len(all_files)}")
    print(f"   Total size: {total_size:,} bytes ({total_size/1024/1024:.2f} MB)")
    print("=" * 70)
    
    # Ask confirmation
    print(f"\n⚠️  This will download {len(all_files)} files to:")
    print(f"   {LOCAL_DIR}")
    response = input("\nProceed with download? (yes/no): ").strip().lower()
    
    if response != 'yes':
        print("❌ Download cancelled by user")
        ftp.quit()
        return 0
    
    # Download all files
    print(f"\n📥 Downloading files...")
    print("=" * 70)
    
    success_count = 0
    fail_count = 0
    
    for idx, file_info in enumerate(all_files, start=1):
        remote_path = file_info['remote_path']
        local_path = file_info['local_path']
        size = file_info['size']
        
        print(f"\n[{idx}/{len(all_files)}] {local_path}")
        print(f"  Size: {size:,} bytes")
        
        if download_file(ftp, remote_path, local_path):
            print(f"  ✅ Downloaded")
            success_count += 1
        else:
            fail_count += 1
    
    # Final summary
    print(f"\n" + "=" * 70)
    print(f"✅ DOWNLOAD COMPLETE!")
    print(f"   Success: {success_count} files")
    print(f"   Failed: {fail_count} files")
    print(f"   Location: {LOCAL_DIR}")
    print("=" * 70)
    
    ftp.quit()
    return 0

if __name__ == "__main__":
    sys.exit(main())
