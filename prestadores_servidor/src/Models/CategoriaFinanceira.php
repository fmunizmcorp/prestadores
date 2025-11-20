<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * CategoriaFinanceira Model
 * 
 * Gerencia categorias financeiras hierárquicas para classificação de lançamentos
 * 
 * Funcionalidades:
 * - CRUD completo de categorias
 * - Hierarquia multinível (pai/filho)
 * - Tipos: receita, despesa, transferencia
 * - Naturezas: operacional, financeira, investimento, outra
 * - Sistema de caminho completo para navegação
 * - Validação de ciclos na hierarquia
 * - Contadores de uso
 * 
 * @package App\Models
 * @author Clinfec Prestadores
 * @since Sprint 7
 */
class CategoriaFinanceira
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Cria nova categoria financeira
     * 
     * @param array $data Dados da categoria
     * @return int|false ID da categoria criada ou false em caso de erro
     */
    public function create(array $data)
    {
        try {
            // Validar código único
            if ($this->codigoExists($data['codigo'])) {
                throw new \Exception("Código {$data['codigo']} já está em uso.");
            }
            
            // Calcular nível e caminho
            $nivel = 1;
            $caminho = $data['codigo'];
            
            if (!empty($data['pai_id'])) {
                $pai = $this->findById($data['pai_id']);
                if (!$pai) {
                    throw new \Exception("Categoria pai não encontrada.");
                }
                $nivel = $pai['nivel'] + 1;
                $caminho = $pai['caminho'] . '.' . $data['codigo'];
            }
            
            $sql = "INSERT INTO categorias_financeiras (
                        codigo, nome, descricao, pai_id, nivel, caminho,
                        tipo, natureza, centro_custo_id, aceita_lancamento,
                        criado_por, ativo
                    ) VALUES (
                        :codigo, :nome, :descricao, :pai_id, :nivel, :caminho,
                        :tipo, :natureza, :centro_custo_id, :aceita_lancamento,
                        :criado_por, TRUE
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'codigo' => $data['codigo'],
                'nome' => $data['nome'],
                'descricao' => $data['descricao'] ?? null,
                'pai_id' => $data['pai_id'] ?? null,
                'nivel' => $nivel,
                'caminho' => $caminho,
                'tipo' => $data['tipo'],
                'natureza' => $data['natureza'] ?? 'operacional',
                'centro_custo_id' => $data['centro_custo_id'] ?? null,
                'aceita_lancamento' => $data['aceita_lancamento'] ?? true,
                'criado_por' => $data['criado_por'] ?? $_SESSION['user_id'] ?? null
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (\Exception $e) {
            error_log("Erro ao criar categoria financeira: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca categoria por ID
     * 
     * @param int $id ID da categoria
     * @return array|false Dados da categoria ou false se não encontrada
     */
    public function findById($id)
    {
        $sql = "SELECT cf.*, 
                       cp.nome as pai_nome,
                       cc.nome as centro_custo_nome
                FROM categorias_financeiras cf
                LEFT JOIN categorias_financeiras cp ON cf.pai_id = cp.id
                LEFT JOIN centros_custo cc ON cf.centro_custo_id = cc.id
                WHERE cf.id = :id AND cf.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca categoria por código
     * 
     * @param string $codigo Código da categoria
     * @return array|false
     */
    public function findByCodigo($codigo)
    {
        $sql = "SELECT * FROM categorias_financeiras 
                WHERE codigo = :codigo AND ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['codigo' => $codigo]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista todas as categorias com filtros
     * 
     * @param array $filtros Filtros opcionais
     * @param int $page Página atual
     * @param int $limit Itens por página
     * @return array
     */
    public function all(array $filtros = [], $page = 1, $limit = 50)
    {
        $offset = ($page - 1) * $limit;
        $where = ['cf.ativo = TRUE'];
        $params = [];
        
        // Filtros
        if (!empty($filtros['tipo'])) {
            $where[] = 'cf.tipo = :tipo';
            $params['tipo'] = $filtros['tipo'];
        }
        
        if (!empty($filtros['natureza'])) {
            $where[] = 'cf.natureza = :natureza';
            $params['natureza'] = $filtros['natureza'];
        }
        
        if (!empty($filtros['pai_id'])) {
            $where[] = 'cf.pai_id = :pai_id';
            $params['pai_id'] = $filtros['pai_id'];
        }
        
        if (isset($filtros['aceita_lancamento'])) {
            $where[] = 'cf.aceita_lancamento = :aceita_lancamento';
            $params['aceita_lancamento'] = $filtros['aceita_lancamento'] ? 1 : 0;
        }
        
        if (!empty($filtros['busca'])) {
            $where[] = '(cf.codigo LIKE :busca OR cf.nome LIKE :busca OR cf.descricao LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Total de registros
        $sqlCount = "SELECT COUNT(*) as total FROM categorias_financeiras cf WHERE $whereClause";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Buscar dados
        $sql = "SELECT cf.*, 
                       cp.nome as pai_nome,
                       cc.nome as centro_custo_nome,
                       (SELECT COUNT(*) FROM lancamentos_financeiros 
                        WHERE categoria_id = cf.id AND ativo = TRUE) as total_lancamentos
                FROM categorias_financeiras cf
                LEFT JOIN categorias_financeiras cp ON cf.pai_id = cp.id
                LEFT JOIN centros_custo cc ON cf.centro_custo_id = cc.id
                WHERE $whereClause
                ORDER BY cf.caminho, cf.codigo
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $data,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
            'per_page' => $limit
        ];
    }
    
    /**
     * Busca categorias raiz (nível 1)
     * 
     * @return array
     */
    public function getRaizes()
    {
        $sql = "SELECT * FROM categorias_financeiras 
                WHERE pai_id IS NULL AND ativo = TRUE 
                ORDER BY codigo";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca subcategorias de uma categoria
     * 
     * @param int $paiId ID da categoria pai
     * @return array
     */
    public function getFilhas($paiId)
    {
        $sql = "SELECT * FROM categorias_financeiras 
                WHERE pai_id = :pai_id AND ativo = TRUE 
                ORDER BY codigo";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pai_id' => $paiId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Monta árvore hierárquica completa de categorias
     * 
     * @param string|null $tipo Filtrar por tipo (receita, despesa, transferencia)
     * @return array
     */
    public function getTree($tipo = null)
    {
        $where = 'ativo = TRUE';
        $params = [];
        
        if ($tipo) {
            $where .= ' AND tipo = :tipo';
            $params['tipo'] = $tipo;
        }
        
        $sql = "SELECT * FROM categorias_financeiras 
                WHERE $where
                ORDER BY caminho, codigo";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->buildTree($categorias);
    }
    
    /**
     * Constrói árvore hierárquica a partir de lista plana
     * 
     * @param array $elementos Lista de categorias
     * @param int|null $paiId ID do pai para começar
     * @return array
     */
    private function buildTree(array $elementos, $paiId = null)
    {
        $branch = [];
        
        foreach ($elementos as $elemento) {
            if ($elemento['pai_id'] == $paiId) {
                $children = $this->buildTree($elementos, $elemento['id']);
                if ($children) {
                    $elemento['filhas'] = $children;
                }
                $branch[] = $elemento;
            }
        }
        
        return $branch;
    }
    
    /**
     * Atualiza categoria
     * 
     * @param int $id ID da categoria
     * @param array $data Dados a atualizar
     * @return bool
     */
    public function update($id, array $data)
    {
        try {
            $categoria = $this->findById($id);
            if (!$categoria) {
                throw new \Exception("Categoria não encontrada.");
            }
            
            // Validar código único (se mudou)
            if (isset($data['codigo']) && $data['codigo'] != $categoria['codigo']) {
                if ($this->codigoExists($data['codigo'], $id)) {
                    throw new \Exception("Código {$data['codigo']} já está em uso.");
                }
            }
            
            // Validar mudança de pai (não criar ciclos)
            if (isset($data['pai_id']) && $data['pai_id'] != $categoria['pai_id']) {
                if ($this->criaCiclo($id, $data['pai_id'])) {
                    throw new \Exception("Alteração criaria um ciclo na hierarquia.");
                }
            }
            
            $fields = [];
            $params = ['id' => $id];
            
            $allowedFields = ['codigo', 'nome', 'descricao', 'pai_id', 'tipo', 'natureza', 
                            'centro_custo_id', 'aceita_lancamento'];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return true;
            }
            
            $sql = "UPDATE categorias_financeiras SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            // Recalcular caminho se mudou pai ou código
            if (isset($data['pai_id']) || isset($data['codigo'])) {
                $this->recalcularCaminho($id);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Erro ao atualizar categoria: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Soft delete de categoria
     * 
     * @param int $id ID da categoria
     * @return bool
     */
    public function delete($id)
    {
        try {
            // Verificar se tem subcategorias ativas
            $filhas = $this->getFilhas($id);
            if (!empty($filhas)) {
                throw new \Exception("Não é possível excluir categoria com subcategorias ativas.");
            }
            
            // Verificar se tem lançamentos
            $sqlCheck = "SELECT COUNT(*) as total FROM lancamentos_financeiros 
                         WHERE categoria_id = :id AND ativo = TRUE";
            $stmt = $this->db->prepare($sqlCheck);
            $stmt->execute(['id' => $id]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($total > 0) {
                throw new \Exception("Não é possível excluir categoria com lançamentos associados.");
            }
            
            $sql = "UPDATE categorias_financeiras SET ativo = FALSE WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute(['id' => $id]);
            
        } catch (\Exception $e) {
            error_log("Erro ao excluir categoria: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se código já existe
     * 
     * @param string $codigo Código a verificar
     * @param int|null $excludeId ID para excluir da verificação (para updates)
     * @return bool
     */
    private function codigoExists($codigo, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM categorias_financeiras 
                WHERE codigo = :codigo AND ativo = TRUE";
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = ['codigo' => $codigo];
        if ($excludeId) {
            $params['exclude_id'] = $excludeId;
        }
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }
    
    /**
     * Verifica se mudança de pai criaria ciclo na hierarquia
     * 
     * @param int $categoriaId ID da categoria sendo movida
     * @param int|null $novoPaiId Novo pai
     * @return bool True se criaria ciclo
     */
    private function criaCiclo($categoriaId, $novoPaiId)
    {
        if (!$novoPaiId) {
            return false; // Movendo para raiz, sem ciclo
        }
        
        // Verificar se o novo pai é descendente da categoria
        $sql = "SELECT * FROM categorias_financeiras 
                WHERE id = :pai_id AND caminho LIKE :caminho AND ativo = TRUE";
        
        $categoria = $this->findById($categoriaId);
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'pai_id' => $novoPaiId,
            'caminho' => $categoria['caminho'] . '%'
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Recalcula caminho da categoria e todas suas filhas
     * 
     * @param int $categoriaId ID da categoria
     * @return void
     */
    private function recalcularCaminho($categoriaId)
    {
        $categoria = $this->findById($categoriaId);
        
        // Calcular novo caminho
        $novoCaminho = $categoria['codigo'];
        $novoNivel = 1;
        
        if ($categoria['pai_id']) {
            $pai = $this->findById($categoria['pai_id']);
            $novoCaminho = $pai['caminho'] . '.' . $categoria['codigo'];
            $novoNivel = $pai['nivel'] + 1;
        }
        
        // Atualizar categoria
        $sql = "UPDATE categorias_financeiras 
                SET caminho = :caminho, nivel = :nivel 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caminho' => $novoCaminho,
            'nivel' => $novoNivel,
            'id' => $categoriaId
        ]);
        
        // Recalcular filhas recursivamente
        $filhas = $this->getFilhas($categoriaId);
        foreach ($filhas as $filha) {
            $this->recalcularCaminho($filha['id']);
        }
    }
    
    /**
     * Busca categorias que aceitam lançamento
     * 
     * @param string|null $tipo Filtrar por tipo
     * @return array
     */
    public function getQueAceitamLancamento($tipo = null)
    {
        $where = 'aceita_lancamento = TRUE AND ativo = TRUE';
        $params = [];
        
        if ($tipo) {
            $where .= ' AND tipo = :tipo';
            $params['tipo'] = $tipo;
        }
        
        $sql = "SELECT id, codigo, nome, caminho, tipo, natureza 
                FROM categorias_financeiras 
                WHERE $where
                ORDER BY caminho, codigo";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Estatísticas de uso da categoria
     * 
     * @param int $id ID da categoria
     * @return array
     */
    public function getEstatisticas($id)
    {
        $sql = "SELECT 
                    COUNT(lf.id) as total_lancamentos,
                    SUM(lf.valor) as total_valor,
                    COUNT(DISTINCT lf.projeto_id) as total_projetos,
                    COUNT(DISTINCT lf.contrato_id) as total_contratos,
                    MIN(lf.data_lancamento) as primeiro_lancamento,
                    MAX(lf.data_lancamento) as ultimo_lancamento
                FROM lancamentos_financeiros lf
                WHERE lf.categoria_id = :id AND lf.ativo = TRUE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
