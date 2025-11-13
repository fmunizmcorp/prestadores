<?php

namespace App\Models;

use App\Database;
use PDO;

class ProjetoEquipe
{
    private $db;
    private $table = 'projeto_equipe';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca todos os membros da equipe de um projeto
     *
     * @param int $projetoId
     * @param array $filtros
     * @return array
     */
    public function getByProjeto($projetoId, $filtros = [])
    {
        $sql = "SELECT pe.*, 
                       u.nome as usuario_nome,
                       u.email as usuario_email,
                       u.telefone as usuario_telefone,
                       u.foto as usuario_foto,
                       COALESCE(SUM(pex.horas), 0) as horas_executadas,
                       COALESCE(SUM(pex.valor), 0) as valor_executado
                FROM {$this->table} pe
                INNER JOIN usuarios u ON pe.usuario_id = u.id
                LEFT JOIN projeto_execucao pex ON pex.projeto_id = pe.projeto_id 
                    AND pex.usuario_id = pe.usuario_id 
                    AND pex.ativo = TRUE
                WHERE pe.projeto_id = :projeto_id 
                AND pe.ativo = TRUE";

        $params = ['projeto_id' => $projetoId];

        // Filtros
        if (!empty($filtros['papel'])) {
            $sql .= " AND pe.papel = :papel";
            $params['papel'] = $filtros['papel'];
        }

        if (isset($filtros['disponivel']) && $filtros['disponivel'] !== '') {
            $sql .= " AND pe.disponivel = :disponivel";
            $params['disponivel'] = (int)$filtros['disponivel'];
        }

        $sql .= " GROUP BY pe.id, u.id ORDER BY pe.papel, u.nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca alocações de um usuário
     *
     * @param int $usuarioId
     * @param array $filtros
     * @return array
     */
    public function getByUsuario($usuarioId, $filtros = [])
    {
        $sql = "SELECT pe.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       p.status as projeto_status,
                       p.data_inicio as projeto_inicio,
                       p.data_fim as projeto_fim,
                       et.nome as empresa_nome,
                       COALESCE(SUM(pex.horas), 0) as horas_executadas
                FROM {$this->table} pe
                INNER JOIN projetos p ON pe.projeto_id = p.id
                LEFT JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
                LEFT JOIN projeto_execucao pex ON pex.projeto_id = pe.projeto_id 
                    AND pex.usuario_id = pe.usuario_id 
                    AND pex.ativo = TRUE
                WHERE pe.usuario_id = :usuario_id 
                AND pe.ativo = TRUE";

        $params = ['usuario_id' => $usuarioId];

        // Filtros
        if (!empty($filtros['projeto_status'])) {
            $sql .= " AND p.status = :projeto_status";
            $params['projeto_status'] = $filtros['projeto_status'];
        }

        if (!empty($filtros['papel'])) {
            $sql .= " AND pe.papel = :papel";
            $params['papel'] = $filtros['papel'];
        }

        $sql .= " GROUP BY pe.id, p.id, et.id ORDER BY p.data_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca membro da equipe por ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT pe.*, 
                       u.nome as usuario_nome,
                       u.email as usuario_email,
                       u.perfil as usuario_perfil,
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       COALESCE(SUM(pex.horas), 0) as horas_executadas,
                       COALESCE(SUM(pex.valor), 0) as valor_executado
                FROM {$this->table} pe
                INNER JOIN usuarios u ON pe.usuario_id = u.id
                INNER JOIN projetos p ON pe.projeto_id = p.id
                LEFT JOIN projeto_execucao pex ON pex.projeto_id = pe.projeto_id 
                    AND pex.usuario_id = pe.usuario_id 
                    AND pex.ativo = TRUE
                WHERE pe.id = :id 
                AND pe.ativo = TRUE
                GROUP BY pe.id, u.id, p.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se usuário já está alocado no projeto
     *
     * @param int $projetoId
     * @param int $usuarioId
     * @param int|null $exceptId
     * @return bool
     */
    public function verificarAlocacao($projetoId, $usuarioId, $exceptId = null)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE projeto_id = :projeto_id 
                AND usuario_id = :usuario_id 
                AND ativo = TRUE";

        $params = [
            'projeto_id' => $projetoId,
            'usuario_id' => $usuarioId
        ];

        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Cria nova alocação de membro na equipe
     *
     * @param array $data
     * @return int ID do registro criado
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    projeto_id, usuario_id, papel, horas_previstas, 
                    custo_hora, disponivel, observacoes, data_entrada
                ) VALUES (
                    :projeto_id, :usuario_id, :papel, :horas_previstas, 
                    :custo_hora, :disponivel, :observacoes, :data_entrada
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'projeto_id' => $data['projeto_id'],
            'usuario_id' => $data['usuario_id'],
            'papel' => $data['papel'],
            'horas_previstas' => $data['horas_previstas'] ?? null,
            'custo_hora' => $data['custo_hora'] ?? null,
            'disponivel' => $data['disponivel'] ?? 1,
            'observacoes' => $data['observacoes'] ?? null,
            'data_entrada' => $data['data_entrada'] ?? date('Y-m-d')
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Atualiza alocação de membro da equipe
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                    papel = :papel,
                    horas_previstas = :horas_previstas,
                    custo_hora = :custo_hora,
                    disponivel = :disponivel,
                    observacoes = :observacoes,
                    avaliacao_desempenho = :avaliacao_desempenho,
                    avaliacao_comentario = :avaliacao_comentario
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'papel' => $data['papel'],
            'horas_previstas' => $data['horas_previstas'] ?? null,
            'custo_hora' => $data['custo_hora'] ?? null,
            'disponivel' => $data['disponivel'] ?? 1,
            'observacoes' => $data['observacoes'] ?? null,
            'avaliacao_desempenho' => $data['avaliacao_desempenho'] ?? null,
            'avaliacao_comentario' => $data['avaliacao_comentario'] ?? null
        ]);
    }

    /**
     * Define data de saída do membro da equipe
     *
     * @param int $id
     * @param string|null $dataSaida
     * @param string|null $motivo
     * @return bool
     */
    public function definirSaida($id, $dataSaida = null, $motivo = null)
    {
        $sql = "UPDATE {$this->table} SET 
                    data_saida = :data_saida,
                    motivo_saida = :motivo_saida,
                    disponivel = FALSE
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'data_saida' => $dataSaida ?? date('Y-m-d'),
            'motivo_saida' => $motivo
        ]);
    }

    /**
     * Atualiza disponibilidade do membro
     *
     * @param int $id
     * @param bool $disponivel
     * @return bool
     */
    public function atualizarDisponibilidade($id, $disponivel)
    {
        $sql = "UPDATE {$this->table} SET 
                    disponivel = :disponivel
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'disponivel' => (int)$disponivel
        ]);
    }

    /**
     * Registra avaliação de desempenho do membro
     *
     * @param int $id
     * @param int $nota (1-5)
     * @param string|null $comentario
     * @return bool
     */
    public function avaliarDesempenho($id, $nota, $comentario = null)
    {
        $sql = "UPDATE {$this->table} SET 
                    avaliacao_desempenho = :nota,
                    avaliacao_comentario = :comentario
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nota' => $nota,
            'comentario' => $comentario
        ]);
    }

    /**
     * Remove membro da equipe (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Busca usuários disponíveis para alocação no projeto
     *
     * @param int $projetoId
     * @return array
     */
    public function getUsuariosDisponiveis($projetoId)
    {
        $sql = "SELECT u.id, u.nome, u.email, u.perfil, u.foto
                FROM usuarios u
                WHERE u.ativo = TRUE
                AND u.id NOT IN (
                    SELECT usuario_id 
                    FROM {$this->table} 
                    WHERE projeto_id = :projeto_id 
                    AND ativo = TRUE
                )
                ORDER BY u.nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projetoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcula estatísticas da equipe do projeto
     *
     * @param int $projetoId
     * @return array
     */
    public function getEstatisticas($projetoId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_membros,
                    SUM(CASE WHEN disponivel = TRUE THEN 1 ELSE 0 END) as membros_disponiveis,
                    SUM(horas_previstas) as total_horas_previstas,
                    SUM(custo_hora * horas_previstas) as custo_total_previsto,
                    AVG(avaliacao_desempenho) as media_avaliacao
                FROM {$this->table}
                WHERE projeto_id = :projeto_id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projetoId]);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Buscar total executado
        $sqlExec = "SELECT 
                        COALESCE(SUM(horas), 0) as total_horas_executadas,
                        COALESCE(SUM(valor), 0) as total_valor_executado
                    FROM projeto_execucao
                    WHERE projeto_id = :projeto_id 
                    AND ativo = TRUE";

        $stmtExec = $this->db->prepare($sqlExec);
        $stmtExec->execute(['projeto_id' => $projetoId]);
        $exec = $stmtExec->fetch(PDO::FETCH_ASSOC);

        return array_merge($stats, $exec);
    }

    /**
     * Busca papéis utilizados no sistema
     *
     * @return array
     */
    public function getPapeisDisponiveis()
    {
        return [
            'gerente' => 'Gerente de Projeto',
            'coordenador' => 'Coordenador',
            'analista' => 'Analista',
            'desenvolvedor' => 'Desenvolvedor',
            'designer' => 'Designer',
            'consultor' => 'Consultor',
            'especialista' => 'Especialista Técnico',
            'suporte' => 'Suporte',
            'qa' => 'Analista de Qualidade',
            'devops' => 'DevOps',
            'outro' => 'Outro'
        ];
    }
}
