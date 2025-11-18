<?php
/**
 * Classe de Conexão com Banco de Dados
 * Singleton Pattern para gerenciar conexões PDO
 * Sprint 62 - Fixed namespace declaration order
 */

namespace App;

use PDO;
use PDOException;

// FORCE OPCACHE INVALIDATE - Sprint 62
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}

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
     * Wrapper para prepare() - delega para PDO
     * 
     * @param string $sql Query SQL com placeholders
     * @return \PDOStatement
     */
    public function prepare(string $sql): \PDOStatement {
        return $this->connection->prepare($sql);
    }
    
    /**
     * Wrapper para query() - delega para PDO
     * 
     * @param string $sql Query SQL
     * @return \PDOStatement
     */
    public function query(string $sql): \PDOStatement {
        return $this->connection->query($sql);
    }
    
    /**
     * Wrapper para exec() - delega para PDO
     * 
     * @param string $sql Query SQL
     * @return int Número de linhas afetadas
     */
    public function exec(string $sql): int {
        return $this->connection->exec($sql);
    }
    
    /**
     * Wrapper para lastInsertId() - delega para PDO
     * 
     * @return string ID do último insert
     */
    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Wrapper para beginTransaction() - delega para PDO
     * 
     * @return bool
     */
    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Wrapper para commit() - delega para PDO
     * 
     * @return bool
     */
    public function commit(): bool {
        return $this->connection->commit();
    }
    
    /**
     * Wrapper para rollBack() - delega para PDO
     * 
     * @return bool
     */
    public function rollBack(): bool {
        return $this->connection->rollBack();
    }
    
    /**
     * Wrapper para quote() - delega para PDO
     * 
     * @param string $string String a ser escapada
     * @return string
     */
    public function quote(string $string): string {
        return $this->connection->quote($string);
    }
    
    // Prevenir clonagem
    private function __clone() {}
    
    // Prevenir unserialize
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
