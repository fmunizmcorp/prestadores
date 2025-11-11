<?php

namespace App\Models;

use App\Database;
use PDO;

class Atividade extends BaseModel
{
    protected $table = 'atividades';
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todas as atividades com filtros e paginação
     */
    public function all($filtros = [], $page = 1, $limit = 20)
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT 
            a.*,
            p.nome as projeto_nome,
            p.codigo as codigo_projeto,
            p.status as projeto_status,
            s.nome as servico_nome,
            s.categoria as servico_categoria,
            u.nome as responsavel_nome,
            u.email as responsavel_email,
            ep.nome_fantasia as empresa_prestadora_nome,
            et.nome_fantasia as empresa_tomadora_nome,
            uc.nome as criado_por_nome,
            pe.nome as etapa_nome,
            COUNT(DISTINCT ap.id) as total_profissionais,
            COUNT(DISTINCT ac.id) as total_comentarios,
            COUNT(DISTINCT ad.id) as total_documentos,
            DATEDIFF(a.data_fim_prevista, CURDATE()) as dias_restantes
        FROM {$this->table} a
        LEFT JOIN projetos p ON a.projeto_id = p.id
        LEFT JOIN servicos s ON a.servico_id = s.id
        LEFT JOIN usuarios u ON a.responsavel_id = u.id
        LEFT JOIN empresas_prestadoras ep ON a.empresa_prestadora_id = ep.id
        LEFT JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
        LEFT JOIN usuarios uc ON a.created_by = uc.id
        LEFT JOIN projeto_etapas pe ON a.etapa_id = pe.id
        LEFT JOIN atividade_profissionais ap ON a.id = ap.atividade_id AND ap.status = 'ativo'
        LEFT JOIN atividades_comentarios ac ON a.id = ac.atividade_id AND ac.deleted_at IS NULL
        LEFT JOIN atividades_documentos ad ON a.id = ad.atividade_id AND ad.deleted_at IS NULL
        WHERE a.deleted_at IS NULL";
        
        // Aplicar filtros
        if (!empty($filtros['projeto_id'])) {
            $sql .= " AND a.projeto_id = :projeto_id";
        }
        
        if (!empty($filtros['status'])) {
            if (is_array($filtros['status'])) {
                $placeholders = implode(',', array_fill(0, count($filtros['status']), '?'));
                $sql .= " AND a.status IN ($placeholders)";
            } else {
                $sql .= " AND a.status = :status";
            }
        }
        
        if (!empty($filtros['tipo_atividade'])) {
            $sql .= " AND a.tipo_atividade = :tipo_atividade";
        }
        
        if (!empty($filtros['servico_id'])) {
            $sql .= " AND a.servico_id = :servico_id";
        }
        
        if (!empty($filtros['responsavel_id'])) {
            $sql .= " AND a.responsavel_id = :responsavel_id";
        }
        
        if (!empty($filtros['empresa_prestadora_id'])) {
            $sql .= " AND a.empresa_prestadora_id = :empresa_prestadora_id";
        }
        
        if (!empty($filtros['etapa_id'])) {
            $sql .= " AND a.etapa_id = :etapa_id";
        }
        
        if (!empty($filtros['prioridade'])) {
            $sql .= " AND a.prioridade = :prioridade";
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND a.data_inicio >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND a.data_fim_prevista <= :data_fim";
        }
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (a.titulo LIKE :search OR a.descricao LIKE :search OR a.codigo LIKE :search)";
        }
        
        $sql .= " GROUP BY a.id";
        
        // Ordenação
        $orderBy = $filtros['order_by'] ?? 'a.created_at';
        $orderDir = $filtros['order_dir'] ?? 'DESC';
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        
        // Paginação
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind dos parâmetros
        if (!empty($filtros['projeto_id'])) {
            $stmt->bindValue(':projeto_id', $filtros['projeto_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['status']) && !is_array($filtros['status'])) {
            $stmt->bindValue(':status', $filtros['status']);
        }
        if (!empty($filtros['tipo_atividade'])) {
            $stmt->bindValue(':tipo_atividade', $filtros['tipo_atividade']);
        }
        if (!empty($filtros['servico_id'])) {
            $stmt->bindValue(':servico_id', $filtros['servico_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['responsavel_id'])) {
            $stmt->bindValue(':responsavel_id', $filtros['responsavel_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['empresa_prestadora_id'])) {
            $stmt->bindValue(':empresa_prestadora_id', $filtros['empresa_prestadora_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['etapa_id'])) {
            $stmt->bindValue(':etapa_id', $filtros['etapa_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['prioridade'])) {
            $stmt->bindValue(':prioridade', $filtros['prioridade']);
        }
        if (!empty($filtros['data_inicio'])) {
            $stmt->bindValue(':data_inicio', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $stmt->bindValue(':data_fim', $filtros['data_fim']);
        }
        if (!empty($filtros['search'])) {
            $searchTerm = '%' . $filtros['search'] . '%';
            $stmt->bindValue(':search', $searchTerm);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Atividade::all error: " . $e->getMessage());
            // Fallback simples se der erro
            try {
                $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', ($page - 1) * $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e2) {
                error_log("Atividade::all fallback error: " . $e2->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Contar total de atividades com filtros
     */
    public function count($filtros = [])
    {
        $sql = "SELECT COUNT(DISTINCT a.id) as total
        FROM {$this->table} a
        WHERE a.deleted_at IS NULL";
        
        if (!empty($filtros['projeto_id'])) {
            $sql .= " AND a.projeto_id = :projeto_id";
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND a.status = :status";
        }
        if (!empty($filtros['tipo_atividade'])) {
            $sql .= " AND a.tipo_atividade = :tipo_atividade";
        }
        if (!empty($filtros['responsavel_id'])) {
            $sql .= " AND a.responsavel_id = :responsavel_id";
        }
        if (!empty($filtros['search'])) {
            $sql .= " AND (a.titulo LIKE :search OR a.descricao LIKE :search)";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($filtros['projeto_id'])) {
            $stmt->bindValue(':projeto_id', $filtros['projeto_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['status'])) {
            $stmt->bindValue(':status', $filtros['status']);
        }
        if (!empty($filtros['tipo_atividade'])) {
            $stmt->bindValue(':tipo_atividade', $filtros['tipo_atividade']);
        }
        if (!empty($filtros['responsavel_id'])) {
            $stmt->bindValue(':responsavel_id', $filtros['responsavel_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['search'])) {
            $searchTerm = '%' . $filtros['search'] . '%';
            $stmt->bindValue(':search', $searchTerm);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Buscar atividade por ID com todos os relacionamentos
     */
    public function findById($id)
    {
        $sql = "SELECT 
            a.*,
            p.nome as projeto_nome,
            p.codigo as codigo_projeto,
            p.status as projeto_status,
            p.empresa_tomadora_id,
            s.nome as servico_nome,
            s.categoria as servico_categoria,
            s.unidade_medida,
            u.nome as responsavel_nome,
            u.email as responsavel_email,
            u.telefone as responsavel_telefone,
            ep.nome_fantasia as empresa_prestadora_nome,
            ep.razao_social as empresa_prestadora_razao,
            et.nome_fantasia as empresa_tomadora_nome,
            et.razao_social as empresa_tomadora_razao,
            uc.nome as criado_por_nome,
            pe.nome as etapa_nome,
            pe.ordem as etapa_ordem,
            COUNT(DISTINCT ap.id) as total_profissionais,
            COUNT(DISTINCT ac.id) as total_comentarios,
            COUNT(DISTINCT ad.id) as total_documentos,
            SUM(ap.horas_realizadas) as total_horas_realizadas,
            SUM(ap.valor_pago) as total_valor_pago,
            DATEDIFF(a.data_fim_prevista, CURDATE()) as dias_restantes,
            DATEDIFF(CURDATE(), a.data_inicio_planejada) as dias_decorridos
        FROM {$this->table} a
        LEFT JOIN projetos p ON a.projeto_id = p.id
        LEFT JOIN servicos s ON a.servico_id = s.id
        LEFT JOIN usuarios u ON a.responsavel_id = u.id
        LEFT JOIN empresas_prestadoras ep ON a.empresa_prestadora_id = ep.id
        LEFT JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
        LEFT JOIN usuarios uc ON a.created_by = uc.id
        LEFT JOIN projeto_etapas pe ON a.etapa_id = pe.id
        LEFT JOIN atividade_profissionais ap ON a.id = ap.atividade_id
        LEFT JOIN atividades_comentarios ac ON a.id = ac.atividade_id AND ac.deleted_at IS NULL
        LEFT JOIN atividades_documentos ad ON a.id = ad.atividade_id AND ad.deleted_at IS NULL
        WHERE a.id = :id AND a.deleted_at IS NULL
        GROUP BY a.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar nova atividade
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
            projeto_id, etapa_id, codigo, titulo, descricao,
            tipo_atividade, local_execucao, requer_presenca_fisica,
            data_inicio_planejada, data_fim_planejada,
            horas_planejadas, custo_planejado,
            responsavel_id, empresa_prestadora_id, servico_id,
            tipo_remuneracao, valor_base, valor_bonificacao,
            status, prioridade, progresso,
            permite_candidatura, max_profissionais, horas_dia_limite,
            observacoes, created_by, created_at
        ) VALUES (
            :projeto_id, :etapa_id, :codigo, :titulo, :descricao,
            :tipo_atividade, :local_execucao, :requer_presenca_fisica,
            :data_inicio_planejada, :data_fim_planejada,
            :horas_planejadas, :custo_planejado,
            :responsavel_id, :empresa_prestadora_id, :servico_id,
            :tipo_remuneracao, :valor_base, :valor_bonificacao,
            :status, :prioridade, :progresso,
            :permite_candidatura, :max_profissionais, :horas_dia_limite,
            :observacoes, :created_by, NOW()
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
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
            tipo_atividade = :tipo_atividade,
            local_execucao = :local_execucao,
            data_inicio_planejada = :data_inicio_planejada,
            data_fim_planejada = :data_fim_planejada,
            horas_planejadas = :horas_planejadas,
            custo_planejado = :custo_planejado,
            responsavel_id = :responsavel_id,
            empresa_prestadora_id = :empresa_prestadora_id,
            servico_id = :servico_id,
            tipo_remuneracao = :tipo_remuneracao,
            valor_base = :valor_base,
            valor_bonificacao = :valor_bonificacao,
            prioridade = :prioridade,
            progresso = :progresso,
            permite_candidatura = :permite_candidatura,
            max_profissionais = :max_profissionais,
            horas_dia_limite = :horas_dia_limite,
            observacoes = :observacoes,
            updated_at = NOW()
        WHERE id = :id AND deleted_at IS NULL";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Soft delete
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Buscar profissionais alocados na atividade
     */
    public function getProfissionais($atividadeId)
    {
        $sql = "SELECT 
            ap.*,
            u.nome as usuario_nome,
            u.email as usuario_email,
            u.telefone as usuario_telefone,
            ep.nome_fantasia as empresa_nome,
            ua.nome as aprovado_por_nome
        FROM atividade_profissionais ap
        LEFT JOIN usuarios u ON ap.usuario_id = u.id
        LEFT JOIN empresas_prestadoras ep ON ap.empresa_prestadora_id = ep.id
        LEFT JOIN usuarios ua ON ap.aprovado_por = ua.id
        WHERE ap.atividade_id = :atividade_id
        ORDER BY ap.tipo_atribuicao ASC, ap.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar custos da atividade
     */
    public function getCustos($atividadeId)
    {
        $sql = "SELECT 
            af.*,
            u.nome as usuario_nome,
            s.nome as servico_nome
        FROM atividade_financeiro af
        LEFT JOIN usuarios u ON af.usuario_id = u.id
        LEFT JOIN servicos s ON af.servico_id = s.id
        WHERE af.atividade_id = :atividade_id
        ORDER BY af.data_lancamento DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar horas registradas na atividade
     */
    public function getHorasRegistradas($atividadeId)
    {
        $sql = "SELECT 
            pr.*,
            u.nome as usuario_nome
        FROM ponto_registros pr
        LEFT JOIN usuarios u ON pr.usuario_id = u.id
        WHERE pr.atividade_id = :atividade_id
        ORDER BY pr.data_hora_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar comentários da atividade
     */
    public function getComentarios($atividadeId)
    {
        $sql = "SELECT 
            ac.*,
            u.nome as usuario_nome,
            u.email as usuario_email
        FROM atividades_comentarios ac
        LEFT JOIN usuarios u ON ac.usuario_id = u.id
        WHERE ac.atividade_id = :atividade_id AND ac.deleted_at IS NULL
        ORDER BY ac.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar documentos da atividade
     */
    public function getDocumentos($atividadeId)
    {
        $sql = "SELECT 
            ad.*,
            u.nome as enviado_por_nome
        FROM atividades_documentos ad
        LEFT JOIN usuarios u ON ad.enviado_por = u.id
        WHERE ad.atividade_id = :atividade_id AND ad.deleted_at IS NULL
        ORDER BY ad.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar recursos necessários
     */
    public function getRecursos($atividadeId)
    {
        $sql = "SELECT * FROM atividade_recursos 
        WHERE atividade_id = :atividade_id
        ORDER BY tipo_recurso ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar certificações necessárias
     */
    public function getCertificacoesNecessarias($atividadeId)
    {
        $sql = "SELECT * FROM atividade_certificacoes 
        WHERE atividade_id = :atividade_id
        ORDER BY obrigatoria DESC, nome_certificacao ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar estatísticas gerais
     */
    public function getEstatisticasGerais($filtros = [])
    {
        $sql = "SELECT
            COUNT(*) as total_atividades,
            SUM(CASE WHEN status = 'aguardando_recursos' THEN 1 ELSE 0 END) as aguardando_recursos,
            SUM(CASE WHEN status = 'disponivel' THEN 1 ELSE 0 END) as disponiveis,
            SUM(CASE WHEN status = 'em_execucao' THEN 1 ELSE 0 END) as em_execucao,
            SUM(CASE WHEN status = 'pausada' THEN 1 ELSE 0 END) as pausadas,
            SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as concluidas,
            SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
            SUM(CASE WHEN status IN ('disponivel', 'em_execucao') AND CURDATE() > data_fim_planejada THEN 1 ELSE 0 END) as atrasadas,
            SUM(horas_planejadas) as total_horas_planejadas,
            SUM(horas_realizadas) as total_horas_realizadas,
            SUM(custo_planejado) as total_custo_planejado,
            SUM(custo_realizado) as total_custo_realizado
        FROM {$this->table}
        WHERE deleted_at IS NULL";
        
        if (!empty($filtros['projeto_id'])) {
            $sql .= " AND projeto_id = :projeto_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($filtros['projeto_id'])) {
            $stmt->bindValue(':projeto_id', $filtros['projeto_id'], PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualizar custos realizados
     */
    public function atualizarCustosRealizados($atividadeId)
    {
        $sql = "UPDATE {$this->table} a
        SET 
            horas_realizadas = (
                SELECT COALESCE(SUM(duracao_paga_minutos / 60.0), 0)
                FROM ponto_registros
                WHERE atividade_id = a.id AND status = 'finalizado'
            ),
            custo_realizado = (
                SELECT COALESCE(SUM(valor_total), 0)
                FROM atividade_financeiro
                WHERE atividade_id = a.id
            ),
            updated_at = NOW()
        WHERE a.id = :atividade_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Atualizar progresso da atividade
     */
    public function atualizarProgresso($atividadeId, $progresso)
    {
        $sql = "UPDATE {$this->table} 
        SET progresso = :progresso, updated_at = NOW()
        WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $atividadeId, PDO::PARAM_INT);
        $stmt->bindValue(':progresso', $progresso, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Atualizar status da atividade
     */
    public function atualizarStatus($atividadeId, $novoStatus, $usuarioId = null)
    {
        $sql = "UPDATE {$this->table} 
        SET status = :status, updated_at = NOW()";
        
        if ($novoStatus === 'em_execucao' && $usuarioId) {
            $sql .= ", data_inicio_real = NOW()";
        }
        
        if ($novoStatus === 'concluida' && $usuarioId) {
            $sql .= ", data_fim_real = NOW(), progresso = 100";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $atividadeId, PDO::PARAM_INT);
        $stmt->bindValue(':status', $novoStatus);
        
        return $stmt->execute();
    }
    
    /**
     * Buscar atividades disponíveis para candidatura
     */
    public function getAtividadesDisponiveis($usuarioId = null)
    {
        $sql = "SELECT 
            a.*,
            p.nome as projeto_nome,
            p.codigo as codigo_projeto,
            s.nome as servico_nome,
            ep.nome_fantasia as empresa_prestadora_nome,
            COUNT(DISTINCT ap.id) as total_candidatos,
            COUNT(DISTINCT ap2.id) as total_aprovados
        FROM {$this->table} a
        LEFT JOIN projetos p ON a.projeto_id = p.id
        LEFT JOIN servicos s ON a.servico_id = s.id
        LEFT JOIN empresas_prestadoras ep ON a.empresa_prestadora_id = ep.id
        LEFT JOIN atividade_profissionais ap ON a.id = ap.atividade_id AND ap.tipo_atribuicao = 'candidato'
        LEFT JOIN atividade_profissionais ap2 ON a.id = ap2.atividade_id AND ap2.tipo_atribuicao = 'aprovado'
        WHERE a.deleted_at IS NULL
        AND a.status = 'disponivel'
        AND a.permite_candidatura = 1";
        
        if ($usuarioId) {
            $sql .= " AND NOT EXISTS (
                SELECT 1 FROM atividade_profissionais ap3
                WHERE ap3.atividade_id = a.id 
                AND ap3.usuario_id = :usuario_id
            )";
        }
        
        $sql .= " GROUP BY a.id
        HAVING total_aprovados < a.max_profissionais
        ORDER BY a.data_inicio_planejada ASC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($usuarioId) {
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Candidatar-se a uma atividade
     */
    public function candidatar($atividadeId, $usuarioId, $empresaPrestadoraId)
    {
        $sql = "INSERT INTO atividade_profissionais (
            atividade_id, usuario_id, empresa_prestadora_id,
            tipo_atribuicao, data_candidatura, status, created_at
        ) VALUES (
            :atividade_id, :usuario_id, :empresa_prestadora_id,
            'candidato', NOW(), 'ativo', NOW()
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':atividade_id', $atividadeId, PDO::PARAM_INT);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_prestadora_id', $empresaPrestadoraId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Aprovar candidatura
     */
    public function aprovarCandidatura($atividadeProfissionalId, $aprovadorId, $horasAlocadas = null)
    {
        $sql = "UPDATE atividade_profissionais 
        SET tipo_atribuicao = 'aprovado',
            data_aprovacao = NOW(),
            aprovado_por = :aprovado_por,
            horas_alocadas = :horas_alocadas
        WHERE id = :id AND tipo_atribuicao = 'candidato'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $atividadeProfissionalId, PDO::PARAM_INT);
        $stmt->bindValue(':aprovado_por', $aprovadorId, PDO::PARAM_INT);
        $stmt->bindValue(':horas_alocadas', $horasAlocadas, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Rejeitar candidatura
     */
    public function rejeitarCandidatura($atividadeProfissionalId, $aprovadorId)
    {
        $sql = "UPDATE atividade_profissionais 
        SET status = 'inativo',
            aprovado_por = :aprovado_por
        WHERE id = :id AND tipo_atribuicao = 'candidato'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $atividadeProfissionalId, PDO::PARAM_INT);
        $stmt->bindValue(':aprovado_por', $aprovadorId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Contar total de atividades (compatibilidade com controller)
     */
    public function countTotal()
    {
        return $this->count([]);
    }
    
    /**
     * Contar atividades por status
     */
    public function countPorStatus($status)
    {
        $sql = "SELECT COUNT(*) as total 
        FROM {$this->table} 
        WHERE status = :status AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Alterar status da atividade (compatibilidade com controller)
     */
    public function alterarStatus($id, $novoStatus, $usuarioId = null)
    {
        return $this->atualizarStatus($id, $novoStatus, $usuarioId);
    }
}
