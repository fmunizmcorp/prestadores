<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * Model Documento
 * Gerencia documentos das empresas (tomadoras e prestadoras)
 */
class Documento {
    
    private $db;
    private $table = 'empresa_documentos';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Buscar documentos de uma empresa
     * 
     * @param int $empresaId
     * @param string $tipoEmpresa 'tomadora' ou 'prestadora'
     * @return array
     */
    public function getByEmpresa($empresaId, $tipoEmpresa) {
        $sql = "SELECT d.*, u.nome as uploader_nome,
                       DATEDIFF(d.data_validade, NOW()) as dias_validade,
                       CASE 
                           WHEN d.data_validade IS NULL THEN 'sem_vencimento'
                           WHEN d.data_validade < CURDATE() THEN 'vencido'
                           WHEN DATEDIFF(d.data_validade, NOW()) <= 30 THEN 'vencendo'
                           ELSE 'valido'
                       END as status_validade
                FROM {$this->table} d
                LEFT JOIN usuarios u ON d.created_by = u.id
                WHERE d.empresa_id = :empresa_id 
                  AND d.tipo_empresa = :tipo_empresa 
                  AND d.ativo = 1
                ORDER BY d.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_empresa', $tipoEmpresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar documento por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT d.*, u.nome as uploader_nome
                FROM {$this->table} d
                LEFT JOIN usuarios u ON d.created_by = u.id
                WHERE d.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Criar novo documento
     * 
     * @param array $data
     * @return int
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
                    empresa_id, tipo_empresa, tipo_documento,
                    nome_arquivo, arquivo_path, tamanho_bytes,
                    descricao, numero_documento, data_emissao, data_validade,
                    ativo, created_by, created_at
                ) VALUES (
                    :empresa_id, :tipo_empresa, :tipo_documento,
                    :nome_arquivo, :arquivo_path, :tamanho_bytes,
                    :descricao, :numero_documento, :data_emissao, :data_validade,
                    :ativo, :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':empresa_id', $data['empresa_id'], PDO::PARAM_INT);
        $stmt->bindValue(':tipo_empresa', $data['tipo_empresa']);
        $stmt->bindValue(':tipo_documento', $data['tipo_documento']);
        $stmt->bindValue(':nome_arquivo', $data['nome_arquivo']);
        $stmt->bindValue(':arquivo_path', $data['arquivo_path']);
        $stmt->bindValue(':tamanho_bytes', $data['tamanho_bytes'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':numero_documento', $data['numero_documento'] ?? null);
        $stmt->bindValue(':data_emissao', $data['data_emissao'] ?? null);
        $stmt->bindValue(':data_validade', $data['data_validade'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar documento
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET
                    tipo_documento = :tipo_documento,
                    descricao = :descricao,
                    numero_documento = :numero_documento,
                    data_emissao = :data_emissao,
                    data_validade = :data_validade";
        
        // Se está atualizando o arquivo
        if (!empty($data['nome_arquivo'])) {
            $sql .= ", nome_arquivo = :nome_arquivo,
                       arquivo_path = :arquivo_path,
                       tamanho_bytes = :tamanho_bytes";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_documento', $data['tipo_documento']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':numero_documento', $data['numero_documento'] ?? null);
        $stmt->bindValue(':data_emissao', $data['data_emissao'] ?? null);
        $stmt->bindValue(':data_validade', $data['data_validade'] ?? null);
        
        if (!empty($data['nome_arquivo'])) {
            $stmt->bindValue(':nome_arquivo', $data['nome_arquivo']);
            $stmt->bindValue(':arquivo_path', $data['arquivo_path']);
            $stmt->bindValue(':tamanho_bytes', $data['tamanho_bytes'] ?? 0, PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Excluir documento
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $documento = $this->findById($id);
        
        if (!$documento) {
            throw new \Exception('Documento não encontrado');
        }
        
        // Deletar arquivo físico
        if (file_exists($documento['arquivo_path'])) {
            unlink($documento['arquivo_path']);
        }
        
        // Soft delete no banco
        $sql = "UPDATE {$this->table} 
                SET ativo = 0
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Buscar documentos vencidos
     * 
     * @param string|null $tipoEmpresa Filtrar por tipo
     * @return array
     */
    public function getVencidos($tipoEmpresa = null) {
        $sql = "SELECT d.*, 
                       DATEDIFF(NOW(), d.data_validade) as dias_vencido
                FROM {$this->table} d
                WHERE d.data_validade < CURDATE()
                  AND d.ativo = 1";
        
        if ($tipoEmpresa) {
            $sql .= " AND d.tipo_empresa = :tipo_empresa";
        }
        
        $sql .= " ORDER BY d.data_validade ASC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($tipoEmpresa) {
            $stmt->bindValue(':tipo_empresa', $tipoEmpresa);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar documentos próximos ao vencimento
     * 
     * @param int $dias
     * @param string|null $tipoEmpresa
     * @return array
     */
    public function getProximosVencimento($dias = 30, $tipoEmpresa = null) {
        $sql = "SELECT d.*, 
                       DATEDIFF(d.data_validade, NOW()) as dias_restantes
                FROM {$this->table} d
                WHERE d.data_validade >= CURDATE()
                  AND DATEDIFF(d.data_validade, NOW()) <= :dias
                  AND d.ativo = 1";
        
        if ($tipoEmpresa) {
            $sql .= " AND d.tipo_empresa = :tipo_empresa";
        }
        
        $sql .= " ORDER BY d.data_validade ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        
        if ($tipoEmpresa) {
            $stmt->bindValue(':tipo_empresa', $tipoEmpresa);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Contar documentos por tipo
     * 
     * @param int $empresaId
     * @param string $tipoEmpresa
     * @return array
     */
    public function contarPorTipo($empresaId, $tipoEmpresa) {
        $sql = "SELECT tipo_documento, COUNT(*) as total
                FROM {$this->table}
                WHERE empresa_id = :empresa_id
                  AND tipo_empresa = :tipo_empresa
                  AND ativo = 1
                GROUP BY tipo_documento";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_empresa', $tipoEmpresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Validar upload de arquivo
     * 
     * @param array $file $_FILES array
     * @param array $allowedTypes
     * @param int $maxSize
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function validarUpload($file, $allowedTypes = null, $maxSize = null) {
        $allowedTypes = $allowedTypes ?? ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
        $maxSize = $maxSize ?? 10485760; // 10MB
        
        // Verificar se houve erro no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Erro no upload do arquivo'
            ];
        }
        
        // Verificar tamanho
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'message' => 'Arquivo muito grande. Máximo: ' . ($maxSize / 1024 / 1024) . 'MB'
            ];
        }
        
        // Verificar extensão
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Tipo de arquivo não permitido. Permitidos: ' . implode(', ', $allowedTypes)
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Arquivo válido',
            'data' => [
                'nome' => $file['name'],
                'tamanho' => $file['size'],
                'tipo' => $file['type'],
                'extensao' => $ext
            ]
        ];
    }
    
    /**
     * Fazer upload de arquivo
     * 
     * @param array $file $_FILES array
     * @param string $destino Pasta de destino
     * @return array ['success' => bool, 'path' => string, 'message' => string]
     */
    public function uploadArquivo($file, $destino = 'uploads/documentos/') {
        // Validar
        $validacao = $this->validarUpload($file);
        if (!$validacao['success']) {
            return $validacao;
        }
        
        // Criar diretório se não existir
        if (!is_dir($destino)) {
            mkdir($destino, 0777, true);
        }
        
        // Nome único do arquivo
        $nomeOriginal = pathinfo($file['name'], PATHINFO_FILENAME);
        $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nomeArquivo = $nomeOriginal . '_' . time() . '_' . uniqid() . '.' . $extensao;
        $caminhoCompleto = $destino . $nomeArquivo;
        
        // Mover arquivo
        if (move_uploaded_file($file['tmp_name'], $caminhoCompleto)) {
            return [
                'success' => true,
                'path' => $caminhoCompleto,
                'nome' => $file['name'],
                'tamanho' => $file['size'],
                'message' => 'Upload realizado com sucesso'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erro ao mover arquivo para destino'
        ];
    }
}
