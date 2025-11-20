<?php

namespace App\Models;

use App\Database;
use PDO;

class ProjetoExecucao
{
    private $db;
    private $table = 'projeto_execucao';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByProjeto($projetoId, $filtros = [])
    {
        $sql = "SELECT pex.*, 
                       u.nome as usuario_nome,
                       pe.nome as etapa_nome,
                       apr.nome as aprovador_nome
                FROM {$this->table} pex
                INNER JOIN usuarios u ON pex.usuario_id = u.id
                LEFT JOIN projeto_etapas pe ON pex.etapa_id = pe.id
                LEFT JOIN usuarios apr ON pex.aprovador_id = apr.id
                WHERE pex.projeto_id = :projeto_id 
                AND pex.ativo = TRUE";

        $params = ['projeto_id' => $projetoId];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND pex.usuario_id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['etapa_id'])) {
            $sql .= " AND pex.etapa_id = :etapa_id";
            $params['etapa_id'] = $filtros['etapa_id'];
        }

        if (!empty($filtros['tipo_hora'])) {
            $sql .= " AND pex.tipo_hora = :tipo_hora";
            $params['tipo_hora'] = $filtros['tipo_hora'];
        }

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $sql .= " AND pex.data BETWEEN :data_inicio AND :data_fim";
            $params['data_inicio'] = $filtros['data_inicio'];
            $params['data_fim'] = $filtros['data_fim'];
        }

        $sql .= " ORDER BY pex.data DESC, pex.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUsuario($usuarioId, $filtros = [])
    {
        $sql = "SELECT pex.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       pe.nome as etapa_nome
                FROM {$this->table} pex
                INNER JOIN projetos p ON pex.projeto_id = p.id
                LEFT JOIN projeto_etapas pe ON pex.etapa_id = pe.id
                WHERE pex.usuario_id = :usuario_id 
                AND pex.ativo = TRUE";

        $params = ['usuario_id' => $usuarioId];

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $sql .= " AND pex.data BETWEEN :data_inicio AND :data_fim";
            $params['data_inicio'] = $filtros['data_inicio'];
            $params['data_fim'] = $filtros['data_fim'];
        }

        $sql .= " ORDER BY pex.data DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT pex.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       u.nome as usuario_nome,
                       pe.nome as etapa_nome,
                       apr.nome as aprovador_nome
                FROM {$this->table} pex
                INNER JOIN projetos p ON pex.projeto_id = p.id
                INNER JOIN usuarios u ON pex.usuario_id = u.id
                LEFT JOIN projeto_etapas pe ON pex.etapa_id = pe.id
                LEFT JOIN usuarios apr ON pex.aprovador_id = apr.id
                WHERE pex.id = :id 
                AND pex.ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    projeto_id, etapa_id, usuario_id, data, horas, 
                    tipo_hora, descricao_atividade, valor, faturavel, 
                    aprovado, observacoes
                ) VALUES (
                    :projeto_id, :etapa_id, :usuario_id, :data, :horas, 
                    :tipo_hora, :descricao_atividade, :valor, :faturavel, 
                    :aprovado, :observacoes
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'projeto_id' => $data['projeto_id'],
            'etapa_id' => $data['etapa_id'] ?? null,
            'usuario_id' => $data['usuario_id'],
            'data' => $data['data'] ?? date('Y-m-d'),
            'horas' => $data['horas'],
            'tipo_hora' => $data['tipo_hora'] ?? 'normal',
            'descricao_atividade' => $data['descricao_atividade'],
            'valor' => $data['valor'] ?? 0,
            'faturavel' => $data['faturavel'] ?? 1,
            'aprovado' => $data['aprovado'] ?? 0,
            'observacoes' => $data['observacoes'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                    etapa_id = :etapa_id,
                    data = :data,
                    horas = :horas,
                    tipo_hora = :tipo_hora,
                    descricao_atividade = :descricao_atividade,
                    valor = :valor,
                    faturavel = :faturavel,
                    observacoes = :observacoes
                WHERE id = :id 
                AND ativo = TRUE
                AND aprovado = FALSE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'etapa_id' => $data['etapa_id'] ?? null,
            'data' => $data['data'],
            'horas' => $data['horas'],
            'tipo_hora' => $data['tipo_hora'] ?? 'normal',
            'descricao_atividade' => $data['descricao_atividade'],
            'valor' => $data['valor'] ?? 0,
            'faturavel' => $data['faturavel'] ?? 1,
            'observacoes' => $data['observacoes'] ?? null
        ]);
    }

    public function aprovar($id, $aprovadorId, $observacoes = null)
    {
        $sql = "UPDATE {$this->table} SET 
                    aprovado = TRUE,
                    aprovador_id = :aprovador_id,
                    data_aprovacao = NOW(),
                    observacoes_aprovacao = :observacoes
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'aprovador_id' => $aprovadorId,
            'observacoes' => $observacoes
        ]);
    }

    public function aprovarLote($ids, $aprovadorId)
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE {$this->table} SET 
                    aprovado = TRUE,
                    aprovador_id = ?,
                    data_aprovacao = NOW()
                WHERE id IN ($placeholders) 
                AND ativo = TRUE";

        $params = array_merge([$aprovadorId], $ids);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        // Só permite excluir se não foi aprovado
        $sql = "UPDATE {$this->table} SET ativo = FALSE 
                WHERE id = :id AND aprovado = FALSE";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getTotais($projetoId, $filtros = [])
    {
        $sql = "SELECT 
                    COUNT(*) as total_registros,
                    SUM(horas) as total_horas,
                    SUM(valor) as total_valor,
                    SUM(CASE WHEN aprovado = TRUE THEN horas ELSE 0 END) as horas_aprovadas,
                    SUM(CASE WHEN aprovado = FALSE THEN horas ELSE 0 END) as horas_pendentes
                FROM {$this->table}
                WHERE projeto_id = :projeto_id 
                AND ativo = TRUE";

        $params = ['projeto_id' => $projetoId];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND usuario_id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPendentesAprovacao($projetoId = null)
    {
        $sql = "SELECT pex.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       u.nome as usuario_nome,
                       pe.nome as etapa_nome
                FROM {$this->table} pex
                INNER JOIN projetos p ON pex.projeto_id = p.id
                INNER JOIN usuarios u ON pex.usuario_id = u.id
                LEFT JOIN projeto_etapas pe ON pex.etapa_id = pe.id
                WHERE pex.aprovado = FALSE 
                AND pex.ativo = TRUE";

        $params = [];

        if ($projetoId) {
            $sql .= " AND pex.projeto_id = :projeto_id";
            $params['projeto_id'] = $projetoId;
        }

        $sql .= " ORDER BY pex.data DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTiposHoraDisponiveis()
    {
        return [
            'normal' => 'Normal',
            'extra_50' => 'Extra 50%',
            'extra_100' => 'Extra 100%',
            'noturno' => 'Noturno',
            'feriado' => 'Feriado',
            'sobreaviso' => 'Sobreaviso'
        ];
    }
}
