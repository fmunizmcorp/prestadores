<?php

namespace App\Models;

use App\Database;
use PDO;

class Contrato {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // CRUD BÁSICO
    public function create($data) {
        $sql = "INSERT INTO contratos (
            numero_contrato, empresa_tomadora_id, empresa_prestadora_id,
            tipo_contrato, objeto_contrato, descricao, data_assinatura,
            data_inicio_vigencia, data_fim_vigencia, prazo_meses,
            renovavel, prazo_renovacao_meses, valor_total, moeda,
            forma_pagamento, condicoes_pagamento, prazo_pagamento_dias,
            dia_vencimento, periodicidade_faturamento, reajuste_previsto,
            indice_reajuste, periodicidade_reajuste_meses, percentual_multa_rescisao,
            percentual_retencao_impostos, exige_garantia, tipo_garantia,
            valor_garantia, percentual_garantia, vigencia_garantia,
            clausulas_especiais, penalidades, sla_atendimento_horas,
            permite_subcontratacao, exige_seguro, valor_seguro_minimo,
            status, observacoes, arquivo_contrato, criado_por
        ) VALUES (
            :numero_contrato, :empresa_tomadora_id, :empresa_prestadora_id,
            :tipo_contrato, :objeto_contrato, :descricao, :data_assinatura,
            :data_inicio_vigencia, :data_fim_vigencia, :prazo_meses,
            :renovavel, :prazo_renovacao_meses, :valor_total, :moeda,
            :forma_pagamento, :condicoes_pagamento, :prazo_pagamento_dias,
            :dia_vencimento, :periodicidade_faturamento, :reajuste_previsto,
            :indice_reajuste, :periodicidade_reajuste_meses, :percentual_multa_rescisao,
            :percentual_retencao_impostos, :exige_garantia, :tipo_garantia,
            :valor_garantia, :percentual_garantia, :vigencia_garantia,
            :clausulas_especiais, :penalidades, :sla_atendimento_horas,
            :permite_subcontratacao, :exige_seguro, :valor_seguro_minimo,
            :status, :observacoes, :arquivo_contrato, :criado_por
        )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT c.*, 
                et.nome_fantasia as tomadora_nome, et.cnpj as tomadora_cnpj,
                ep.nome_fantasia as prestadora_nome, ep.cnpj as prestadora_cnpj
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                LEFT JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.id = :id AND c.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function findByNumero($numeroContrato) {
        $sql = "SELECT c.*, 
                et.nome_fantasia as tomadora_nome,
                ep.nome_fantasia as prestadora_nome
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                LEFT JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.numero_contrato = :numero_contrato AND c.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['numero_contrato' => $numeroContrato]);
        return $stmt->fetch();
    }
    
    public function all($filtros = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = ["c.deleted_at IS NULL"];
        $params = [];
        
        if (!empty($filtros['status'])) {
            $where[] = "c.status = :status";
            $params['status'] = $filtros['status'];
        }
        
        if (!empty($filtros['empresa_tomadora_id'])) {
            $where[] = "c.empresa_tomadora_id = :empresa_tomadora_id";
            $params['empresa_tomadora_id'] = $filtros['empresa_tomadora_id'];
        }
        
        if (!empty($filtros['empresa_prestadora_id'])) {
            $where[] = "c.empresa_prestadora_id = :empresa_prestadora_id";
            $params['empresa_prestadora_id'] = $filtros['empresa_prestadora_id'];
        }
        
        if (!empty($filtros['tipo_contrato'])) {
            $where[] = "c.tipo_contrato = :tipo_contrato";
            $params['tipo_contrato'] = $filtros['tipo_contrato'];
        }
        
        if (!empty($filtros['vencimento_ate'])) {
            $where[] = "c.data_fim_vigencia <= :vencimento_ate";
            $params['vencimento_ate'] = $filtros['vencimento_ate'];
        }
        
        if (!empty($filtros['search'])) {
            $where[] = "(c.numero_contrato LIKE :search OR c.objeto_contrato LIKE :search OR et.nome_fantasia LIKE :search OR ep.nome_fantasia LIKE :search)";
            $params['search'] = "%{$filtros['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT c.*, 
                et.nome_fantasia as tomadora_nome,
                ep.nome_fantasia as prestadora_nome
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                LEFT JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE $whereClause 
                ORDER BY c.data_assinatura DESC 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function count($filtros = []) {
        $where = ["c.deleted_at IS NULL"];
        $params = [];
        
        if (!empty($filtros['status'])) {
            $where[] = "c.status = :status";
            $params['status'] = $filtros['status'];
        }
        
        if (!empty($filtros['empresa_tomadora_id'])) {
            $where[] = "c.empresa_tomadora_id = :empresa_tomadora_id";
            $params['empresa_tomadora_id'] = $filtros['empresa_tomadora_id'];
        }
        
        if (!empty($filtros['empresa_prestadora_id'])) {
            $where[] = "c.empresa_prestadora_id = :empresa_prestadora_id";
            $params['empresa_prestadora_id'] = $filtros['empresa_prestadora_id'];
        }
        
        if (!empty($filtros['tipo_contrato'])) {
            $where[] = "c.tipo_contrato = :tipo_contrato";
            $params['tipo_contrato'] = $filtros['tipo_contrato'];
        }
        
        if (!empty($filtros['vencimento_ate'])) {
            $where[] = "c.data_fim_vigencia <= :vencimento_ate";
            $params['vencimento_ate'] = $filtros['vencimento_ate'];
        }
        
        if (!empty($filtros['search'])) {
            $where[] = "(c.numero_contrato LIKE :search OR c.objeto_contrato LIKE :search)";
            $params['search'] = "%{$filtros['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as total 
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                LEFT JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE $whereClause";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch()['total'];
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'numero_contrato', 'empresa_tomadora_id', 'empresa_prestadora_id',
            'tipo_contrato', 'objeto_contrato', 'descricao', 'data_assinatura',
            'data_inicio_vigencia', 'data_fim_vigencia', 'prazo_meses',
            'renovavel', 'prazo_renovacao_meses', 'valor_total', 'moeda',
            'forma_pagamento', 'condicoes_pagamento', 'prazo_pagamento_dias',
            'dia_vencimento', 'periodicidade_faturamento', 'reajuste_previsto',
            'indice_reajuste', 'periodicidade_reajuste_meses', 'percentual_multa_rescisao',
            'percentual_retencao_impostos', 'exige_garantia', 'tipo_garantia',
            'valor_garantia', 'percentual_garantia', 'vigencia_garantia',
            'clausulas_especiais', 'penalidades', 'sla_atendimento_horas',
            'permite_subcontratacao', 'exige_seguro', 'valor_seguro_minimo',
            'status', 'observacoes', 'arquivo_contrato', 'atualizado_por'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE contratos SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "UPDATE contratos SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // VALIDAÇÕES
    public function validateUniqueNumero($numeroContrato, $id = null) {
        $sql = "SELECT id FROM contratos WHERE numero_contrato = :numero_contrato AND deleted_at IS NULL";
        
        if ($id) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $params = ['numero_contrato' => $numeroContrato];
        
        if ($id) {
            $params['id'] = $id;
        }
        
        $stmt->execute($params);
        return $stmt->fetch() === false;
    }
    
    // SERVIÇOS DO CONTRATO
    public function addServico($contratoId, $data) {
        $sql = "INSERT INTO contrato_servicos (
            contrato_id, servico_id, descricao_customizada, quantidade,
            unidade, valor_unitario, valor_total, periodicidade,
            data_inicio, data_fim, observacoes, criado_por
        ) VALUES (
            :contrato_id, :servico_id, :descricao_customizada, :quantidade,
            :unidade, :valor_unitario, :valor_total, :periodicidade,
            :data_inicio, :data_fim, :observacoes, :criado_por
        )";
        
        $data['contrato_id'] = $contratoId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getServicos($contratoId) {
        $sql = "SELECT cs.*, s.codigo as servico_codigo, s.nome as servico_nome
                FROM contrato_servicos cs
                LEFT JOIN servicos s ON cs.servico_id = s.id
                WHERE cs.contrato_id = :contrato_id 
                AND cs.deleted_at IS NULL
                ORDER BY cs.created_at ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['contrato_id' => $contratoId]);
        
        return $stmt->fetchAll();
    }
    
    public function updateServico($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'descricao_customizada', 'quantidade', 'unidade', 'valor_unitario',
            'valor_total', 'periodicidade', 'data_inicio', 'data_fim',
            'observacoes', 'atualizado_por'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE contrato_servicos SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteServico($id) {
        $sql = "UPDATE contrato_servicos SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // ADITIVOS
    public function addAditivo($contratoId, $data) {
        $sql = "INSERT INTO contrato_aditivos (
            contrato_id, numero_aditivo, tipo_aditivo, data_aditivo,
            descricao, justificativa, valor_anterior, valor_novo,
            data_vigencia_anterior, data_vigencia_nova, percentual_alteracao,
            aprovado_por, data_aprovacao, arquivo, observacoes, criado_por
        ) VALUES (
            :contrato_id, :numero_aditivo, :tipo_aditivo, :data_aditivo,
            :descricao, :justificativa, :valor_anterior, :valor_novo,
            :data_vigencia_anterior, :data_vigencia_nova, :percentual_alteracao,
            :aprovado_por, :data_aprovacao, :arquivo, :observacoes, :criado_por
        )";
        
        $data['contrato_id'] = $contratoId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getAditivos($contratoId) {
        $sql = "SELECT ca.*, u.nome as aprovador_nome
                FROM contrato_aditivos ca
                LEFT JOIN usuarios u ON ca.aprovado_por = u.id
                WHERE ca.contrato_id = :contrato_id 
                AND ca.deleted_at IS NULL
                ORDER BY ca.data_aditivo DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['contrato_id' => $contratoId]);
        
        return $stmt->fetchAll();
    }
    
    public function updateAditivo($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'numero_aditivo', 'tipo_aditivo', 'data_aditivo', 'descricao',
            'justificativa', 'valor_anterior', 'valor_novo', 'data_vigencia_anterior',
            'data_vigencia_nova', 'percentual_alteracao', 'aprovado_por',
            'data_aprovacao', 'arquivo', 'observacoes', 'atualizado_por'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE contrato_aditivos SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteAditivo($id) {
        $sql = "UPDATE contrato_aditivos SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // HISTÓRICO
    public function addHistorico($contratoId, $data) {
        $sql = "INSERT INTO contrato_historico (
            contrato_id, tipo_evento, descricao, usuario_id,
            data_evento, detalhes_json
        ) VALUES (
            :contrato_id, :tipo_evento, :descricao, :usuario_id,
            :data_evento, :detalhes_json
        )";
        
        $data['contrato_id'] = $contratoId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getHistorico($contratoId, $limit = 50) {
        $sql = "SELECT ch.*, u.nome as usuario_nome
                FROM contrato_historico ch
                LEFT JOIN usuarios u ON ch.usuario_id = u.id
                WHERE ch.contrato_id = :contrato_id
                ORDER BY ch.data_evento DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // VALORES POR PERÍODO
    public function addValorPeriodo($contratoId, $data) {
        $sql = "INSERT INTO contrato_valores_periodo (
            contrato_id, mes_ano, valor_previsto, valor_realizado,
            valor_pago, data_pagamento, numero_nf, status_periodo,
            observacoes, criado_por
        ) VALUES (
            :contrato_id, :mes_ano, :valor_previsto, :valor_realizado,
            :valor_pago, :data_pagamento, :numero_nf, :status_periodo,
            :observacoes, :criado_por
        )";
        
        $data['contrato_id'] = $contratoId;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
    
    public function getValoresPeriodo($contratoId, $anoInicio = null, $anoFim = null) {
        $sql = "SELECT * FROM contrato_valores_periodo
                WHERE contrato_id = :contrato_id 
                AND deleted_at IS NULL";
        
        $params = ['contrato_id' => $contratoId];
        
        if ($anoInicio) {
            $sql .= " AND YEAR(mes_ano) >= :ano_inicio";
            $params['ano_inicio'] = $anoInicio;
        }
        
        if ($anoFim) {
            $sql .= " AND YEAR(mes_ano) <= :ano_fim";
            $params['ano_fim'] = $anoFim;
        }
        
        $sql .= " ORDER BY mes_ano ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function updateValorPeriodo($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = [
            'valor_previsto', 'valor_realizado', 'valor_pago',
            'data_pagamento', 'numero_nf', 'status_periodo',
            'observacoes', 'atualizado_por'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE contrato_valores_periodo SET $fieldsStr WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    // ESTATÍSTICAS
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM contratos WHERE deleted_at IS NULL";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch()['total'];
    }
    
    public function countPorStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM contratos 
                WHERE status = :status AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['status' => $status]);
        return $stmt->fetch()['total'];
    }
    
    public function getAtivos() {
        $sql = "SELECT c.*, 
                et.nome_fantasia as tomadora_nome,
                ep.nome_fantasia as prestadora_nome
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                LEFT JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.status = 'Vigente' 
                AND c.deleted_at IS NULL
                ORDER BY c.data_fim_vigencia ASC";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getVencendo($dias = 90) {
        $sql = "SELECT c.*, 
                et.nome_fantasia as tomadora_nome,
                ep.nome_fantasia as prestadora_nome,
                DATEDIFF(c.data_fim_vigencia, CURDATE()) as dias_restantes
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                LEFT JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.status = 'Vigente' 
                AND c.deleted_at IS NULL
                AND DATEDIFF(c.data_fim_vigencia, CURDATE()) <= :dias
                AND DATEDIFF(c.data_fim_vigencia, CURDATE()) >= 0
                ORDER BY c.data_fim_vigencia ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['dias' => $dias]);
        return $stmt->fetchAll();
    }
    
    public function getValorTotalAtivos() {
        $sql = "SELECT SUM(valor_total) as total 
                FROM contratos 
                WHERE status = 'Vigente' 
                AND deleted_at IS NULL";
        
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getPorTipo() {
        $sql = "SELECT tipo_contrato, COUNT(*) as total 
                FROM contratos 
                WHERE deleted_at IS NULL 
                GROUP BY tipo_contrato
                ORDER BY total DESC";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getPorEmpresaTomadora($empresaId = null) {
        $sql = "SELECT et.nome_fantasia, COUNT(c.id) as total, SUM(c.valor_total) as valor_total
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                WHERE c.deleted_at IS NULL";
        
        $params = [];
        if ($empresaId) {
            $sql .= " AND c.empresa_tomadora_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " GROUP BY c.empresa_tomadora_id ORDER BY total DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getPorEmpresaPrestadora($empresaId = null) {
        $sql = "SELECT ep.nome_fantasia, COUNT(c.id) as total, SUM(c.valor_total) as valor_total
                FROM contratos c
                LEFT JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.deleted_at IS NULL";
        
        $params = [];
        if ($empresaId) {
            $sql .= " AND c.empresa_prestadora_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        
        $sql .= " GROUP BY c.empresa_prestadora_id ORDER BY total DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function getMediaValor($empresaTomadoraId = null) {
        $sql = "SELECT AVG(valor_total) as media 
                FROM contratos 
                WHERE deleted_at IS NULL";
        
        $params = [];
        if ($empresaTomadoraId) {
            $sql .= " AND empresa_tomadora_id = :empresa_id";
            $params['empresa_id'] = $empresaTomadoraId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['media'] ?? 0;
    }
}
