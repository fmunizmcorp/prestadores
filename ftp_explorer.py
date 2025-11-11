#!/usr/bin/env python3
"""
FTP EXPLORER - Discover complete FTP structure and upload files
"""
import ftplib
import os
import sys
from datetime import datetime

FTP_SERVER = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"

def explore_ftp():
    print("=== FTP EXPLORER ===\n")
    
    try:
        ftp = ftplib.FTP(FTP_SERVER)
        print(f"✅ Connected to {FTP_SERVER}")
        
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Logged in as {FTP_USER}\n")
        
        pwd = ftp.pwd()
        print(f"Current Directory: {pwd}\n")
        
        # List current directory
        print("=== ROOT DIRECTORY CONTENTS ===\n")
        try:
            files = []
            ftp.dir(files.append)
            for line in files:
                print(line)
        except Exception as e:
            print(f"Error listing: {e}")
        
        print("\n=== SEARCHING FOR PRESTADORES ===\n")
        
        # Try common paths
        possible_paths = [
            'prestadores',
            'public_html/prestadores',
            'domains/clinfec.com.br/public_html/prestadores',
            '/prestadores',
            '/public_html/prestadores',
        ]
        
        prestadores_path = None
        
        for path in possible_paths:
            print(f"Trying: {path} ... ", end='')
            try:
                ftp.cwd(path)
                print("✅ FOUND!")
                prestadores_path = ftp.pwd()
                print(f"Full path: {prestadores_path}\n")
                
                print("Contents:")
                files = []
                ftp.dir(files.append)
                for line in files[:20]:  # First 20 items
                    print(f"  {line}")
                
                # Check for src/Models directory
                print("\nChecking for src/Models directory...")
                try:
                    ftp.cwd('src')
                    print("  ✅ src/ exists")
                    try:
                        ftp.cwd('Models')
                        print("  ✅ src/Models/ exists")
                        
                        print("\n  Current Models:")
                        files = []
                        ftp.dir(files.append)
                        for line in files:
                            print(f"    {line}")
                        
                        ftp.cwd(prestadores_path)  # Go back
                    except:
                        print("  ❌ src/Models/ not found")
                        ftp.cwd(prestadores_path)
                except:
                    print("  ❌ src/ not found")
                
                break
            except Exception as e:
                print(f"❌ Not found")
        
        if prestadores_path:
            print(f"\n✅ Prestadores directory located: {prestadores_path}")
            return ftp, prestadores_path
        else:
            print("\n❌ Prestadores directory NOT found")
            print("\nListing all directories in root:")
            ftp.cwd(pwd)
            
            def list_dirs(ftp, path='', level=0, max_level=2):
                if level > max_level:
                    return
                try:
                    items = ftp.nlst(path)
                    for item in items:
                        basename = os.path.basename(item)
                        if basename.startswith('.'):
                            continue
                        
                        try:
                            # Try to CWD to see if it's a directory
                            current = ftp.pwd()
                            ftp.cwd(item)
                            print("  " * level + f"📁 {basename}/")
                            
                            if 'prestadores' in basename.lower():
                                print("  " * level + f"   ⭐ FOUND!")
                            
                            ftp.cwd(current)
                            list_dirs(ftp, item, level + 1, max_level)
                        except:
                            print("  " * level + f"📄 {basename}")
                except Exception as e:
                    pass
            
            list_dirs(ftp)
            
            return ftp, None
        
    except Exception as e:
        print(f"❌ ERROR: {e}")
        import traceback
        traceback.print_exc()
        return None, None

if __name__ == "__main__":
    ftp, path = explore_ftp()
    if ftp:
        ftp.quit()
    print("\n=== EXPLORATION COMPLETE ===")
