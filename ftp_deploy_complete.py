#!/usr/bin/env python3
"""
COMPLETE FTP DEPLOYMENT STRATEGY
Try multiple approaches to deploy files
"""
import ftplib
import os
import sys

FTP_SERVER = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"

def deploy_via_ftp():
    print("═══════════════════════════════════════════════════════════")
    print("COMPLETE FTP DEPLOYMENT - SPRINT 14")
    print("═══════════════════════════════════════════════════════════\n")
    
    # Files to upload
    files_to_upload = [
        {
            'local': 'check_notas_WITH_DEPLOYER.php',
            'remote_attempts': [
                'prestadores/check_notas_fiscais_table.php',
                '../prestadores/check_notas_fiscais_table.php',
                '/prestadores/check_notas_fiscais_table.php',
                '../domains/clinfec.com.br/public_html/prestadores/check_notas_fiscais_table.php',
            ]
        },
        {
            'local': 'ultimate_deployer.php',
            'remote_attempts': [
                'prestadores/ultimate_deployer.php',
                '../prestadores/ultimate_deployer.php',
            ]
        }
    ]
    
    try:
        ftp = ftplib.FTP(FTP_SERVER)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Connected and logged in\n")
        
        pwd = ftp.pwd()
        print(f"Current directory: {pwd}\n")
        
        # Try to upload files to various paths
        for file_info in files_to_upload:
            local_file = file_info['local']
            
            if not os.path.exists(local_file):
                print(f"⚠️  Local file not found: {local_file}\n")
                continue
            
            file_size = os.path.getsize(local_file)
            print(f"📄 Uploading: {local_file} ({file_size:,} bytes)")
            
            uploaded = False
            
            for remote_path in file_info['remote_attempts']:
                print(f"  Trying: {remote_path} ... ", end='')
                
                try:
                    with open(local_file, 'rb') as f:
                        ftp.storbinary(f'STOR {remote_path}', f)
                    print("✅ SUCCESS!")
                    uploaded = True
                    break
                except Exception as e:
                    print(f"❌ Failed ({str(e)[:50]})")
            
            if not uploaded:
                # Try uploading to root with different name
                root_name = f"{os.path.splitext(local_file)[0]}_for_prestadores{os.path.splitext(local_file)[1]}"
                print(f"  Fallback: {root_name} ... ", end='')
                try:
                    with open(local_file, 'rb') as f:
                        ftp.storbinary(f'STOR {root_name}', f)
                    print("✅ SUCCESS (in root)")
                    print(f"    → Manual copy needed to /prestadores/")
                except Exception as e:
                    print(f"❌ Failed ({str(e)[:50]})")
            
            print()
        
        # Try to list what we have in root
        print("\n═══════════════════════════════════════════════════════════")
        print("FILES IN FTP ROOT:")
        print("═══════════════════════════════════════════════════════════")
        
        files = []
        ftp.dir(files.append)
        
        # Filter to show our uploaded files
        for line in files:
            if any(name in line for name in ['check_notas', 'deployer', 'ultimate']):
                print(line)
        
        ftp.quit()
        print("\n✅ FTP session complete")
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    deploy_via_ftp()
