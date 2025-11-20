<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException;

/**
 * Model ServicoValor
 * Gerencia valores de serviços por período (histórico de preços)
 */
class ServicoValor {
    
    private $db;
    private $table = 'servico_valores';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buscar todos os valores
     * 
     * @param array $filtros
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function all($filtros = [], $page = 1, $limit = 25) {
        $sql = "SELECT 
                    sv.*,
                    c.numero_contrato,
                    s.nome as servico_nome,
                    et.nome_fantasia as tomadora_nome,
                    ep.nome_fantasia as prestadora_nome,
                    CASE 
                        WHEN sv.data_fim IS NULL THEN 'vigente'
                        WHEN sv.data_fim < CURDATE() THEN 'expirado'
                        WHEN sv.data_inicio > CURDATE() THEN 'futuro'
                        ELSE 'vigente'
                    END as status_periodo
                FROM {$this->table} sv
                INNER JOIN contratos c ON sv.contrato_id = c.id
                INNER JOIN servicos s ON sv.servico_id = s.id
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtro: Contrato
        if (!empty($filtros['contrato_id'])) {
            $sql .= " AND sv.contrato_id = :contrato_id";
            $params[':contrato_id'] = $filtros['contrato_id'];
        }
        
        // Filtro: Serviço
        if (!empty($filtros['servico_id'])) {
            $sql .= " AND sv.servico_id = :servico_id";
            $params[':servico_id'] = $filtros['servico_id'];
        }
        
        // Filtro: Status do período
        if (!empty($filtros['status_periodo'])) {
            if ($filtros['status_periodo'] == 'vigente') {
                $sql .= " AND sv.data_inicio <= CURDATE() AND (sv.data_fim IS NULL OR sv.data_fim >= CURDATE())";
            } elseif ($filtros['status_periodo'] == 'expirado') {
                $sql .= " AND sv.data_fim < CURDATE()";
            } elseif ($filtros['status_periodo'] == 'futuro') {
                $sql .= " AND sv.data_inicio > CURDATE()";
            }
        }
        
        // Filtro: Ativo
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $sql .= " AND sv.ativo = :ativo";
            $params[':ativo'] = $filtros['ativo'];
        }
        
        // Ordenação
        $orderBy = $filtros['order_by'] ?? 'sv.created_at';
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
     * Buscar por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT 
                    sv.*,
                    c.numero_contrato,
                    s.nome as servico_nome,
                    et.nome_fantasia as tomadora_nome,
                    ep.nome_fantasia as prestadora_nome,
                    u1.nome as criado_por_nome
                FROM {$this->table} sv
                INNER JOIN contratos c ON sv.contrato_id = c.id
                INNER JOIN servicos s ON sv.servico_id = s.id
                INNER JOIN empresas_tomadoras et ON c.empresa_tomadora_id = et.id
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                LEFT JOIN usuarios u1 ON sv.created_by = u1.id
                WHERE sv.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Buscar valores de um contrato específico
     * 
     * @param int $contratoId
     * @return array
     */
    public function getByContrato($contratoId) {
        $sql = "SELECT 
                    sv.*,
                    s.nome as servico_nome,
                    CASE 
                        WHEN sv.data_fim IS NULL THEN 'vigente'
                        WHEN sv.data_fim < CURDATE() THEN 'expirado'
                        WHEN sv.data_inicio > CURDATE() THEN 'futuro'
                        ELSE 'vigente'
                    END as status_periodo
                FROM {$this->table} sv
                INNER JOIN servicos s ON sv.servico_id = s.id
                WHERE sv.contrato_id = :contrato_id AND sv.ativo = 1
                ORDER BY s.nome ASC, sv.data_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar valor vigente para um serviço em uma data
     * 
     * @param int $contratoId
     * @param int $servicoId
     * @param string $data Data no formato Y-m-d
     * @return array|null
     */
    public function getValorVigente($contratoId, $servicoId, $data = null) {
        $data = $data ?? date('Y-m-d');
        
        $sql = "SELECT * FROM {$this->table}
                WHERE contrato_id = :contrato_id
                  AND servico_id = :servico_id
                  AND ativo = 1
                  AND data_inicio <= :data
                  AND (data_fim IS NULL OR data_fim >= :data)
                ORDER BY data_inicio DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':servico_id', $servicoId, PDO::PARAM_INT);
        $stmt->bindValue(':data', $data);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Verificar se há sobreposição de períodos
     * 
     * @param int $contratoId
     * @param int $servicoId
     * @param string $dataInicio
     * @param string|null $dataFim
     * @param int|null $exceptId
     * @return bool
     */
    public function verificarSobreposicao($contratoId, $servicoId, $dataInicio, $dataFim, $exceptId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE contrato_id = :contrato_id
                  AND servico_id = :servico_id
                  AND ativo = 1";
        
        if ($exceptId) {
            $sql .= " AND id != :except_id";
        }
        
        // Lógica de sobreposição de períodos
        if ($dataFim) {
            $sql .= " AND (
                        (data_inicio BETWEEN :data_inicio AND :data_fim)
                        OR (data_fim BETWEEN :data_inicio AND :data_fim)
                        OR (data_inicio <= :data_inicio AND (data_fim >= :data_fim OR data_fim IS NULL))
                    )";
        } else {
            // Se novo período é indeterminado (sem data fim)
            $sql .= " AND (data_fim IS NULL OR data_fim >= :data_inicio)";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':servico_id', $servicoId, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio', $dataInicio);
        
        if ($dataFim) {
            $stmt->bindValue(':data_fim', $dataFim);
        }
        
        if ($exceptId) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
    
    /**
     * Criar novo valor de serviço
     * 
     * @param array $data
     * @return int
     */
    public function create($data) {
        // Verificar sobreposição
        $sobreposicao = $this->verificarSobreposicao(
            $data['contrato_id'],
            $data['servico_id'],
            $data['data_inicio'],
            $data['data_fim'] ?? null
        );
        
        if ($sobreposicao) {
            throw new \Exception('Já existe um valor cadastrado para este serviço neste período');
        }
        
        $sql = "INSERT INTO {$this->table} (
                    contrato_id, servico_id,
                    data_inicio, data_fim,
                    tipo_remuneracao,
                    valor_base, valor_hora_extra, valor_jornada_curta,
                    valor_noturno, valor_domingo_feriado,
                    horas_mes_limite, horas_dia_limite, dias_semana_limite,
                    observacoes, ativo,
                    created_by, created_at
                ) VALUES (
                    :contrato_id, :servico_id,
                    :data_inicio, :data_fim,
                    :tipo_remuneracao,
                    :valor_base, :valor_hora_extra, :valor_jornada_curta,
                    :valor_noturno, :valor_domingo_feriado,
                    :horas_mes_limite, :horas_dia_limite, :dias_semana_limite,
                    :observacoes, :ativo,
                    :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':contrato_id', $data['contrato_id'], PDO::PARAM_INT);
        $stmt->bindValue(':servico_id', $data['servico_id'], PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio', $data['data_inicio']);
        $stmt->bindValue(':data_fim', $data['data_fim'] ?? null);
        $stmt->bindValue(':tipo_remuneracao', $data['tipo_remuneracao']);
        $stmt->bindValue(':valor_base', $data['valor_base']);
        $stmt->bindValue(':valor_hora_extra', $data['valor_hora_extra'] ?? null);
        $stmt->bindValue(':valor_jornada_curta', $data['valor_jornada_curta'] ?? null);
        $stmt->bindValue(':valor_noturno', $data['valor_noturno'] ?? null);
        $stmt->bindValue(':valor_domingo_feriado', $data['valor_domingo_feriado'] ?? null);
        $stmt->bindValue(':horas_mes_limite', $data['horas_mes_limite'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':horas_dia_limite', $data['horas_dia_limite'] ?? 12, PDO::PARAM_INT);
        $stmt->bindValue(':dias_semana_limite', $data['dias_semana_limite'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * "Atualizar" valor (na verdade, cria novo e inativa o anterior)
     * 
     * @param int $id ID do valor atual
     * @param array $data Dados do novo valor
     * @return int ID do novo valor criado
     */
    public function atualizar($id, $data) {
        $valorAtual = $this->findById($id);
        
        if (!$valorAtual) {
            throw new \Exception('Valor não encontrado');
        }
        
        // Inativar valor atual e definir data fim
        $dataFimAnterior = date('Y-m-d', strtotime($data['data_inicio'] . ' -1 day'));
        
        $sql = "UPDATE {$this->table} 
                SET data_fim = :data_fim,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':data_fim', $dataFimAnterior);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        $stmt->execute();
        
        // Criar novo valor
        $data['contrato_id'] = $valorAtual['contrato_id'];
        $data['servico_id'] = $valorAtual['servico_id'];
        
        return $this->create($data);
    }
    
    /**
     * Inativar valor
     * 
     * @param int $id
     * @return bool
     */
    public function inativar($id) {
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
     * Buscar histórico de valores de um serviço
     * 
     * @param int $contratoId
     * @param int $servicoId
     * @return array
     */
    public function getHistoricoValores($contratoId, $servicoId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE contrato_id = :contrato_id
                  AND servico_id = :servico_id
                ORDER BY data_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':servico_id', $servicoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Comparar valores (atual vs anterior)
     * 
     * @param int $contratoId
     * @param int $servicoId
     * @return array
     */
    public function compararValores($contratoId, $servicoId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE contrato_id = :contrato_id
                  AND servico_id = :servico_id
                  AND ativo = 1
                ORDER BY data_inicio DESC
                LIMIT 2";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindValue(':servico_id', $servicoId, PDO::PARAM_INT);
        $stmt->execute();
        
        $valores = $stmt->fetchAll();
        
        if (count($valores) < 2) {
            return [
                'atual' => $valores[0] ?? null,
                'anterior' => null,
                'variacao_percentual' => 0,
                'variacao_absoluta' => 0
            ];
        }
        
        $atual = $valores[0];
        $anterior = $valores[1];
        
        $variacaoAbsoluta = $atual['valor_base'] - $anterior['valor_base'];
        $variacaoPercentual = ($anterior['valor_base'] > 0) 
            ? (($variacaoAbsoluta / $anterior['valor_base']) * 100) 
            : 0;
        
        return [
            'atual' => $atual,
            'anterior' => $anterior,
            'variacao_percentual' => round($variacaoPercentual, 2),
            'variacao_absoluta' => $variacaoAbsoluta
        ];
    }
    
    /**
     * Calcular valor por período
     * 
     * @param int $id ID do serviço valor
     * @param float $quantidade Quantidade (horas, dias, etc)
     * @param array $opcoes Opções adicionais (hora_extra, noturno, etc)
     * @return float
     */
    public function calcularValor($id, $quantidade, $opcoes = []) {
        $servicoValor = $this->findById($id);
        
        if (!$servicoValor) {
            throw new \Exception('Valor de serviço não encontrado');
        }
        
        $valorTotal = 0;
        
        // Valor base
        $valorBase = $servicoValor['valor_base'];
        
        // Se tem hora extra
        if (!empty($opcoes['hora_extra']) && $servicoValor['valor_hora_extra']) {
            $horasExtras = $opcoes['horas_extras'];
            $horasNormais = $quantidade - $horasExtras;
            
            $valorTotal = ($horasNormais * $valorBase) + ($horasExtras * $servicoValor['valor_hora_extra']);
        } else {
            $valorTotal = $quantidade * $valorBase;
        }
        
        // Adicional noturno
        if (!empty($opcoes['noturno']) && $servicoValor['valor_noturno']) {
            $valorTotal += $servicoValor['valor_noturno'];
        }
        
        // Adicional domingo/feriado
        if (!empty($opcoes['domingo_feriado']) && $servicoValor['valor_domingo_feriado']) {
            $valorTotal += $servicoValor['valor_domingo_feriado'];
        }
        
        return $valorTotal;
    }
}
