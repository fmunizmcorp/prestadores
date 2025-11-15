<?php /* Cache-Buster: 2025-11-15 13:58:50 */

namespace App\Models;

use App\Database;
use PDO;

// Updated: 2025-11-11 06:42 - Removed BaseModel inheritance
class Projeto
{
    protected $table = 'projetos';
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os projetos com filtros e paginação
     */
    public function all($filtros = [], $page = 1, $limit = 20)
    {
        try {
            // Garantir que são integers para operações matemáticas
            $page = (int) $page;
            $limit = (int) $limit;
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT 
                p.*,
                et.razao_social as tomadora_razao,
                et.nome_fantasia as tomadora_nome,
                ep.razao_social as prestadora_razao,
                ep.nome_fantasia as prestadora_nome,
                c.numero_contrato,
                u.nome as gestor_nome,
                u.email as gestor_email,
                pc.nome as categoria_nome,
                uc.nome as criado_por_nome,
                COUNT(DISTINCT pe.id) as total_etapas,
                COUNT(DISTINCT a.id) as total_atividades,
                SUM(CASE WHEN a.status = 'concluida' THEN 1 ELSE 0 END) as atividades_concluidas,
                DATEDIFF(p.data_fim_prevista, CURDATE()) as dias_restantes,
                DATEDIFF(CURDATE(), p.data_inicio) as dias_decorridos
            FROM {$this->table} p
            LEFT JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
            LEFT JOIN empresas_prestadoras ep ON p.empresa_prestadora_id = ep.id
            LEFT JOIN contratos c ON p.contrato_id = c.id
            LEFT JOIN usuarios u ON p.gerente_id = u.id
            LEFT JOIN projeto_categorias pc ON p.categoria_id = pc.id
            LEFT JOIN usuarios uc ON p.created_by = uc.id
            LEFT JOIN projeto_etapas pe ON p.id = pe.projeto_id AND pe.deleted_at IS NULL
            LEFT JOIN atividades a ON p.id = a.projeto_id AND a.deleted_at IS NULL
            WHERE p.deleted_at IS NULL";
            
            // Aplicar filtros
            if (!empty($filtros['status'])) {
                $sql .= " AND p.status = :status";
            }
            
            if (!empty($filtros['contrato_id'])) {
                $sql .= " AND p.contrato_id = :contrato_id";
            }
            
            if (!empty($filtros['empresa_tomadora_id'])) {
                $sql .= " AND p.empresa_tomadora_id = :empresa_tomadora_id";
            }
            
            if (!empty($filtros['empresa_prestadora_id'])) {
                $sql .= " AND p.empresa_prestadora_id = :empresa_prestadora_id";
            }
            
            if (!empty($filtros['gestor_projeto_id'])) {
                $sql .= " AND p.gerente_id = :gestor_projeto_id";
            }
            
            if (!empty($filtros['categoria_id'])) {
                $sql .= " AND p.categoria_id = :categoria_id";
            }
            
            if (!empty($filtros['prioridade'])) {
                $sql .= " AND p.prioridade = :prioridade";
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND p.data_inicio >= :data_inicio";
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND p.data_fim_prevista <= :data_fim";
            }
            
            if (!empty($filtros['search'])) {
                // CORRIGIDO: codigo ao invés de codigo_projeto
                $sql .= " AND (p.codigo LIKE :search OR p.nome LIKE :search OR p.descricao LIKE :search)";
            }
            
            $sql .= " GROUP BY p.id";
            
            // Ordenação
            $orderBy = $filtros['order_by'] ?? 'p.created_at';
            $orderDir = $filtros['order_dir'] ?? 'DESC';
            $sql .= " ORDER BY {$orderBy} {$orderDir}";
            
            // Paginação
            $sql .= " LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind dos parâmetros
            if (!empty($filtros['status'])) {
                $stmt->bindValue(':status', $filtros['status']);
            }
            if (!empty($filtros['contrato_id'])) {
                $stmt->bindValue(':contrato_id', $filtros['contrato_id'], PDO::PARAM_INT);
            }
            if (!empty($filtros['empresa_tomadora_id'])) {
                $stmt->bindValue(':empresa_tomadora_id', $filtros['empresa_tomadora_id'], PDO::PARAM_INT);
            }
            if (!empty($filtros['empresa_prestadora_id'])) {
                $stmt->bindValue(':empresa_prestadora_id', $filtros['empresa_prestadora_id'], PDO::PARAM_INT);
            }
            if (!empty($filtros['gestor_projeto_id'])) {
                $stmt->bindValue(':gestor_projeto_id', $filtros['gestor_projeto_id'], PDO::PARAM_INT);
            }
            if (!empty($filtros['categoria_id'])) {
                $stmt->bindValue(':categoria_id', $filtros['categoria_id'], PDO::PARAM_INT);
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
            error_log("Projeto::all error: " . $e->getMessage());
            // Fallback simples se der erro
            try {
                $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL LIMIT :limit OFFSET :offset";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', ($page - 1) * $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e2) {
                error_log("Projeto::all fallback error: " . $e2->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Contar total de projetos com filtros
     */
    public function count($filtros = [])
    {
        $sql = "SELECT COUNT(DISTINCT p.id) as total
        FROM {$this->table} p
        WHERE p.deleted_at IS NULL";
        
        if (!empty($filtros['status'])) {
            $sql .= " AND p.status = :status";
        }
        if (!empty($filtros['contrato_id'])) {
            $sql .= " AND p.contrato_id = :contrato_id";
        }
        if (!empty($filtros['empresa_tomadora_id'])) {
            $sql .= " AND p.empresa_tomadora_id = :empresa_tomadora_id";
        }
        if (!empty($filtros['empresa_prestadora_id'])) {
            $sql .= " AND p.empresa_prestadora_id = :empresa_prestadora_id";
        }
        if (!empty($filtros['gestor_projeto_id'])) {
            $sql .= " AND p.gerente_id = :gestor_projeto_id";
        }
        if (!empty($filtros['search'])) {
            $sql .= " AND (p.codigo_projeto LIKE :search OR p.nome LIKE :search)";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($filtros['status'])) {
            $stmt->bindValue(':status', $filtros['status']);
        }
        if (!empty($filtros['contrato_id'])) {
            $stmt->bindValue(':contrato_id', $filtros['contrato_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['empresa_tomadora_id'])) {
            $stmt->bindValue(':empresa_tomadora_id', $filtros['empresa_tomadora_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['empresa_prestadora_id'])) {
            $stmt->bindValue(':empresa_prestadora_id', $filtros['empresa_prestadora_id'], PDO::PARAM_INT);
        }
        if (!empty($filtros['gestor_projeto_id'])) {
            $stmt->bindValue(':gestor_projeto_id', $filtros['gestor_projeto_id'], PDO::PARAM_INT);
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
     * Buscar projeto por ID com todos os relacionamentos
     */
    public function findById($id)
    {
        $sql = "SELECT 
            p.*,
            et.razao_social as tomadora_razao,
            et.nome_fantasia as tomadora_nome,
            et.email_principal as tomadora_email,
            et.telefone_principal as tomadora_telefone,
            ep.razao_social as prestadora_razao,
            ep.nome_fantasia as prestadora_nome,
            ep.email_principal as prestadora_email,
            ep.telefone_principal as prestadora_telefone,
            c.numero_contrato,
            c.data_inicio as contrato_data_inicio,
            c.data_fim as contrato_data_fim,
            c.valor_total as contrato_valor_total,
            u.nome as gestor_nome,
            u.email as gestor_email,
            u.telefone as gestor_telefone,
            pc.nome as categoria_nome,
            pc.descricao as categoria_descricao,
            uc.nome as criado_por_nome,
            COUNT(DISTINCT pe.id) as total_etapas,
            COUNT(DISTINCT a.id) as total_atividades,
            SUM(CASE WHEN a.status = 'concluida' THEN 1 ELSE 0 END) as atividades_concluidas,
            SUM(CASE WHEN a.status = 'em_execucao' THEN 1 ELSE 0 END) as atividades_em_execucao,
            SUM(CASE WHEN a.status = 'pendente' THEN 1 ELSE 0 END) as atividades_pendentes,
            DATEDIFF(p.data_fim_prevista, CURDATE()) as dias_restantes,
            DATEDIFF(CURDATE(), p.data_inicio) as dias_decorridos
        FROM {$this->table} p
        LEFT JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
        LEFT JOIN empresas_prestadoras ep ON p.empresa_prestadora_id = ep.id
        LEFT JOIN contratos c ON p.contrato_id = c.id
        LEFT JOIN usuarios u ON p.gerente_id = u.id
        LEFT JOIN projeto_categorias pc ON p.categoria_id = pc.id
        LEFT JOIN usuarios uc ON p.created_by = uc.id
        LEFT JOIN projeto_etapas pe ON p.id = pe.projeto_id AND pe.deleted_at IS NULL
        LEFT JOIN atividades a ON p.id = a.projeto_id AND a.deleted_at IS NULL
        WHERE p.id = :id AND p.deleted_at IS NULL
        GROUP BY p.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar novo projeto
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
            codigo_projeto, nome, descricao, 
            empresa_tomadora_id, empresa_prestadora_id, contrato_id,
            categoria_id, gerente_id, responsavel_tomadora_id, responsavel_prestadora_id,
            data_inicio, data_fim_prevista, data_fim_real,
            endereco_execucao, cidade, estado, cep, latitude, longitude,
            requer_presenca_fisica, permite_hora_extra, permite_trabalho_feriado, permite_trabalho_fim_semana,
            horas_semanais_padrao, valor_orcado, valor_gasto, valor_previsto_restante,
            percentual_gasto, percentual_concluido, status, prioridade,
            alerta_orcamento_percentual, alerta_prazo_dias,
            notificar_estouro_orcamento, notificar_atraso_cronograma,
            observacoes, created_by, created_at
        ) VALUES (
            :codigo_projeto, :nome, :descricao,
            :empresa_tomadora_id, :empresa_prestadora_id, :contrato_id,
            :categoria_id, :gerente_id, :responsavel_tomadora_id, :responsavel_prestadora_id,
            :data_inicio, :data_fim_prevista, :data_fim_real,
            :endereco_execucao, :cidade, :estado, :cep, :latitude, :longitude,
            :requer_presenca_fisica, :permite_hora_extra, :permite_trabalho_feriado, :permite_trabalho_fim_semana,
            :horas_semanais_padrao, :valor_orcado, :valor_gasto, :valor_previsto_restante,
            :percentual_gasto, :percentual_concluido, :status, :prioridade,
            :alerta_orcamento_percentual, :alerta_prazo_dias,
            :notificar_estouro_orcamento, :notificar_atraso_cronograma,
            :observacoes, :created_by, NOW()
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar projeto
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
            nome = :nome,
            descricao = :descricao,
            categoria_id = :categoria_id,
            gerente_id = :gerente_id,
            responsavel_tomadora_id = :responsavel_tomadora_id,
            responsavel_prestadora_id = :responsavel_prestadora_id,
            data_inicio = :data_inicio,
            data_fim_prevista = :data_fim_prevista,
            endereco_execucao = :endereco_execucao,
            cidade = :cidade,
            estado = :estado,
            cep = :cep,
            latitude = :latitude,
            longitude = :longitude,
            requer_presenca_fisica = :requer_presenca_fisica,
            permite_hora_extra = :permite_hora_extra,
            permite_trabalho_feriado = :permite_trabalho_feriado,
            permite_trabalho_fim_semana = :permite_trabalho_fim_semana,
            horas_semanais_padrao = :horas_semanais_padrao,
            valor_orcado = :valor_orcado,
            prioridade = :prioridade,
            alerta_orcamento_percentual = :alerta_orcamento_percentual,
            alerta_prazo_dias = :alerta_prazo_dias,
            notificar_estouro_orcamento = :notificar_estouro_orcamento,
            notificar_atraso_cronograma = :notificar_atraso_cronograma,
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
     * Buscar etapas do projeto
     */
    public function getFases($projetoId)
    {
        $sql = "SELECT 
            pe.*,
            u.nome as responsavel_nome,
            COUNT(DISTINCT a.id) as total_atividades,
            SUM(CASE WHEN a.status = 'concluida' THEN 1 ELSE 0 END) as atividades_concluidas
        FROM projeto_etapas pe
        LEFT JOIN usuarios u ON pe.responsavel_id = u.id
        LEFT JOIN atividades a ON pe.id = a.etapa_id AND a.deleted_at IS NULL
        WHERE pe.projeto_id = :projeto_id AND pe.deleted_at IS NULL
        GROUP BY pe.id
        ORDER BY pe.ordem ASC, pe.data_inicio ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar marcos do projeto
     */
    public function getMarcos($projetoId)
    {
        $sql = "SELECT * FROM projeto_marcos 
        WHERE projeto_id = :projeto_id AND deleted_at IS NULL
        ORDER BY data_prevista ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar riscos do projeto
     */
    public function getRiscos($projetoId)
    {
        $sql = "SELECT 
            pr.*,
            u.nome as identificado_por_nome
        FROM projeto_riscos pr
        LEFT JOIN usuarios u ON pr.identificado_por = u.id
        WHERE pr.projeto_id = :projeto_id AND pr.deleted_at IS NULL
        ORDER BY (pr.probabilidade * pr.impacto) DESC, pr.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar mudanças/change requests do projeto
     */
    public function getMudancas($projetoId)
    {
        $sql = "SELECT 
            pm.*,
            u.nome as solicitante_nome,
            ua.nome as aprovador_nome
        FROM projeto_mudancas pm
        LEFT JOIN usuarios u ON pm.solicitante_id = u.id
        LEFT JOIN usuarios ua ON pm.aprovador_id = ua.id
        WHERE pm.projeto_id = :projeto_id AND pm.deleted_at IS NULL
        ORDER BY pm.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar anexos do projeto
     */
    public function getAnexos($projetoId)
    {
        $sql = "SELECT 
            pa.*,
            u.nome as enviado_por_nome
        FROM projeto_anexos pa
        LEFT JOIN usuarios u ON pa.enviado_por = u.id
        WHERE pa.projeto_id = :projeto_id AND pa.deleted_at IS NULL
        ORDER BY pa.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar histórico de alterações do projeto
     */
    public function getHistorico($projetoId)
    {
        $sql = "SELECT 
            ph.*,
            u.nome as usuario_nome
        FROM projeto_historico ph
        LEFT JOIN usuarios u ON ph.usuario_id = u.id
        WHERE ph.projeto_id = :projeto_id
        ORDER BY ph.created_at DESC
        LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar alocações/equipe do projeto
     */
    public function getAlocacoes($projetoId)
    {
        $sql = "SELECT 
            pe.*,
            u.nome as usuario_nome,
            u.email as usuario_email,
            ep.nome_fantasia as empresa_nome,
            s.nome as servico_nome
        FROM projeto_equipe pe
        LEFT JOIN usuarios u ON pe.usuario_id = u.id
        LEFT JOIN empresas_prestadoras ep ON pe.empresa_prestadora_id = ep.id
        LEFT JOIN servicos s ON pe.servico_id = s.id
        WHERE pe.projeto_id = :projeto_id AND pe.deleted_at IS NULL
        ORDER BY pe.data_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar estatísticas gerais
     */
    public function getEstatisticasGerais()
    {
        $sql = "SELECT
            COUNT(*) as total_projetos,
            SUM(CASE WHEN status = 'planejamento' THEN 1 ELSE 0 END) as em_planejamento,
            SUM(CASE WHEN status = 'orcamento' THEN 1 ELSE 0 END) as em_orcamento,
            SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) as aprovados,
            SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
            SUM(CASE WHEN status = 'pausado' THEN 1 ELSE 0 END) as pausados,
            SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
            SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
            SUM(CASE WHEN status = 'em_andamento' AND CURDATE() > data_fim_prevista THEN 1 ELSE 0 END) as atrasados,
            SUM(CASE WHEN percentual_gasto > 100 THEN 1 ELSE 0 END) as estouro_orcamento,
            SUM(valor_orcado) as valor_total,
            SUM(valor_gasto) as valor_gasto_total,
            COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 END) as novos_mes
        FROM {$this->table}
        WHERE deleted_at IS NULL";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualizar valores gastos do projeto
     */
    public function atualizarValoresGastos($projetoId)
    {
        $sql = "UPDATE {$this->table} p
        SET 
            valor_gasto = (
                SELECT COALESCE(SUM(custo_realizado), 0)
                FROM atividades
                WHERE projeto_id = p.id AND deleted_at IS NULL
            ),
            percentual_gasto = (
                SELECT CASE 
                    WHEN p.valor_orcado > 0 
                    THEN (COALESCE(SUM(custo_realizado), 0) / p.valor_orcado) * 100
                    ELSE 0
                END
                FROM atividades
                WHERE projeto_id = p.id AND deleted_at IS NULL
            ),
            valor_previsto_restante = p.valor_orcado - (
                SELECT COALESCE(SUM(custo_realizado), 0)
                FROM atividades
                WHERE projeto_id = p.id AND deleted_at IS NULL
            )
        WHERE p.id = :projeto_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Atualizar percentual concluído
     */
    public function atualizarPercentualConcluido($projetoId)
    {
        $sql = "UPDATE {$this->table} p
        SET percentual_concluido = (
            SELECT CASE
                WHEN COUNT(*) > 0
                THEN (SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) / COUNT(*)) * 100
                ELSE 0
            END
            FROM atividades
            WHERE projeto_id = p.id AND deleted_at IS NULL
        )
        WHERE p.id = :projeto_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Iniciar projeto
     */
    public function iniciar($projetoId, $usuarioId)
    {
        $sql = "UPDATE {$this->table} 
        SET status = 'em_andamento', 
            data_inicio_real = CURDATE(),
            updated_at = NOW()
        WHERE id = :id AND status = 'aprovado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $projetoId, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        if ($result) {
            $this->registrarHistorico($projetoId, $usuarioId, 'projeto_iniciado', 'status', 'aprovado', 'em_andamento');
        }
        
        return $result;
    }
    
    /**
     * Pausar projeto
     */
    public function pausar($projetoId, $motivo, $usuarioId)
    {
        $sql = "UPDATE {$this->table} 
        SET status = 'pausado',
            observacoes = CONCAT(COALESCE(observacoes, ''), '\n[PAUSADO] ', :motivo),
            updated_at = NOW()
        WHERE id = :id AND status = 'em_andamento'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $projetoId, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $motivo);
        $result = $stmt->execute();
        
        if ($result) {
            $this->registrarHistorico($projetoId, $usuarioId, 'projeto_pausado', 'status', 'em_andamento', 'pausado', $motivo);
        }
        
        return $result;
    }
    
    /**
     * Concluir projeto
     */
    public function concluir($projetoId, $avaliacao, $usuarioId)
    {
        $sql = "UPDATE {$this->table} 
        SET status = 'concluido',
            data_fim_real = CURDATE(),
            avaliacao_qualidade = :avaliacao_qualidade,
            avaliacao_prazo = :avaliacao_prazo,
            avaliacao_custo = :avaliacao_custo,
            comentario_avaliacao = :comentario,
            licoes_aprendidas = :licoes,
            updated_at = NOW()
        WHERE id = :id AND status IN ('em_andamento', 'pausado')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $projetoId, PDO::PARAM_INT);
        $stmt->bindValue(':avaliacao_qualidade', $avaliacao['qualidade']);
        $stmt->bindValue(':avaliacao_prazo', $avaliacao['prazo']);
        $stmt->bindValue(':avaliacao_custo', $avaliacao['custo']);
        $stmt->bindValue(':comentario', $avaliacao['comentario']);
        $stmt->bindValue(':licoes', $avaliacao['licoes']);
        $result = $stmt->execute();
        
        if ($result) {
            $this->registrarHistorico($projetoId, $usuarioId, 'projeto_concluido', 'status', 'em_andamento', 'concluido');
        }
        
        return $result;
    }
    
    /**
     * Cancelar projeto
     */
    public function cancelar($projetoId, $motivo, $usuarioId)
    {
        $sql = "UPDATE {$this->table} 
        SET status = 'cancelado',
            motivo_cancelamento = :motivo,
            updated_at = NOW()
        WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $projetoId, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $motivo);
        $result = $stmt->execute();
        
        if ($result) {
            $this->registrarHistorico($projetoId, $usuarioId, 'projeto_cancelado', 'status', null, 'cancelado', $motivo);
        }
        
        return $result;
    }
    
    /**
     * Registrar no histórico
     */
    private function registrarHistorico($projetoId, $usuarioId, $acao, $campo = null, $valorAnterior = null, $valorNovo = null, $observacao = null)
    {
        $sql = "INSERT INTO projeto_historico (
            projeto_id, usuario_id, acao, campo_alterado, 
            valor_anterior, valor_novo, observacao, created_at
        ) VALUES (
            :projeto_id, :usuario_id, :acao, :campo,
            :valor_anterior, :valor_novo, :observacao, NOW()
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':projeto_id', $projetoId, PDO::PARAM_INT);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':acao', $acao);
        $stmt->bindValue(':campo', $campo);
        $stmt->bindValue(':valor_anterior', $valorAnterior);
        $stmt->bindValue(':valor_novo', $valorNovo);
        $stmt->bindValue(':observacao', $observacao);
        
        return $stmt->execute();
    }
    
    /**
     * Contar total de projetos (compatibilidade com controller)
     */
    public function countTotal()
    {
        return $this->count([]);
    }
    
    /**
     * Contar projetos por status
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
     * Buscar projetos atrasados
     */
    public function getAtrasados()
    {
        $sql = "SELECT p.*
        FROM {$this->table} p
        WHERE p.deleted_at IS NULL
        AND p.status IN ('em_andamento', 'orcamento', 'aprovado')
        AND CURDATE() > p.data_fim_prevista
        ORDER BY p.data_fim_prevista ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter dados para dashboard
     */
    public function getDashboard()
    {
        $sql = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'planejamento' THEN 1 ELSE 0 END) as planejamento,
            SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
            SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluido,
            SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelado,
            SUM(CASE WHEN status IN ('em_andamento', 'aprovado') AND CURDATE() > data_fim_prevista THEN 1 ELSE 0 END) as atrasados,
            SUM(valor_orcado) as valor_total_orcado,
            SUM(valor_gasto) as valor_total_gasto,
            AVG(percentual_concluido) as media_conclusao
        FROM {$this->table}
        WHERE deleted_at IS NULL";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Alterar status do projeto (compatibilidade com controller)
     */
    public function alterarStatus($id, $novoStatus, $usuarioId = null)
    {
        $sql = "UPDATE {$this->table} 
        SET status = :status, updated_at = NOW()
        WHERE id = :id AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':status', $novoStatus);
        
        $result = $stmt->execute();
        
        if ($result && $usuarioId) {
            $this->registrarHistorico($id, $usuarioId, 'status_alterado', 'status', null, $novoStatus);
        }
        
        return $result;
    }
    
    /**
     * Validar se código do projeto é único
     */
    public function validateUniqueCodigo($codigo, $projetoIdExcluir = null)
    {
        $sql = "SELECT COUNT(*) as total 
        FROM {$this->table} 
        WHERE codigo_projeto = :codigo 
        AND deleted_at IS NULL";
        
        if ($projetoIdExcluir) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        
        if ($projetoIdExcluir) {
            $stmt->bindValue(':id', $projetoIdExcluir, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] == 0;
    }
}
