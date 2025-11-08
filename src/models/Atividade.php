<?php

namespace App\Models;

use PDO;
use PDOException;

class Atividade extends BaseModel
{
    protected $table = 'atividades';

    /**
     * Buscar todas as atividades com filtros
     */
    public function all($filtros = [], $page = 1, $limit = 25)
    {
        $offset = ($page - 1) * $limit;
        $where = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($filtros['projeto_id'])) {
            $where[] = 'a.projeto_id = :projeto_id';
            $params[':projeto_id'] = $filtros['projeto_id'];
        }

        if (!empty($filtros['status'])) {
            $where[] = 'a.status = :status';
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['responsavel_id'])) {
            $where[] = 'a.responsavel_id = :responsavel_id';
            $params[':responsavel_id'] = $filtros['responsavel_id'];
        }

        if (!empty($filtros['busca'])) {
            $where[] = '(a.titulo LIKE :busca OR a.descricao LIKE :busca)';
            $params[':busca'] = '%' . $filtros['busca'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT a.*,
                       p.nome AS projeto_nome,
                       u.nome AS responsavel_nome
                FROM {$this->table} a
                INNER JOIN projetos p ON p.id = a.projeto_id
                LEFT JOIN usuarios u ON u.id = a.responsavel_id
                WHERE {$whereClause}
                ORDER BY a.prioridade DESC, a.data_fim ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar atividades com filtros
     */
    public function count($filtros = [])
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($filtros['projeto_id'])) {
            $where[] = 'projeto_id = :projeto_id';
            $params[':projeto_id'] = $filtros['projeto_id'];
        }

        if (!empty($filtros['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['responsavel_id'])) {
            $where[] = 'responsavel_id = :responsavel_id';
            $params[':responsavel_id'] = $filtros['responsavel_id'];
        }

        if (!empty($filtros['busca'])) {
            $where[] = '(titulo LIKE :busca OR descricao LIKE :busca)';
            $params[':busca'] = '%' . $filtros['busca'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Contar total de atividades
     */
    public function countTotal()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    /**
     * Contar atividades por status
     */
    public function countPorStatus($status)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE status = :status AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Buscar atividade por ID
     */
    public function findById($id)
    {
        $sql = "SELECT a.*,
                       p.nome AS projeto_nome,
                       p.codigo AS projeto_codigo,
                       u.nome AS responsavel_nome,
                       u.email AS responsavel_email
                FROM {$this->table} a
                INNER JOIN projetos p ON p.id = a.projeto_id
                LEFT JOIN usuarios u ON u.id = a.responsavel_id
                WHERE a.id = :id AND a.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Criar nova atividade
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    projeto_id, titulo, descricao, responsavel_id,
                    data_inicio, data_fim, prioridade, status,
                    custo_estimado, horas_estimadas, observacoes,
                    created_by
                ) VALUES (
                    :projeto_id, :titulo, :descricao, :responsavel_id,
                    :data_inicio, :data_fim, :prioridade, :status,
                    :custo_estimado, :horas_estimadas, :observacoes,
                    :created_by
                )";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':projeto_id' => $data['projeto_id'],
            ':titulo' => $data['titulo'],
            ':descricao' => $data['descricao'] ?? null,
            ':responsavel_id' => $data['responsavel_id'] ?? null,
            ':data_inicio' => $data['data_inicio'] ?? null,
            ':data_fim' => $data['data_fim'] ?? null,
            ':prioridade' => $data['prioridade'] ?? 'media',
            ':status' => $data['status'] ?? 'pendente',
            ':custo_estimado' => $data['custo_estimado'] ?? 0,
            ':horas_estimadas' => $data['horas_estimadas'] ?? 0,
            ':observacoes' => $data['observacoes'] ?? null,
            ':created_by' => $_SESSION['usuario_id'] ?? null
        ];

        $stmt->execute($params);

        return $this->db->lastInsertId();
    }

    /**
     * Atualizar atividade
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                    titulo = :titulo,
                    descricao = :descricao,
                    responsavel_id = :responsavel_id,
                    data_inicio = :data_inicio,
                    data_fim = :data_fim,
                    prioridade = :prioridade,
                    custo_estimado = :custo_estimado,
                    horas_estimadas = :horas_estimadas,
                    observacoes = :observacoes,
                    updated_by = :updated_by
                WHERE id = :id AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':id' => $id,
            ':titulo' => $data['titulo'],
            ':descricao' => $data['descricao'] ?? null,
            ':responsavel_id' => $data['responsavel_id'] ?? null,
            ':data_inicio' => $data['data_inicio'] ?? null,
            ':data_fim' => $data['data_fim'] ?? null,
            ':prioridade' => $data['prioridade'] ?? 'media',
            ':custo_estimado' => $data['custo_estimado'] ?? 0,
            ':horas_estimadas' => $data['horas_estimadas'] ?? 0,
            ':observacoes' => $data['observacoes'] ?? null,
            ':updated_by' => $_SESSION['usuario_id'] ?? null
        ];

        return $stmt->execute($params);
    }

    /**
     * Alterar status da atividade
     */
    public function alterarStatus($id, $novoStatus)
    {
        $sql = "UPDATE {$this->table} SET
                    status = :status,
                    updated_by = :updated_by";

        // Se concluir, registrar data de conclusão
        if ($novoStatus === 'concluida') {
            $sql .= ", data_conclusao = NOW(), progresso = 100";
        }

        $sql .= " WHERE id = :id AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':status' => $novoStatus,
            ':updated_by' => $_SESSION['usuario_id'] ?? null
        ]);
    }

    /**
     * Soft delete - marcar como deletado
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET
                    deleted_at = NOW(),
                    updated_by = :updated_by
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':updated_by' => $_SESSION['usuario_id'] ?? null
        ]);
    }

    /**
     * Buscar atividades de um projeto
     */
    public function getByProjeto($projetoId)
    {
        $sql = "SELECT a.*,
                       u.nome AS responsavel_nome
                FROM {$this->table} a
                LEFT JOIN usuarios u ON u.id = a.responsavel_id
                WHERE a.projeto_id = :projeto_id AND a.deleted_at IS NULL
                ORDER BY a.prioridade DESC, a.data_fim ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':projeto_id' => $projetoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar atividades atrasadas
     */
    public function getAtrasadas()
    {
        $sql = "SELECT a.*,
                       p.nome AS projeto_nome,
                       u.nome AS responsavel_nome,
                       DATEDIFF(CURDATE(), a.data_fim) AS dias_atraso
                FROM {$this->table} a
                INNER JOIN projetos p ON p.id = a.projeto_id
                LEFT JOIN usuarios u ON u.id = a.responsavel_id
                WHERE a.data_fim < CURDATE()
                  AND a.status NOT IN ('concluida', 'cancelada')
                  AND a.deleted_at IS NULL
                ORDER BY a.prioridade DESC, dias_atraso DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar atividades por responsável
     */
    public function getByResponsavel($usuarioId)
    {
        $sql = "SELECT a.*,
                       p.nome AS projeto_nome
                FROM {$this->table} a
                INNER JOIN projetos p ON p.id = a.projeto_id
                WHERE a.responsavel_id = :usuario_id
                  AND a.status NOT IN ('concluida', 'cancelada')
                  AND a.deleted_at IS NULL
                ORDER BY a.prioridade DESC, a.data_fim ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adicionar membro à equipe da atividade
     */
    public function addMembroEquipe($atividadeId, $data)
    {
        $sql = "INSERT INTO atividade_equipe (
                    atividade_id, usuario_id, funcao, papel,
                    percentual_alocacao, horas_alocadas, custo_hora_membro,
                    data_inicio, data_fim, created_by
                ) VALUES (
                    :atividade_id, :usuario_id, :funcao, :papel,
                    :percentual_alocacao, :horas_alocadas, :custo_hora_membro,
                    :data_inicio, :data_fim, :created_by
                )";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':atividade_id' => $atividadeId,
            ':usuario_id' => $data['usuario_id'],
            ':funcao' => $data['funcao'] ?? null,
            ':papel' => $data['papel'] ?? 'executor',
            ':percentual_alocacao' => $data['percentual_alocacao'] ?? 100,
            ':horas_alocadas' => $data['horas_alocadas'] ?? 0,
            ':custo_hora_membro' => $data['custo_hora_membro'] ?? 0,
            ':data_inicio' => $data['data_inicio'] ?? null,
            ':data_fim' => $data['data_fim'] ?? null,
            ':created_by' => $_SESSION['usuario_id'] ?? null
        ];

        return $stmt->execute($params);
    }

    /**
     * Buscar equipe da atividade
     */
    public function getEquipe($atividadeId)
    {
        $sql = "SELECT ae.*, u.nome AS usuario_nome, u.email AS usuario_email
                FROM atividade_equipe ae
                INNER JOIN usuarios u ON u.id = ae.usuario_id
                WHERE ae.atividade_id = :atividade_id AND ae.ativo = TRUE
                ORDER BY ae.papel, u.nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':atividade_id' => $atividadeId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar tempo trabalhado
     */
    public function registrarTempo($atividadeId, $data)
    {
        $sql = "INSERT INTO atividade_tempo (
                    atividade_id, usuario_id, data, hora_inicio, hora_fim,
                    horas_trabalhadas, descricao, tipo_trabalho
                ) VALUES (
                    :atividade_id, :usuario_id, :data, :hora_inicio, :hora_fim,
                    :horas_trabalhadas, :descricao, :tipo_trabalho
                )";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':atividade_id' => $atividadeId,
            ':usuario_id' => $data['usuario_id'],
            ':data' => $data['data'],
            ':hora_inicio' => $data['hora_inicio'] ?? null,
            ':hora_fim' => $data['hora_fim'] ?? null,
            ':horas_trabalhadas' => $data['horas_trabalhadas'],
            ':descricao' => $data['descricao'],
            ':tipo_trabalho' => $data['tipo_trabalho'] ?? 'desenvolvimento'
        ];

        return $stmt->execute($params);
    }

    /**
     * Buscar registros de tempo da atividade
     */
    public function getRegistrosTempo($atividadeId)
    {
        $sql = "SELECT at.*, u.nome AS usuario_nome
                FROM atividade_tempo at
                INNER JOIN usuarios u ON u.id = at.usuario_id
                WHERE at.atividade_id = :atividade_id
                ORDER BY at.data DESC, at.hora_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':atividade_id' => $atividadeId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adicionar recurso à atividade
     */
    public function addRecurso($atividadeId, $data)
    {
        $sql = "INSERT INTO atividade_recursos (
                    atividade_id, nome, tipo, descricao, quantidade, unidade,
                    custo_unitario, custo_total, fornecedor, status,
                    data_solicitacao, created_by
                ) VALUES (
                    :atividade_id, :nome, :tipo, :descricao, :quantidade, :unidade,
                    :custo_unitario, :custo_total, :fornecedor, :status,
                    :data_solicitacao, :created_by
                )";

        $stmt = $this->db->prepare($sql);

        $custoTotal = ($data['quantidade'] ?? 1) * ($data['custo_unitario'] ?? 0);

        $params = [
            ':atividade_id' => $atividadeId,
            ':nome' => $data['nome'],
            ':tipo' => $data['tipo'] ?? 'material',
            ':descricao' => $data['descricao'] ?? null,
            ':quantidade' => $data['quantidade'] ?? 1,
            ':unidade' => $data['unidade'] ?? 'un',
            ':custo_unitario' => $data['custo_unitario'] ?? 0,
            ':custo_total' => $custoTotal,
            ':fornecedor' => $data['fornecedor'] ?? null,
            ':status' => $data['status'] ?? 'solicitado',
            ':data_solicitacao' => $data['data_solicitacao'] ?? date('Y-m-d'),
            ':created_by' => $_SESSION['usuario_id'] ?? null
        ];

        return $stmt->execute($params);
    }

    /**
     * Buscar recursos da atividade
     */
    public function getRecursos($atividadeId)
    {
        $sql = "SELECT * FROM atividade_recursos
                WHERE atividade_id = :atividade_id
                ORDER BY status, nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':atividade_id' => $atividadeId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
