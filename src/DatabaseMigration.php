<?php
/**
 * Sistema de Migrations Automáticas
 * Verifica e aplica atualizações do banco de dados automaticamente
 */

namespace App;

use PDO;
use PDOException;

class DatabaseMigration {
    private $db;
    private $migrationsPath;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/../database/migrations/';
    }
    
    /**
     * Verifica se o banco de dados existe e está atualizado
     */
    public function checkAndMigrate() {
        try {
            // Cria tabela de controle de versão se não existir
            $this->createVersionTable();
            
            // Verifica versão atual do banco
            $currentVersion = $this->getCurrentVersion();
            
            // Verifica versão esperada do sistema
            $versionConfig = require __DIR__ . '/../config/version.php';
            $expectedVersion = $versionConfig['db_version'];
            
            if ($currentVersion < $expectedVersion) {
                $this->runMigrations($currentVersion, $expectedVersion);
            }
            
            return [
                'success' => true,
                'current_version' => $this->getCurrentVersion(),
                'expected_version' => $expectedVersion,
                'up_to_date' => true
            ];
            
        } catch (\Exception $e) {
            error_log("Erro na migração: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Cria a tabela de controle de versão
     */
    private function createVersionTable() {
        $sql = "CREATE TABLE IF NOT EXISTS system_version (
            id INT PRIMARY KEY DEFAULT 1,
            db_version INT NOT NULL DEFAULT 0,
            system_version VARCHAR(20) NOT NULL DEFAULT '1.0.0',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT single_row CHECK (id = 1)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->exec($sql);
        
        // Insere registro inicial se não existir
        $checkSql = "SELECT COUNT(*) FROM system_version WHERE id = 1";
        $count = $this->db->query($checkSql)->fetchColumn();
        
        if ($count == 0) {
            $insertSql = "INSERT INTO system_version (id, db_version, system_version) VALUES (1, 0, '1.0.0')";
            $this->db->exec($insertSql);
        }
    }
    
    /**
     * Retorna a versão atual do banco de dados
     */
    private function getCurrentVersion() {
        try {
            $sql = "SELECT db_version FROM system_version WHERE id = 1";
            $stmt = $this->db->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Executa as migrations necessárias
     */
    private function runMigrations($from, $to) {
        try {
            $this->db->beginTransaction();
            // Escaneia todos os arquivos .sql disponíveis
            $migrationFiles = glob($this->migrationsPath . '*.sql');
            sort($migrationFiles);
            
            foreach ($migrationFiles as $migrationFile) {
                // Extrai o número da versão do nome do arquivo
                $basename = basename($migrationFile);
                if (preg_match('/^(\d+)_/', $basename, $matches)) {
                    $fileVersion = (int)$matches[1];
                    
                    // Só executa se estiver no range necessário
                    if ($fileVersion > $from && $fileVersion <= $to) {
                        error_log("Executando migration: $basename");
                        
                        $sql = file_get_contents($migrationFile);
                        
                        // Remove comentários SQL
                        $sql = preg_replace('/^--.*$/m', '', $sql);
                        
                        // Divide por ; e executa cada statement
                        $statements = array_filter(
                            array_map('trim', explode(';', $sql)),
                            function($s) { return !empty($s) && !preg_match('/^\s*$/', $s); }
                        );
                        
                        foreach ($statements as $statement) {
                            if (!empty(trim($statement))) {
                                try {
                                    $this->db->exec($statement);
                                } catch (\PDOException $stmtError) {
                                    // Ignora erros de "já existe" mas loga outros
                                    if (strpos($stmtError->getMessage(), 'already exists') === false && 
                                        strpos($stmtError->getMessage(), 'Duplicate entry') === false) {
                                        error_log("Erro no statement: " . $stmtError->getMessage());
                                        throw $stmtError;
                                    }
                                }
                            }
                        }
                        
                        error_log("Migration $fileVersion ($basename) aplicada com sucesso");
                    }
                }
            }
            
            // Atualiza versão final
            $this->updateVersion($to);
            
            $this->db->commit();
            
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception("Erro ao aplicar migration: " . $e->getMessage());
        }
    }
    
    /**
     * Atualiza a versão do banco de dados
     */
    private function updateVersion($version) {
        $versionConfig = require __DIR__ . '/../config/version.php';
        $systemVersion = $versionConfig['version'];
        
        $sql = "UPDATE system_version SET db_version = ?, system_version = ? WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$version, $systemVersion]);
    }
    
    /**
     * Verifica se o sistema precisa de instalação inicial
     */
    public function needsInitialSetup() {
        try {
            // Tenta consultar a tabela de versão
            $sql = "SELECT COUNT(*) FROM system_version";
            $this->db->query($sql);
            return false;
        } catch (PDOException $e) {
            return true;
        }
    }
    
    /**
     * Executa a instalação inicial do sistema
     */
    public function runInitialSetup() {
        $this->db->beginTransaction();
        
        try {
            // Carrega e executa o script de instalação inicial
            $setupFile = $this->migrationsPath . '001_migration.sql';
            
            if (!file_exists($setupFile)) {
                throw new \Exception("Arquivo de instalação inicial não encontrado");
            }
            
            $sql = file_get_contents($setupFile);
            
            // Divide e executa cada statement
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($s) { return !empty($s); }
            );
            
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $this->db->exec($statement);
                }
            }
            
            // Cria usuário inicial admin/admin
            $this->createInitialAdmin();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Sistema instalado com sucesso!'
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Erro na instalação inicial: " . $e->getMessage());
        }
    }
    
    /**
     * Cria o usuário administrador inicial
     */
    private function createInitialAdmin() {
        // Verifica se já existe usuário admin
        $checkSql = "SELECT COUNT(*) FROM usuarios WHERE email = 'admin@clinfec.com.br'";
        $count = $this->db->query($checkSql)->fetchColumn();
        
        if ($count == 0) {
            $sql = "INSERT INTO usuarios (nome, email, senha, role, ativo, email_verificado) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'Administrador do Sistema',
                'admin@clinfec.com.br',
                password_hash('admin', PASSWORD_DEFAULT),
                'master',
                1,
                1
            ]);
            
            // Insere serviços básicos
            $servicosBasicos = [
                'Consultoria em TI',
                'Desenvolvimento de Software',
                'Suporte Técnico',
                'Infraestrutura de Rede',
                'Segurança da Informação',
                'Treinamento',
                'Design Gráfico',
                'Marketing Digital',
                'Contabilidade',
                'Jurídico'
            ];
            
            $sqlServico = "INSERT INTO servicos (nome, descricao, ativo) VALUES (?, ?, 1)";
            $stmtServico = $this->db->prepare($sqlServico);
            
            foreach ($servicosBasicos as $servico) {
                $stmtServico->execute([$servico, "Serviço de $servico"]);
            }
            
            // Log da instalação
            $sqlLog = "INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) 
                       VALUES (1, 'SISTEMA_INSTALADO', 'Sistema instalado e configurado', '127.0.0.1')";
            $this->db->exec($sqlLog);
        }
    }
    
    /**
     * Retorna informações sobre o status do banco de dados
     */
    public function getStatus() {
        try {
            $version = $this->getCurrentVersion();
            $versionConfig = require __DIR__ . '/../config/version.php';
            
            return [
                'installed' => !$this->needsInitialSetup(),
                'current_db_version' => $version,
                'system_version' => $versionConfig['version'],
                'up_to_date' => $version >= $versionConfig['db_version']
            ];
        } catch (\Exception $e) {
            return [
                'installed' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
