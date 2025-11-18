<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * CentroCusto Model
 * 
 * Gerencia centros de custo para classificação de despesas e receitas
 * 
 * Funcionalidades:
 * - CRUD completo de centros de custo
 * - Centros pré-populados (6 padrões)
 * - Estatísticas de uso
 * - Relatórios por centro
 * - Ativação/desativação
 * 
 * Centros Padrão:
 * 1. Administrativo
 * 2. Comercial
 * 3. Operacional
 * 4. Financeiro
 * 5. TI/Tecnologia
 * 6. Recursos Humanos
 * 
 * @package App\Models
 * @author Clinfec Prestadores
 * @since Sprint 7
 */
class CentroCusto
{
    private $db;
    private $table = 'centros_custo';
    
    /**
     * Construtor
     */
    public function __construct()
    {
        // SPRINT 73 FIX: Usar Database singleton ao invés de global $db (Bug #23)
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria novo centro de custo
     * 
     * @param array $data Dados do centro de custo
     * @return int|false ID do centro criado ou false em caso de erro
     */
    public function create(array $data)
    {
        try {
            // Validar código único
            if ($this->codigoExists($data['codigo'])) {
                throw new \Exception("Código {$data['codigo']} já está em uso.");
            }
            
            $sql = "INSERT INTO {$this->table} (
                        codigo, nome, descricao, responsavel, 
                        departamento, orcamento_mensal, ativo,
                        criado_por, criado_em
                    ) VALUES (
                        :codigo, :nome, :descricao, :responsavel,
                        :departamento, :orcamento_mensal, TRUE,
                        :criado_por, NOW()
                    )";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                'codigo' => $data['codigo'],
                'nome' => $data['nome'],
                'descricao' => $data['descricao'] ?? null,
                'responsavel' => $data['responsavel'] ?? null,
                'departamento' => $data['departamento'] ?? null,
                'orcamento_mensal' => $data['orcamento_mensal'] ?? null,
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ];
            
            if ($stmt->execute($params)) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("Erro ao criar centro de custo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca centro de custo por ID
     * 
     * @param int $id ID do centro de custo
     * @return array|false Dados do centro ou false se não encontrado
     */
    public function findById($id)
    {
        try {
            $sql = "SELECT cc.*,
                           u.nome as criado_por_nome,
                           (SELECT COUNT(*) FROM contas_pagar WHERE centro_custo_id = cc.id) as total_contas_pagar,
                           (SELECT COUNT(*) FROM contas_receber WHERE centro_custo_id = cc.id) as total_contas_receber,
                           (SELECT COUNT(*) FROM categorias_financeiras WHERE centro_custo_id = cc.id) as total_categorias
                    FROM {$this->table} cc
                    LEFT JOIN usuarios u ON cc.criado_por = u.id
                    WHERE cc.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar centro de custo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca centro de custo por código
     * 
     * @param string $codigo Código do centro
     * @return array|false Dados do centro ou false
     */
    public function findByCodigo($codigo)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE codigo = :codigo";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['codigo' => $codigo]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar centro de custo por código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todos os centros de custo com filtros
     * 
     * @param array $filtros Filtros opcionais
     * @param int $page Página atual
     * @param int $limit Registros por página
     * @return array Lista de centros
     */
    public function all(array $filtros = [], $page = 1, $limit = 50)
    {
        try {
            $where = ['1=1'];
            $params = [];
            
            if (!empty($filtros['ativo'])) {
                $where[] = 'cc.ativo = :ativo';
                $params['ativo'] = $filtros['ativo'];
            }
            
            if (!empty($filtros['departamento'])) {
                $where[] = 'cc.departamento = :departamento';
                $params['departamento'] = $filtros['departamento'];
            }
            
            if (!empty($filtros['busca'])) {
                $where[] = '(cc.codigo LIKE :busca OR cc.nome LIKE :busca OR cc.descricao LIKE :busca)';
                $params['busca'] = '%' . $filtros['busca'] . '%';
            }
            
            $whereClause = implode(' AND ', $where);
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT cc.*,
                           (SELECT COUNT(*) FROM contas_pagar WHERE centro_custo_id = cc.id) as total_contas_pagar,
                           (SELECT COUNT(*) FROM contas_receber WHERE centro_custo_id = cc.id) as total_contas_receber,
                           (SELECT SUM(valor_original) FROM contas_pagar WHERE centro_custo_id = cc.id AND status != 'cancelada') as total_despesas,
                           (SELECT SUM(valor_original) FROM contas_receber WHERE centro_custo_id = cc.id AND status != 'cancelada') as total_receitas
                    FROM {$this->table} cc
                    WHERE {$whereClause}
                    ORDER BY cc.codigo ASC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Erro ao listar centros de custo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta total de centros de custo
     * 
     * @param array $filtros Filtros opcionais
     * @return int Total de registros
     */
    public function count(array $filtros = [])
    {
        try {
            $where = ['1=1'];
            $params = [];
            
            if (!empty($filtros['ativo'])) {
                $where[] = 'ativo = :ativo';
                $params['ativo'] = $filtros['ativo'];
            }
            
            if (!empty($filtros['departamento'])) {
                $where[] = 'departamento = :departamento';
                $params['departamento'] = $filtros['departamento'];
            }
            
            if (!empty($filtros['busca'])) {
                $where[] = '(codigo LIKE :busca OR nome LIKE :busca OR descricao LIKE :busca)';
                $params['busca'] = '%' . $filtros['busca'] . '%';
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
            
        } catch (\Exception $e) {
            error_log("Erro ao contar centros de custo: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Atualiza centro de custo
     * 
     * @param int $id ID do centro
     * @param array $data Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function update($id, array $data)
    {
        try {
            // Validar código único (exceto próprio registro)
            if (isset($data['codigo']) && $this->codigoExists($data['codigo'], $id)) {
                throw new \Exception("Código {$data['codigo']} já está em uso.");
            }
            
            $fields = [];
            $params = ['id' => $id];
            
            $allowedFields = ['codigo', 'nome', 'descricao', 'responsavel', 
                             'departamento', 'orcamento_mensal', 'ativo'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[$field] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $fields[] = "atualizado_em = NOW()";
            
            if (isset($data['atualizado_por'])) {
                $fields[] = "atualizado_por = :atualizado_por";
                $params['atualizado_por'] = $data['atualizado_por'];
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar centro de custo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desativa centro de custo (soft delete)
     * 
     * @param int $id ID do centro
     * @return bool Sucesso da operação
     */
    public function delete($id)
    {
        try {
            // Verificar se está em uso
            $centro = $this->findById($id);
            
            if ($centro['total_contas_pagar'] > 0 || $centro['total_contas_receber'] > 0) {
                throw new \Exception("Centro de custo está em uso e não pode ser excluído. Desative-o em vez disso.");
            }
            
            $sql = "UPDATE {$this->table} SET ativo = FALSE, atualizado_em = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute(['id' => $id]);
            
        } catch (\Exception $e) {
            error_log("Erro ao deletar centro de custo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista apenas centros de custo ativos
     * 
     * @return array Lista de centros ativos
     */
    public function getAtivos()
    {
        return $this->all(['ativo' => 1], 1, 1000);
    }
    
    /**
     * Verifica se código já existe
     * 
     * @param string $codigo Código a verificar
     * @param int|null $excludeId ID para excluir da verificação
     * @return bool True se existe
     */
    public function codigoExists($codigo, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE codigo = :codigo";
            
            if ($excludeId) {
                $sql .= " AND id != :id";
            }
            
            $stmt = $this->db->prepare($sql);
            $params = ['codigo' => $codigo];
            
            if ($excludeId) {
                $params['id'] = $excludeId;
            }
            
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] > 0;
            
        } catch (\Exception $e) {
            error_log("Erro ao verificar código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém estatísticas de uso do centro de custo
     * 
     * @param int $id ID do centro
     * @param string $dataInicio Data inicial
     * @param string $dataFim Data final
     * @return array Estatísticas
     */
    public function getEstatisticas($id, $dataInicio = null, $dataFim = null)
    {
        try {
            $params = ['id' => $id];
            $wherePeriodo = '';
            
            if ($dataInicio && $dataFim) {
                $wherePeriodo = " AND data_vencimento BETWEEN :data_inicio AND :data_fim";
                $params['data_inicio'] = $dataInicio;
                $params['data_fim'] = $dataFim;
            }
            
            // Total de despesas
            $sql = "SELECT 
                        COUNT(*) as total_contas,
                        SUM(valor_original) as total_valor,
                        SUM(valor_pago) as total_pago,
                        SUM(valor_original - valor_pago) as total_pendente
                    FROM contas_pagar 
                    WHERE centro_custo_id = :id AND status != 'cancelada' {$wherePeriodo}";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $despesas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Total de receitas
            $sql = "SELECT 
                        COUNT(*) as total_contas,
                        SUM(valor_original) as total_valor,
                        SUM(valor_recebido) as total_recebido,
                        SUM(valor_original - valor_recebido) as total_pendente
                    FROM contas_receber 
                    WHERE centro_custo_id = :id AND status != 'cancelada' {$wherePeriodo}";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $receitas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Saldo
            $saldo = ($receitas['total_recebido'] ?? 0) - ($despesas['total_pago'] ?? 0);
            
            return [
                'despesas' => $despesas,
                'receitas' => $receitas,
                'saldo' => $saldo,
                'resultado' => ($receitas['total_valor'] ?? 0) - ($despesas['total_valor'] ?? 0)
            ];
            
        } catch (\Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém relatório comparativo de centros de custo
     * 
     * @param string $dataInicio Data inicial
     * @param string $dataFim Data final
     * @return array Relatório comparativo
     */
    public function getRelatorioComparativo($dataInicio, $dataFim)
    {
        try {
            $sql = "SELECT 
                        cc.id,
                        cc.codigo,
                        cc.nome,
                        cc.orcamento_mensal,
                        COALESCE(SUM(CASE WHEN cp.id IS NOT NULL THEN cp.valor_original ELSE 0 END), 0) as total_despesas,
                        COALESCE(SUM(CASE WHEN cr.id IS NOT NULL THEN cr.valor_original ELSE 0 END), 0) as total_receitas,
                        COALESCE(SUM(CASE WHEN cp.id IS NOT NULL THEN cp.valor_pago ELSE 0 END), 0) as total_despesas_pagas,
                        COALESCE(SUM(CASE WHEN cr.id IS NOT NULL THEN cr.valor_recebido ELSE 0 END), 0) as total_receitas_recebidas
                    FROM {$this->table} cc
                    LEFT JOIN contas_pagar cp ON cp.centro_custo_id = cc.id 
                        AND cp.data_vencimento BETWEEN :data_inicio AND :data_fim
                        AND cp.status != 'cancelada'
                    LEFT JOIN contas_receber cr ON cr.centro_custo_id = cc.id 
                        AND cr.data_vencimento BETWEEN :data_inicio AND :data_fim
                        AND cr.status != 'cancelada'
                    WHERE cc.ativo = TRUE
                    GROUP BY cc.id, cc.codigo, cc.nome, cc.orcamento_mensal
                    ORDER BY total_despesas DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Erro ao obter relatório comparativo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém lista de departamentos distintos
     * 
     * @return array Lista de departamentos
     */
    public function getDepartamentos()
    {
        try {
            $sql = "SELECT DISTINCT departamento 
                    FROM {$this->table} 
                    WHERE departamento IS NOT NULL AND departamento != '' 
                    ORDER BY departamento";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (\Exception $e) {
            error_log("Erro ao obter departamentos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Popula centros de custo padrão
     * 
     * @return bool Sucesso da operação
     */
    public function popularCentrosPadrao()
    {
        $centrosPadrao = [
            [
                'codigo' => 'ADM',
                'nome' => 'Administrativo',
                'descricao' => 'Despesas administrativas gerais da empresa',
                'departamento' => 'Administração',
                'orcamento_mensal' => 10000.00
            ],
            [
                'codigo' => 'COM',
                'nome' => 'Comercial',
                'descricao' => 'Despesas com vendas e marketing',
                'departamento' => 'Comercial',
                'orcamento_mensal' => 15000.00
            ],
            [
                'codigo' => 'OPE',
                'nome' => 'Operacional',
                'descricao' => 'Despesas operacionais e de produção',
                'departamento' => 'Operações',
                'orcamento_mensal' => 50000.00
            ],
            [
                'codigo' => 'FIN',
                'nome' => 'Financeiro',
                'descricao' => 'Despesas financeiras (juros, taxas bancárias, etc)',
                'departamento' => 'Financeiro',
                'orcamento_mensal' => 5000.00
            ],
            [
                'codigo' => 'TI',
                'nome' => 'TI/Tecnologia',
                'descricao' => 'Despesas com tecnologia e infraestrutura',
                'departamento' => 'Tecnologia',
                'orcamento_mensal' => 20000.00
            ],
            [
                'codigo' => 'RH',
                'nome' => 'Recursos Humanos',
                'descricao' => 'Despesas com folha de pagamento e benefícios',
                'departamento' => 'RH',
                'orcamento_mensal' => 100000.00
            ]
        ];
        
        try {
            foreach ($centrosPadrao as $centro) {
                // Verificar se já existe
                if (!$this->codigoExists($centro['codigo'])) {
                    $this->create($centro);
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Erro ao popular centros padrão: " . $e->getMessage());
            return false;
        }
    }
}
