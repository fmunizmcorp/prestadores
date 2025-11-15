#!/usr/bin/env python3
"""
Add Cache Buster to PHP Files
Adds a timestamp comment to force OPcache to see files as "new"
"""

import os
import time
from datetime import datetime

def add_cache_buster(file_path):
    """Add timestamp comment to PHP file"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Check if file starts with <?php
        if not content.startswith('<?php'):
            print(f"   ⚠️  Skipping {file_path} (não inicia com <?php)")
            return False
        
        # Create cache buster comment
        timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        cache_buster = f"<?php /* Cache-Buster: {timestamp} */ "
        
        # Remove old <?php and add new with cache buster
        content = content[5:].lstrip()  # Remove <?php and whitespace
        new_content = cache_buster + "\n" + content
        
        # Write back
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print(f"   ✅ {os.path.basename(file_path)}")
        return True
        
    except Exception as e:
        print(f"   ❌ Erro em {file_path}: {e}")
        return False

def main():
    """Add cache buster to all critical PHP files"""
    
    print("🔥 CACHE BUSTER - Adicionando timestamps a arquivos PHP\n")
    
    # Critical files to update
    files_to_update = [
        'index.php',
        'src/Controllers/AuthController.php',
        'src/Controllers/BaseController.php',
        'src/Controllers/DashboardController.php',
        'src/Controllers/EmpresaTomadoraController.php',
        'src/Controllers/EmpresaPrestadoraController.php',
        'src/Controllers/ContratoController.php',
        'src/Controllers/ProjetoController.php',
        'src/Controllers/AtividadeController.php',
        'src/Controllers/ServicoController.php',
        'src/Controllers/ServicoValorController.php',
        'src/Database.php',
        'src/helpers.php',
        'config/database.php',
        'config/app.php',
        'config/config.php',
    ]
    
    base_path = '/home/user/webapp'
    success = 0
    failed = 0
    
    for file_rel in files_to_update:
        file_path = os.path.join(base_path, file_rel)
        if os.path.exists(file_path):
            if add_cache_buster(file_path):
                success += 1
            else:
                failed += 1
        else:
            print(f"   ⚠️  Arquivo não existe: {file_rel}")
            failed += 1
    
    print(f"\n✅ Sucesso: {success} arquivos")
    print(f"❌ Falhas: {failed} arquivos")
    print(f"\n🚀 Próximo passo: Deploy via FTP")

if __name__ == '__main__':
    main()
