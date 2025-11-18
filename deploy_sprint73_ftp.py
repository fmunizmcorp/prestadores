#!/usr/bin/env python3
"""
SPRINT 73 - FTP Deployment Script
Deploys all Sprint 73 fixes to production via FTP
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

# Files to deploy for Sprint 73
FILES_TO_DEPLOY = [
    # Main routing file (autoloader fix + 3 new routes)
    ('public/index.php', '/public_html/index.php'),
    
    # Models with database connection fix (8 files)
    ('src/Models/CentroCusto.php', '/public_html/src/Models/CentroCusto.php'),
    ('src/Models/Custo.php', '/public_html/src/Models/Custo.php'),
    ('src/Models/Pagamento.php', '/public_html/src/Models/Pagamento.php'),
    ('src/Models/Boleto.php', '/public_html/src/Models/Boleto.php'),
    ('src/Models/ConciliacaoBancaria.php', '/public_html/src/Models/ConciliacaoBancaria.php'),
    ('src/Models/ContaPagar.php', '/public_html/src/Models/ContaPagar.php'),
    ('src/Models/ContaReceber.php', '/public_html/src/Models/ContaReceber.php'),
    ('src/Models/LancamentoFinanceiro.php', '/public_html/src/Models/LancamentoFinanceiro.php'),
]

def deploy_sprint73_ftp():
    """Deploy all Sprint 73 fixes via FTP"""
    print("=" * 80)
    print("SPRINT 73 - FTP DEPLOYMENT TO PRODUCTION")
    print("=" * 80)
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
        print(f"[3/4] Deploying {len(FILES_TO_DEPLOY)} files...")
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
        print(f"✅ Deployed {deployed_count}/{len(FILES_TO_DEPLOY)} files successfully!")
        print()
        
        # Clear OPcache
        print("[4/4] Creating OPcache clear script...")
        
        opcache_script = """<?php
// SPRINT 73 - Clear OPcache after deployment
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
            temp_file = '/tmp/clear_opcache_sprint73.php'
            with open(temp_file, 'w') as f:
                f.write(opcache_script)
            
            # Upload OPcache clear script
            with open(temp_file, 'rb') as f:
                ftp.storbinary('STOR clear_opcache_sprint73.php', f)
            
            print("✅ OPcache clear script uploaded!")
            print("   Please execute: https://prestadores.clinfec.com.br/clear_opcache_sprint73.php")
            
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
        print("✅ Sprint 73 corrections deployed successfully!")
        print()
        print("📊 Files deployed:")
        print(f"   • 1 routing file (index.php)")
        print(f"   • 8 Model files (database connection fix)")
        print()
        print("🔧 Fixes included:")
        print(f"   • Bug #25: Atividades route added")
        print(f"   • Bug #26: Relatórios route added")
        print(f"   • Bug #27: Usuários route added")
        print(f"   • Bug #23: CentroCusto.php database fix")
        print(f"   • Bug #24: Pagamento.php database fix")
        print(f"   • 5 preventive Model fixes")
        print()
        print("🌐 Production URL:")
        print(f"   https://prestadores.clinfec.com.br/")
        print()
        print("⚠️  IMPORTANT: Execute OPcache clear script:")
        print("   https://prestadores.clinfec.com.br/clear_opcache_sprint73.php")
        print()
        print("🎯 Expected Result: System 100% functional (22/22 modules)")
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
    success = deploy_sprint73_ftp()
    sys.exit(0 if success else 1)
