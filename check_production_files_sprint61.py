#!/usr/bin/env python3
"""
Sprint 61: Check Production Files

Verify what files are actually in production.
"""

import ftplib

FTP_HOST = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"

try:
    print("Connecting to FTP...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    
    print("\nFiles in /public_html:")
    ftp.cwd("/public_html")
    files = []
    ftp.retrlines('LIST', files.append)
    
    sprint60_files = [f for f in files if 'sprint60' in f.lower()]
    sprint58_files = [f for f in files if 'sprint58' in f.lower()]
    
    print("\n Sprint 60 files:")
    if sprint60_files:
        for f in sprint60_files:
            print(f"  {f}")
    else:
        print("  ❌ NO SPRINT 60 FILES FOUND!")
    
    print("\n Sprint 58 files:")
    if sprint58_files:
        for f in sprint58_files:
            print(f"  {f}")
    else:
        print("  (none)")
    
    ftp.quit()
    
except Exception as e:
    print(f"Error: {str(e)}")
