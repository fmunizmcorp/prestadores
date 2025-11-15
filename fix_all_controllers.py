#!/usr/bin/env python3
"""
Aplica lazy instantiation em TODOS os Controllers
"""

import re
import os
from pathlib import Path

CONTROLLERS_DIR = Path('src/Controllers')

def fix_controller(filepath):
    """Aplica lazy instantiation em um Controller"""
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Verificar se já tem lazy instantiation
    if 'private function getModel()' in content:
        print(f"   ⏭️  {filepath.name} - já corrigido")
        return False
    
    # Verificar se tem construtor com $this->model = new
    if 'public function __construct()' not in content:
        print(f"   ⏭️  {filepath.name} - sem construtor")
        return False
        
    if '$this->model = new' not in content:
        print(f"   ⏭️  {filepath.name} - sem instanciação de model")
        return False
    
    # Extrair o nome da classe do Model
    model_match = re.search(r'\$this->model = new (\w+)\(\);', content)
    if not model_match:
        print(f"   ❌ {filepath.name} - padrão não encontrado")
        return False
    
    model_class = model_match.group(1)
    
    # Remover a linha do construtor que inst ancia o model
    content = re.sub(
        r'(\s+)\$this->model = new ' + model_class + r'\(\);',
        '',
        content
    )
    
    # Adicionar getModel() depois da declaração de $model
    old_declaration = 'private $model;'
    new_declaration = f'''private $model = null;
    
    /**
     * Get model (lazy instantiation)
     */
    private function getModel() {{
        if ($this->model === null) {{
            $this->model = new {model_class}();
        }}
        return $this->model;
    }}'''
    
    if old_declaration in content:
        content = content.replace(old_declaration, new_declaration)
    
    # Substituir todas as ocorrências de $this->model-> por $this->getModel()->
    content = re.sub(
        r'\$this->model->',
        r'$this->getModel()->',
        content
    )
    
    # Salvar
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"   ✅ {filepath.name} - corrigido")
    return True

def main():
    print("🔧 Aplicando lazy instantiation em todos os Controllers...\n")
    
    controllers = list(CONTROLLERS_DIR.glob('*Controller.php'))
    
    fixed = 0
    for controller in controllers:
        if fix_controller(controller):
            fixed += 1
    
    print(f"\n✅ Total: {fixed} controllers corrigidos")

if __name__ == '__main__':
    main()
