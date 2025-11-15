<?php
/**
 * Classe de Conexão com Banco de Dados
 * Singleton Pattern para gerenciar conexões PDO
 */

namespace App;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        try {
            // Primeiro tenta conectar ao banco de dados específico
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            // Se falhar, tenta criar o banco de dados
            error_log("Banco não existe, tentando criar: " . $e->getMessage());
            
            try {
                // Conecta sem especificar banco
                $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
                $tempConn = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                
                // Cria o banco de dados
                $dbName = $config['database'];
                $tempConn->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$config['charset']} COLLATE {$config['collation']}");
                error_log("Banco de dados '{$dbName}' criado com sucesso");
                
                // Fecha conexão temporária
                $tempConn = null;
                
                // Conecta ao banco recém-criado
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                
            } catch (PDOException $createError) {
                error_log("Erro ao criar banco de dados: " . $createError->getMessage());
                throw new \Exception("Erro ao conectar/criar banco de dados: " . $createError->getMessage());
            }
        }
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    /**
     * Métodos Proxy para compatibilidade com código em cache OPcache
     * Sprint 26 - Adiciona métodos que o cache antigo espera
     */
    
    public function exec($statement) {
        return $this->connection->exec($statement);
    }
    
    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args) {
        return $this->connection->query($statement, $mode, ...$fetch_mode_args);
    }
    
    public function prepare($statement, $driver_options = []) {
        return $this->connection->prepare($statement, $driver_options);
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollBack() {
        return $this->connection->rollBack();
    }
    
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    public function lastInsertId($name = null) {
        return $this->connection->lastInsertId($name);
    }
    
    public function quote($string, $parameter_type = PDO::PARAM_STR) {
        return $this->connection->quote($string, $parameter_type);
    }
    
    // Previne clonagem
    private function __clone() {}
    
    // Previne deserialização
    public function __wakeup() {
        throw new \Exception("Não é possível deserializar singleton");
    }
}
