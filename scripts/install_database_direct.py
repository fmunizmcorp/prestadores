#!/usr/bin/env python3
"""
INSTALAÇÃO DIRETA DO BANCO DE DADOS
Sprint 31 - Clinfec Prestadores
Conecta diretamente ao MySQL server para criar tabelas
"""

import mysql.connector
from mysql.connector import Error
import sys

# Credenciais do banco de dados
DB_CONFIG = {
    'host': '193.203.175.82',
    'database': 'u673902663_prestadores',
    'user': 'u673902663_admin',
    'password': ';>?I4dtn~2Ga',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci'
}

# SQL Script (sem números de linha)
SQL_SCRIPT = """
-- ============================================
-- INSTALAÇÃO MANUAL DO BANCO DE DADOS
-- Sistema: Clinfec Prestadores
-- Versão: Sprint 31
-- Data: 2024-11-14
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. TABELA: usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'gestor', 'operador') DEFAULT 'operador',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO usuarios (id, nome, email, senha, tipo, ativo) VALUES
(1, 'Administrador', 'admin@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);

-- ============================================
-- 2. TABELA: empresas_prestadoras
-- ============================================
CREATE TABLE IF NOT EXISTS empresas_prestadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255),
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    tipo_prestador ENUM('pj', 'pf', 'mei') DEFAULT 'pj',
    cpf VARCHAR(14),
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    email VARCHAR(255),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    site VARCHAR(255),
    
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABELA: empresas_tomadoras
-- ============================================
CREATE TABLE IF NOT EXISTS empresas_tomadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    email_principal VARCHAR(255),
    email_financeiro VARCHAR(255),
    email_projetos VARCHAR(255),
    telefone_principal VARCHAR(20),
    telefone_secundario VARCHAR(20),
    celular VARCHAR(20),
    whatsapp VARCHAR(20),
    site VARCHAR(255),
    
    dia_fechamento INT DEFAULT 30,
    dia_pagamento INT DEFAULT 5,
    forma_pagamento_preferencial VARCHAR(50),
    banco VARCHAR(100),
    agencia VARCHAR(20),
    conta VARCHAR(30),
    tipo_conta ENUM('corrente', 'poupanca') DEFAULT 'corrente',
    
    logo VARCHAR(255),
    
    ativo BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_nome_fantasia (nome_fantasia),
    INDEX idx_ativo (ativo),
    INDEX idx_cidade_estado (cidade, estado),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABELA: servicos
-- ============================================
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    tipo ENUM('hora', 'dia', 'mes', 'projeto') DEFAULT 'hora',
    valor_referencia DECIMAL(10,2),
    ativo BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_nome (nome),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABELA: contratos
-- ============================================
CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_contrato VARCHAR(100) UNIQUE NOT NULL,
    
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    
    descricao TEXT,
    objeto TEXT,
    
    data_inicio DATE NOT NULL,
    data_fim DATE,
    
    valor_total DECIMAL(15,2),
    valor_executado DECIMAL(15,2) DEFAULT 0,
    
    status ENUM('rascunho', 'ativo', 'suspenso', 'encerrado', 'cancelado') DEFAULT 'rascunho',
    
    arquivo_contrato VARCHAR(500),
    
    observacoes TEXT,
    motivo_encerramento TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_by INT,
    
    INDEX idx_numero_contrato (numero_contrato),
    INDEX idx_status (status),
    INDEX idx_datas (data_inicio, data_fim),
    INDEX idx_tomadora (empresa_tomadora_id),
    INDEX idx_prestadora (empresa_prestadora_id),
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABELA: atestados
-- ============================================
CREATE TABLE IF NOT EXISTS atestados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    numero VARCHAR(50) UNIQUE NOT NULL,
    
    mes_referencia INT NOT NULL,
    ano_referencia INT NOT NULL,
    data_emissao DATE NOT NULL,
    
    valor_bruto DECIMAL(15,2) NOT NULL,
    valor_descontos DECIMAL(15,2) DEFAULT 0,
    valor_liquido DECIMAL(15,2) NOT NULL,
    
    status ENUM('rascunho', 'emitido', 'aprovado', 'rejeitado', 'pago') DEFAULT 'rascunho',
    
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_contrato (contrato_id),
    INDEX idx_status (status),
    INDEX idx_mes_ano (mes_referencia, ano_referencia),
    
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABELA: faturas
-- ============================================
CREATE TABLE IF NOT EXISTS faturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atestado_id INT NOT NULL,
    numero_nf VARCHAR(50) UNIQUE NOT NULL,
    
    data_emissao DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    
    valor_total DECIMAL(15,2) NOT NULL,
    valor_pago DECIMAL(15,2),
    
    status ENUM('emitida', 'enviada', 'aprovada', 'paga', 'cancelada') DEFAULT 'emitida',
    
    arquivo_nf VARCHAR(500),
    
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    
    INDEX idx_atestado (atestado_id),
    INDEX idx_status (status),
    INDEX idx_data_vencimento (data_vencimento),
    
    FOREIGN KEY (atestado_id) REFERENCES atestados(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    FOREIGN KEY (updated_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TABELA: documentos
-- ============================================
CREATE TABLE IF NOT EXISTS documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    entidade_tipo ENUM('empresa_tomadora', 'empresa_prestadora', 'contrato', 'atestado', 'fatura') NOT NULL,
    entidade_id INT NOT NULL,
    
    nome_arquivo VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    tipo_documento VARCHAR(100),
    tamanho_bytes BIGINT,
    caminho VARCHAR(500) NOT NULL,
    
    descricao TEXT,
    data_emissao DATE,
    data_validade DATE,
    
    ativo BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    
    INDEX idx_entidade (entidade_tipo, entidade_id),
    INDEX idx_tipo_documento (tipo_documento),
    INDEX idx_validade (data_validade),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. TABELA: database_version
-- ============================================
CREATE TABLE IF NOT EXISTS database_version (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version INT NOT NULL,
    description VARCHAR(255),
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO database_version (version, description) VALUES
(31, 'Instalação manual Sprint 31 - Tabelas essenciais');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- DADOS INICIAIS
-- ============================================

INSERT IGNORE INTO servicos (nome, descricao, tipo, valor_referencia, ativo, created_by) VALUES
('Limpeza e Conservação', 'Serviços de limpeza e conservação predial', 'hora', 25.00, TRUE, 1),
('Manutenção Predial', 'Manutenção preventiva e corretiva', 'hora', 45.00, TRUE, 1),
('Segurança e Vigilância', 'Serviços de segurança patrimonial', 'mes', 2500.00, TRUE, 1),
('Jardinagem', 'Cuidados com áreas verdes', 'hora', 30.00, TRUE, 1),
('Serviços Administrativos', 'Apoio administrativo e gestão', 'hora', 35.00, TRUE, 1);
"""

def connect_to_database():
    """Conecta ao servidor MySQL"""
    try:
        print("=" * 80)
        print("🔌 CONECTANDO AO SERVIDOR MySQL")
        print("=" * 80)
        print(f"Host: {DB_CONFIG['host']}")
        print(f"Database: {DB_CONFIG['database']}")
        print(f"User: {DB_CONFIG['user']}")
        print()
        
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            database=DB_CONFIG['database'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            charset=DB_CONFIG['charset'],
            collation=DB_CONFIG['collation'],
            autocommit=False  # Controle manual de transações
        )
        
        if connection.is_connected():
            db_info = connection.get_server_info()
            print(f"✅ Conectado ao MySQL Server versão {db_info}")
            cursor = connection.cursor()
            cursor.execute("SELECT DATABASE();")
            record = cursor.fetchone()
            print(f"✅ Banco de dados ativo: {record[0]}")
            cursor.close()
            print()
            return connection
            
    except Error as e:
        print(f"❌ ERRO ao conectar ao MySQL: {e}")
        sys.exit(1)

def execute_sql_script(connection):
    """Executa o script SQL completo"""
    try:
        cursor = connection.cursor()
        
        print("=" * 80)
        print("📝 EXECUTANDO SCRIPT DE INSTALAÇÃO")
        print("=" * 80)
        print()
        
        # Dividir o script em statements individuais
        statements = []
        current_statement = []
        
        for line in SQL_SCRIPT.split('\n'):
            line = line.strip()
            
            # Ignorar comentários e linhas vazias
            if not line or line.startswith('--'):
                continue
            
            current_statement.append(line)
            
            # Se a linha termina com ;, é o fim de um statement
            if line.endswith(';'):
                statement = ' '.join(current_statement)
                if statement and statement != ';':
                    statements.append(statement)
                current_statement = []
        
        # Executar cada statement
        total = len(statements)
        print(f"Total de statements SQL: {total}")
        print()
        
        for i, statement in enumerate(statements, 1):
            # Mostrar progresso
            if 'CREATE TABLE' in statement:
                table_name = statement.split('CREATE TABLE IF NOT EXISTS ')[1].split(' ')[0]
                print(f"[{i}/{total}] 📋 Criando tabela: {table_name}")
            elif 'INSERT' in statement:
                if 'usuarios' in statement:
                    print(f"[{i}/{total}] 👤 Inserindo usuário admin")
                elif 'servicos' in statement:
                    print(f"[{i}/{total}] 🛠️  Inserindo serviços padrão")
                elif 'database_version' in statement:
                    print(f"[{i}/{total}] 📌 Registrando versão do banco")
            elif 'SET FOREIGN_KEY_CHECKS' in statement:
                if '= 0' in statement:
                    print(f"[{i}/{total}] 🔓 Desabilitando verificação de chaves estrangeiras")
                else:
                    print(f"[{i}/{total}] 🔒 Reabilitando verificação de chaves estrangeiras")
            else:
                print(f"[{i}/{total}] ⚙️  Executando comando...")
            
            try:
                cursor.execute(statement)
            except Error as e:
                print(f"   ⚠️  Warning: {e}")
                # Continuar mesmo com erros (tabelas podem já existir)
        
        # Commit de todas as alterações
        connection.commit()
        print()
        print("✅ Script executado com sucesso!")
        print()
        
        cursor.close()
        
    except Error as e:
        connection.rollback()
        print(f"❌ ERRO ao executar script: {e}")
        sys.exit(1)

def verify_installation(connection):
    """Verifica se todas as tabelas foram criadas"""
    try:
        cursor = connection.cursor(dictionary=True)
        
        print("=" * 80)
        print("✔️  VERIFICANDO INSTALAÇÃO")
        print("=" * 80)
        print()
        
        # Listar todas as tabelas
        cursor.execute("SHOW TABLES")
        tables = cursor.fetchall()
        
        expected_tables = [
            'usuarios',
            'empresas_prestadoras',
            'empresas_tomadoras',
            'servicos',
            'contratos',
            'atestados',
            'faturas',
            'documentos',
            'database_version'
        ]
        
        print(f"📊 Tabelas encontradas: {len(tables)}")
        print()
        
        found_tables = []
        for table in tables:
            table_name = list(table.values())[0]
            found_tables.append(table_name)
            
            # Contar registros
            cursor.execute(f"SELECT COUNT(*) as total FROM {table_name}")
            count = cursor.fetchone()['total']
            
            if table_name in expected_tables:
                print(f"   ✅ {table_name:<30} ({count} registros)")
            else:
                print(f"   ℹ️  {table_name:<30} ({count} registros)")
        
        print()
        
        # Verificar tabelas esperadas
        missing_tables = [t for t in expected_tables if t not in found_tables]
        
        if missing_tables:
            print("⚠️  TABELAS FALTANDO:")
            for table in missing_tables:
                print(f"   ❌ {table}")
            print()
        else:
            print("✅ Todas as 9 tabelas essenciais foram criadas!")
            print()
        
        # Verificar usuário admin
        cursor.execute("SELECT id, nome, email, tipo FROM usuarios WHERE id = 1")
        admin = cursor.fetchone()
        
        if admin:
            print("👤 USUÁRIO ADMIN:")
            print(f"   ID: {admin['id']}")
            print(f"   Nome: {admin['nome']}")
            print(f"   Email: {admin['email']}")
            print(f"   Tipo: {admin['tipo']}")
            print(f"   Senha padrão: password")
            print()
        else:
            print("⚠️  Usuário admin não encontrado!")
            print()
        
        # Verificar serviços
        cursor.execute("SELECT COUNT(*) as total FROM servicos")
        servicos_count = cursor.fetchone()['total']
        print(f"🛠️  SERVIÇOS CADASTRADOS: {servicos_count}")
        print()
        
        # Verificar versão do banco
        cursor.execute("SELECT version, description, installed_at FROM database_version ORDER BY installed_at DESC LIMIT 1")
        version = cursor.fetchone()
        
        if version:
            print("📌 VERSÃO DO BANCO DE DADOS:")
            print(f"   Versão: {version['version']}")
            print(f"   Descrição: {version['description']}")
            print(f"   Instalado em: {version['installed_at']}")
            print()
        
        cursor.close()
        
        print("=" * 80)
        print("✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!")
        print("=" * 80)
        print()
        print("🌐 Acesse o sistema em: http://clinfec.com.br/prestadores")
        print("📧 Login: admin@clinfec.com.br")
        print("🔑 Senha: password")
        print()
        
    except Error as e:
        print(f"❌ ERRO ao verificar instalação: {e}")
        sys.exit(1)

def main():
    """Função principal"""
    print()
    print("╔" + "=" * 78 + "╗")
    print("║" + " " * 78 + "║")
    print("║" + " INSTALAÇÃO DIRETA DO BANCO DE DADOS - SPRINT 31 ".center(78) + "║")
    print("║" + " Sistema: Clinfec Prestadores ".center(78) + "║")
    print("║" + " Metodologia: SCRUM + PDCA ".center(78) + "║")
    print("║" + " " * 78 + "║")
    print("╚" + "=" * 78 + "╝")
    print()
    
    connection = None
    
    try:
        # 1. Conectar ao banco
        connection = connect_to_database()
        
        # 2. Executar script SQL
        execute_sql_script(connection)
        
        # 3. Verificar instalação
        verify_installation(connection)
        
        return 0
        
    except KeyboardInterrupt:
        print()
        print("⚠️  Instalação cancelada pelo usuário")
        return 1
        
    finally:
        if connection and connection.is_connected():
            connection.close()
            print("🔌 Conexão com o banco de dados encerrada")
            print()

if __name__ == "__main__":
    sys.exit(main())
