<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException;

/**
 * Model ProjetoEtapa
 * Gerenciamento de etapas e cronograma de projeto
 */
class ProjetoEtapa {
    
    private $db;
    private $table = 'projeto_etapas';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Buscar todas as etapas de um projeto
     * 
     * @param int $projetoId
     * @param array $filtros
     * @return array
     */
    public function getByProjeto($projetoId, $filtros = []) {
        $sql = "SELECT 
                    pe.*,
                    u1.nome as responsavel_nome,
                    u2.nome as aprovador_nome,
                    u3.nome as criador_nome,
                    (SELECT COUNT(*) FROM projeto_etapas pe2 WHERE pe2.etapa_pai_id = pe.id AND pe2.ativo = 1) as total_subetapas
                FROM {$this->table} pe
                LEFT JOIN usuarios u1 ON pe.responsavel_id = u1.id
                LEFT JOIN usuarios u2 ON pe.aprovado_por = u2.id
                LEFT JOIN usuarios u3 ON pe.created_by = u3.id
                WHERE pe.projeto_id = :projeto_id";
        
        $params = [':projeto_id' => $projetoId];
        
        if (isset($filtros['status']) && $filtros['status'] !== '') {
            $sql .= " AND pe.status = :status";
            $params[':status'] = $filtros['status'];
        }
        
        if (isset($filtros['responsavel_id'])) {
            $sql .= " AND pe.responsavel_id = :responsavel_id";
            $params[':responsavel_id'] = $filtros['responsavel_id'];
        }
        
        if (isset($filtros['e_milestone']) && $filtros['e_milestone'] !== '') {
            $sql .= " AND pe.e_milestone = :e_milestone";
            $params[':e_milestone'] = $filtros['e_milestone'];
        }
        
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $sql .= " AND pe.ativo = :ativo";
            $params[':ativo'] = $filtros['ativo'];
        } else {
            $sql .= " AND pe.ativo = 1";
        }
        
        $sql .= " ORDER BY pe.ordem ASC, pe.data_inicio_prevista ASC";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar cronograma completo (formato Gantt)
     * 
     * @param int $projetoId
     * @return array
     */
    public function getCronogramaGantt($projetoId) {
        $etapas = $this->getByProjeto($projetoId);
        
        // Formatar para Gantt Chart
        $ganttData = [];
        foreach ($etapas as $etapa) {
            $ganttData[] = [
                'id' => $etapa['id'],
                'text' => $etapa['nome'],
                'start_date' => $etapa['data_inicio_prevista'] ?? $etapa['data_inicio_real'],
                'end_date' => $etapa['data_fim_prevista'] ?? $etapa['data_fim_real'],
                'duration' => $this->calcularDuracao($etapa['data_inicio_prevista'], $etapa['data_fim_prevista']),
                'progress' => $etapa['percentual_conclusao'] / 100,
                'parent' => $etapa['etapa_pai_id'],
                'dependencies' => $etapa['dependencias'] ? json_decode($etapa['dependencias'], true) : [],
                'status' => $etapa['status'],
                'milestone' => $etapa['e_milestone'] == 1
            ];
        }
        
        return $ganttData;
    }
    
    /**
     * Buscar timeline do projeto
     * 
     * @param int $projetoId
     * @return array
     */
    public function getTimeline($projetoId) {
        $sql = "SELECT 
                    pe.*,
                    u.nome as responsavel_nome
                FROM {$this->table} pe
                LEFT JOIN usuarios u ON pe.responsavel_id = u.id
                WHERE pe.projeto_id = :projeto_id 
                  AND pe.ativo = 1
                  AND pe.e_milestone = 1
                ORDER BY pe.data_inicio_prevista ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar etapa por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT 
                    pe.*,
                    u1.nome as responsavel_nome,
                    u1.email as responsavel_email,
                    u2.nome as aprovador_nome,
                    u3.nome as criador_nome
                FROM {$this->table} pe
                LEFT JOIN usuarios u1 ON pe.responsavel_id = u1.id
                LEFT JOIN usuarios u2 ON pe.aprovado_por = u2.id
                LEFT JOIN usuarios u3 ON pe.created_by = u3.id
                WHERE pe.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Criar etapa
     * 
     * @param array $data
     * @return int
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
                    projeto_id, etapa_pai_id, nome, descricao, ordem,
                    data_inicio_prevista, data_fim_prevista,
                    percentual_conclusao, status,
                    horas_estimadas, orcamento_estimado,
                    dependencias, tipo_dependencia,
                    responsavel_id,
                    entregavel, e_milestone,
                    observacoes, ativo,
                    created_by, created_at
                ) VALUES (
                    :projeto_id, :etapa_pai_id, :nome, :descricao, :ordem,
                    :data_inicio_prevista, :data_fim_prevista,
                    :percentual_conclusao, :status,
                    :horas_estimadas, :orcamento_estimado,
                    :dependencias, :tipo_dependencia,
                    :responsavel_id,
                    :entregavel, :e_milestone,
                    :observacoes, :ativo,
                    :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':projeto_id', $data['projeto_id'], PDO::PARAM_INT);
        $stmt->bindValue(':etapa_pai_id', $data['etapa_pai_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':ordem', $data['ordem'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio_prevista', $data['data_inicio_prevista'] ?? null);
        $stmt->bindValue(':data_fim_prevista', $data['data_fim_prevista'] ?? null);
        $stmt->bindValue(':percentual_conclusao', $data['percentual_conclusao'] ?? 0);
        $stmt->bindValue(':status', $data['status'] ?? 'nao_iniciada');
        $stmt->bindValue(':horas_estimadas', $data['horas_estimadas'] ?? 0);
        $stmt->bindValue(':orcamento_estimado', $data['orcamento_estimado'] ?? 0);
        $stmt->bindValue(':dependencias', isset($data['dependencias']) ? json_encode($data['dependencias']) : null);
        $stmt->bindValue(':tipo_dependencia', $data['tipo_dependencia'] ?? 'fim_inicio');
        $stmt->bindValue(':responsavel_id', $data['responsavel_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':entregavel', $data['entregavel'] ?? null);
        $stmt->bindValue(':e_milestone', $data['e_milestone'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar etapa
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET
                    etapa_pai_id = :etapa_pai_id,
                    nome = :nome,
                    descricao = :descricao,
                    ordem = :ordem,
                    data_inicio_prevista = :data_inicio_prevista,
                    data_fim_prevista = :data_fim_prevista,
                    data_inicio_real = :data_inicio_real,
                    data_fim_real = :data_fim_real,
                    percentual_conclusao = :percentual_conclusao,
                    status = :status,
                    horas_estimadas = :horas_estimadas,
                    horas_executadas = :horas_executadas,
                    orcamento_estimado = :orcamento_estimado,
                    custo_realizado = :custo_realizado,
                    dependencias = :dependencias,
                    tipo_dependencia = :tipo_dependencia,
                    responsavel_id = :responsavel_id,
                    entregavel = :entregavel,
                    entregavel_arquivo = :entregavel_arquivo,
                    e_milestone = :e_milestone,
                    observacoes = :observacoes,
                    ativo = :ativo,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':etapa_pai_id', $data['etapa_pai_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':ordem', $data['ordem'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio_prevista', $data['data_inicio_prevista'] ?? null);
        $stmt->bindValue(':data_fim_prevista', $data['data_fim_prevista'] ?? null);
        $stmt->bindValue(':data_inicio_real', $data['data_inicio_real'] ?? null);
        $stmt->bindValue(':data_fim_real', $data['data_fim_real'] ?? null);
        $stmt->bindValue(':percentual_conclusao', $data['percentual_conclusao'] ?? 0);
        $stmt->bindValue(':status', $data['status'] ?? 'nao_iniciada');
        $stmt->bindValue(':horas_estimadas', $data['horas_estimadas'] ?? 0);
        $stmt->bindValue(':horas_executadas', $data['horas_executadas'] ?? 0);
        $stmt->bindValue(':orcamento_estimado', $data['orcamento_estimado'] ?? 0);
        $stmt->bindValue(':custo_realizado', $data['custo_realizado'] ?? 0);
        $stmt->bindValue(':dependencias', isset($data['dependencias']) ? json_encode($data['dependencias']) : null);
        $stmt->bindValue(':tipo_dependencia', $data['tipo_dependencia'] ?? 'fim_inicio');
        $stmt->bindValue(':responsavel_id', $data['responsavel_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':entregavel', $data['entregavel'] ?? null);
        $stmt->bindValue(':entregavel_arquivo', $data['entregavel_arquivo'] ?? null);
        $stmt->bindValue(':e_milestone', $data['e_milestone'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $data['updated_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Atualizar percentual de conclusão
     * 
     * @param int $id
     * @param float $percentual
     * @return bool
     */
    public function atualizarPercentual($id, $percentual) {
        $sql = "UPDATE {$this->table} SET
                    percentual_conclusao = :percentual,
                    status = CASE 
                        WHEN :percentual >= 100 THEN 'concluida'
                        WHEN :percentual > 0 THEN 'em_andamento'
                        ELSE status
                    END,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':percentual', $percentual);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Aprovar etapa
     * 
     * @param int $id
     * @param int $aprovadorId
     * @return bool
     */
    public function aprovar($id, $aprovadorId = null) {
        $sql = "UPDATE {$this->table} SET
                    aprovado = 1,
                    aprovado_por = :aprovado_por,
                    data_aprovacao = NOW(),
                    status = 'concluida',
                    percentual_conclusao = 100,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':aprovado_por', $aprovadorId ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Rejeitar aprovação da etapa
     * 
     * @param int $id
     * @param string $motivo
     * @return bool
     */
    public function rejeitarAprovacao($id, $motivo) {
        $sql = "UPDATE {$this->table} SET
                    aprovado = 0,
                    status = 'em_andamento',
                    observacoes = CONCAT(IFNULL(observacoes, ''), '\nRejeição: ', :motivo),
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $motivo);
        
        return $stmt->execute();
    }
    
    /**
     * Soft delete
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} 
                SET ativo = 0, 
                    updated_by = :updated_by,
                    updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar dependências da etapa
     * 
     * @param int $id
     * @return array
     */
    public function verificarDependencias($id) {
        $etapa = $this->findById($id);
        if (!$etapa || !$etapa['dependencias']) {
            return ['pode_iniciar' => true, 'dependencias_pendentes' => []];
        }
        
        $dependencias = json_decode($etapa['dependencias'], true);
        if (empty($dependencias)) {
            return ['pode_iniciar' => true, 'dependencias_pendentes' => []];
        }
        
        $dependenciasPendentes = [];
        foreach ($dependencias as $depId) {
            $dep = $this->findById($depId);
            if ($dep && $dep['status'] !== 'concluida') {
                $dependenciasPendentes[] = [
                    'id' => $dep['id'],
                    'nome' => $dep['nome'],
                    'status' => $dep['status'],
                    'percentual_conclusao' => $dep['percentual_conclusao']
                ];
            }
        }
        
        return [
            'pode_iniciar' => empty($dependenciasPendentes),
            'dependencias_pendentes' => $dependenciasPendentes
        ];
    }
    
    /**
     * Buscar etapas atrasadas de um projeto
     * 
     * @param int $projetoId
     * @return array
     */
    public function getAtrasadas($projetoId) {
        $sql = "SELECT pe.*, u.nome as responsavel_nome,
                       DATEDIFF(CURDATE(), pe.data_fim_prevista) as dias_atraso
                FROM {$this->table} pe
                LEFT JOIN usuarios u ON pe.responsavel_id = u.id
                WHERE pe.projeto_id = :projeto_id
                  AND pe.status IN ('nao_iniciada', 'em_andamento')
                  AND pe.data_fim_prevista < CURDATE()
                  AND pe.ativo = 1
                ORDER BY pe.data_fim_prevista ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Calcular duração entre duas datas (dias úteis)
     * 
     * @param string $dataInicio
     * @param string $dataFim
     * @return int
     */
    private function calcularDuracao($dataInicio, $dataFim) {
        if (!$dataInicio || !$dataFim) {
            return 0;
        }
        
        $inicio = new \DateTime($dataInicio);
        $fim = new \DateTime($dataFim);
        $diff = $inicio->diff($fim);
        
        return $diff->days;
    }
    
    /**
     * Reordenar etapas
     * 
     * @param int $projetoId
     * @param array $ordem Array com [id => nova_ordem]
     * @return bool
     */
    public function reordenar($projetoId, $ordem) {
        try {
            $this->db->beginTransaction();
            
            foreach ($ordem as $id => $novaOrdem) {
                $sql = "UPDATE {$this->table} 
                        SET ordem = :ordem, updated_at = NOW() 
                        WHERE id = :id AND projeto_id = :projeto_id";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':ordem', $novaOrdem, PDO::PARAM_INT);
                $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Buscar caminho crítico do projeto
     * 
     * @param int $projetoId
     * @return array
     */
    public function getCaminhoCritico($projetoId) {
        // Implementação simplificada do caminho crítico
        // Retorna as etapas que, se atrasadas, atrasam o projeto inteiro
        
        $sql = "SELECT pe.*
                FROM {$this->table} pe
                WHERE pe.projeto_id = :projeto_id
                  AND pe.ativo = 1
                  AND pe.e_milestone = 1
                ORDER BY pe.data_fim_prevista DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
