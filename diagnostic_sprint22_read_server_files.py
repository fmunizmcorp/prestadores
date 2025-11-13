#!/usr/bin/env python3
"""
SPRINT 22 - TASK 1: DIAGNÓSTICO PROFUNDO
Ler arquivos problemáticos do servidor via FTP (READ ONLY)
"""
import ftplib
import sys
from datetime import datetime

# Credenciais FTP
FTP_HOST = 'ftp.clinfec.com.br'
FTP_USER = 'u673902663.genspark1'
FTP_PASS = 'Genspark1@'
FTP_ROOT = ''  # Já estamos em /public_html ao conectar, sem subdir

# Arquivos para diagnóstico (erros V11)
FILES_TO_DIAGNOSE = {
    'E2': 'src/Controllers/EmpresaTomadoraController.php',
    'E3': 'src/Controllers/ContratoController.php',
    'E4': 'src/Controllers/EmpresaPrestadoraController.php',
    'E1': 'src/Views/dashboard/index.php',
    'E5': 'config/database.php'
}

def read_file_from_ftp(ftp, remote_path):
    """Lê conteúdo de um arquivo via FTP"""
    try:
        lines = []
        ftp.retrlines(f'RETR {remote_path}', lines.append)
        return '\n'.join(lines)
    except Exception as e:
        return f"ERROR: {str(e)}"

def main():
    print("=" * 80)
    print("SPRINT 22 - DIAGNÓSTICO PROFUNDO (READ ONLY)")
    print("=" * 80)
    print(f"Data/Hora: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Objetivo: Ler 5 arquivos problemáticos do servidor")
    print("=" * 80)
    print()
    
    # Conectar FTP
    print("📡 Conectando ao FTP...")
    try:
        ftp = ftplib.FTP(FTP_HOST, FTP_USER, FTP_PASS)
        print(f"✅ Conectado: {FTP_HOST}")
        print(f"📂 Diretório: {FTP_ROOT}")
        # Já estamos em /public_html ao conectar
        if FTP_ROOT:
            ftp.cwd(FTP_ROOT)
        print()
    except Exception as e:
        print(f"❌ ERRO ao conectar: {e}")
        return 1
    
    # Ler cada arquivo
    results = {}
    diagnostic_report = []
    
    diagnostic_report.append("=" * 80)
    diagnostic_report.append("SPRINT 22 - RELATÓRIO DE DIAGNÓSTICO")
    diagnostic_report.append("=" * 80)
    diagnostic_report.append(f"Data: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    diagnostic_report.append(f"Servidor: {FTP_HOST}")
    diagnostic_report.append(f"Base Path: {FTP_ROOT}")
    diagnostic_report.append("=" * 80)
    diagnostic_report.append("")
    
    for error_id, file_path in FILES_TO_DIAGNOSE.items():
        print(f"\n{'=' * 80}")
        print(f"Lendo {error_id}: {file_path}")
        print(f"{'=' * 80}")
        
        remote_full_path = file_path if not FTP_ROOT else f"{FTP_ROOT}/{file_path}"
        
        # Tentar ler arquivo
        content = read_file_from_ftp(ftp, file_path)
        
        if content.startswith("ERROR"):
            print(f"❌ ERRO: {content}")
            results[error_id] = {"status": "ERROR", "content": content}
            diagnostic_report.append(f"\n## {error_id}: {file_path}")
            diagnostic_report.append(f"**Status:** ❌ ERRO")
            diagnostic_report.append(f"**Erro:** {content}")
        else:
            lines_count = len(content.split('\n'))
            chars_count = len(content)
            print(f"✅ Lido com sucesso")
            print(f"   Linhas: {lines_count}")
            print(f"   Bytes: {chars_count}")
            
            # Análise específica
            analysis = analyze_file(error_id, file_path, content)
            
            results[error_id] = {
                "status": "OK",
                "lines": lines_count,
                "chars": chars_count,
                "content": content,
                "analysis": analysis
            }
            
            # Adicionar ao relatório
            diagnostic_report.append(f"\n## {error_id}: {file_path}")
            diagnostic_report.append(f"**Status:** ✅ OK")
            diagnostic_report.append(f"**Linhas:** {lines_count}")
            diagnostic_report.append(f"**Bytes:** {chars_count}")
            diagnostic_report.append("")
            diagnostic_report.append("### Análise:")
            for key, value in analysis.items():
                diagnostic_report.append(f"- **{key}:** {value}")
            diagnostic_report.append("")
            diagnostic_report.append("### Primeiras 30 linhas:")
            diagnostic_report.append("```php")
            first_lines = content.split('\n')[:30]
            for i, line in enumerate(first_lines, 1):
                diagnostic_report.append(f"{i:3d} | {line}")
            diagnostic_report.append("```")
    
    # Fechar FTP
    ftp.quit()
    print(f"\n{'=' * 80}")
    print("✅ Diagnóstico completo!")
    print(f"{'=' * 80}\n")
    
    # Salvar relatório
    report_file = 'SPRINT22_DIAGNOSTIC_REPORT.md'
    with open(report_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(diagnostic_report))
    
    print(f"📄 Relatório salvo: {report_file}")
    
    # Salvar conteúdos completos
    for error_id, data in results.items():
        if data['status'] == 'OK':
            filename = f"SPRINT22_{error_id}_{FILES_TO_DIAGNOSE[error_id].replace('/', '_')}"
            with open(filename, 'w', encoding='utf-8') as f:
                f.write(data['content'])
            print(f"📄 Arquivo salvo: {filename}")
    
    print("\n🎯 Próximo passo: Analisar relatório e aplicar correções")
    return 0

def analyze_file(error_id, file_path, content):
    """Análise específica por tipo de erro"""
    analysis = {}
    
    if error_id in ['E2', 'E3', 'E4']:  # Controllers
        # Verificar classe e método index()
        analysis['Tem class'] = 'class' in content and 'Controller' in content
        analysis['Tem método index()'] = 'function index(' in content or 'public function index(' in content
        analysis['Tem namespace'] = 'namespace' in content
        analysis['Tem extends'] = 'extends' in content
        
        # Contar métodos
        method_count = content.count('function ')
        analysis['Total de métodos'] = method_count
        
    elif error_id == 'E1':  # Dashboard view
        # Verificar session_start()
        lines = content.split('\n')
        for i, line in enumerate(lines[:10], 1):
            if 'session_start' in line:
                analysis['session_start() na linha'] = i
                break
        else:
            analysis['session_start()'] = 'NÃO ENCONTRADO nas primeiras 10 linhas'
        
        # Verificar output antes
        first_line = lines[0] if lines else ''
        analysis['Primeira linha'] = first_line[:50] + '...' if len(first_line) > 50 else first_line
        analysis['Tem espaços antes de <?php'] = not first_line.startswith('<?php')
        
    elif error_id == 'E5':  # Database config
        # Verificar credenciais
        analysis['Tem DB_HOST'] = 'DB_HOST' in content or "'host'" in content
        analysis['Tem DB_NAME'] = 'DB_NAME' in content or "'database'" in content
        analysis['Tem DB_USER'] = 'DB_USER' in content or "'username'" in content
        analysis['Tem DB_PASS'] = 'DB_PASS' in content or "'password'" in content
        
        # Tentar extrair valores
        if "define('DB_HOST'" in content:
            for line in content.split('\n'):
                if "define('DB_HOST'" in line:
                    analysis['DB_HOST'] = line.strip()
                elif "define('DB_NAME'" in line:
                    analysis['DB_NAME'] = line.strip()
    
    return analysis

if __name__ == '__main__':
    sys.exit(main())
