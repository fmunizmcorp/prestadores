<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException;

/**
 * Model Projeto
 * Gerenciamento completo de projetos
 */
class Projeto {
    
    private $db;
    private $table = 'projetos';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Buscar todos os projetos com filtros e paginação
     * 
     * @param array $filtros
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function all($filtros = [], $page = 1, $limit = 25) {
        $sql = "SELECT 
                    p.*,
                    et.nome_fantasia as tomadora_nome,
                    c.numero_contrato,
                    pc.nome as categoria_nome,
                    pc.cor as categoria_cor,
                    u1.nome as gerente_nome,
                    u2.nome as criador_nome,
                    (SELECT COUNT(*) FROM projeto_etapas pe WHERE pe.projeto_id = p.id AND pe.ativo = 1) as total_etapas,
                    (SELECT COUNT(*) FROM projeto_etapas pe WHERE pe.projeto_id = p.id AND pe.status = 'concluida' AND pe.ativo = 1) as etapas_concluidas,
                    (SELECT COUNT(*) FROM projeto_equipe peq WHERE peq.projeto_id = p.id AND peq.ativo = 1) as total_equipe,
                    DATEDIFF(p.data_fim_prevista, CURDATE()) as dias_restantes
                FROM {$this->table} p
                INNER JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
                LEFT JOIN contratos c ON p.contrato_id = c.id
                LEFT JOIN projeto_categorias pc ON p.categoria_id = pc.id
                LEFT JOIN usuarios u1 ON p.gerente_id = u1.id
                LEFT JOIN usuarios u2 ON p.created_by = u2.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtros
        if (isset($filtros['status']) && $filtros['status'] !== '') {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filtros['status'];
        }
        
        if (isset($filtros['prioridade']) && $filtros['prioridade'] !== '') {
            $sql .= " AND p.prioridade = :prioridade";
            $params[':prioridade'] = $filtros['prioridade'];
        }
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (p.codigo LIKE :search 
                         OR p.nome LIKE :search 
                         OR p.descricao LIKE :search
                         OR et.nome_fantasia LIKE :search)";
            $params[':search'] = '%' . $filtros['search'] . '%';
        }
        
        if (!empty($filtros['empresa_tomadora_id'])) {
            $sql .= " AND p.empresa_tomadora_id = :empresa_tomadora_id";
            $params[':empresa_tomadora_id'] = $filtros['empresa_tomadora_id'];
        }
        
        if (!empty($filtros['contrato_id'])) {
            $sql .= " AND p.contrato_id = :contrato_id";
            $params[':contrato_id'] = $filtros['contrato_id'];
        }
        
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $filtros['categoria_id'];
        }
        
        if (!empty($filtros['gerente_id'])) {
            $sql .= " AND p.gerente_id = :gerente_id";
            $params[':gerente_id'] = $filtros['gerente_id'];
        }
        
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $sql .= " AND p.ativo = :ativo";
            $params[':ativo'] = $filtros['ativo'];
        }
        
        // Filtro de data
        if (!empty($filtros['data_inicio_de'])) {
            $sql .= " AND p.data_inicio >= :data_inicio_de";
            $params[':data_inicio_de'] = $filtros['data_inicio_de'];
        }
        
        if (!empty($filtros['data_inicio_ate'])) {
            $sql .= " AND p.data_inicio <= :data_inicio_ate";
            $params[':data_inicio_ate'] = $filtros['data_inicio_ate'];
        }
        
        // Ordenação
        $orderBy = $filtros['order_by'] ?? 'p.data_inicio';
        $orderDir = $filtros['order_dir'] ?? 'DESC';
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        
        // Paginação
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Contar projetos com filtros
     * 
     * @param array $filtros
     * @return int
     */
    public function count($filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} p
                INNER JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filtros['status']) && $filtros['status'] !== '') {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filtros['status'];
        }
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (p.codigo LIKE :search OR p.nome LIKE :search OR et.nome_fantasia LIKE :search)";
            $params[':search'] = '%' . $filtros['search'] . '%';
        }
        
        if (!empty($filtros['empresa_tomadora_id'])) {
            $sql .= " AND p.empresa_tomadora_id = :empresa_tomadora_id";
            $params[':empresa_tomadora_id'] = $filtros['empresa_tomadora_id'];
        }
        
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $sql .= " AND p.ativo = :ativo";
            $params[':ativo'] = $filtros['ativo'];
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar projeto por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT 
                    p.*,
                    et.nome_fantasia as tomadora_nome,
                    et.razao_social as tomadora_razao_social,
                    c.numero_contrato,
                    c.valor_total as contrato_valor,
                    pc.nome as categoria_nome,
                    pc.cor as categoria_cor,
                    pc.icone as categoria_icone,
                    u1.nome as gerente_nome,
                    u1.email as gerente_email,
                    u2.nome as aprovador_nome,
                    u3.nome as criador_nome,
                    u4.nome as atualizador_nome
                FROM {$this->table} p
                INNER JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
                LEFT JOIN contratos c ON p.contrato_id = c.id
                LEFT JOIN projeto_categorias pc ON p.categoria_id = pc.id
                LEFT JOIN usuarios u1 ON p.gerente_id = u1.id
                LEFT JOIN usuarios u2 ON p.aprovador_id = u2.id
                LEFT JOIN usuarios u3 ON p.created_by = u3.id
                LEFT JOIN usuarios u4 ON p.updated_by = u4.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Buscar por código
     * 
     * @param string $codigo
     * @param int|null $exceptId
     * @return array|null
     */
    public function findByCodigo($codigo, $exceptId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = :codigo";
        
        if ($exceptId) {
            $sql .= " AND id != :except_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        
        if ($exceptId) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Criar projeto
     * 
     * @param array $data
     * @return int
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
                    codigo, nome, descricao, categoria_id,
                    empresa_tomadora_id, contrato_id, projeto_pai_id,
                    data_inicio, data_fim_prevista, duracao_prevista_dias,
                    status, prioridade, percentual_conclusao,
                    orcamento_total, margem_percentual,
                    horas_estimadas,
                    gerente_id, aprovador_id,
                    permite_horas_extras, exige_aprovacao_etapas, notificar_atrasos, prazo_alerta_dias,
                    logo, link_drive, link_trello, link_jira,
                    observacoes,
                    ativo, created_by, created_at
                ) VALUES (
                    :codigo, :nome, :descricao, :categoria_id,
                    :empresa_tomadora_id, :contrato_id, :projeto_pai_id,
                    :data_inicio, :data_fim_prevista, :duracao_prevista_dias,
                    :status, :prioridade, :percentual_conclusao,
                    :orcamento_total, :margem_percentual,
                    :horas_estimadas,
                    :gerente_id, :aprovador_id,
                    :permite_horas_extras, :exige_aprovacao_etapas, :notificar_atrasos, :prazo_alerta_dias,
                    :logo, :link_drive, :link_trello, :link_jira,
                    :observacoes,
                    :ativo, :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':categoria_id', $data['categoria_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_tomadora_id', $data['empresa_tomadora_id'], PDO::PARAM_INT);
        $stmt->bindValue(':contrato_id', $data['contrato_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':projeto_pai_id', $data['projeto_pai_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio', $data['data_inicio']);
        $stmt->bindValue(':data_fim_prevista', $data['data_fim_prevista'] ?? null);
        $stmt->bindValue(':duracao_prevista_dias', $data['duracao_prevista_dias'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':status', $data['status'] ?? 'planejamento');
        $stmt->bindValue(':prioridade', $data['prioridade'] ?? 'media');
        $stmt->bindValue(':percentual_conclusao', $data['percentual_conclusao'] ?? 0);
        $stmt->bindValue(':orcamento_total', $data['orcamento_total'] ?? 0);
        $stmt->bindValue(':margem_percentual', $data['margem_percentual'] ?? null);
        $stmt->bindValue(':horas_estimadas', $data['horas_estimadas'] ?? 0);
        $stmt->bindValue(':gerente_id', $data['gerente_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':aprovador_id', $data['aprovador_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':permite_horas_extras', $data['permite_horas_extras'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':exige_aprovacao_etapas', $data['exige_aprovacao_etapas'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':notificar_atrasos', $data['notificar_atrasos'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':prazo_alerta_dias', $data['prazo_alerta_dias'] ?? 7, PDO::PARAM_INT);
        $stmt->bindValue(':logo', $data['logo'] ?? null);
        $stmt->bindValue(':link_drive', $data['link_drive'] ?? null);
        $stmt->bindValue(':link_trello', $data['link_trello'] ?? null);
        $stmt->bindValue(':link_jira', $data['link_jira'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar projeto
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET
                    codigo = :codigo,
                    nome = :nome,
                    descricao = :descricao,
                    categoria_id = :categoria_id,
                    empresa_tomadora_id = :empresa_tomadora_id,
                    contrato_id = :contrato_id,
                    projeto_pai_id = :projeto_pai_id,
                    data_inicio = :data_inicio,
                    data_fim_prevista = :data_fim_prevista,
                    data_fim_real = :data_fim_real,
                    duracao_prevista_dias = :duracao_prevista_dias,
                    status = :status,
                    prioridade = :prioridade,
                    percentual_conclusao = :percentual_conclusao,
                    orcamento_total = :orcamento_total,
                    margem_percentual = :margem_percentual,
                    horas_estimadas = :horas_estimadas,
                    gerente_id = :gerente_id,
                    aprovador_id = :aprovador_id,
                    permite_horas_extras = :permite_horas_extras,
                    exige_aprovacao_etapas = :exige_aprovacao_etapas,
                    notificar_atrasos = :notificar_atrasos,
                    prazo_alerta_dias = :prazo_alerta_dias,
                    logo = :logo,
                    link_drive = :link_drive,
                    link_trello = :link_trello,
                    link_jira = :link_jira,
                    observacoes = :observacoes,
                    ativo = :ativo,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':categoria_id', $data['categoria_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_tomadora_id', $data['empresa_tomadora_id'], PDO::PARAM_INT);
        $stmt->bindValue(':contrato_id', $data['contrato_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':projeto_pai_id', $data['projeto_pai_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio', $data['data_inicio']);
        $stmt->bindValue(':data_fim_prevista', $data['data_fim_prevista'] ?? null);
        $stmt->bindValue(':data_fim_real', $data['data_fim_real'] ?? null);
        $stmt->bindValue(':duracao_prevista_dias', $data['duracao_prevista_dias'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':status', $data['status'] ?? 'planejamento');
        $stmt->bindValue(':prioridade', $data['prioridade'] ?? 'media');
        $stmt->bindValue(':percentual_conclusao', $data['percentual_conclusao'] ?? 0);
        $stmt->bindValue(':orcamento_total', $data['orcamento_total'] ?? 0);
        $stmt->bindValue(':margem_percentual', $data['margem_percentual'] ?? null);
        $stmt->bindValue(':horas_estimadas', $data['horas_estimadas'] ?? 0);
        $stmt->bindValue(':gerente_id', $data['gerente_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':aprovador_id', $data['aprovador_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':permite_horas_extras', $data['permite_horas_extras'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':exige_aprovacao_etapas', $data['exige_aprovacao_etapas'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':notificar_atrasos', $data['notificar_atrasos'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':prazo_alerta_dias', $data['prazo_alerta_dias'] ?? 7, PDO::PARAM_INT);
        $stmt->bindValue(':logo', $data['logo'] ?? null);
        $stmt->bindValue(':link_drive', $data['link_drive'] ?? null);
        $stmt->bindValue(':link_trello', $data['link_trello'] ?? null);
        $stmt->bindValue(':link_jira', $data['link_jira'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $data['updated_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Alterar status do projeto
     * 
     * @param int $id
     * @param string $novoStatus
     * @param string|null $motivo
     * @return bool
     */
    public function alterarStatus($id, $novoStatus, $motivo = null) {
        $validStatuses = ['planejamento', 'aprovacao', 'em_andamento', 'pausado', 'cancelado', 'concluido', 'arquivado'];
        
        if (!in_array($novoStatus, $validStatuses)) {
            throw new \Exception('Status inválido');
        }
        
        $sql = "UPDATE {$this->table} SET 
                status = :status,
                updated_by = :updated_by,
                updated_at = NOW()";
        
        if ($novoStatus === 'cancelado' && $motivo) {
            $sql .= ", motivo_cancelamento = :motivo";
        }
        
        if ($novoStatus === 'concluido') {
            $sql .= ", data_fim_real = CURDATE()";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':status', $novoStatus);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        if ($novoStatus === 'cancelado' && $motivo) {
            $stmt->bindValue(':motivo', $motivo);
        }
        
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
                    status = 'arquivado',
                    updated_by = :updated_by,
                    updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Validar unicidade do código
     * 
     * @param string $codigo
     * @param int|null $exceptId
     * @return bool
     */
    public function validateUniqueCodigo($codigo, $exceptId = null) {
        $existing = $this->findByCodigo($codigo, $exceptId);
        return $existing === null;
    }
    
    /**
     * Contar total de projetos
     * 
     * @return int
     */
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Contar por status
     * 
     * @param string $status
     * @return int
     */
    public function countPorStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar projetos atrasados
     * 
     * @return array
     */
    public function getAtrasados() {
        $sql = "SELECT p.*, et.nome_fantasia as tomadora_nome,
                       DATEDIFF(CURDATE(), p.data_fim_prevista) as dias_atraso
                FROM {$this->table} p
                INNER JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
                WHERE p.status IN ('em_andamento', 'pausado')
                  AND p.data_fim_prevista < CURDATE()
                  AND p.ativo = 1
                ORDER BY p.data_fim_prevista ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar projetos com prazo próximo
     * 
     * @param int $dias
     * @return array
     */
    public function getProximosPrazo($dias = 7) {
        $sql = "SELECT p.*, et.nome_fantasia as tomadora_nome,
                       DATEDIFF(p.data_fim_prevista, CURDATE()) as dias_restantes
                FROM {$this->table} p
                INNER JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
                WHERE p.status = 'em_andamento'
                  AND p.data_fim_prevista IS NOT NULL
                  AND DATEDIFF(p.data_fim_prevista, CURDATE()) <= :dias
                  AND DATEDIFF(p.data_fim_prevista, CURDATE()) >= 0
                  AND p.ativo = 1
                ORDER BY p.data_fim_prevista ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar estatísticas gerais
     * 
     * @return array
     */
    public function getEstatisticasGerais() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
                    SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
                    SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                    SUM(CASE WHEN status = 'pausado' THEN 1 ELSE 0 END) as pausados,
                    SUM(orcamento_total) as orcamento_total,
                    SUM(custo_realizado) as custo_total,
                    AVG(percentual_conclusao) as media_conclusao,
                    AVG(nota_final) as media_avaliacao
                FROM {$this->table}
                WHERE ativo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Buscar dashboard do projeto (estatísticas completas)
     * 
     * @param int $id
     * @return array
     */
    public function getDashboard($id) {
        $projeto = $this->findById($id);
        if (!$projeto) {
            return null;
        }
        
        // Estatísticas de etapas
        $sql_etapas = "SELECT 
                           COUNT(*) as total,
                           SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as concluidas,
                           SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
                           SUM(CASE WHEN status = 'atrasada' THEN 1 ELSE 0 END) as atrasadas,
                           SUM(horas_estimadas) as horas_estimadas,
                           SUM(horas_executadas) as horas_executadas
                       FROM projeto_etapas 
                       WHERE projeto_id = :id AND ativo = 1";
        
        $stmt = $this->db->prepare($sql_etapas);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $etapas_stats = $stmt->fetch();
        
        // Estatísticas de equipe
        $sql_equipe = "SELECT 
                           COUNT(*) as total,
                           SUM(CASE WHEN status = 'em_atividade' THEN 1 ELSE 0 END) as ativos
                       FROM projeto_equipe 
                       WHERE projeto_id = :id AND ativo = 1";
        
        $stmt = $this->db->prepare($sql_equipe);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $equipe_stats = $stmt->fetch();
        
        // Estatísticas de orçamento
        $sql_orcamento = "SELECT 
                              SUM(CASE WHEN tipo = 'receita' THEN valor_planejado ELSE 0 END) as receita_planejada,
                              SUM(CASE WHEN tipo = 'receita' THEN valor_realizado ELSE 0 END) as receita_realizada,
                              SUM(CASE WHEN tipo = 'despesa' THEN valor_planejado ELSE 0 END) as despesa_planejada,
                              SUM(CASE WHEN tipo = 'despesa' THEN valor_realizado ELSE 0 END) as despesa_realizada
                          FROM projeto_orcamento 
                          WHERE projeto_id = :id AND ativo = 1";
        
        $stmt = $this->db->prepare($sql_orcamento);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $orcamento_stats = $stmt->fetch();
        
        return [
            'projeto' => $projeto,
            'etapas' => $etapas_stats,
            'equipe' => $equipe_stats,
            'orcamento' => $orcamento_stats
        ];
    }
}
